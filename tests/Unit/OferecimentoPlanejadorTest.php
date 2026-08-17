<?php

namespace GDE\Tests\Unit;

use GDE\Curso;
use GDE\Dimensao;
use GDE\Disciplina;
use GDE\Oferecimento;
use GDE\OferecimentoReserva;
use GDE\Professor;
use GDE\Sala;
use GDE\Usuario;
use PHPUnit\Framework\TestCase;

/**
 * Pergunta de avaliacao que devolve uma media fixa, sem tocar no banco
 *
 * Nao estende AvaliacaoPergunta de proposito: uma subclasse de entidade nao
 * tem metadata no Doctrine. Dados_Professores so chama getMedia().
 */
class PerguntaFake {

	public $media = array('v' => 0, 'w' => 0);
	public $chamadas = 0;

	public function getMedia($id_professor = null, $id_disciplina = null, $cache = true) {
		$this->chamadas++;
		return $this->media;
	}
}

class OferecimentoPlanejadorTest extends TestCase {

	private function oferecimento($sigla = 'MC102', $turma = 'A', $vagas = 40, $fechado = false) {
		$D = new Disciplina();
		$D->setId_Disciplina(99);
		$D->setSigla($sigla);

		$Sala = new Sala();
		$Sala->setNome('CB01');
		$Dim = new Dimensao();
		$Dim->setDia(2);
		$Dim->setHorario(8);
		$Dim->setSala($Sala);

		$O = new Oferecimento();
		$O->setId_Oferecimento(77);
		$O->setDisciplina($D);
		$O->setTurma($turma);
		$O->setVagas($vagas);
		$O->setFechado($fechado);
		$O->setDimensoes(array($Dim));
		return $O;
	}

	private function professor($id, $nome) {
		$P = new Professor();
		$P->setId_Professor($id);
		$P->setNome($nome);
		return $P;
	}

	// $medias: 4 pares [v, w], na ordem em que o planejador usa as perguntas
	private function perguntas(array $medias) {
		$Perguntas = array();
		foreach($medias as $m) {
			$P = new PerguntaFake();
			$P->media = array('v' => $m[0], 'w' => $m[1]);
			$Perguntas[] = $P;
		}
		return $Perguntas;
	}

	public function testDadosPlanejador() {
		$O = $this->oferecimento();
		$dados = $O->Dados_Planejador(array(), '#FF0000');

		$this->assertSame(77, $dados['id']);
		$this->assertSame('A', $dados['turma']);
		$this->assertSame(40, $dados['vagas']);
		$this->assertFalse($dados['fechado']);
		$this->assertTrue($dados['possivel']);
		$this->assertFalse($dados['adicionado']);
		$this->assertSame('MC102 A (40)', $dados['link']);
		$this->assertSame('#FF0000', $dados['eventSources']['color']);
		$this->assertFalse($dados['eventSources']['editable']);
		$this->assertSame(array(), $dados['Amigos']);
		$this->assertSame(-1, $dados['total']);
	}

	public function testDadosPlanejadorFechadoNaoEhPossivel() {
		$dados = $this->oferecimento('MC102', 'A', 0, true)->Dados_Planejador(array(), '#FF0000');
		$this->assertTrue($dados['fechado']);
		$this->assertFalse($dados['possivel']);
	}

	public function testDadosPlanejadorSemUsuarioNaoChecaReserva() {
		$dados = $this->oferecimento()->Dados_Planejador(array(), '#FF0000');
		$this->assertArrayNotHasKey('viola_reserva', $dados);
	}

	public function testDadosPlanejadorComUsuarioChecaReserva() {
		$O = $this->oferecimento();
		$Curso = new Curso();
		$Curso->setId_Curso(34);
		$Reserva = new OferecimentoReserva();
		$Reserva->setCurso($Curso);
		$O->setReservas(array($Reserva));

		$U = new Usuario();
		$dados = $O->Dados_Planejador(array(), '#FF0000', false, $U);
		$this->assertArrayHasKey('viola_reserva', $dados);
		$this->assertTrue($dados['viola_reserva']);
	}

	// Quirk pinado: o planejador inteiro devolve as vagas como inteiro e a
	// consulta de uma disciplina so as devolve como string (getVagas(true)
	// passa por htmlspecialchars)
	public function testDadosPlanejadorVagasHtmlViramString() {
		$dados = $this->oferecimento()->Dados_Planejador(array(), '#FF0000', false, null, true);
		$this->assertSame('40', $dados['vagas']);
		$this->assertSame('MC102 A (40)', $dados['link']);
	}

	public function testDadosPlanejadorPreservaAOrdemDasChaves() {
		$O = $this->oferecimento();
		$U = new Usuario();
		$this->assertSame(array(
			'id', 'siglan', 'turma', 'professor', 'vagas', 'fechado', 'link',
			'horarios', 'professores', 'possivel', 'adicionado', 'viola_reserva',
			'eventSources', 'Amigos', 'total'
		), array_keys($O->Dados_Planejador(array(), '#FF0000', false, $U)));
	}

	public function testDadosProfessoresSemProfessores() {
		$O = $this->oferecimento();
		$Perguntas = $this->perguntas(array(array(9, 0.8), array(9, 0.8), array(9, 0.8), array(9, 0.8)));
		$this->assertSame(array(), $O->Dados_Professores($Perguntas));
	}

	public function testDadosProfessoresComMediasSuficientes() {
		$O = $this->oferecimento();
		$O->setProfessores(array($this->professor(5, 'Fulano')));
		// v >= CONFIG_AVALIACAO_MINIMO (3) -> media = round(w * 10)
		$Perguntas = $this->perguntas(array(
			array(9, 0.85), array(9, 0.70), array(9, 0.60), array(9, 0.50)
		));

		$professores = $O->Dados_Professores($Perguntas);
		$this->assertCount(1, $professores);
		$this->assertSame('Fulano', $professores[0]['nome']);
		$this->assertEquals(9, $professores[0]['mediap']);
		$this->assertEquals(7, $professores[0]['media1']);
		$this->assertEquals(6, $professores[0]['media2']);
		$this->assertEquals(5, $professores[0]['media3']);
	}

	public function testDadosProfessoresComPoucasAvaliacoesRetornaMenosUm() {
		$O = $this->oferecimento();
		$O->setProfessores(array($this->professor(5, 'Fulano')));
		// v < CONFIG_AVALIACAO_MINIMO (3) -> -1 em todas
		$Perguntas = $this->perguntas(array(
			array(2, 0.85), array(2, 0.70), array(2, 0.60), array(2, 0.50)
		));

		$professores = $O->Dados_Professores($Perguntas);
		$this->assertSame(-1, $professores[0]['mediap']);
		$this->assertSame(-1, $professores[0]['media1']);
		$this->assertSame(-1, $professores[0]['media2']);
		$this->assertSame(-1, $professores[0]['media3']);
	}

	public function testDadosProfessoresUsaOCacheEntreChamadas() {
		$Perguntas = $this->perguntas(array(
			array(9, 0.85), array(9, 0.70), array(9, 0.60), array(9, 0.50)
		));
		$Prof = $this->professor(5, 'Fulano');

		$media_professor = $media_prof_disci = array();
		$A = $this->oferecimento('MC102', 'A');
		$A->setProfessores(array($Prof));
		$B = $this->oferecimento('MC102', 'B');
		$B->setProfessores(array($Prof));

		$primeira = $A->Dados_Professores($Perguntas, $media_professor, $media_prof_disci);
		$chamadas = array_map(function(PerguntaFake $P) {
			return $P->chamadas;
		}, $Perguntas);

		$segunda = $B->Dados_Professores($Perguntas, $media_professor, $media_prof_disci);

		// A segunda turma reaproveita as medias ja calculadas
		$this->assertSame($primeira, $segunda);
		$this->assertSame($chamadas, array_map(function(PerguntaFake $P) {
			return $P->chamadas;
		}, $Perguntas));
	}
}

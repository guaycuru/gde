<?php

namespace GDE\Tests\Integration;

use GDE\Base;
use GDE\Curso;
use GDE\Disciplina;
use GDE\Oferecimento;
use GDE\Periodo;
use GDE\Planejado;
use GDE\Usuario;

/**
 * Cobertura de fumaca do miolo do planejador (a antiga acao 'c' do
 * ajax/planejador.php). Usa um usuario simulado (sem Aluno) com curso
 * definido, que e o cenario mais simples em que a Arvore e montada.
 */
class PlanejadoMontarDadosTest extends IntegrationTestCase {

	/** @var Usuario */
	private $Usuario;
	/** @var Periodo */
	private $Anterior;
	/** @var Periodo */
	private $Atual;
	/** @var Disciplina */
	private $Disciplina;
	/** @var Oferecimento */
	private $Oferecimento;
	/** @var Planejado */
	private $Planejado;

	private function seed($planeja_oferecimento = true) {
		$this->Anterior = Periodo::Novo(2095, 1);
		$this->Anterior->Save(false);
		$this->Atual = Periodo::Novo(2095, 2);
		$this->Atual->Save(false);

		$Curso = new Curso();
		$Curso->setNumero(34);
		$Curso->setNivel('G');
		$Curso->setNome('Ciencia da Computacao');
		$Curso->Save(false);

		$this->Usuario = new Usuario();
		$this->Usuario->setLogin('planejador_montar');
		$this->Usuario->setNome('Fulano');
		$this->Usuario->setSobrenome('de Tal');
		$this->Usuario->setCurso($Curso);
		$this->Usuario->setCatalogo(2020);
		$this->Usuario->setIngresso(2020);
		$this->Usuario->Save(false);

		$this->Disciplina = new Disciplina();
		$this->Disciplina->setSigla('MC102');
		$this->Disciplina->setNivel('G');
		$this->Disciplina->setNome('Algoritmos');
		$this->Disciplina->setCreditos(6);
		$this->Disciplina->Save(false);

		$this->Oferecimento = new Oferecimento();
		$this->Oferecimento->setDisciplina($this->Disciplina);
		$this->Oferecimento->setPeriodo($this->Atual);
		$this->Oferecimento->setTurma('A');
		$this->Oferecimento->setVagas(40);
		$this->Oferecimento->Save(false);
		Base::_EM_Flush();

		$this->Planejado = Planejado::Criar_Opcao($this->Usuario, $this->Atual->getPeriodo(), 0);
		$this->Planejado->Save(true);
		if($planeja_oferecimento) {
			$this->Planejado->addOferecimentos($this->Oferecimento);
			$this->Planejado->Save(true);
		}
	}

	public function testMontarDadosDoPlanejado() {
		$this->seed();
		$Ret = $this->Planejado->Montar_Dados($this->Usuario);

		$this->assertNotNull($Ret);
		$this->assertSame($this->Atual->getPeriodo(), $Ret['Planejado']['periodo']);
		$this->assertSame($this->Anterior->getPeriodo(), $Ret['Planejado']['periodo_atual']);
		$this->assertSame('f', $Ret['Planejado']['compartilhado']);
		// Usuario sem Aluno => Criar_Opcao marcou como simulado
		$this->assertSame('t', $Ret['Planejado']['simulado']);
		$this->assertSame(array(), $Ret['Planejado']['Config']);
	}

	public function testMontarDadosTrazAsDisciplinasPlanejadas() {
		$this->seed();
		$Ret = $this->Planejado->Montar_Dados($this->Usuario);

		$chave = 'D'.$this->Disciplina->getId();
		$this->assertArrayHasKey($chave, $Ret['Oferecimentos']);
		$Bloco = $Ret['Oferecimentos'][$chave];
		$this->assertSame('MC102', $Bloco['Disciplina']['sigla']);
		$this->assertSame('N', $Bloco['Disciplina']['semestre']);
		$this->assertTrue($Bloco['Disciplina']['pode']);
		$this->assertTrue($Bloco['Disciplina']['tem']);

		$this->assertArrayHasKey('A', $Bloco['Oferecimentos']);
		$this->assertTrue($Bloco['Oferecimentos']['A']['adicionado']);
		$this->assertSame(40, $Bloco['Oferecimentos']['A']['vagas']);
		$this->assertArrayHasKey('viola_reserva', $Bloco['Oferecimentos']['A']);
	}

	public function testMontarDadosSemNadaPlanejado() {
		$this->seed(false);
		$Ret = $this->Planejado->Montar_Dados($this->Usuario);

		$this->assertNotNull($Ret);
		$this->assertSame(array(), $Ret['Oferecimentos']);
		$this->assertSame(array(), $Ret['Extras']);
	}

	public function testMontarDadosDevolveOsIndicesDeCor() {
		$this->seed();
		$Ret = $this->Planejado->Montar_Dados($this->Usuario, 3, 2);

		// Uma disciplina consumiu uma cor; os extras nao mexeram no indice deles
		$this->assertSame(4, $Ret['c']);
		$this->assertSame(2, $Ret['ce']);
		$this->assertSame(3, $Ret['Oferecimentos']['D'.$this->Disciplina->getId()]['Disciplina']['c']);
	}

	public function testMontarDadosTrazAArvore() {
		$this->seed();
		$Ret = $this->Planejado->Montar_Dados($this->Usuario);

		$this->assertSame(array('cp', 'cpf', 'integralizacao', 'tipos'), array_keys($Ret['Arvore']));
		$this->assertStringContainsString('Ciencia da Computacao', $Ret['Arvore']['integralizacao']);
		// Sem curriculo cadastrado a disciplina planejada e extra-curricular
		$this->assertSame(
			array($this->Disciplina->getId() => 'Extra-Curricular'),
			$Ret['Arvore']['tipos']
		);
	}

	public function testMontarDadosSemDebugNaoTrazTempos() {
		$this->seed();
		$Ret = $this->Planejado->Montar_Dados($this->Usuario);
		$this->assertArrayNotHasKey('times', $Ret);
	}

	public function testMontarDadosComDebugTrazTempos() {
		$this->seed();
		$Ret = $this->Planejado->Montar_Dados($this->Usuario, 0, 0, null, true);

		$this->assertArrayHasKey('times', $Ret);
		$this->assertArrayHasKey('cria_arvore', $Ret['times']);
		$this->assertArrayHasKey('times_arvore1', $Ret);
		$this->assertArrayHasKey('times_arvore2', $Ret);
	}

	public function testMontarDadosDeUsuarioSemCursoRetornaNull() {
		$this->seed();
		$this->Usuario->setCurso(null);

		$this->assertNull($this->Planejado->Montar_Dados($this->Usuario));
	}

	public function testMontarDadosDisciplina() {
		$this->seed(false);
		$Ret = $this->Planejado->Montar_Dados_Disciplina($this->Usuario, $this->Disciplina);

		$Bloco = $Ret['Oferecimentos'][$this->Disciplina->getId()];
		$this->assertSame('MC102', $Bloco['Disciplina']['sigla']);
		$this->assertSame('N', $Bloco['Disciplina']['semestre']);
		$this->assertTrue($Bloco['Disciplina']['pode']);
		$this->assertTrue($Bloco['Disciplina']['tem']);

		// Diferente do planejador inteiro: nunca marca como adicionado, as vagas
		// vem como string e nao ha checagem de reserva
		$this->assertFalse($Bloco['Oferecimentos']['A']['adicionado']);
		$this->assertSame('40', $Bloco['Oferecimentos']['A']['vagas']);
		$this->assertArrayNotHasKey('viola_reserva', $Bloco['Oferecimentos']['A']);
	}

	public function testMontarDadosDisciplinaDeUsuarioSemCursoRetornaNull() {
		$this->seed(false);
		$this->Usuario->setCurso(null);

		$this->assertNull($this->Planejado->Montar_Dados_Disciplina($this->Usuario, $this->Disciplina));
	}
}

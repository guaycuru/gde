<?php

namespace GDE\Tests\Integration;

use GDE\Arvore;
use GDE\Base;
use GDE\Periodo;
use GDE\Usuario;

/**
 * A Arvore de integralizacao calculada sobre dados reais (ver AmostraTestCase):
 * o usuario 1 e aluno de Engenharia de Computacao, catalogo 2007, com 92
 * oferecimentos cursados e 74 eliminadas.
 *
 * O segundo parametro do construtor decide o que e montado, e o nome engana:
 *
 *   $completa = false  arvore personalizada - considera o historico do aluno e
 *                      calcula creditos e coeficientes. E o que todos os
 *                      chamadores de producao usam.
 *   $completa = true   apenas a grade do curriculo, sem o historico. Creditos
 *                      feitos e coeficientes saem zerados. E o DEFAULT do
 *                      construtor, exposto so pela views/arvore.php.
 */
class ArvoreAmostraTest extends AmostraTestCase {

	private function usuario() {
		Base::_EM()->clear();
		return Usuario::Load(self::ID_USUARIO);
	}

	private function arvore($completa = false, $periodo = null) {
		$Periodo = ($periodo !== null) ? Periodo::Load($periodo) : null;
		return new Arvore($this->usuario(), $completa, $Periodo);
	}

	// -- o curriculo ---------------------------------------------------------

	public function testMontaSemErroECarregaOCurriculo() {
		$A = $this->arvore();

		$this->assertFalse($A->getErro());
		$this->assertSame(10, $A->getNumero_Semestres());
		$this->assertCount(54, $A->getSiglas_Obrigatorias());
	}

	public function testAsObrigatoriasVemDoCurriculoDoAluno() {
		$A = $this->arvore();
		$obrigatorias = $A->getSiglas_Obrigatorias();

		// Basicas do primeiro semestre de Engenharia de Computacao
		foreach(array('MA111', 'MA141', 'MC102', 'F 128', 'QG100') as $sigla)
			$this->assertContains($sigla, $obrigatorias, $sigla.' deveria ser obrigatoria');
	}

	public function testTrazOsConjuntosDeEletivas() {
		$A = $this->arvore();

		$this->assertCount(3, $A->getEletivas());
		$this->assertIsArray($A->getEletivas_Faltantes());
	}

	// -- creditos e coeficientes --------------------------------------------

	public function testCalculaOsCreditosDoAluno() {
		$A = $this->arvore();

		$this->assertSame(241, $A->getCreditos_Totais());
		$this->assertSame(235, $A->getCreditos_Feitos());
		$this->assertSame(28, $A->getCreditos_Proficiencia());
		$this->assertSame(0, $A->getCreditos_Atuais());
	}

	public function testCoeficientesFormatados() {
		$A = $this->arvore();

		$this->assertSame('0,9751', $A->getCP(4));
		$this->assertSame('0,9751', $A->getCPF(4));
	}

	/**
	 * Com um periodo no passado, o que ele estava cursando naquele semestre sai
	 * de "feitos" e entra em "atuais" - e os coeficientes caem.
	 */
	public function testPeriodoPassadoSeparaOQueEstavaEmCurso() {
		$A = $this->arvore(false, 20112);

		$this->assertSame(241, $A->getCreditos_Totais());
		$this->assertSame(194, $A->getCreditos_Feitos());
		$this->assertSame(8, $A->getCreditos_Atuais());
		$this->assertCount(2, $A->getSiglas_Atuais());
		$this->assertSame('0,8050', $A->getCP(4));
		$this->assertSame('0,8382', $A->getCPF(4));
	}

	public function testPeriodoAtualNaoTemDisciplinasEmCurso() {
		$A = $this->arvore(false, self::PERIODO_PROXIMO);

		$this->assertSame(0, $A->getCreditos_Atuais());
		$this->assertCount(0, $A->getSiglas_Atuais());
		$this->assertSame($this->periodoID($A), self::PERIODO_PROXIMO);
	}

	private function periodoID(Arvore $A) {
		return ($A->getPeriodo() !== null) ? $A->getPeriodo()->getID() : null;
	}

	// -- modo completa -------------------------------------------------------

	/**
	 * O default do construtor monta so a grade: nenhum credito do aluno e
	 * contabilizado e os coeficientes saem zerados. Nao e bug, e o modo usado
	 * para desenhar a arvore do curriculo - mas e uma armadilha, porque e o
	 * valor padrao do parametro.
	 */
	public function testModoCompletaNaoContabilizaOHistoricoDoAluno() {
		$A = $this->arvore(true);

		$this->assertFalse($A->getErro());
		$this->assertSame(0, $A->getCreditos_Feitos());
		$this->assertSame(0, $A->getCreditos_Proficiencia());
		$this->assertSame('0,0000', $A->getCP(4));
		$this->assertSame('0,0000', $A->getCPF(4));
		// A grade em si continua la
		$this->assertCount(54, $A->getSiglas_Obrigatorias());
		$this->assertSame(10, $A->getNumero_Semestres());
	}

	/** Só o modo completa preenche a grade semestre a semestre (para desenho) */
	public function testSomenteOModoCompletaPreencheAsDisciplinasPorSemestre() {
		$completa = $this->arvore(true)->getDisciplinas();
		$this->assertSame(range(1, 9), array_keys($completa));
		$this->assertCount(7, $completa[1], 'primeiro semestre');
		$total = 0;
		foreach($completa as $lista)
			$total += count($lista);
		$this->assertGreaterThan(40, $total);

		$personalizada = $this->arvore(false)->getDisciplinas();
		foreach($personalizada as $semestre => $lista)
			$this->assertCount(0, $lista, 'semestre '.$semestre.' vem vazio fora do modo completa');
	}

	/** O total de creditos exigidos difere de um modo para o outro */
	public function testTotalDeCreditosDifereEntreOsModos() {
		$this->assertSame(240, $this->arvore(true)->getCreditos_Totais());
		$this->assertSame(241, $this->arvore(false)->getCreditos_Totais());
	}

	// -- classificacao por sigla ---------------------------------------------

	public function testGetTipoClassificaAsDisciplinas() {
		$A = $this->arvore();

		$this->assertSame('Obrigat&oacute;ria', $A->getTipo('MC102', false));
		$this->assertSame('Obrigat&oacute;ria', $A->getTipo('MA111', false));
	}

	public function testIntegralizacaoDescreveOAluno() {
		$texto = $this->arvore()->Integralizacao();

		$this->assertStringContainsString('Engenharia de Computa', $texto);
		$this->assertStringContainsString('070812', $texto);
		$this->assertStringContainsString('2007', $texto, 'catalogo');
	}

	// -- desenho -------------------------------------------------------------

	private function arquivoTemporario() {
		$caminho = sys_get_temp_dir().DIRECTORY_SEPARATOR.'arvore_teste_'.getmypid().'.png';
		if(file_exists($caminho))
			unlink($caminho);
		return $caminho;
	}

	/**
	 * Fumaca do pipeline de desenho (GD): garante que Desenha() percorre todo o
	 * caminho sem estourar e produz um PNG legivel. Recebendo um caminho ele
	 * grava em arquivo, sem emitir header nem escrever na saida padrao.
	 */
	public function testDesenhaGeraUmPngValidoNoModoCompleta() {
		$caminho = $this->arquivoTemporario();

		$this->assertTrue($this->arvore(true)->Desenha($caminho));
		$this->assertFileExists($caminho);

		$info = getimagesize($caminho);
		$this->assertNotFalse($info, 'deveria ser uma imagem legivel');
		$this->assertSame(IMAGETYPE_PNG, $info[2]);
		$this->assertGreaterThan(0, $info[0], 'largura');
		$this->assertGreaterThan(0, $info[1], 'altura');

		unlink($caminho);
	}

	public function testDesenhaGeraUmPngValidoNaArvorePersonalizada() {
		$caminho = $this->arquivoTemporario();

		$this->assertTrue($this->arvore(false, 20112)->Desenha($caminho));

		$info = getimagesize($caminho);
		$this->assertNotFalse($info);
		$this->assertSame(IMAGETYPE_PNG, $info[2]);

		unlink($caminho);
	}

	/** O mapa de areas clicaveis acompanha o desenho: HTML de <area> */
	public function testMapaDevolveAreasParaAImagem() {
		$mapa = $this->arvore(true)->Mapa();

		$this->assertIsString($mapa);
		$this->assertStringContainsString("<area shape='rect'", $mapa);
		// Uma area por disciplina da grade, com id derivado do id da disciplina
		$this->assertSame(54, substr_count($mapa, '<area '));
		$this->assertStringContainsString("id='Shape_4162'", $mapa, 'MC102');
		$this->assertStringContainsString('Algoritmos e Programa', $mapa);
	}
}

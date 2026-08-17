<?php

namespace GDE\Tests\Unit;

use GDE\Disciplina;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Listas fixas da Disciplina (niveis, periodicidades) e os getters que as
 * consultam, mais o tratamento da ementa.
 */
class DisciplinaMetadadosTest extends TestCase {

	// -- listas --------------------------------------------------------------

	public function testListarNiveis() {
		$niveis = Disciplina::Listar_Niveis();

		$this->assertSame(
			array(Disciplina::NIVEL_GRAD, Disciplina::NIVEL_POS, Disciplina::NIVEL_MP),
			array_keys($niveis)
		);
		$this->assertSame('P&oacute;s-Gradua&ccedil;&atilde;o', $niveis[Disciplina::NIVEL_POS]);
	}

	public function testListarPeriodicidades() {
		$p = Disciplina::Listar_Periodicidades();

		$this->assertSame('Desconhecido', $p[Disciplina::PERIODICIDADE_DESCONHECIDA]);
		$this->assertSame('Primeiro Semestre do Ano', $p[Disciplina::PERIODICIDADE_PRIMEIRO]);
		$this->assertSame('Os Dois Semestres do Ano', $p[Disciplina::PERIODICIDADE_AMBOS]);
		$this->assertCount(5, $p);
	}

	/** Os codigos nao sao sequenciais: pulam de 2 para 5 */
	public function testCodigosDePeriodicidadeNaoSaoSequenciais() {
		$this->assertSame(
			array(0, 1, 2, 5, 6),
			array_keys(Disciplina::Listar_Periodicidades())
		);
	}

	// -- getNivel ------------------------------------------------------------

	public function testGetNivelDevolveCodigoOuNome() {
		$D = new Disciplina();
		$D->setNivel(Disciplina::NIVEL_GRAD);

		$this->assertSame(Disciplina::NIVEL_GRAD, $D->getNivel());
		$this->assertSame('Gradua&ccedil;&atilde;o / Tecnologia', $D->getNivel(true));
	}

	/** Sem nivel: string vazia pelo codigo, 'Desconhecido' pelo nome */
	public function testGetNivelSemValor() {
		$D = new Disciplina();

		$this->assertSame('', $D->getNivel());
		$this->assertSame('Desconhecido', $D->getNivel(true));
	}

	public function testGetNivelDeCodigoDesconhecidoVoltaComoVeio() {
		$D = new Disciplina();
		$D->setNivel('Z');

		$this->assertSame('Z', $D->getNivel(true));
	}

	// -- getPeriodicidade ----------------------------------------------------

	public static function periodicidades() {
		return array(
			'primeiro' => array(Disciplina::PERIODICIDADE_PRIMEIRO, 'Primeiro Semestre do Ano'),
			'segundo' => array(Disciplina::PERIODICIDADE_SEGUNDO, 'Segundo Semestre do Ano'),
			'ambos' => array(Disciplina::PERIODICIDADE_AMBOS, 'Os Dois Semestres do Ano'),
			'criterio' => array(Disciplina::PERIODICIDADE_CRITERIO, 'A Crit&eacute;rio do Instituto'),
		);
	}

	#[DataProvider('periodicidades')]
	public function testGetPeriodicidadeTraduzOCodigo($codigo, $nome) {
		$D = new Disciplina();
		$D->setPeriodicidade($codigo);

		$this->assertSame($codigo, $D->getPeriodicidade());
		$this->assertSame($nome, $D->getPeriodicidade(true));
	}

	/**
	 * Sem periodicidade: null pelo codigo, mas 'Desconhecido' pelo nome. E o
	 * codigo 0 (PERIODICIDADE_DESCONHECIDA) cai no mesmo ramo do null, porque a
	 * comparacao e com == e nao ===.
	 */
	public function testGetPeriodicidadeSemValor() {
		$D = new Disciplina();

		$this->assertNull($D->getPeriodicidade());
		$this->assertSame('Desconhecido', $D->getPeriodicidade(true));
	}

	public function testPeriodicidadeZeroSeComportaComoNaoDefinida() {
		$D = new Disciplina();
		$D->setPeriodicidade(Disciplina::PERIODICIDADE_DESCONHECIDA);

		$this->assertNull($D->getPeriodicidade());
		$this->assertSame('Desconhecido', $D->getPeriodicidade(true));
	}

	// -- ementa --------------------------------------------------------------

	/** Por padrao a ementa sai sem tags e com as entidades resolvidas */
	public function testGetEmentaRemoveHtmlPorPadrao() {
		$D = new Disciplina();
		$D->setEmenta('<p>Algoritmos e <strong>programa&ccedil;&atilde;o</strong></p>');

		$this->assertSame('Algoritmos e programação', $D->getEmenta());
	}

	/** Com $no_html = false o conteudo original e preservado */
	public function testGetEmentaPodeManterOHtmlOriginal() {
		$D = new Disciplina();
		$D->setEmenta('<p>Algoritmos</p>');

		$this->assertSame('<p>Algoritmos</p>', $D->getEmenta(false, false));
	}

	/** Com $html = true o resultado e escapado para saida */
	public function testGetEmentaEscapaParaSaida() {
		$D = new Disciplina();
		$D->setEmenta('Tipos <abstratos> & concretos');

		$this->assertSame('Tipos  &amp; concretos', $D->getEmenta(true));
	}

	public function testGetEmentaVaziaNaoEhProcessada() {
		$D = new Disciplina();

		$this->assertNull($D->getEmenta());
	}
}

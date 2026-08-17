<?php

namespace GDE\Tests\Unit;

use DAC\DisciplinaContinencias;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Parser do campo de continencias da DAC (CADEXPSIGA), que e a origem das
 * equivalencias entre disciplinas. E o unico pedaco de EquivalenciasGDE() que
 * nao precisa de banco.
 */
class DisciplinaContinenciasTest extends TestCase {

	public static function vazios() {
		return array(
			'null' => array(null),
			'string vazia' => array(''),
			'so espacos' => array("   \t  "),
		);
	}

	#[DataProvider('vazios')]
	public function testSemContinenciasNaoDevolveConjunto($valor) {
		$this->assertSame(array(), DisciplinaContinencias::Conjuntos($valor));
	}

	public function testUmConjuntoComUmaSigla() {
		$this->assertSame(
			array(array('MA211')),
			DisciplinaContinencias::Conjuntos('MA211')
		);
	}

	public function testVariasSiglasNoMesmoConjunto() {
		$this->assertSame(
			array(array('EC332', 'MA043', 'MA211', 'MA301', 'TT201')),
			DisciplinaContinencias::Conjuntos('EC332 MA043 MA211 MA301 TT201')
		);
	}

	/** Conjuntos alternativos vem separados por " / " */
	public function testConjuntosSeparadosPorBarra() {
		$this->assertSame(
			array(array('MA211', 'MA301'), array('TT201', 'TT220')),
			DisciplinaContinencias::Conjuntos('MA211 MA301 / TT201 TT220')
		);
	}

	/** Siglas de instituto de uma letra so vem com espaco no meio */
	public function testSiglaComEspaco() {
		$this->assertSame(
			array(array('F 328', 'F 429')),
			DisciplinaContinencias::Conjuntos('F 328 F 429')
		);
	}

	/** Espacos extras dentro da sigla sao normalizados para um so */
	public function testEspacosExtrasNaSiglaSaoNormalizados() {
		$this->assertSame(
			array(array('F 328')),
			DisciplinaContinencias::Conjuntos('F   328')
		);
	}

	public function testSiglaRepetidaEntraUmaVezSo() {
		$this->assertSame(
			array(array('MA211', 'MA301')),
			DisciplinaContinencias::Conjuntos('MA211 MA301 MA211')
		);
	}

	/**
	 * A deduplicacao normaliza antes de comparar: "F  328" e "F 328" sao a
	 * mesma sigla. A versao anterior comparava a forma crua e guardava a
	 * normalizada, entao duplicava nesse caso.
	 */
	public function testSiglaRepetidaComEspacamentoDiferenteEntraUmaVezSo() {
		$this->assertSame(
			array(array('F 328')),
			DisciplinaContinencias::Conjuntos('F 328 F  328')
		);
	}

	/** Conjuntos marcados com *** sao descartados */
	public function testConjuntoComAsteriscosEIgnorado() {
		$this->assertSame(
			array(array('MA211')),
			DisciplinaContinencias::Conjuntos('MA211 / *** MA301 TT201')
		);
	}

	public function testTrechoSemSiglaEIgnorado() {
		$this->assertSame(
			array(array('MA211')),
			DisciplinaContinencias::Conjuntos('MA211 / sem nada aqui')
		);
	}

	public function testTodosOsConjuntosDescartadosDevolveVazio() {
		$this->assertSame(
			array(),
			DisciplinaContinencias::Conjuntos('*** MA211 / *** TT201')
		);
	}
}

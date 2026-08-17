<?php

namespace GDE\Tests\Unit;

use GDE\CurriculoEletiva;
use PHPUnit\Framework\TestCase;

class CurriculoEletivaTest extends TestCase {

	public function testSemCuringaExigeIgualdadeExata() {
		$this->assertTrue(CurriculoEletiva::Bate_Eletiva('MC102', 'MC102'));
		$this->assertFalse(CurriculoEletiva::Bate_Eletiva('MC102', 'MC103'));
		$this->assertFalse(CurriculoEletiva::Bate_Eletiva('MC102', 'mc102'));
	}

	public function testCuringaCasaQualquerCaractere() {
		$this->assertTrue(CurriculoEletiva::Bate_Eletiva('MC1--', 'MC199'));
		$this->assertTrue(CurriculoEletiva::Bate_Eletiva('MC1--', 'MC100'));
		$this->assertFalse(CurriculoEletiva::Bate_Eletiva('MC1--', 'MA199'));
		$this->assertFalse(CurriculoEletiva::Bate_Eletiva('MC1--', 'MC299'));
	}

	public function testCuringaTotal() {
		$this->assertTrue(CurriculoEletiva::Bate_Eletiva('-----', 'MC102'));
	}

	public function testSiglaMaisCurtaNaoCasa() {
		$this->assertFalse(CurriculoEletiva::Bate_Eletiva('MC1--', 'MC1'));
		$this->assertFalse(CurriculoEletiva::Bate_Eletiva('MC1--', ''));
	}

	public function testSiglaMaisLongaComparaSomenteOPrefixo() {
		// Comportamento atual: caracteres alem do tamanho da eletiva sao ignorados
		$this->assertTrue(CurriculoEletiva::Bate_Eletiva('MC1--', 'MC102X'));
	}

	public function testSemiForcadoFalseUsaIgualdade() {
		$this->assertTrue(CurriculoEletiva::Bate_Eletiva('MC1--', 'MC1--', false));
		$this->assertFalse(CurriculoEletiva::Bate_Eletiva('MC1--', 'MC100', false));
	}

	public function testSemiForcadoTrueComparaPorCaractere() {
		$this->assertTrue(CurriculoEletiva::Bate_Eletiva('MC102', 'MC102', true));
		$this->assertFalse(CurriculoEletiva::Bate_Eletiva('MC102', 'MC103', true));
	}
}

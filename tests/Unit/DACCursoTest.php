<?php

namespace GDE\Tests\Unit;

use DAC\Curso as DACCurso;
use PHPUnit\Framework\TestCase;

/**
 * Testa o mapeamento DAC -> GDE (Dado_GDE) sem tocar o banco ($load = false)
 */
class DACCursoTest extends TestCase {

	private function dacCurso($codigo, $nivel, $nome) {
		$R = new \ReflectionClass(DACCurso::class);
		$C = new DACCurso();
		foreach(array('codigo' => $codigo, 'nivel' => $nivel, 'nome' => $nome) as $prop => $valor)
			$R->getProperty($prop)->setValue($C, $valor);
		return $C;
	}

	public function testDadoGDECriaCursoNovoEMarcaDirty() {
		$DAC = $this->dacCurso(34, 'G', 'Engenharia de Computação');
		$dirty = false;
		$Curso = $DAC->Dado_GDE(false, null, $dirty);

		$this->assertInstanceOf(\GDE\Curso::class, $Curso);
		$this->assertSame(34, $Curso->getNumero(false));
		$this->assertSame('G', $Curso->getNivel(false));
		$this->assertSame('Engenharia de Computação', $Curso->getNome(false));
		$this->assertTrue($dirty);
	}

	public function testDadoGDECacheiaOResultado() {
		$DAC = $this->dacCurso(34, 'G', 'Engenharia de Computação');
		$dirty = false;
		$this->assertSame($DAC->Dado_GDE(false, null, $dirty), $DAC->Dado_GDE(false, null, $dirty));
	}
}

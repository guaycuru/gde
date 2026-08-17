<?php

namespace GDE\Tests\Unit;

use GDE\Arvore;
use GDE\CurriculoEletiva;
use GDE\CurriculoEletivaConjunto;
use PHPUnit\Framework\TestCase;

class ArvoreTest extends TestCase {

	// O construtor de Arvore depende do banco; instancia sem construtor e
	// injeta as propriedades por reflexao
	private function arvore(array $props) {
		$R = new \ReflectionClass(Arvore::class);
		$A = $R->newInstanceWithoutConstructor();
		foreach($props as $nome => $valor)
			$R->getProperty($nome)->setValue($A, $valor);
		return $A;
	}

	public function testLimiteSemIngressoRetornaFalse() {
		$this->assertFalse($this->arvore(array('ingresso' => null))->Limite());
	}

	public function testLimiteComCurriculoCompleto() {
		// 10 semestres sugeridos -> maximo 15; sem proficiencia usa todos
		$A = $this->arvore(array(
			'ingresso' => 2020,
			'numero_semestres' => 10,
			'creditos_totais' => 180,
			'creditos_proficiencia' => 0,
		));
		$this->assertEquals(array(2027, 1), $A->Limite());
	}

	public function testLimiteProficienciaReduzOPrazo() {
		$A = $this->arvore(array(
			'ingresso' => 2020,
			'numero_semestres' => 10,
			'creditos_totais' => 180,
			'creditos_proficiencia' => 12,
		));
		$this->assertEquals(array(2026, 2), $A->Limite());
	}

	public function testLimiteMinimoDe5Semestres() {
		// Sem creditos totais cai no minimo de 5 semestres
		$A = $this->arvore(array(
			'ingresso' => 2020,
			'numero_semestres' => 4,
			'creditos_totais' => 0,
			'creditos_proficiencia' => 0,
		));
		$this->assertEquals(array(2022, 1), $A->Limite());
	}

	public function testGetTipoClassificaPorSigla() {
		$A = $this->arvore(array(
			'siglas_obrigatorias' => array('MC102'),
			'siglas_eletivas' => array('MA111'),
		));
		$this->assertSame('+', $A->getTipo('MC102'));
		$this->assertSame('*', $A->getTipo('MA111'));
		$this->assertSame('X', $A->getTipo('EF102'));
		$this->assertSame('Obrigat&oacute;ria', $A->getTipo('MC102', false));
		$this->assertSame('Eletiva', $A->getTipo('MA111', false));
		$this->assertSame('Extra-Curricular', $A->getTipo('EF102', false));
	}

	public function testGetCPFormataComVirgula() {
		$A = $this->arvore(array('cp' => 0.7345, 'cpf' => 0.851));
		$this->assertSame('0,73', $A->getCP(2));
		$this->assertSame('0,7345', $A->getCP(4));
		$this->assertSame('0,9', $A->getCPF(1));
	}

	public function testGetCPAA400() {
		$A = $this->arvore(array('cp' => 0.7345));
		$this->assertEquals(73, $A->getCPAA400());
	}

	public function testOrdenaEletivasFechadasPrimeiro() {
		$Fechada = new CurriculoEletiva();
		$Conjunto = new CurriculoEletivaConjunto();
		$Conjunto->setSigla('F 128');
		$Fechada->setConjuntos(array($Conjunto));

		$Semi = new CurriculoEletiva();
		$ConjuntoSemi = new CurriculoEletivaConjunto();
		$ConjuntoSemi->setSigla('MC1--');
		$Semi->setConjuntos(array($ConjuntoSemi));

		$Livre = new CurriculoEletiva();

		$this->assertLessThan(0, Arvore::OrdenaEletivas($Fechada, $Semi));
		$this->assertLessThan(0, Arvore::OrdenaEletivas($Semi, $Livre));
		$this->assertLessThan(0, Arvore::OrdenaEletivas($Fechada, $Livre));
		$this->assertGreaterThan(0, Arvore::OrdenaEletivas($Livre, $Fechada));
		$this->assertSame(0, Arvore::OrdenaEletivas($Livre, $Livre));
	}
}

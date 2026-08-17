<?php

namespace GDE\Tests\Unit;

use GDE\CurriculoEletiva;
use GDE\CurriculoEletivaConjunto;
use GDE\Disciplina;
use GDE\Equivalencia;
use GDE\EquivalenciaEquivalente;
use GDE\Periodo;
use GDE\UsuarioEliminada;
use PHPUnit\Framework\TestCase;

class UsuarioEliminadaTest extends TestCase {

	private function disciplina($id, $sigla, $creditos) {
		$D = new Disciplina();
		$D->setId_Disciplina($id);
		$D->setSigla($sigla);
		$D->setCreditos($creditos);
		return $D;
	}

	private function eliminada(?Disciplina $D = null, ?Periodo $P = null) {
		$UE = new UsuarioEliminada();
		if($D !== null)
			$UE->setDisciplina($D);
		if($P !== null)
			$UE->setPeriodo($P);
		return $UE;
	}

	// Adiciona a $Alvo um conjunto de equivalencia formado por $Equivalentes
	private function comEquivalencia(Disciplina $Alvo, $id_equivalencia, array $Equivalentes) {
		$Eq = new Equivalencia();
		$Eq->setId_Equivalencia($id_equivalencia);
		$lista = array();
		foreach($Equivalentes as $D) {
			$EE = new EquivalenciaEquivalente();
			$EE->setSigla($D->getSigla(false));
			$EE->setDisciplina($D);
			$lista[] = $EE;
		}
		$Eq->setEquivalentes($lista);
		$Alvo->setEquivalencias(array($Eq));
		return $Alvo;
	}

	private function eletiva($id, $creditos, array $Conjunto = array()) {
		$E = new CurriculoEletiva();
		$E->setId_Eletivas($id);
		$E->setCreditos($creditos);
		$conjuntos = array();
		foreach($Conjunto as $sigla => $D) {
			$C = new CurriculoEletivaConjunto();
			$C->setSigla($sigla);
			if($D !== null)
				$C->setDisciplina($D);
			$conjuntos[] = $C;
		}
		$E->setConjuntos($conjuntos);
		return $E;
	}

	// ---- Elimina ----

	public function testEliminaSemDisciplinaRetornaFalse() {
		$UE = new UsuarioEliminada();
		$this->assertFalse($UE->Elimina($this->disciplina(1, 'MC102', 4), array()));
	}

	public function testEliminaDisciplinaIgual() {
		$MC102 = $this->disciplina(10, 'MC102', 4);
		$UE = $this->eliminada($MC102);
		$this->assertSame(array($UE), $UE->Elimina($MC102, array()));
	}

	public function testEliminaPorEquivalenciaCompleta() {
		// MC102 e equivalente ao conjunto {F 128, F 129}
		$F128 = $this->disciplina(20, 'F 128', 4);
		$F129 = $this->disciplina(21, 'F 129', 2);
		$MC102 = $this->comEquivalencia($this->disciplina(10, 'MC102', 4), 1, array($F128, $F129));

		$UE = $this->eliminada($F128);
		$UE_Outra = $this->eliminada($F129);
		$this->assertSame(
			array($UE, $UE_Outra),
			$UE->Elimina($MC102, array('F 129' => $UE_Outra))
		);
	}

	public function testEliminaPorEquivalenciaIncompletaRetornaFalse() {
		$F128 = $this->disciplina(20, 'F 128', 4);
		$F129 = $this->disciplina(21, 'F 129', 2);
		$MC102 = $this->comEquivalencia($this->disciplina(10, 'MC102', 4), 1, array($F128, $F129));

		// Eliminou somente F 128; sem F 129 nas Outras o conjunto nao fecha
		$UE = $this->eliminada($F128);
		$this->assertFalse($UE->Elimina($MC102, array()));
	}

	public function testEliminaDisciplinaForaDoConjuntoRetornaFalse() {
		$F128 = $this->disciplina(20, 'F 128', 4);
		$MC102 = $this->comEquivalencia($this->disciplina(10, 'MC102', 4), 1, array($F128));

		$UE = $this->eliminada($this->disciplina(30, 'BD500', 4));
		$this->assertFalse($UE->Elimina($MC102, array()));
	}

	// ---- Elimina_Eletiva ----

	public function testEliminaEletivaLivreAbateCreditos() {
		// Eletiva livre (sem conjuntos): qualquer disciplina abate creditos
		$Faltantes = array($this->eletiva(1, 8));
		$lcreditos = $lconjuntos = array();

		$UE = $this->eliminada($this->disciplina(20, 'MC102', 4));
		$ret = $UE->Elimina_Eletiva($Faltantes, array(), $lcreditos, $lconjuntos);

		$this->assertSame(4, $ret['creditos']);
		$this->assertSame(array('MC102'), $ret['siglas']);
		$this->assertSame('-----', $ret['eliminada']);
		$this->assertSame(4, $ret['sobraram']);
		$this->assertSame(0, $ret['diff_creditos']);
		// Ainda faltam 4 creditos, a eletiva continua na lista
		$this->assertCount(1, $Faltantes);
		$this->assertSame(4, $lcreditos[1]);

		// Segunda eliminada de 4 creditos completa a eletiva
		$UE2 = $this->eliminada($this->disciplina(21, 'MA111', 4));
		$ret2 = $UE2->Elimina_Eletiva($Faltantes, array(), $lcreditos, $lconjuntos);

		$this->assertSame(4, $ret2['creditos']);
		$this->assertSame(0, $ret2['sobraram']);
		$this->assertSame(0, $ret2['diff_creditos']);
		$this->assertCount(0, $Faltantes);
	}

	public function testEliminaEletivaFechadaPorDisciplinaExata() {
		$F128 = $this->disciplina(30, 'F 128', 4);
		$Faltantes = array($this->eletiva(2, 4, array('F 128' => $F128)));
		$lcreditos = $lconjuntos = array();

		$UE = $this->eliminada($F128);
		$ret = $UE->Elimina_Eletiva($Faltantes, array(), $lcreditos, $lconjuntos);

		$this->assertSame(4, $ret['creditos']);
		$this->assertSame(array('F 128'), $ret['siglas']);
		$this->assertSame('F 128', $ret['eliminada']);
		$this->assertSame(0, $ret['sobraram']);
		$this->assertCount(0, $Faltantes);
	}

	public function testEliminaEletivaSemMatchRetornaFalse() {
		$F128 = $this->disciplina(30, 'F 128', 4);
		$Faltantes = array($this->eletiva(3, 4, array('F 128' => $F128)));
		$lcreditos = $lconjuntos = array();

		$UE = $this->eliminada($this->disciplina(40, 'BD500', 4));
		$this->assertFalse($UE->Elimina_Eletiva($Faltantes, array(), $lcreditos, $lconjuntos));
		$this->assertCount(1, $Faltantes);
	}

	// ---- Ordenacao ----

	public function testOrdenarDACPorPeriodo() {
		$A = $this->eliminada($this->disciplina(1, 'MC102', 4), Periodo::Novo(2024, 1));
		$B = $this->eliminada($this->disciplina(2, 'MA111', 4), Periodo::Novo(2024, 2));
		$this->assertLessThan(0, UsuarioEliminada::Ordenar_DAC($A, $B));
		$this->assertGreaterThan(0, UsuarioEliminada::Ordenar_DAC($B, $A));
	}

	public function testOrdenarDACColocaFeriasDeVeraoAposO2oSemestre() {
		// Periodos YYYY0 (verao) ganham +3 na comparacao: 20240 -> 20243
		$Verao = $this->eliminada($this->disciplina(1, 'F 128', 4), Periodo::Novo(2024, 0));
		$Segundo = $this->eliminada($this->disciplina(2, 'MA111', 4), Periodo::Novo(2024, 2));
		$this->assertGreaterThan(0, UsuarioEliminada::Ordenar_DAC($Verao, $Segundo));
	}

	public function testOrdenarDACDesempataPorSigla() {
		$P = Periodo::Novo(2024, 1);
		$A = $this->eliminada($this->disciplina(1, 'MA111', 4), $P);
		$B = $this->eliminada($this->disciplina(2, 'mc102', 4), $P);
		$this->assertLessThan(0, UsuarioEliminada::Ordenar_DAC($A, $B));
	}

	public function testOrdenarCreditosMaisCreditosPrimeiro() {
		$A = $this->eliminada($this->disciplina(1, 'MC102', 4), Periodo::Novo(2024, 1));
		$B = $this->eliminada($this->disciplina(2, 'F 128', 2), Periodo::Novo(2024, 1));
		$this->assertLessThan(0, UsuarioEliminada::Ordenar_Creditos($A, $B));
	}

	public function testOrdenarCreditosDesempataPorPeriodo() {
		$A = $this->eliminada($this->disciplina(1, 'MC102', 4), Periodo::Novo(2023, 2));
		$B = $this->eliminada($this->disciplina(2, 'MA111', 4), Periodo::Novo(2024, 1));
		$this->assertLessThan(0, UsuarioEliminada::Ordenar_Creditos($A, $B));
	}
}

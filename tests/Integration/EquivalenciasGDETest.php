<?php

namespace GDE\Tests\Integration;

use DAC\DisciplinaContinencias;
use GDE\Base;
use GDE\Disciplina;

/**
 * EquivalenciasGDE() e o unico lugar que preenche gde_equivalencias. Ate
 * 2026-08 ela nunca ligava o conjunto a disciplina dona nem resolvia as
 * siglas dos equivalentes, entao a coluna id_disciplina ficava NULL nas duas
 * tabelas - e como Disciplina::getEquivalencias() usa mappedBy="disciplina",
 * nenhuma equivalencia era visivel no sistema (o "Contida Em" da pagina da
 * disciplina saia sempre vazio).
 *
 * A entidade DAC nao e persistida aqui: so montada em memoria com o campo de
 * continencias, que e o que a view institucional entrega.
 */
class EquivalenciasGDETest extends IntegrationTestCase {

	/** @var Disciplina */
	private $MA211;

	private function seed() {
		$this->MA211 = $this->novaDisciplina('MA211', 'G');
		$this->novaDisciplina('MA301', 'G');
		$this->novaDisciplina('TT201', 'G');
		// Mesma sigla em outro nivel: nao pode ser escolhida no lugar da de graduacao
		$this->novaDisciplina('MA301', 'P');
		Base::_EM_Flush();
	}

	private function novaDisciplina($sigla, $nivel) {
		$D = new Disciplina();
		$D->setSigla($sigla);
		$D->setNivel($nivel);
		$D->setNome('Disciplina '.$sigla);
		$D->setCreditos(4);
		$D->Save(false);
		return $D;
	}

	private function continencias($conjuntos) {
		$C = new DisciplinaContinencias();
		$C->setSigla('MA211');
		$C->setConjuntos($conjuntos);
		return $C;
	}

	public function testLigaOConjuntoADisciplinaDona() {
		$this->seed();

		$Equivalencias = $this->continencias('MA301 TT201')->EquivalenciasGDE($this->MA211);

		$this->assertCount(1, $Equivalencias);
		$this->assertSame(
			$this->MA211->getId(),
			$Equivalencias[0]->getDisciplina()->getId(),
			'sem isto a gde_equivalencias.id_disciplina fica NULL e o conjunto some do sistema'
		);
	}

	public function testResolveASiglaDosEquivalentes() {
		$this->seed();

		$Equivalencias = $this->continencias('MA301 TT201')->EquivalenciasGDE($this->MA211);
		$Equivalentes = $Equivalencias[0]->getEquivalentes();

		$this->assertCount(2, $Equivalentes);
		foreach($Equivalentes as $E) {
			$D = $E->getDisciplina(false);
			$this->assertNotNull($D, $E->getSigla(false).' devia ter sido resolvida');
			$this->assertSame($E->getSigla(false), $D->getSigla(false));
			$this->assertNotNull($D->getId(), 'precisa ser a Disciplina do banco, nao uma vazia');
		}
	}

	/** A resolucao respeita o nivel da disciplina dona */
	public function testResolveNoMesmoNivelDaDona() {
		$this->seed();

		$Equivalencias = $this->continencias('MA301')->EquivalenciasGDE($this->MA211);
		$Equivalente = $Equivalencias[0]->getEquivalentes()[0];

		$this->assertSame('G', $Equivalente->getDisciplina()->getNivel(false));
	}

	/** Sigla que nao existe no GDE fica sem disciplina, mas nao derruba a importacao */
	public function testSiglaDesconhecidaFicaSemDisciplina() {
		$this->seed();

		$Equivalencias = $this->continencias('MA301 ZZ999')->EquivalenciasGDE($this->MA211);
		$Equivalentes = $Equivalencias[0]->getEquivalentes();

		$this->assertCount(2, $Equivalentes);
		$porSigla = array();
		foreach($Equivalentes as $E)
			$porSigla[$E->getSigla(false)] = $E->getDisciplina(false);

		$this->assertNotNull($porSigla['MA301']);
		$this->assertNull($porSigla['ZZ999']);
	}

	public function testVariosConjuntosViramVariasEquivalencias() {
		$this->seed();

		$Equivalencias = $this->continencias('MA301 / TT201')->EquivalenciasGDE($this->MA211);

		$this->assertCount(2, $Equivalencias);
		foreach($Equivalencias as $Equivalencia) {
			$this->assertSame($this->MA211->getId(), $Equivalencia->getDisciplina()->getId());
			$this->assertCount(1, $Equivalencia->getEquivalentes());
		}
	}

	public function testSemContinenciasNaoGeraEquivalencia() {
		$this->seed();

		$this->assertSame(array(), $this->continencias(null)->EquivalenciasGDE($this->MA211));
		$this->assertSame(array(), $this->continencias('')->EquivalenciasGDE($this->MA211));
	}

	/**
	 * Fecha o ciclo: depois de salvo, o conjunto aparece em
	 * Disciplina::Equivalencias() - que e o que a pagina da disciplina usa e
	 * que hoje, em producao, volta sempre vazio.
	 */
	public function testDepoisDeSalvoApareceEmDisciplinaEquivalencias() {
		$this->seed();

		foreach($this->continencias('MA301 TT201')->EquivalenciasGDE($this->MA211) as $Equivalencia)
			$Equivalencia->Save(false);
		Base::_EM_Flush();

		$Lista = Disciplina::Load($this->MA211->getId())->Equivalencias(false);

		$this->assertCount(1, $Lista);
		$Conjunto = reset($Lista);
		$this->assertSame(array('MA301', 'TT201'), array_keys($Conjunto));
		foreach($Conjunto as $sigla => $D)
			$this->assertSame($sigla, $D->getSigla(false));
	}
}

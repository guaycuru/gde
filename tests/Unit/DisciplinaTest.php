<?php

namespace GDE\Tests\Unit;

use GDE\Disciplina;
use PHPUnit\Framework\TestCase;

class DisciplinaTest extends TestCase {

	public function testURLDisciplinaSemCuringaApontaParaPagina() {
		$this->assertSame(CONFIG_URL.'disciplina/5/', Disciplina::URL_Disciplina(5, 'MC102'));
	}

	public function testURLDisciplinaComCuringaApontaParaBusca() {
		$this->assertSame(
			CONFIG_URL.'busca/?t=tab_disciplinas&sigla=MC1--&buscar#tab_disciplinas',
			Disciplina::URL_Disciplina(5, 'MC1--')
		);
	}

	public function testOrganizaOrdenaPorSiglaNatural() {
		$A = new Disciplina();
		$A->setSigla('MC102');
		$B = new Disciplina();
		$B->setSigla('MA111');
		$this->assertGreaterThan(0, Disciplina::Organiza($A, $B));
		$this->assertLessThan(0, Disciplina::Organiza($B, $A));
		$this->assertSame(0, Disciplina::Organiza($A, $A));
		// Ordenacao natural: MC2 vem antes de MC10
		$C = new Disciplina();
		$C->setSigla('MC2');
		$D = new Disciplina();
		$D->setSigla('MC10');
		$this->assertLessThan(0, Disciplina::Organiza($C, $D));
	}

	public function testOrganizaComTiposInvalidosRetornaZero() {
		$this->assertSame(0, Disciplina::Organiza('MC102', 'MA111'));
		$this->assertSame(0, Disciplina::Organiza(null, null));
	}

	public function testFormataConjuntosVazioRetornaTraco() {
		$this->assertSame('-', Disciplina::Formata_Conjuntos(array()));
	}

	public function testFormataConjuntosComDisciplinaDesconhecida() {
		$this->assertSame(
			'MC999 (?)',
			Disciplina::Formata_Conjuntos(array(array('MC999' => null)))
		);
	}

	public function testFormataConjuntosSeparaAlternativasComOu() {
		$this->assertSame(
			'MC998 (?) ou<br />MC999 (?)',
			Disciplina::Formata_Conjuntos(array(array('MC998' => null), array('MC999' => null)))
		);
	}

	private function disciplina($id, $sigla, $nome, $creditos) {
		$D = new Disciplina();
		$D->setId_Disciplina($id);
		$D->setSigla($sigla);
		$D->setNome($nome);
		$D->setCreditos($creditos);
		return $D;
	}

	public function testFormataConjuntosComDisciplinaConhecida() {
		$D = $this->disciplina(10, 'MC102', 'Programacao', 4);
		$this->assertSame(
			'<a href="'.CONFIG_URL.'disciplina/10/" title="Programacao">MC102</a> (4)',
			Disciplina::Formata_Conjuntos(array(array('MC102' => $D)))
		);
	}

	public function testFormataConjuntosSemCreditosMostraInterrogacao() {
		$D = $this->disciplina(10, 'MC102', 'Programacao', 0);
		$this->assertSame(
			'<a href="'.CONFIG_URL.'disciplina/10/" title="Programacao">MC102</a> (?)',
			Disciplina::Formata_Conjuntos(array(array('MC102' => $D)))
		);
	}

	public function testFormataConjuntosJuntaDisciplinasDoConjuntoComE() {
		$A = $this->disciplina(10, 'F 128', 'Fisica Teorica', 4);
		$B = $this->disciplina(11, 'F 129', 'Fisica Experimental', 2);
		$this->assertSame(
			'<a href="'.CONFIG_URL.'disciplina/10/" title="Fisica Teorica">F 128</a> (4)'.
			' e <a href="'.CONFIG_URL.'disciplina/11/" title="Fisica Experimental">F 129</a> (2)',
			Disciplina::Formata_Conjuntos(array(array('F 128' => $A, 'F 129' => $B)))
		);
	}
}

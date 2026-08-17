<?php

namespace GDE\Tests\Integration;

use GDE\Aluno;
use GDE\Base;
use GDE\Disciplina;
use GDE\Oferecimento;
use GDE\Periodo;
use GDE\PreConjunto;
use GDE\PreLista;

/**
 * Pre-requisitos por catalogo e a contagem de desistencias da Disciplina.
 *
 * Um PreConjunto e um conjunto de pre-requisitos valido para um catalogo; a
 * mesma disciplina costuma ter conjuntos diferentes em catalogos diferentes.
 */
class DisciplinaPreRequisitosTest extends IntegrationTestCase {

	/** @var Disciplina */
	private $MC202;

	private function disciplina($sigla) {
		$D = new Disciplina();
		$D->setSigla($sigla);
		$D->setNivel('G');
		$D->setNome('Disciplina '.$sigla);
		$D->setCreditos(4);
		$D->Save(false);
		return $D;
	}

	private function conjunto(Disciplina $D, $catalogo, array $siglas) {
		$C = new PreConjunto();
		$C->setDisciplina($D);
		$C->setCatalogo($catalogo);
		foreach($siglas as $sigla) {
			$L = new PreLista();
			$L->setSigla($sigla);
			$L->setParcial(false);
			$C->addLista($L);
		}
		$C->Save(false);
		return $C;
	}

	private function seed() {
		$this->MC202 = $this->disciplina('MC202');
		$this->conjunto($this->MC202, 2007, array('MC102'));
		$this->conjunto($this->MC202, 2020, array('MC102', 'MA111'));
		Base::_EM_Flush();
	}

	// -- Limpar_Pre_Conjuntos -----------------------------------------------

	public function testLimparPreConjuntosRemoveSomenteOCatalogoInformado() {
		$this->seed();
		$this->assertCount(2, $this->MC202->getPre_Conjuntos());

		$this->MC202->Limpar_Pre_Conjuntos(2007, true);
		Base::_EM()->clear();

		$Recarregada = Disciplina::Load($this->MC202->getID());
		$restantes = $Recarregada->getPre_Conjuntos();
		$this->assertCount(1, $restantes);
		// catalogo e string, nao inteiro: pode ser um ano ou uma letra ('P', 'S'
		// para pos-graduacao)
		$this->assertSame('2020', $restantes->first()->getCatalogo(false));
	}

	public function testLimparCatalogoSemConjuntosNaoRemoveNada() {
		$this->seed();

		$this->MC202->Limpar_Pre_Conjuntos(1998, true);
		Base::_EM()->clear();

		$this->assertCount(2, Disciplina::Load($this->MC202->getID())->getPre_Conjuntos());
	}

	/** As PreListas do conjunto vao junto (orphanRemoval) */
	public function testAsListasDoConjuntoRemovidoTambemSaoApagadas() {
		$this->seed();
		$antes = (int)Base::_EM()->getConnection()->fetchOne('SELECT COUNT(*) FROM gde_pre_lista');
		$this->assertSame(3, $antes, '1 do catalogo 2007 + 2 do 2020');

		$this->MC202->Limpar_Pre_Conjuntos(2007, true);

		$depois = (int)Base::_EM()->getConnection()->fetchOne('SELECT COUNT(*) FROM gde_pre_lista');
		$this->assertSame(2, $depois);
	}

	// -- Desistencias --------------------------------------------------------

	private function aluno($ra) {
		$A = new Aluno();
		$A->setRA($ra);
		$A->setNome('Aluno '.$ra);
		$A->setNivel('G');
		return $A;
	}

	private function oferecimento(Disciplina $D, Periodo $P, $turma) {
		$O = new Oferecimento();
		$O->setDisciplina($D);
		$O->setPeriodo($P);
		$O->setTurma($turma);
		$O->Save(false);
		return $O;
	}

	/**
	 * Diferente de Oferecimento::Desistencias(), aqui a contagem e da
	 * disciplina inteira: soma os trancamentos de todas as turmas e periodos.
	 */
	public function testDesistenciasSomaTodasAsTurmasEPeriodos() {
		$this->seed();
		$Primeiro = Periodo::Novo(2095, 1);
		$Primeiro->Save(false);
		$Segundo = Periodo::Novo(2095, 2);
		$Segundo->Save(false);

		$A = $this->oferecimento($this->MC202, $Primeiro, 'A');
		$B = $this->oferecimento($this->MC202, $Primeiro, 'B');
		$C = $this->oferecimento($this->MC202, $Segundo, 'A');
		Base::_EM_Flush();

		$this->assertSame(0, (int)$this->MC202->Desistencias());

		$um = $this->aluno(5001);
		$um->addTrancados($A);
		$um->Save(false);
		$dois = $this->aluno(5002);
		$dois->addTrancados($B);
		$dois->Save(false);
		$tres = $this->aluno(5003);
		$tres->addTrancados($C);
		$tres->Save(false);
		Base::_EM_Flush();

		$this->assertSame(3, (int)$this->MC202->Desistencias());
	}

	public function testDesistenciasIgnoraOutrasDisciplinas() {
		$this->seed();
		$Periodo = Periodo::Novo(2095, 1);
		$Periodo->Save(false);
		$Outra = $this->disciplina('MC102');

		$A = $this->aluno(6001);
		$A->addTrancados($this->oferecimento($Outra, $Periodo, 'A'));
		$A->Save(false);
		Base::_EM_Flush();

		$this->assertSame(0, (int)$this->MC202->Desistencias());
		$this->assertSame(1, (int)$Outra->Desistencias());
	}
}

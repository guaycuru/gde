<?php

namespace GDE\Tests\Integration;

use GDE\Aluno;
use GDE\Base;
use GDE\Disciplina;
use GDE\Oferecimento;
use GDE\Periodo;

/**
 * Busca de Oferecimentos e as contagens de alunos.
 *
 * A chave natural de um oferecimento e disciplina + turma + periodo.
 */
class OferecimentoBuscaTest extends IntegrationTestCase {

	/** @var Disciplina */
	private $MC102;
	/** @var Disciplina */
	private $MA111;
	/** @var Periodo */
	private $Primeiro;
	/** @var Periodo */
	private $Segundo;

	private function disciplina($sigla) {
		$D = new Disciplina();
		$D->setSigla($sigla);
		$D->setNivel('G');
		$D->setNome('Disciplina '.$sigla);
		$D->setCreditos(4);
		$D->Save(false);
		return $D;
	}

	private function oferecimento(Disciplina $D, Periodo $P, $turma) {
		$O = new Oferecimento();
		$O->setDisciplina($D);
		$O->setPeriodo($P);
		$O->setTurma($turma);
		$O->Save(false);
		return $O;
	}

	private function seed() {
		$this->Primeiro = Periodo::Novo(2095, 1);
		$this->Primeiro->Save(false);
		$this->Segundo = Periodo::Novo(2095, 2);
		$this->Segundo->Save(false);

		$this->MC102 = $this->disciplina('MC102');
		$this->MA111 = $this->disciplina('MA111');

		$this->oferecimento($this->MC102, $this->Primeiro, 'A');
		$this->oferecimento($this->MC102, $this->Primeiro, 'B');
		$this->oferecimento($this->MC102, $this->Segundo, 'A');
		$this->oferecimento($this->MA111, $this->Primeiro, 'A');

		Base::_EM_Flush();
	}

	// -- Por_Unique ----------------------------------------------------------

	public function testPorUniqueEncontraPelaChaveNatural() {
		$this->seed();

		$O = Oferecimento::Por_Unique($this->MC102, 'A', $this->Primeiro);
		$this->assertNotNull($O);
		$this->assertSame('MC102', $O->getDisciplina()->getSigla(false));
		$this->assertSame('A', $O->getTurma(false));
		$this->assertSame($this->Primeiro->getID(), $O->getPeriodo()->getID());
	}

	/** A mesma turma em outro periodo e outro oferecimento */
	public function testPorUniqueDistinguePeloPeriodo() {
		$this->seed();

		$Um = Oferecimento::Por_Unique($this->MC102, 'A', $this->Primeiro);
		$Dois = Oferecimento::Por_Unique($this->MC102, 'A', $this->Segundo);

		$this->assertNotSame($Um->getID(), $Dois->getID());
	}

	public function testPorUniqueDeTurmaInexistenteDevolveNull() {
		$this->seed();

		$this->assertNull(Oferecimento::Por_Unique($this->MC102, 'Z', $this->Primeiro));
		$this->assertNull(Oferecimento::Por_Unique($this->MA111, 'B', $this->Primeiro));
	}

	// -- Por_Disciplina_Periodo ---------------------------------------------

	public function testPorDisciplinaPeriodoTrazTodasAsTurmas() {
		$this->seed();

		$Turmas = Oferecimento::Por_Disciplina_Periodo($this->MC102, $this->Primeiro);
		$this->assertCount(2, $Turmas);

		$siglas = array();
		foreach($Turmas as $O)
			$siglas[] = $O->getTurma(false);
		sort($siglas);
		$this->assertSame(array('A', 'B'), $siglas);
	}

	public function testPorDisciplinaPeriodoSemOferecimentoDevolveVazio() {
		$this->seed();

		$this->assertCount(0, Oferecimento::Por_Disciplina_Periodo($this->MA111, $this->Segundo));
	}

	// -- contagens -----------------------------------------------------------

	private function aluno($ra) {
		$A = new Aluno();
		$A->setRA($ra);
		$A->setNome('Aluno '.$ra);
		$A->setNivel('G');
		return $A;
	}

	public function testDesistenciasContaOsAlunosQueTrancaram() {
		$this->seed();
		$O = Oferecimento::Por_Unique($this->MC102, 'A', $this->Primeiro);

		$this->assertSame(0, (int)$O->Desistencias());

		foreach(array(1001, 1002) as $ra) {
			$A = $this->aluno($ra);
			$A->addTrancados($O);
			$A->Save(false);
		}
		Base::_EM_Flush();

		$this->assertSame(2, (int)$O->Desistencias());
	}

	/** Quem cursou nao conta como desistencia */
	public function testDesistenciasIgnoraQuemCursou() {
		$this->seed();
		$O = Oferecimento::Por_Unique($this->MC102, 'A', $this->Primeiro);

		$A = $this->aluno(2001);
		$A->addOferecimentos($O);
		$A->Save(false);
		Base::_EM_Flush();

		$this->assertSame(0, (int)$O->Desistencias());
	}

	public function testAtualizaNumeroMatriculadosCopiaAContagemDaColecao() {
		$this->seed();
		$O = Oferecimento::Por_Unique($this->MC102, 'A', $this->Primeiro);

		$this->assertSame(0, $O->getMatriculados(false));

		foreach(array(3001, 3002, 3003) as $ra) {
			$A = $this->aluno($ra);
			$A->addOferecimentos($O);
			$A->Save(false);
		}
		Base::_EM_Flush();

		$O->Atualiza_Numero_Matriculados();
		$this->assertSame(3, $O->getMatriculados(false));
	}
}

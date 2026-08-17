<?php

namespace GDE\Tests\Integration;

use GDE\Aluno;
use GDE\Base;
use GDE\Disciplina;
use GDE\Oferecimento;
use GDE\Periodo;
use GDE\Professor;

/**
 * Historico do Aluno: o que ele cursou, o que trancou e com quais professores.
 *
 * As duas relacoes sao independentes - 'oferecimentos' guarda o que foi
 * cursado e 'trancados' o que foi trancado - e as consultas abaixo passam por
 * DQL, nao pelas colecoes.
 */
class AlunoHistoricoTest extends IntegrationTestCase {

	/** @var Aluno */
	private $Aluno;
	/** @var Professor */
	private $Professora;
	/** @var Professor */
	private $Outro;
	/** @var Disciplina */
	private $MC102;
	/** @var Disciplina */
	private $MA111;
	/** @var Disciplina */
	private $F328;

	private function disciplina($sigla) {
		$D = new Disciplina();
		$D->setSigla($sigla);
		$D->setNivel('G');
		$D->setNome('Disciplina '.$sigla);
		$D->setCreditos(4);
		$D->Save(false);
		return $D;
	}

	private function professor($matricula, $nome) {
		$P = new Professor();
		$P->setMatricula($matricula);
		$P->setNome($nome);
		$P->Save(false);
		return $P;
	}

	private function oferecimento(Disciplina $D, Periodo $P, $turma, ?Professor $Prof = null) {
		$O = new Oferecimento();
		$O->setDisciplina($D);
		$O->setPeriodo($P);
		$O->setTurma($turma);
		if($Prof !== null)
			$O->addProfessores($Prof);
		$O->Save(false);
		return $O;
	}

	/**
	 * O aluno cursou MC102 (com a Professora) e MA111 (com o Outro), e trancou
	 * F 328 (com a Professora).
	 */
	private function seed() {
		$Periodo = Periodo::Novo(2095, 1);
		$Periodo->Save(false);

		$this->Professora = $this->professor(1111, 'Professora Ana');
		$this->Outro = $this->professor(2222, 'Professor Bruno');
		$this->MC102 = $this->disciplina('MC102');
		$this->MA111 = $this->disciplina('MA111');
		$this->F328 = $this->disciplina('F 328');

		$this->Aluno = new Aluno();
		$this->Aluno->setRA(70812);
		$this->Aluno->setNome('Fulano de Tal');
		$this->Aluno->setNivel('G');
		$this->Aluno->addOferecimentos($this->oferecimento($this->MC102, $Periodo, 'A', $this->Professora));
		$this->Aluno->addOferecimentos($this->oferecimento($this->MA111, $Periodo, 'B', $this->Outro));
		$this->Aluno->addTrancados($this->oferecimento($this->F328, $Periodo, 'C', $this->Professora));
		$this->Aluno->Save(false);

		Base::_EM_Flush();
	}

	// -- busca ---------------------------------------------------------------

	public function testPorRA() {
		$this->seed();

		$Achado = Aluno::Por_RA(70812);
		$this->assertNotNull($Achado);
		$this->assertSame('Fulano de Tal', $Achado->getNome(false));
	}

	public function testPorRAInexistenteDevolveNull() {
		$this->seed();

		$this->assertNull(Aluno::Por_RA(999999));
	}

	// -- trancamentos --------------------------------------------------------

	public function testTrancouReconheceADisciplinaTrancada() {
		$this->seed();

		$this->assertTrue($this->Aluno->Trancou($this->F328));
	}

	/** Cursada nao e trancada: as relacoes sao independentes */
	public function testTrancouNaoConfundeComDisciplinaCursada() {
		$this->seed();

		$this->assertFalse($this->Aluno->Trancou($this->MC102));
	}

	// -- cursou com professor ------------------------------------------------

	public function testCursouComOProfessor() {
		$this->seed();

		$this->assertTrue($this->Aluno->Cursou_Com($this->Professora));
		$this->assertTrue($this->Aluno->Cursou_Com($this->Outro));
	}

	public function testCursouComProfessorDesconhecidoEhFalso() {
		$this->seed();
		$Terceiro = $this->professor(3333, 'Professor Carlos');
		Base::_EM_Flush();

		$this->assertFalse($this->Aluno->Cursou_Com($Terceiro));
	}

	/** Com a disciplina informada, o par professor+disciplina precisa bater */
	public function testCursouComProfessorNaDisciplinaEspecifica() {
		$this->seed();

		$this->assertTrue($this->Aluno->Cursou_Com($this->Professora, $this->MC102));
		// A Professora deu MC102, nao MA111
		$this->assertFalse($this->Aluno->Cursou_Com($this->Professora, $this->MA111));
		$this->assertTrue($this->Aluno->Cursou_Com($this->Outro, $this->MA111));
	}

	/** Trancada nao conta como cursada nem com o professor certo */
	public function testDisciplinaTrancadaNaoContaComoCursada() {
		$this->seed();

		$this->assertFalse($this->Aluno->Cursou_Com($this->Professora, $this->F328));
	}

	// -- trancou com professor -----------------------------------------------

	public function testTrancouComOProfessor() {
		$this->seed();

		$this->assertTrue($this->Aluno->Trancou_Com($this->Professora));
		$this->assertTrue($this->Aluno->Trancou_Com($this->Professora, $this->F328));
	}

	public function testTrancouComOProfessorErrado() {
		$this->seed();

		$this->assertFalse($this->Aluno->Trancou_Com($this->Outro));
	}

	/** Cursada nao aparece do lado dos trancamentos */
	public function testTrancouComNaoContaDisciplinaCursada() {
		$this->seed();

		$this->assertFalse($this->Aluno->Trancou_Com($this->Professora, $this->MC102));
	}
}

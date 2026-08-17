<?php

namespace GDE\Tests\Integration;

use GDE\Aluno;
use GDE\Base;
use GDE\Disciplina;
use GDE\Oferecimento;
use GDE\Periodo;

class AlunoCreditosTest extends IntegrationTestCase {

	/** @var Aluno */
	private $Aluno;
	/** @var Oferecimento[] */
	private $Ofe = array();

	// Monta um aluno com 3 oferecimentos em 20951: MC102 (4cr, G),
	// F 128 (4cr, G) e BD500 (3cr, P)
	private function seedAluno() {
		$P = Periodo::Novo(2095, 1);
		$P->Save(false);

		$disciplinas = array(
			'MC102' => array('G', 4),
			'F 128' => array('G', 4),
			'BD500' => array('P', 3),
		);
		foreach($disciplinas as $sigla => $dados) {
			$D = new Disciplina();
			$D->setSigla($sigla);
			$D->setNivel($dados[0]);
			$D->setNome('Disciplina '.$sigla);
			$D->setCreditos($dados[1]);
			$D->Save(false);

			$O = new Oferecimento();
			$O->setDisciplina($D);
			$O->setPeriodo($P);
			$O->setTurma('A');
			$O->Save(false);
			$this->Ofe[$sigla] = $O;
		}

		$this->Aluno = new Aluno();
		$this->Aluno->setRA(74822);
		$this->Aluno->setNome('Aluno Teste');
		foreach($this->Ofe as $O)
			$this->Aluno->addOferecimentos($O);
		$this->Aluno->Save(false);
		Base::_EM_Flush();
	}

	public function testCreditosAtuais() {
		$this->seedAluno();
		$this->assertEquals(11, $this->Aluno->Creditos_Atuais('20951'));
		$this->assertEquals(8, $this->Aluno->Creditos_Atuais('20951', Disciplina::$NIVEIS_GRAD));
		$this->assertEquals(3, $this->Aluno->Creditos_Atuais('20951', array('P')));
	}

	public function testCreditosAtuaisDePeriodoSemMatriculaEhNull() {
		$this->seedAluno();
		$this->assertNull($this->Aluno->Creditos_Atuais('20952'));
	}

	public function testGetOferecimentosFiltraPorPeriodoENivel() {
		$this->seedAluno();
		$this->assertCount(3, $this->Aluno->getOferecimentos());
		$this->assertCount(3, $this->Aluno->getOferecimentos('20951'));
		$this->assertCount(2, $this->Aluno->getOferecimentos('20951', Disciplina::NIVEL_GRAD));
		$this->assertCount(1, $this->Aluno->getOferecimentos('20951', array('P')));
		$this->assertCount(0, $this->Aluno->getOferecimentos('20952'));
	}

	public function testCursou() {
		$this->seedAluno();
		$this->assertTrue($this->Aluno->Cursou(Disciplina::Por_Sigla('MC102')));
		$this->assertFalse($this->Aluno->Cursou(Disciplina::Por_Sigla('XX999')));
	}

	public function testCreditosTrancados() {
		$this->seedAluno();
		$this->Aluno->addTrancados($this->Ofe['BD500']);
		$this->Aluno->Save();

		$this->assertEquals(3, $this->Aluno->Creditos_Trancados('20951'));
		$this->assertEquals(0, $this->Aluno->Creditos_Trancados('20951', Disciplina::$NIVEIS_GRAD));
	}
}

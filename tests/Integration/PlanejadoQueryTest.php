<?php

namespace GDE\Tests\Integration;

use GDE\Aluno;
use GDE\Base;
use GDE\Disciplina;
use GDE\Oferecimento;
use GDE\Periodo;
use GDE\Planejado;
use GDE\Usuario;

class PlanejadoQueryTest extends IntegrationTestCase {

	/** @var Usuario */
	private $Usuario;
	/** @var Periodo */
	private $Anterior;
	/** @var Periodo */
	private $Atual;

	private function seedPeriodos() {
		$this->Anterior = Periodo::Novo(2095, 1);
		$this->Anterior->Save(false);
		$this->Atual = Periodo::Novo(2095, 2);
		$this->Atual->Save(false);
	}

	private function seedUsuario($login = 'planejador_teste') {
		$this->Usuario = new Usuario();
		$this->Usuario->setLogin($login);
		$this->Usuario->Save(false);
		return $this->Usuario;
	}

	private function seedDisciplina($sigla, $creditos = 4) {
		$D = new Disciplina();
		$D->setSigla($sigla);
		$D->setNivel('G');
		$D->setNome('Disciplina '.$sigla);
		$D->setCreditos($creditos);
		$D->Save(false);
		return $D;
	}

	public function testCriarOpcaoUsaOPeriodoAnteriorQuandoPaEhZero() {
		$this->seedPeriodos();
		$this->seedUsuario();
		Base::_EM_Flush();

		$Planejado = Planejado::Criar_Opcao($this->Usuario, $this->Atual->getPeriodo(), 0);

		$this->assertInstanceOf(Planejado::class, $Planejado);
		$this->assertSame($this->Atual->getPeriodo(), $Planejado->getPeriodo()->getPeriodo());
		$this->assertSame($this->Anterior->getPeriodo(), $Planejado->getPeriodo_Atual()->getPeriodo());
	}

	public function testCriarOpcaoRespeitaOPeriodoAtualInformado() {
		$this->seedPeriodos();
		$Futuro = Periodo::Novo(2096, 1);
		$Futuro->Save(false);
		$this->seedUsuario();
		Base::_EM_Flush();

		$Planejado = Planejado::Criar_Opcao($this->Usuario, $Futuro->getPeriodo(), $this->Atual->getPeriodo());
		$this->assertSame($this->Atual->getPeriodo(), $Planejado->getPeriodo_Atual()->getPeriodo());
	}

	public function testCriarOpcaoDePeriodoInexistenteRetornaNull() {
		$this->seedUsuario();
		Base::_EM_Flush();

		$this->assertNull(Planejado::Criar_Opcao($this->Usuario, 20991, 0));
	}

	public function testCriarOpcaoSemAnteriorRetornaNull() {
		// Unico periodo cadastrado: nao existe anterior a ele
		$Unico = Periodo::Novo(2095, 1);
		$Unico->Save(false);
		$this->seedUsuario();
		Base::_EM_Flush();

		$this->assertNull(Planejado::Criar_Opcao($this->Usuario, $Unico->getPeriodo(), 0));
	}

	public function testCriarOpcaoDeQuemNaoEhAlunoEhSimulado() {
		$this->seedPeriodos();
		$this->seedUsuario();
		Base::_EM_Flush();

		$Planejado = Planejado::Criar_Opcao($this->Usuario, $this->Atual->getPeriodo(), 0);
		$this->assertTrue($Planejado->getSimulado());
	}

	public function testCriarOpcaoDeAlunoNaoEhSimulado() {
		$this->seedPeriodos();
		$this->seedUsuario();
		$Aluno = new Aluno();
		$Aluno->setRA(74822);
		$Aluno->setNome('Aluno Teste');
		$Aluno->setUsuario($this->Usuario);
		$Aluno->Save(false);
		Base::_EM_Flush();

		$Planejado = Planejado::Criar_Opcao($this->Usuario, $this->Atual->getPeriodo(), 0);
		$this->assertFalse($Planejado->getSimulado());
	}

	public function testMarcarEliminadas() {
		$this->seedPeriodos();
		$this->seedUsuario();
		$MC102 = $this->seedDisciplina('MC102');
		$MA111 = $this->seedDisciplina('MA111');
		Base::_EM_Flush();

		$Planejado = Planejado::Criar_Opcao($this->Usuario, $this->Atual->getPeriodo(), 0);
		$Planejado->Save(true);

		$this->assertTrue($Planejado->Marcar_Eliminadas(array($MC102->getId(), $MA111->getId()), array($MA111->getId())));
		$this->assertCount(2, $Planejado->getEliminadas());
		$this->assertFalse($Planejado->Tem_Eliminada($MC102)->getParcial());
		$this->assertTrue($Planejado->Tem_Eliminada($MA111)->getParcial());
	}

	public function testMarcarEliminadasSubstituiAsAnteriores() {
		$this->seedPeriodos();
		$this->seedUsuario();
		$MC102 = $this->seedDisciplina('MC102');
		$MA111 = $this->seedDisciplina('MA111');
		Base::_EM_Flush();

		$Planejado = Planejado::Criar_Opcao($this->Usuario, $this->Atual->getPeriodo(), 0);
		$Planejado->Save(true);
		$Planejado->Marcar_Eliminadas(array($MC102->getId()));

		$Planejado->Marcar_Eliminadas(array($MA111->getId()));
		$this->assertCount(1, $Planejado->getEliminadas());
		$this->assertFalse($Planejado->Tem_Eliminada($MC102));
		$this->assertNotFalse($Planejado->Tem_Eliminada($MA111));
	}

	public function testMarcarEliminadasSemNadaLimpaTudo() {
		$this->seedPeriodos();
		$this->seedUsuario();
		$MC102 = $this->seedDisciplina('MC102');
		Base::_EM_Flush();

		$Planejado = Planejado::Criar_Opcao($this->Usuario, $this->Atual->getPeriodo(), 0);
		$Planejado->Save(true);
		$Planejado->Marcar_Eliminadas(array($MC102->getId()));

		$this->assertTrue($Planejado->Marcar_Eliminadas());
		$this->assertCount(0, $Planejado->getEliminadas());
	}

	public function testEliminadasPossiveisMontaOConfigDasAtuais() {
		$this->seedPeriodos();
		$this->seedUsuario();
		$MC102 = $this->seedDisciplina('MC102');
		$MA111 = $this->seedDisciplina('MA111');

		$Aluno = new Aluno();
		$Aluno->setRA(74822);
		$Aluno->setNome('Aluno Teste');
		$Aluno->setUsuario($this->Usuario);
		foreach(array($MC102, $MA111) as $D) {
			$O = new Oferecimento();
			$O->setDisciplina($D);
			$O->setPeriodo($this->Anterior);
			$O->setTurma('A');
			$O->Save(false);
			$Aluno->addOferecimentos($O);
		}
		$Aluno->Save(false);
		Base::_EM_Flush();

		// Periodo planejado = 20952, periodo atual = 20951 (onde estao as atuais)
		$Planejado = Planejado::Criar_Opcao($this->Usuario, $this->Atual->getPeriodo(), 0);
		$Planejado->Save(true);
		// So MC102 foi marcada como "provavelmente vou passar"
		$Planejado->Marcar_Eliminadas(array($MC102->getId()));

		$Config = array();
		$Eliminadas = $Planejado->Eliminadas_Possiveis($this->Usuario, $Config);

		$this->assertCount(1, $Eliminadas);
		$this->assertSame($MC102->getId(), $Eliminadas[0]->getDisciplina()->getId());

		// O Config descreve as duas atuais, marcando qual delas e eliminada
		$this->assertCount(2, $Config);
		$estado = array();
		foreach($Config as $linha)
			$estado[$linha['sigla']] = $linha['eliminada'];
		$this->assertSame(array('MC102' => true, 'MA111' => false), $estado);
	}

	public function testEliminadasPossiveisDeQuemNaoEhAlunoEhVazio() {
		$this->seedPeriodos();
		$this->seedUsuario();
		Base::_EM_Flush();

		$Planejado = Planejado::Criar_Opcao($this->Usuario, $this->Atual->getPeriodo(), 0);
		$Planejado->Save(true);

		$Config = array();
		$this->assertSame(array(), $Planejado->Eliminadas_Possiveis($this->Usuario, $Config));
		$this->assertSame(array(), $Config);
	}
}

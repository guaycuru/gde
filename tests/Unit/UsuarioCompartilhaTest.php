<?php

namespace GDE\Tests\Unit;

use GDE\Usuario;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Regras de compartilhamento do Usuario que nao dependem do banco.
 *
 * Os campos compartilha_* usam tres valores: 't' (todos), 'a' (so amigos) e
 * 'f' (ninguem).
 */
class UsuarioCompartilhaTest extends TestCase {

	private function novoUsuario() {
		return new Usuario();
	}

	// -- getCompartilha ------------------------------------------------------

	public function testGetCompartilhaLeOCampoCorrespondente() {
		$U = $this->novoUsuario();
		$U->setCompartilha_Arvore('a');
		$U->setCompartilha_Horario('f');

		$this->assertSame('a', $U->getCompartilha('arvore'));
		$this->assertSame('f', $U->getCompartilha('horario'));
	}

	public function testGetCompartilhaTemPadraoTodos() {
		$U = $this->novoUsuario();

		$this->assertSame('t', $U->getCompartilha('arvore'));
		$this->assertSame('t', $U->getCompartilha('horario'));
	}

	/** Campo inexistente devolve false em vez de estourar */
	public function testGetCompartilhaDeCampoInexistenteDevolveFalse() {
		$U = $this->novoUsuario();

		$this->assertFalse($U->getCompartilha('inexistente'));
		$this->assertFalse($U->getCompartilha('nome'), 'so campos compartilha_* valem');
	}

	// -- Pode_Mudar_Compartilha_Horario --------------------------------------

	/**
	 * A regra existe para evitar que alguem fique alternando a visibilidade do
	 * horario: depois de mudar, so pode mudar de novo apos ~6 meses (15811200
	 * segundos). Mas afrouxar sempre e permitido.
	 */
	public function testMudarParaOMesmoValorSemprePode() {
		$U = $this->novoUsuario();
		$U->setCompartilha_Horario('a');
		$U->setMudanca_Horario(new \DateTime());

		$this->assertTrue($U->Pode_Mudar_Compartilha_Horario('a'));
	}

	public function testSemMudancaAnteriorPode() {
		$U = $this->novoUsuario();
		$U->setCompartilha_Horario('t');

		$this->assertTrue($U->Pode_Mudar_Compartilha_Horario('f'));
	}

	public function testAdminSemprePode() {
		$U = $this->novoUsuario();
		$U->setCompartilha_Horario('t');
		$U->setAdmin(true);
		$U->setMudanca_Horario(new \DateTime());

		$this->assertTrue($U->Pode_Mudar_Compartilha_Horario('f'));
	}

	public function testDepoisDeSeisMesesPodeQualquerMudanca() {
		$U = $this->novoUsuario();
		$U->setCompartilha_Horario('t');
		$U->setMudanca_Horario(new \DateTime('-7 months'));

		$this->assertTrue($U->Pode_Mudar_Compartilha_Horario('f'));
	}

	/** Abrir para todos nao tem carencia */
	public function testAbrirParaTodosPodeMesmoDentroDaCarencia() {
		$U = $this->novoUsuario();
		$U->setCompartilha_Horario('f');
		$U->setMudanca_Horario(new \DateTime());

		$this->assertTrue($U->Pode_Mudar_Compartilha_Horario('t'));
	}

	/** De fechado para amigos tambem e afrouxamento */
	public function testDeFechadoParaAmigosPodeDentroDaCarencia() {
		$U = $this->novoUsuario();
		$U->setCompartilha_Horario('f');
		$U->setMudanca_Horario(new \DateTime());

		$this->assertTrue($U->Pode_Mudar_Compartilha_Horario('a'));
	}

	/** Restringir dentro da carencia e o unico caso barrado */
	public static function restricoesBarradas() {
		return array(
			'de todos para amigos' => array('t', 'a'),
			'de todos para ninguem' => array('t', 'f'),
			'de amigos para ninguem' => array('a', 'f'),
		);
	}

	#[DataProvider('restricoesBarradas')]
	public function testRestringirDentroDaCarenciaNaoPode($de, $para) {
		$U = $this->novoUsuario();
		$U->setCompartilha_Horario($de);
		$U->setMudanca_Horario(new \DateTime('-1 month'));

		$this->assertFalse($U->Pode_Mudar_Compartilha_Horario($para));
	}

	// -- estados civis -------------------------------------------------------

	public function testListarEstadosCivis() {
		$estados = Usuario::Listar_Estados_Civis();

		$this->assertSame('', $estados[Usuario::ESTADO_CIVIL_VAZIO]);
		$this->assertSame('Solteiro(a)', $estados[Usuario::ESTADO_CIVIL_SOLTEIRO]);
		$this->assertArrayHasKey(Usuario::ESTADO_CIVIL_CASADO, $estados);
		$this->assertCount(9, $estados);
	}
}

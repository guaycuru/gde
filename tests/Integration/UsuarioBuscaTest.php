<?php

namespace GDE\Tests\Integration;

use GDE\Aluno;
use GDE\Base;
use GDE\Professor;
use GDE\Usuario;

/**
 * Busca de Usuario por valor unico e as regras de visibilidade (Pode_Ver).
 *
 * Por_Unique() adivinha o campo quando nao recebe um: valor com '@' e email,
 * so digitos e RA (buscado pela relacao com Aluno), o resto e login.
 */
class UsuarioBuscaTest extends IntegrationTestCase {

	/** @var Usuario */
	private $Usuario;

	private function seed($ativo = true) {
		$this->Usuario = new Usuario();
		$this->Usuario->setLogin('fulano');
		$this->Usuario->setNome('Fulano');
		$this->Usuario->setSobrenome('de Tal');
		$this->Usuario->setEmail('fulano@exemplo.br');
		$this->Usuario->setAtivo($ativo);

		$Aluno = new Aluno();
		$Aluno->setRA(123456);
		$Aluno->setNome('Fulano de Tal');
		$Aluno->setUsuario($this->Usuario);
		$Aluno->Save(false);

		$this->Usuario->Save(false);
		Base::_EM_Flush();
		return $this->Usuario;
	}

	// -- deteccao automatica do campo ---------------------------------------

	public function testPorUniqueDetectaEmailPeloArroba() {
		$this->seed();

		$Achado = Usuario::Por_Unique('fulano@exemplo.br');
		$this->assertNotNull($Achado);
		$this->assertSame($this->Usuario->getID(), $Achado->getID());
	}

	public function testPorUniqueDetectaLoginQuandoNaoEhEmailNemNumero() {
		$this->seed();

		$Achado = Usuario::Por_Unique('fulano');
		$this->assertNotNull($Achado);
		$this->assertSame($this->Usuario->getID(), $Achado->getID());
	}

	/** So digitos e tratado como RA, buscado pela relacao com Aluno */
	public function testPorUniqueDetectaRAQuandoSaoSoDigitos() {
		$this->seed();

		$Achado = Usuario::Por_Unique('123456');
		$this->assertNotNull($Achado);
		$this->assertSame($this->Usuario->getID(), $Achado->getID());
	}

	public function testPorUniqueComValorVazioDevolveNull() {
		$this->seed();

		$this->assertNull(Usuario::Por_Unique(null));
		$this->assertNull(Usuario::Por_Unique(''));
	}

	public function testPorUniqueDeValorInexistenteDevolveNull() {
		$this->seed();

		$this->assertNull(Usuario::Por_Unique('naoexiste'));
	}

	// -- atalhos -------------------------------------------------------------

	public function testPorLogin() {
		$this->seed();

		$this->assertSame($this->Usuario->getID(), Usuario::Por_Login('fulano')->getID());
		$this->assertNull(Usuario::Por_Login('inexistente'));
	}

	public function testPorRA() {
		$this->seed();

		$this->assertSame($this->Usuario->getID(), Usuario::Por_RA(123456)->getID());
		$this->assertNull(Usuario::Por_RA(999999));
	}

	/** Com $vazio = true devolve um Usuario novo em vez de null */
	public function testComVazioDevolveObjetoNovoEmVezDeNull() {
		$this->seed();

		$U = Usuario::Por_Login('inexistente', true, true);
		$this->assertInstanceOf(Usuario::class, $U);
		$this->assertNull($U->getID());

		$U = Usuario::Por_RA(999999, true, true);
		$this->assertInstanceOf(Usuario::class, $U);
		$this->assertNull($U->getID());
	}

	/** O filtro de ativo e aplicado quando nao e null */
	public function testUsuarioInativoNaoEhEncontradoPorPadrao() {
		$this->seed(false);

		$this->assertNull(Usuario::Por_Login('fulano'));
		$this->assertNotNull(Usuario::Por_Login('fulano', false), 'buscando pelos inativos');
		$this->assertNotNull(Usuario::Por_Login('fulano', null), 'sem filtrar por ativo');
	}

	public function testPorMatriculaBuscaPelaRelacaoComProfessor() {
		$Professor = new Professor();
		$Professor->setMatricula(98765);
		$Professor->setNome('Professora Beltrana');
		$Professor->Save(false);

		$U = new Usuario();
		$U->setLogin('beltrana');
		$U->setNome('Beltrana');
		$U->setProfessor($Professor);
		$U->setAtivo(true); // Por_Matricula filtra por ativo = true por padrao
		$U->Save(false);
		Base::_EM_Flush();

		$this->assertSame($U->getID(), Usuario::Por_Matricula(98765)->getID());
		$this->assertNull(Usuario::Por_Matricula(11111));
		// Por_Unique com campo 'matricula' delega para Por_Matricula
		$this->assertSame($U->getID(), Usuario::Por_Unique(98765, 'matricula')->getID());
	}

	// -- Pode_Ver ------------------------------------------------------------

	private function outro($compartilha = 't') {
		$U = new Usuario();
		$U->setLogin('outro');
		$U->setNome('Outro');
		$U->setCompartilha_Arvore($compartilha);
		$U->Save(false);
		Base::_EM_Flush();
		return $U;
	}

	public function testPodeVerOProprioPerfilSempre() {
		$this->seed();
		$this->Usuario->setCompartilha_Arvore('f');

		$this->assertTrue($this->Usuario->Pode_Ver($this->Usuario, 'arvore'));
	}

	public function testAdminVeTudo() {
		$this->seed();
		$this->Usuario->setAdmin(true);
		$Outro = $this->outro('f');

		$this->assertTrue($this->Usuario->Pode_Ver($Outro, 'arvore'));
	}

	public function testAmbosAbertosPodeVer() {
		$this->seed();
		$this->Usuario->setCompartilha_Arvore('t');

		$this->assertTrue($this->Usuario->Pode_Ver($this->outro('t'), 'arvore'));
	}

	/** Distingue de quem e a restricao: minha ou dele */
	public function testMinhaRestricaoTemPrecedenciaSobreAlheia() {
		$this->seed();
		$this->Usuario->setCompartilha_Arvore('f');

		$this->assertSame(
			Usuario::NAO_PODE_VER_MEU,
			$this->Usuario->Pode_Ver($this->outro('f'), 'arvore')
		);
	}

	public function testRestricaoAlheia() {
		$this->seed();
		$this->Usuario->setCompartilha_Arvore('t');

		$this->assertSame(
			Usuario::NAO_PODE_VER_ALHEIO,
			$this->Usuario->Pode_Ver($this->outro('f'), 'arvore')
		);
	}

	/** Com 'a' (so amigos) e sem amizade, barra */
	public function testSomenteAmigosBarraDesconhecido() {
		$this->seed();
		$this->Usuario->setCompartilha_Arvore('a');

		$this->assertSame(
			Usuario::NAO_PODE_VER_MEU,
			$this->Usuario->Pode_Ver($this->outro('t'), 'arvore')
		);
	}

	public function testSomenteAmigosLiberaParaAmigo() {
		$this->seed();
		$this->Usuario->setCompartilha_Arvore('a');
		$Outro = $this->outro('a');
		$this->Usuario->Adicionar_Amigo($Outro, true);

		Base::_EM()->clear();
		$Eu = Usuario::Load($this->Usuario->getID());
		$Ele = Usuario::Load($Outro->getID());

		$this->assertTrue($Eu->Pode_Ver($Ele, 'arvore'));
	}

	/** Aluno sem usuario associado: so depende do meu compartilhamento */
	public function testAlunoSemUsuarioDependeSoDoMeuCompartilhamento() {
		$this->seed();
		$Vazio = new Usuario();

		$this->Usuario->setCompartilha_Arvore('t');
		$this->assertTrue($this->Usuario->Pode_Ver($Vazio, 'arvore'));

		$this->Usuario->setCompartilha_Arvore('a');
		$this->assertSame(Usuario::NAO_PODE_VER_MEU, $this->Usuario->Pode_Ver($Vazio, 'arvore'));
	}
}

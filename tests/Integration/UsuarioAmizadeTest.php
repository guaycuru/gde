<?php

namespace GDE\Tests\Integration;

use GDE\Base;
use GDE\Usuario;
use GDE\UsuarioAmigo;

/**
 * Subsistema de amizades do Usuario. As linhas de UsuarioAmigo sao
 * direcionais (usuario -> amigo) e carregam um flag 'ativo', formando uma
 * maquina de estados com tres situacoes por par:
 *
 *   Quase_Amigo($U)     eu pedi e espero a autorizacao dele  (minha linha, ativo=false)
 *   Amigo_Pendente($U)  ele pediu e espera a minha           (linha dele, ativo=false)
 *   Amigo($U)           amizade ativa                        (minha linha, ativo=true)
 *
 * ATENCAO: Adicionar_Amigo()/Autorizar_Amigo() gravam a linha nova mas nao a
 * acrescentam a colecao 'amigos' ja carregada em memoria. Como Quase_Amigo() e
 * Amigo() leem justamente essa colecao, eles so enxergam a mudanca depois de
 * recarregar o Usuario - por isso cada teste chama recarregar() apos mutar.
 * Numa requisicao real isso nao aparece, porque as entidades vem do banco a
 * cada request; dentro de um mesmo processo, chamar Adicionar_Amigo() duas
 * vezes passa da guarda e estoura a constraint unica.
 */
class UsuarioAmizadeTest extends IntegrationTestCase {

	/** @var Usuario */
	private $Ana;
	/** @var Usuario */
	private $Bruno;
	/** @var Usuario */
	private $Carla;
	/** @var Usuario */
	private $Diego;
	private $ids = array();

	private function novoUsuario($login, $nome, $sobrenome = 'da Silva') {
		$U = new Usuario();
		$U->setLogin($login);
		$U->setNome($nome);
		$U->setSobrenome($sobrenome);
		$U->Save(false);
		return $U;
	}

	private function seed(array $extras = array()) {
		$this->Ana = $this->novoUsuario('ana', 'Ana');
		$this->Bruno = $this->novoUsuario('bruno', 'Bruno');
		foreach($extras as $login)
			$this->{$login} = $this->novoUsuario(strtolower($login), $login);
		Base::_EM_Flush();

		$this->ids = array('Ana' => $this->Ana->getID(), 'Bruno' => $this->Bruno->getID());
		foreach($extras as $login)
			$this->ids[$login] = $this->{$login}->getID();
	}

	/** Simula o que uma nova requisicao veria: entidades vindas do banco */
	private function recarregar() {
		Base::_EM()->clear();
		foreach($this->ids as $nome => $id)
			$this->{$nome} = Usuario::Load($id);
	}

	// -- estado inicial ------------------------------------------------------

	public function testSemRelacaoNenhumDosTresEstadosSeAplica() {
		$this->seed();

		$this->assertFalse($this->Ana->Amigo($this->Bruno));
		$this->assertFalse($this->Ana->Quase_Amigo($this->Bruno));
		$this->assertFalse($this->Ana->Amigo_Pendente($this->Bruno));
		$this->assertCount(0, $this->Ana->Amigos());
	}

	// -- pedido de amizade ---------------------------------------------------

	public function testAdicionarAmigoCriaPedidoPendenteEmUmaDirecaoSo() {
		$this->seed();
		$this->assertTrue($this->Ana->Adicionar_Amigo($this->Bruno));
		$this->recarregar();

		// Ana pediu: ela espera a autorizacao do Bruno
		$this->assertNotFalse($this->Ana->Quase_Amigo($this->Bruno));
		// Bruno ve o pedido dela esperando resposta
		$this->assertNotFalse($this->Bruno->Amigo_Pendente($this->Ana));
		// Mas ninguem e amigo de ninguem ainda
		$this->assertFalse($this->Ana->Amigo($this->Bruno));
		$this->assertFalse($this->Bruno->Amigo($this->Ana));
	}

	public function testPedidoRepetidoEhRecusado() {
		$this->seed();
		$this->Ana->Adicionar_Amigo($this->Bruno);
		$this->recarregar();

		$this->assertFalse($this->Ana->Adicionar_Amigo($this->Bruno), 'ja existe pedido pendente');
	}

	/** Quem recebeu o pedido tambem nao consegue abrir um pedido proprio */
	public function testPedidoNaDirecaoContrariaTambemEhRecusado() {
		$this->seed();
		$this->Ana->Adicionar_Amigo($this->Bruno);
		$this->recarregar();

		$this->assertFalse($this->Bruno->Adicionar_Amigo($this->Ana));
	}

	/** Com $ativo = true a amizade ja nasce feita nos dois sentidos */
	public function testAdicionarAmigoAtivoCriaOsDoisLados() {
		$this->seed();
		$this->assertTrue($this->Ana->Adicionar_Amigo($this->Bruno, true));
		$this->recarregar();

		$this->assertNotFalse($this->Ana->Amigo($this->Bruno));
		$this->assertNotFalse($this->Bruno->Amigo($this->Ana));
		$this->assertCount(1, $this->Ana->Amigos());
		$this->assertCount(1, $this->Bruno->Amigos());
	}

	// -- autorizacao ---------------------------------------------------------

	public function testAutorizarAmigoAtivaAAmizadeNosDoisSentidos() {
		$this->seed();
		$this->Ana->Adicionar_Amigo($this->Bruno);
		$this->recarregar();

		$this->assertTrue($this->Bruno->Autorizar_Amigo($this->Ana));
		$this->recarregar();

		$this->assertNotFalse($this->Ana->Amigo($this->Bruno));
		$this->assertNotFalse($this->Bruno->Amigo($this->Ana));
		// E o estado pendente sai de cena
		$this->assertFalse($this->Bruno->Amigo_Pendente($this->Ana));
		$this->assertFalse($this->Ana->Quase_Amigo($this->Bruno));
	}

	public function testAutorizarSemPedidoPendenteRetornaFalse() {
		$this->seed();

		$this->assertFalse($this->Bruno->Autorizar_Amigo($this->Ana));
	}

	/** Quem pediu nao pode autorizar o proprio pedido */
	public function testQuemPediuNaoAutorizaOProprioPedido() {
		$this->seed();
		$this->Ana->Adicionar_Amigo($this->Bruno);
		$this->recarregar();

		$this->assertFalse($this->Ana->Autorizar_Amigo($this->Bruno));
	}

	// -- remocao -------------------------------------------------------------

	public function testRemoverAmigoApagaOsDoisLados() {
		$this->seed();
		$this->Ana->Adicionar_Amigo($this->Bruno, true);
		$this->recarregar();

		$this->assertTrue($this->Ana->Remover_Amigo($this->Bruno));
		$this->recarregar();

		$this->assertFalse($this->Ana->Amigo($this->Bruno));
		$this->assertFalse($this->Bruno->Amigo($this->Ana));
		$this->assertCount(0, $this->Ana->Amigos());
		$this->assertCount(0, $this->Bruno->Amigos());
	}

	public function testRemoverPedidoPendentePeloQuePediu() {
		$this->seed();
		$this->Ana->Adicionar_Amigo($this->Bruno);
		$this->recarregar();

		$this->assertTrue($this->Ana->Remover_Amigo($this->Bruno));
		$this->recarregar();

		$this->assertFalse($this->Bruno->Amigo_Pendente($this->Ana));
	}

	public function testRemoverSemRelacaoRetornaFalse() {
		$this->seed();

		$this->assertFalse($this->Ana->Remover_Amigo($this->Bruno));
	}

	// -- listagens -----------------------------------------------------------

	public function testAmigosListaSomenteAsAmizadesAtivas() {
		$this->seed(array('Carla'));
		$this->Ana->Adicionar_Amigo($this->Bruno, true); // ativa
		$this->recarregar();
		$this->Ana->Adicionar_Amigo($this->Carla);       // so pedido
		$this->recarregar();

		$this->assertCount(1, $this->Ana->Amigos());
		$this->assertSame(
			$this->ids['Bruno'],
			$this->Ana->Amigos()->first()->getAmigo()->getID()
		);
	}

	public function testAmigosPendentesTrazQuemEstaEsperandoResposta() {
		$this->seed(array('Carla'));
		$this->Ana->Adicionar_Amigo($this->Bruno);
		$this->recarregar();
		$this->Carla->Adicionar_Amigo($this->Bruno);
		$this->recarregar();

		$this->assertCount(2, $this->Bruno->getAmigos_Pendentes());
	}

	// -- consistencia da colecao em memoria ----------------------------------

	/**
	 * Adicionar_Amigo() usa addAmigos(), que alem de gravar tambem acrescenta a
	 * linha a colecao ja carregada. Sem isso a guarda contra pedido duplicado
	 * nao enxergaria o proprio pedido recem-criado (a guarda inicializa a
	 * colecao vazia antes do insert) e a segunda chamada estouraria a
	 * constraint unica id_usuario_amigo em vez de devolver false.
	 */
	public function testPedidoDuplicadoNoMesmoProcessoEhRecusadoSemRecarregar() {
		$this->seed();

		$this->assertTrue($this->Ana->Adicionar_Amigo($this->Bruno));
		$this->assertFalse($this->Ana->Adicionar_Amigo($this->Bruno));
	}

	public function testEstadoVisivelImediatamenteAposAdicionar() {
		$this->seed();
		$this->Ana->Adicionar_Amigo($this->Bruno);

		// Sem recarregar: a colecao ja reflete o pedido
		$this->assertNotFalse($this->Ana->Quase_Amigo($this->Bruno));
		$this->assertCount(1, $this->Ana->getQuase_Amigos());
	}

	public function testAmizadeAtivaVisivelImediatamenteNosDoisLados() {
		$this->seed();
		$this->Ana->Adicionar_Amigo($this->Bruno, true);

		$this->assertNotFalse($this->Ana->Amigo($this->Bruno));
		$this->assertNotFalse($this->Bruno->Amigo($this->Ana));
		$this->assertCount(1, $this->Ana->Amigos());
		$this->assertCount(1, $this->Bruno->Amigos());
	}

	public function testAutorizarRefleteImediatamenteSemRecarregar() {
		$this->seed();
		$this->Ana->Adicionar_Amigo($this->Bruno);
		$this->recarregar();

		$this->Bruno->Autorizar_Amigo($this->Ana);

		$this->assertNotFalse($this->Bruno->Amigo($this->Ana));
		$this->assertCount(1, $this->Bruno->Amigos());
	}

	// -- casos especiais -----------------------------------------------------

	/**
	 * O proprio usuario e sempre "amigo de si mesmo": o metodo devolve um
	 * UsuarioAmigo montado na hora, com apelido 'Eu', que nao existe no banco.
	 */
	public function testUsuarioEhAmigoDeSiMesmoComObjetoSintetico() {
		$this->seed();

		$Eu = $this->Ana->Amigo($this->Ana);

		$this->assertInstanceOf(UsuarioAmigo::class, $Eu);
		$this->assertSame('Eu', $Eu->getApelido(false));
		$this->assertNull($Eu->getID(), 'nao e persistido');
		$this->assertCount(0, $this->Ana->Amigos(), 'e nao entra na lista de amizades');
	}

	public function testApelidoOuNomeUsaOApelidoDaAmizadeQuandoExiste() {
		$this->seed();
		$this->Ana->Adicionar_Amigo($this->Bruno, true);
		$this->recarregar();

		// Sem apelido: cai no nome do proprio usuario
		$this->assertSame('Bruno', $this->Ana->Apelido_Ou_Nome($this->Bruno));

		$Amizade = $this->Ana->Amigo($this->Bruno);
		$Amizade->setApelido('Brunao');
		$Amizade->Save(true);
		$this->recarregar();

		$this->assertSame('Brunao', $this->Ana->Apelido_Ou_Nome($this->Bruno));
		// O apelido e meu: o Bruno continua vendo a Ana pelo nome dela
		$this->assertSame('Ana', $this->Bruno->Apelido_Ou_Nome($this->Ana));
	}

	public function testApelidoOuNomeDeDesconhecidoUsaONome() {
		$this->seed();

		$this->assertSame('Bruno', $this->Ana->Apelido_Ou_Nome($this->Bruno));
		$this->assertSame('Bruno da Silva', $this->Ana->Apelido_Ou_Nome($this->Bruno, true));
	}

	// -- relacionamento ------------------------------------------------------

	public function testRelacionamentoDeAmigoDiretoDevolveOProprioUsuario() {
		$this->seed();
		$this->Ana->Adicionar_Amigo($this->Bruno, true);
		$this->recarregar();

		$this->assertSame($this->Bruno, $this->Ana->Relacionamento($this->Bruno));
	}

	public function testRelacionamentoSemVinculoDevolveFalse() {
		$this->seed();

		$this->assertFalse($this->Ana->Relacionamento($this->Bruno));
	}

	/** Sem amizade direta, devolve o amigo em comum que liga os dois */
	public function testRelacionamentoDevolveOAmigoEmComum() {
		$this->seed(array('Carla'));
		$this->Ana->Adicionar_Amigo($this->Carla, true);
		$this->recarregar();
		$this->Bruno->Adicionar_Amigo($this->Carla, true);
		$this->recarregar();

		$this->assertSame($this->ids['Carla'], $this->Ana->Relacionamento($this->Bruno)->getID());
	}

	public function testAmigosEmComumContaCorretamente() {
		$this->seed(array('Carla', 'Diego'));
		$this->Ana->Adicionar_Amigo($this->Carla, true);
		$this->recarregar();
		$this->Ana->Adicionar_Amigo($this->Diego, true);
		$this->recarregar();
		$this->Bruno->Adicionar_Amigo($this->Carla, true);
		$this->recarregar();

		$total = 0;
		$Comuns = $this->Ana->Amigos_Em_Comum($this->Bruno, false, $total);

		$this->assertSame(1, $total);
		$this->assertSame($this->ids['Carla'], $Comuns->first()->getAmigo()->getID());
	}
}

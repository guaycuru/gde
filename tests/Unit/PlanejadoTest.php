<?php

namespace GDE\Tests\Unit;

use GDE\Arvore;
use GDE\Planejado;
use GDE\Usuario;
use GDE\UsuarioAmigo;
use PHPUnit\Framework\TestCase;

/**
 * Arvore sem construtor (que depende do banco) e sem a integralizacao, que
 * monta um HTML enorme a partir de meia duzia de entidades
 */
class ArvoreFake extends Arvore {

	public function __construct() {}

	public function Integralizacao() {
		return '<pre>integralizacao</pre>';
	}
}

class PlanejadoTest extends TestCase {

	private function usuario($id, $login, $nome, $sobrenome = 'Silva') {
		$U = new Usuario();
		$U->setId_Usuario($id);
		$U->setLogin($login);
		$U->setNome($nome);
		$U->setSobrenome($sobrenome);
		return $U;
	}

	private function amigo(Usuario $Amigo, $apelido = null) {
		$UA = new UsuarioAmigo();
		$UA->setAmigo($Amigo);
		if($apelido !== null)
			$UA->setApelido($apelido);
		return $UA;
	}

	private function planejado($id, ?Usuario $Usuario = null) {
		$P = new Planejado();
		$P->setId_Planejado($id);
		if($Usuario !== null)
			$P->setUsuario($Usuario);
		return $P;
	}

	public function testPertenceAoProprioUsuario() {
		$U = $this->usuario(3, 'fulano', 'Fulano');
		$this->assertTrue($this->planejado(7, $U)->Pertence_A($U));
	}

	public function testNaoPertenceAOutroUsuario() {
		$Dono = $this->usuario(3, 'fulano', 'Fulano');
		$Outro = $this->usuario(4, 'beltrano', 'Beltrano');
		$this->assertFalse($this->planejado(7, $Dono)->Pertence_A($Outro));
	}

	// Load() devolve um objeto vazio quando nao encontra: sem usuario => nao pertence
	public function testPlanejadoInexistenteNaoPertence() {
		$U = $this->usuario(3, 'fulano', 'Fulano');
		$this->assertFalse($this->planejado(null)->Pertence_A($U));
	}

	public function testLinksAmigosOrdenaPorNome() {
		$Amigos = array(
			$this->amigo($this->usuario(1, 'zeca', 'Zeca')),
			$this->amigo($this->usuario(2, 'ana', 'Ana')),
			$this->amigo($this->usuario(3, 'bruno', 'Bruno'))
		);
		$links = Planejado::Links_Amigos($Amigos);

		$this->assertCount(3, $links);
		$this->assertStringContainsString('usuario=ana', $links[0]);
		$this->assertStringContainsString('usuario=bruno', $links[1]);
		$this->assertStringContainsString('usuario=zeca', $links[2]);
	}

	public function testLinksAmigosMontaOLink() {
		$links = Planejado::Links_Amigos(array($this->amigo($this->usuario(1, 'ana', 'Ana', 'Souza'))));
		$this->assertSame(
			'<a href="'.CONFIG_URL.'perfil/?usuario=ana" target="_blank" style="text-decoration: none;" title="Ana Souza">Ana Souza</a>',
			$links[0]
		);
	}

	public function testLinksAmigosUsaOApelidoQuandoExiste() {
		$links = Planejado::Links_Amigos(array($this->amigo($this->usuario(1, 'ana', 'Ana', 'Souza'), 'Aninha')));
		$this->assertStringContainsString('>Aninha</a>', $links[0]);
		// O apelido nao muda o title, que continua sendo o nome completo
		$this->assertStringContainsString('title="Ana Souza"', $links[0]);
	}

	public function testLinksAmigosSemAmigos() {
		$this->assertSame(array(), Planejado::Links_Amigos(array()));
	}

	public function testArvoreDados() {
		$A = new ArvoreFake();
		$R = new \ReflectionClass(Arvore::class);
		$R->getProperty('cp')->setValue($A, 0.75);
		$R->getProperty('cpf')->setValue($A, 0.8125);

		$dados = $A->Dados(array(10 => 'Obrigat&oacute;ria'));
		$this->assertSame(array('cp', 'cpf', 'integralizacao', 'tipos'), array_keys($dados));
		$this->assertSame('0,7500', $dados['cp']);
		$this->assertSame('0,8125', $dados['cpf']);
		$this->assertSame('<pre>integralizacao</pre>', $dados['integralizacao']);
		$this->assertSame(array(10 => 'Obrigat&oacute;ria'), $dados['tipos']);
	}

	public function testArvoreDadosSemTipos() {
		$A = new ArvoreFake();
		$R = new \ReflectionClass(Arvore::class);
		$R->getProperty('cp')->setValue($A, 0);
		$R->getProperty('cpf')->setValue($A, 0);

		$this->assertSame(array(), $A->Dados()['tipos']);
	}
}

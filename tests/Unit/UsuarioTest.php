<?php

namespace GDE\Tests\Unit;

use GDE\Usuario;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class UsuarioTest extends TestCase {

	public static function chatStatusProvider() {
		return array(
			'offline sempre off' => array(false, 'd', true, false, 'off'),
			'invisivel vira off' => array(true, 'i', false, false, 'off'),
			'desconectado vira off' => array(true, 'z', false, false, 'off'),
			'invisivel puro e mantido' => array(true, 'i', false, true, 'i'),
			'desconectado puro e mantido' => array(true, 'z', false, true, 'z'),
			'disponivel admin vira x' => array(true, 'd', true, false, 'x'),
			'disponivel comum e mantido' => array(true, 'd', false, false, 'd'),
			'ausente e mantido' => array(true, 'a', false, false, 'a'),
			'ocupado e mantido' => array(true, 'o', true, false, 'o'),
		);
	}

	#[DataProvider('chatStatusProvider')]
	public function testTrataChatStatus($esta_online, $status, $admin, $puro, $esperado) {
		$this->assertSame($esperado, Usuario::Trata_Chat_Status($esta_online, $status, $admin, $puro));
	}

	public function testCodificarSenha() {
		$this->assertNull(Usuario::Codificar_Senha(null));
		$hash = Usuario::Codificar_Senha('segredo');
		$this->assertTrue(password_verify('segredo', $hash));
		$this->assertFalse(password_verify('errada', $hash));
	}

	public function testVerificarSenhaAntiga() {
		$hash = sha1('velha'.CONFIG_SALT_SENHA_ANTIGA);
		$this->assertTrue(Usuario::Verificar_Senha_Antiga('velha', $hash));
		$this->assertFalse(Usuario::Verificar_Senha_Antiga('outra', $hash));
	}

	public function testVerificarSenhaSemSenhaLocalRetornaFalse() {
		$U = new Usuario();
		$this->assertFalse($U->Verificar_Senha('qualquer', false));
	}

	public function testVerificarSenhaNoFormatoNovo() {
		$U = new Usuario();
		$U->setSenha('segredo'); // codifica com password_hash
		$this->assertTrue($U->Verificar_Senha('segredo', false));
		$this->assertFalse($U->Verificar_Senha('errada', false));
	}

	public function testVerificarSenhaNoFormatoAntigo() {
		$U = new Usuario();
		$U->setSenha(sha1('velha'.CONFIG_SALT_SENHA_ANTIGA), false);
		$this->assertTrue($U->Verificar_Senha('velha', false));
		$this->assertFalse($U->Verificar_Senha('outra', false));
	}

	public function testVerificarSenhaJaCodificadaComparaHashes() {
		$U = new Usuario();
		$U->setSenha('hash_armazenado', false);
		$this->assertTrue($U->Verificar_Senha('hash_armazenado', true));
		$this->assertFalse($U->Verificar_Senha('outro_hash', true));
	}

	// ---- Exibicao ----

	public function testNomeCompleto() {
		$U = new Usuario();
		$U->setNome('José');
		$this->assertSame('José', $U->getNome_Completo());
		$U->setSobrenome('da Silva');
		$this->assertSame('José da Silva', $U->getNome_Completo());
	}

	public function testNomeCompletoComHtmlEscapa() {
		$U = new Usuario();
		$U->setNome('A & B');
		$U->setSobrenome('"C"');
		$this->assertSame('A &amp; B &quot;C&quot;', $U->getNome_Completo(true));
	}

	public function testSexo() {
		$U = new Usuario();
		$U->setSexo('f');
		$this->assertSame('f', $U->getSexo());
		$this->assertSame('Feminino', $U->getSexo(true));
		$this->assertSame('a', $U->getSexo(true, true));
		$U->setSexo('m');
		$this->assertSame('Masculino', $U->getSexo(true));
		$this->assertSame('o', $U->getSexo(true, true));
		$U->setSexo('o');
		$this->assertSame('Outro', $U->getSexo(true));
		$this->assertSame('*', $U->getSexo(true, true));
		$U->setSexo(null);
		$this->assertSame('Desconhecido', $U->getSexo(true));
	}

	public function testEstadoCivil() {
		$U = new Usuario();
		$U->setEstado_Civil(Usuario::ESTADO_CIVIL_SOLTEIRO);
		$this->assertSame('Solteiro(a)', $U->getEstado_Civil());
		$this->assertSame(Usuario::ESTADO_CIVIL_SOLTEIRO, $U->getEstado_Civil(false));
		$U->setEstado_Civil(99);
		$this->assertSame(99, $U->getEstado_Civil());
	}

	public function testOnline() {
		$U = new Usuario();
		$this->assertFalse($U->Online());
		$U->setUltimo_Acesso(new \DateTime('-10 seconds'));
		$this->assertTrue($U->Online());
		// CONFIG_ONLINE_TIMEOUT = 90
		$U->setUltimo_Acesso(new \DateTime('-200 seconds'));
		$this->assertFalse($U->Online());
	}

	public function testChatStatusDeUsuarioOnline() {
		$U = new Usuario();
		$U->setUltimo_Acesso(new \DateTime());
		$U->setChat_Status('d');
		$U->setAdmin(true);
		$this->assertSame('x', $U->getChat_Status());
	}

	public function testChatStatusComIcone() {
		$U = new Usuario();
		$icone = $U->getChat_Status(true);
		$this->assertStringContainsString('<img src="'.CONFIG_URL.'web/images/status_off.png"', $icone);
		$this->assertStringContainsString('alt="off"', $icone);
	}

	public function testFotoPadrao() {
		$this->assertSame(CONFIG_URL.'web/images/nobody.gif', Usuario::getFoto_Padrao());
		$this->assertSame(CONFIG_URL.'web/images/nobody_th.gif', Usuario::getFoto_Padrao(true));
		$this->assertSame(CONFIG_URL.'web/fts/nobody.gif', Usuario::getFoto_Padrao(false, true));
		$this->assertSame(CONFIG_URL.'web/fts/nobody_th.gif', Usuario::getFoto_Padrao(true, true));
	}

	public function testFotoSemFotoUsaPadrao() {
		$U = new Usuario();
		$this->assertSame(CONFIG_URL.'web/images/nobody.gif', $U->getFoto());
	}

	public function testFotoComUrl() {
		$U = new Usuario();
		$U->setFoto('abc123');
		$this->assertSame(CONFIG_URL.'web/fts/abc123.jpg', $U->getFoto(false, false, true));
		$this->assertSame(CONFIG_URL.'web/fts/abc123_th.jpg', $U->getFoto(false, true, true));
	}
}

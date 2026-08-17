<?php

namespace GDE\Tests\Unit;

use GDE\DAC_Token;
use PHPUnit\Framework\TestCase;

class DAC_TokenTest extends TestCase {

	public function testGerarCurtoRetornaApenasSha1() {
		$token = DAC_Token::Gerar(1700000000, 123456, 'A');
		$this->assertSame(40, strlen($token));
		$this->assertMatchesRegularExpression('/^[0-9a-f]{40}$/', $token);
	}

	public function testGerarCompletoTemLayoutFixo() {
		$token = DAC_Token::Gerar(1700000000, 123456, 'Aluno', true);
		$this->assertSame(61, strlen($token));
		$this->assertSame('1700000000', substr($token, 40, 10));
		$this->assertSame('0000123456', substr($token, 50, 10));
		$this->assertSame('A', $token[60]);
	}

	public function testGerarEDeterministico() {
		$this->assertSame(
			DAC_Token::Gerar(1700000000, 123456, 'A', true),
			DAC_Token::Gerar(1700000000, 123456, 'A', true)
		);
	}

	public function testValidarAceitaTokenRecemGerado() {
		$token = DAC_Token::Gerar(time(), 123456, 'A', true);
		$this->assertSame(array(true, 123456, 'A'), DAC_Token::Validar($token));
	}

	public function testTipoComMaisDeUmCaractereUsaSomenteOPrimeiro() {
		// O hash e o sufixo usam apenas o 1o caractere do tipo, entao um tipo
		// longo gera exatamente o mesmo token valido que sua inicial
		$this->assertSame(
			DAC_Token::Gerar(1700000000, 123456, 'A', true),
			DAC_Token::Gerar(1700000000, 123456, 'Aluno', true)
		);
		$token = DAC_Token::Gerar(time(), 123456, 'Aluno', true);
		$this->assertSame(array(true, 123456, 'A'), DAC_Token::Validar($token));
	}

	public function testValidarAceitaAuthEmMaiusculas() {
		$token = DAC_Token::Gerar(time(), 123456, 'A', true);
		$token = strtoupper(substr($token, 0, 40)).substr($token, 40);
		$this->assertSame(array(true, 123456, 'A'), DAC_Token::Validar($token));
	}

	public function testValidarRejeitaTokenExpirado() {
		$token = DAC_Token::Gerar(time() - 3600, 123456, 'A', true);
		$this->assertSame(array(false, 0, 0), DAC_Token::Validar($token));
	}

	public function testValidarRejeitaTokenAdulterado() {
		$token = DAC_Token::Gerar(time(), 123456, 'A', true);
		// Troca a matricula embutida sem recalcular o auth
		$token = substr($token, 0, 50).'0000654321'.substr($token, 60);
		$this->assertSame(array(false, 0, 0), DAC_Token::Validar($token));
	}

	public function testValidarRejeitaEntradasInvalidas() {
		$this->assertSame(array(false, 0, 0), DAC_Token::Validar('curto'));
		$this->assertSame(array(false, 0, 0), DAC_Token::Validar(''));
		$this->assertSame(array(false, 0, 0), DAC_Token::Validar(123456));
		$this->assertSame(array(false, 0, 0), DAC_Token::Validar(null));
	}
}

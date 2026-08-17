<?php

namespace GDE\Tests\Unit;

use GDE\Aluno;
use PHPUnit\Framework\TestCase;

/**
 * Niveis de um Aluno. O codigo de uma letra vem da DAC; as listas separam o
 * que conta como graduacao e o que conta como pos, e 'P' (Aluno Especial)
 * aparece nas duas.
 */
class AlunoNiveisTest extends TestCase {

	public function testListarNiveisTrazTodosOsCodigos() {
		$niveis = Aluno::Listar_Niveis();

		$this->assertSame(
			array('G', 'T', Aluno::NIVEL_EGRESSADO, 'M', 'D', 'P', 'S'),
			array_keys($niveis)
		);
		$this->assertSame('Doutorado', $niveis['D']);
		$this->assertSame('Egressado', $niveis[Aluno::NIVEL_EGRESSADO]);
	}

	public function testNiveisDeGraduacao() {
		$this->assertSame(array('G', 'T', 'P'), Aluno::Listar_Niveis_Grad(true));
		$this->assertSame('Tecnologia', Aluno::Listar_Niveis_Grad()['T']);
	}

	public function testNiveisDePos() {
		$this->assertSame(array('M', 'D', 'P', 'S'), Aluno::Listar_Niveis_Pos(true));
		$this->assertSame('Mestrado Profissional', Aluno::Listar_Niveis_Pos()['S']);
	}

	/** Aluno Especial conta como graduacao e como pos */
	public function testAlunoEspecialApareceNasDuasListas() {
		$this->assertContains('P', Aluno::Listar_Niveis_Grad(true));
		$this->assertContains('P', Aluno::Listar_Niveis_Pos(true));
	}

	/** Egressado esta na lista geral mas fora das duas especificas */
	public function testEgressadoNaoContaComoGraduacaoNemPos() {
		$this->assertArrayHasKey(Aluno::NIVEL_EGRESSADO, Aluno::Listar_Niveis());
		$this->assertNotContains(Aluno::NIVEL_EGRESSADO, Aluno::Listar_Niveis_Grad(true));
		$this->assertNotContains(Aluno::NIVEL_EGRESSADO, Aluno::Listar_Niveis_Pos(true));
	}

	// -- getters -------------------------------------------------------------

	public function testGetNivelDevolveOCodigoOuONomePorExtenso() {
		$A = new Aluno();
		$A->setNivel('G');

		$this->assertSame('G', $A->getNivel());
		$this->assertSame('Gradua&ccedil;&atilde;o', $A->getNivel(true));
	}

	public function testGetNivelPosDevolveOCodigoOuONomePorExtenso() {
		$A = new Aluno();
		$A->setNivel_Pos('M');

		$this->assertSame('M', $A->getNivel_Pos());
		$this->assertSame('Mestrado', $A->getNivel_Pos(true));
	}

	public function testNivelNaoDefinidoDevolveNull() {
		$A = new Aluno();

		$this->assertNull($A->getNivel());
		$this->assertNull($A->getNivel(true));
		$this->assertNull($A->getNivel_Pos());
	}

	/** Codigo desconhecido volta como veio, sem tentar traduzir */
	public function testCodigoDesconhecidoNaoEhTraduzido() {
		$A = new Aluno();
		$A->setNivel('Z');

		$this->assertSame('Z', $A->getNivel(true));
	}
}

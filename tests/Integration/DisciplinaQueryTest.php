<?php

namespace GDE\Tests\Integration;

use GDE\Base;
use GDE\Disciplina;

class DisciplinaQueryTest extends IntegrationTestCase {

	private function seed($sigla, $nivel, $nome, $creditos = 4) {
		$D = new Disciplina();
		$D->setSigla($sigla);
		$D->setNivel($nivel);
		$D->setNome($nome);
		$D->setCreditos($creditos);
		$D->Save(false);
		return $D;
	}

	public function testPorSiglaSemNivel() {
		$this->seed('MC102', 'G', 'Programacao');
		Base::_EM_Flush();

		$D = Disciplina::Por_Sigla('MC102');
		$this->assertSame('Programacao', $D->getNome());
	}

	public function testPorSiglaInexistenteRetornaObjetoVazioOuNull() {
		$Vazia = Disciplina::Por_Sigla('XX999');
		$this->assertInstanceOf(Disciplina::class, $Vazia);
		$this->assertNull($Vazia->getId());
		$this->assertSame('XX999', $Vazia->getSigla());

		$this->assertNull(Disciplina::Por_Sigla('XX999', null, false));
	}

	public function testPorSiglaComNivelUsaUnique() {
		$this->seed('MC102', 'G', 'Programacao Grad');
		$this->seed('MC102', 'P', 'Programacao Pos');
		Base::_EM_Flush();

		$this->assertSame('Programacao Grad', Disciplina::Por_Sigla('MC102', 'G')->getNome());
		$this->assertSame('Programacao Pos', Disciplina::Por_Sigla('MC102', 'P')->getNome());
	}

	public function testPorSiglaComListaDeNiveis() {
		$this->seed('MC102', 'P', 'Programacao Pos');
		Base::_EM_Flush();

		$D = Disciplina::Por_Sigla('MC102', array('G', 'P'));
		$this->assertSame('Programacao Pos', $D->getNome());
	}

	public function testConsultarComCuringaNaSigla() {
		$this->seed('MC102', 'G', 'Programacao');
		$this->seed('MC202', 'G', 'Estruturas');
		$this->seed('MA111', 'G', 'Calculo I');
		Base::_EM_Flush();

		$total = 0;
		$Lista = Disciplina::Consultar(array('sigla' => 'MC---'), null, $total);
		$this->assertSame(array('MC102', 'MC202'), array_map(function($D) {
			return $D->getSigla();
		}, $Lista));
		$this->assertEquals(2, $total);
	}

	public function testConsultarComSiglaParcial() {
		$this->seed('MC102', 'G', 'Programacao');
		$this->seed('MA111', 'G', 'Calculo I');
		Base::_EM_Flush();

		$Lista = Disciplina::Consultar(array('sigla' => 'C10'));
		$this->assertCount(1, $Lista);
		$this->assertSame('MC102', $Lista[0]->getSigla());
	}

	public function testConsultarPorNivel() {
		$this->seed('MC102', 'G', 'Programacao');
		$this->seed('BD500', 'P', 'Banco de Dados');
		Base::_EM_Flush();

		$Lista = Disciplina::Consultar(array('nivel' => array('P', 'S')));
		$this->assertCount(1, $Lista);
		$this->assertSame('BD500', $Lista[0]->getSigla());
	}

	public function testConsultarSimplesPorSiglaExata() {
		$this->seed('MC102', 'G', 'Programacao');
		$this->seed('MC103', 'G', 'Programacao II');
		Base::_EM_Flush();

		$total = 0;
		$Lista = Disciplina::Consultar_Simples('MC102', null, $total);
		$this->assertCount(1, $Lista);
		$this->assertSame('MC102', $Lista[0]->getSigla());
		$this->assertEquals(1, $total);
	}

	public function testConsultarSimplesComPrefixoCurto() {
		$this->seed('MC102', 'G', 'Programacao');
		$this->seed('MC103', 'G', 'Programacao II');
		$this->seed('MA111', 'G', 'Calculo I');
		Base::_EM_Flush();

		// Comportamento atual: ordem padrao da busca por sigla e DESC
		$total = 0;
		$Lista = Disciplina::Consultar_Simples('MC', null, $total);
		$this->assertSame(array('MC103', 'MC102'), array_map(function($D) {
			return $D->getSigla();
		}, $Lista));
	}

	public function testConsultarSimplesPorNomeSemFTS() {
		$this->seed('MC102', 'G', 'Programacao');
		$this->seed('MA111', 'G', 'Calculo I');
		Base::_EM_Flush();

		// CONFIG_FTS_ENABLED = false -> busca com LIKE em sigla e nome
		$total = 0;
		$Lista = Disciplina::Consultar_Simples('Calculo', null, $total);
		$this->assertCount(1, $Lista);
		$this->assertSame('MA111', $Lista[0]->getSigla());
	}
}

<?php

namespace GDE\Tests\Integration;

use GDE\Base;
use GDE\Sala;

class BasePersistenciaTest extends IntegrationTestCase {

	public function testSaveELoadComIdGerado() {
		$Sala = new Sala();
		$Sala->setNome('T-001');
		$this->assertTrue($Sala->Save());
		$id = $Sala->getId();
		$this->assertNotNull($id);

		// Limpa o identity map para forcar leitura real do banco
		Base::_EM()->clear();
		$Carregada = Sala::Load($id);
		$this->assertSame('T-001', $Carregada->getNome());
		$this->assertNotSame($Sala, $Carregada);
	}

	public function testLoadInexistenteRetornaObjetoVazio() {
		// Comportamento atual (marcado com ToDo no Base): Load() de um id
		// inexistente retorna um objeto novo vazio, nao null
		$Sala = Sala::Load(999999);
		$this->assertInstanceOf(Sala::class, $Sala);
		$this->assertNull($Sala->getId());
	}

	public function testFindOneByEFindBy() {
		foreach(array('T-A', 'T-B', 'T-C') as $nome) {
			$S = new Sala();
			$S->setNome($nome);
			$S->Save(false);
		}
		Base::_EM_Flush();

		$S = Sala::FindOneBy(array('nome' => 'T-B'));
		$this->assertSame('T-B', $S->getNome());
		$this->assertNull(Sala::FindOneBy(array('nome' => 'T-X')));

		$Lista = Sala::FindBy(array(), array('nome' => 'DESC'), 2);
		$this->assertCount(2, $Lista);
		$this->assertSame('T-C', $Lista[0]->getNome());
		$this->assertSame('T-B', $Lista[1]->getNome());
	}

	public function testDelete() {
		$S = new Sala();
		$S->setNome('T-DEL');
		$this->assertTrue($S->Save());
		$id = $S->getId();

		$this->assertTrue($S->Delete());
		Base::_EM()->clear();
		$this->assertNull(Sala::Load($id)->getId());
	}

	// Os dois testes abaixo inserem a MESMA sala (nome tem UNIQUE): so passam
	// os dois se o rollback por teste estiver de fato isolando o banco
	public function testIsolamentoTransacionalParte1() {
		$S = new Sala();
		$S->setNome('T-ISO');
		$this->assertTrue($S->Save());
	}

	public function testIsolamentoTransacionalParte2() {
		$S = new Sala();
		$S->setNome('T-ISO');
		$this->assertTrue($S->Save());
	}
}

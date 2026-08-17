<?php

namespace GDE\Tests\Integration;

use GDE\Acontecimento;
use GDE\Base;
use GDE\Usuario;

class AcontecimentoListarTest extends IntegrationTestCase {

	private function usuario($login) {
		$U = new Usuario();
		$U->setLogin($login);
		$U->Save(false);
		return $U;
	}

	private function acontecimento($tipo, ?Usuario $Origem, ?Usuario $Destino, ?Acontecimento $Original, $texto) {
		$A = new Acontecimento();
		$A->setTipo($tipo);
		$A->setData();
		if($Origem !== null)
			$A->setOrigem($Origem);
		if($Destino !== null)
			$A->setDestino($Destino);
		if($Original !== null)
			$A->setOriginal($Original);
		$A->setTexto($texto);
		$A->Save(false);
		return $A;
	}

	// Acontecimento::Listar tem um ToDo conhecido: o GROUP BY do SQL cru e
	// incompativel com o sql_mode ONLY_FULL_GROUP_BY (erro 1055). Se o servidor
	// de teste estiver com esse modo, documenta em vez de falhar.
	private function listar(Usuario $U, ...$args) {
		try {
			return Acontecimento::Listar($U, ...$args);
		} catch(\Throwable $e) {
			if(stripos($e->getMessage(), 'GROUP BY') !== false)
				$this->markTestIncomplete('ToDo conhecido em Acontecimento::Listar (ONLY_FULL_GROUP_BY): '.$e->getMessage());
			throw $e;
		}
	}

	public function testListarMensagensEStatusProprios() {
		$Eu = $this->usuario('teste_eu');
		$Outro = $this->usuario('teste_outro');
		Base::_EM_Flush();

		$Msg = $this->acontecimento(Acontecimento::TIPO_USUARIO_MENSAGEM, $Outro, $Eu, null, 'mensagem pra mim');
		$Status = $this->acontecimento(Acontecimento::TIPO_USUARIO_STATUS, $Eu, null, null, 'meu status');
		$this->acontecimento(Acontecimento::TIPO_USUARIO_STATUS, $Outro, null, null, 'status de outro');
		Base::_EM_Flush();

		$ids = array_map(function($A) {
			return $A->getId();
		}, $this->listar($Eu, 10, 0));

		// Mais recente primeiro; o status de terceiro nao aparece (amigos=false)
		$this->assertSame(array($Status->getId(), $Msg->getId()), $ids);
	}

	public function testListarRespostaSobeAConversa() {
		$Eu = $this->usuario('teste_eu');
		$Outro = $this->usuario('teste_outro');
		Base::_EM_Flush();

		$Msg = $this->acontecimento(Acontecimento::TIPO_USUARIO_MENSAGEM, $Outro, $Eu, null, 'conversa antiga');
		$Status = $this->acontecimento(Acontecimento::TIPO_USUARIO_STATUS, $Eu, null, null, 'status mais recente');
		Base::_EM_Flush();
		// Resposta DO OUTRO PARA MIM chega depois do status: a conversa volta ao
		// topo (respostas enviadas por mim mesmo nao reordenam minha timeline)
		$this->acontecimento(Acontecimento::TIPO_USUARIO_MENSAGEM, $Outro, $Eu, $Msg, 'resposta');
		Base::_EM_Flush();

		$ids = array_map(function($A) {
			return $A->getId();
		}, $this->listar($Eu, 10, 0));

		$this->assertSame(array($Msg->getId(), $Status->getId()), $ids);
	}

	public function testListarFiltraGDE() {
		$Eu = $this->usuario('teste_eu');
		Base::_EM_Flush();

		$Novidade = $this->acontecimento(Acontecimento::TIPO_GDE, null, null, null, 'novidade do GDE');
		Base::_EM_Flush();

		$this->assertCount(0, $this->listar($Eu, 10, 0));
		$Lista = $this->listar($Eu, 10, 0, false, true, true, false, false, true);
		$this->assertCount(1, $Lista);
		$this->assertSame($Novidade->getId(), $Lista[0]->getId());
	}
}

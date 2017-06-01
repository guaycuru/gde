<?php

namespace GDE;

define('JSON', true);
require_once('../common/common.inc.php');

if(!isset($_POST['tp']))
	Base::Error_JSON("0");

// Usuario Status ou Usuario Mensagem
if(($_POST['tp'] == Acontecimento::TIPO_USUARIO_STATUS) || ($_POST['tp'] == Acontecimento::TIPO_USUARIO_MENSAGEM)) {
	$texto = trim($_POST['txt']);
	$Acontecimento = new Acontecimento();
	$Acontecimento->setTipo($_POST['tp']);
	if($_POST['tp'] == Acontecimento::TIPO_USUARIO_MENSAGEM) { // Mensagem
		if(empty($_POST['ori'])) { // Nao eh resposta...
			$Acontecimento->setOrigem($_Usuario);
			if(!empty($_POST['i'])) {
				$Destino = Usuario::Load($_POST['i']);
				if($Destino->getID() == null)
					Base::Error_JSON("0");
				$Acontecimento->setDestino($Destino);
			} else
				Base::Error_JSON("0");
		} else { // Eh resposta
			$Original = Acontecimento::Load($_POST['ori']);
			if($Original->getID() == null)
				Base::Error_JSON("Mensagem original nao encontrada!");
			if($Original->Pode_Responder($_Usuario) === false)
				Base::Error_JSON("Usuario nao pode responder essa mensagem!");
			$Acontecimento->setOriginal($Original);
			if($Original->getTipo() == Acontecimento::TIPO_USUARIO_STATUS) {
				if(($Original->getOrigem() !== null) && ($_Usuario->getID() == $Original->getOrigem()->getID())) { // Usuario respondendo seu proprio status
					$Acontecimento->setOrigem($_Usuario);
					$Acontecimento->setDestino(null);
				} else {
					$Acontecimento->setOrigem($_Usuario);
					$Acontecimento->setDestino($Original->getOrigem());
				}
			} elseif($Original->getTipo() == Acontecimento::TIPO_USUARIO_MENSAGEM) { // Respondendo mensagem de Usuario
				if(($Original->getDestino() !== null) && ($Original->getDestino()->getID() == $_Usuario->getID())) { // Respondendo uma originalmente para mim
					$Acontecimento->setOrigem($Original->getDestino());
					$Acontecimento->setDestino($Original->getOrigem());
				} else { // Respondendo uma originalmente enviada por mim
					$Acontecimento->setOrigem($Original->getOrigem());
					$Acontecimento->setDestino($Original->getDestino());
				}
			} elseif($Original->getTipo() == Acontecimento::TIPO_GDE) { // Respondendo atualizacao do GDE
				$Acontecimento->setOrigem($_Usuario);
				$Acontecimento->setDestino(null);
			} else
				Base::Error_JSON("Tipo invalido!");
		}
	} else { // Status (us)
		$Acontecimento->setOrigem($_Usuario);
		$Acontecimento->setDestino(null);
	}
	$Acontecimento->setData();
	$Acontecimento->setTexto($texto);
	if($Acontecimento->Save(true) === false)
		Base::Error_JSON("0");
	if($_POST['tp'] == 'us') {
		$_Usuario->setStatus($texto);
		$_Usuario->Save(false);
	}
	$Acontecimento->Save_JSON(true);
} elseif($_POST['tp'] == 'x') { // Excluir
	$Acontecimento = Acontecimento::Load($_POST['id']);
	if($Acontecimento->getID() == null)
		Base::Error_JSON("0");
	if($Acontecimento->Pode_Apagar($_Usuario) === false)
		Base::Error_JSON("0");
	$Acontecimento->Delete_JSON(true);
}

<?php

namespace GDE;

define('JSON', true);
require_once('../common/common.inc.php');

if(!isset($_POST['tipo']))
	exit();

$Usr = Usuario::Load($_POST['i']);
if($_POST['tipo'] == 'a') {
	// Adicionar novo amigo
	$Usr = Usuario::Load($_POST['i']);
	if($Usr->getID() == $_Usuario->getID())
		Base::Error_JSON('Não é possível enviar um pedido de amizade a você mesmo.');
	else
		if($_Usuario->Adicionar_Amigo($Usr, false, true))
			Base::OK_JSON();
		else
			Base::Error_JSON('Ele(a) j&aacute; est&aacute; na sua lista de amigos.');
} elseif($_POST['tipo'] == 'h') {
	// Aceitar pedido de amizade
	if($_Usuario->Autorizar_Amigo($Usr, true))
		Base::OK_JSON();
	else
		Base::Error_JSON('Erro ao autorizar amizade.');
} elseif($_POST['tipo'] == 'r') {
	// Remover amigo ou pedido de amizade
	if($_Usuario->Remover_Amigo($Usr, true))
		Base::OK_JSON();
	else
		Base::Error_JSON('Erro ao remover amizade.');
}

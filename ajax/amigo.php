<?php

namespace GDE;

define('NO_CACHE', true);
define('NO_HTML', true);

require_once('../common/common.inc.php');

if(!isset($_POST['tipo']))
	exit();

//ToDo: Nao retornar numeros magicos!
$Usr = Usuario::Load($_POST['i']);
if($_POST['tipo'] == 'a') {
	// Adicionar novo amigo
	$Usr = Usuario::Load($_POST['i']);
	if($Usr->getID() == $_Usuario->getID())
		echo '1';
	else
		echo ($_Usuario->Adicionar_Amigo($Usr, false, true)) ? '2' : '3';
} elseif($_POST['tipo'] == 'h') {
	// Aceitar pedido de amizade
	echo ($_Usuario->Autorizar_Amigo($Usr, true)) ? '1' : '2';
} elseif($_POST['tipo'] == 'r') {
	// Remover amigo ou pedido de amizade
	echo ($_Usuario->Remover_Amigo($Usr, true)) ? '1': '2';
}

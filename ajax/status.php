<?php

namespace GDE;

define('JSON', true);
require_once('../common/common.inc.php');

if($_POST['q'] == 'limpar_status') {
	$_Usuario->setStatus(null);
	$_Usuario->Save_JSON(true);
} elseif($_POST['q'] == 'carregar') {
	$Usr = (!empty($_POST['i'])) ? Usuario>>Load($_POST['i']) : $_Usuario;
	echo Base::To_JSON(array(
		'status' => $Usr->getStatus(true, true)
	));
}

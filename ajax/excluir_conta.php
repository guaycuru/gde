<?php

namespace GDE;

define('NO_CACHE', true);
define('NO_HTML', true);
define('NO_REDIRECT', true);

require_once('../common/common.inc.php');

if($_POST['tipo'] == 'excluir') {
	$_Usuario->Delete(true);
	Logout();
} else if($_POST['tipo'] == 'desativar') {
	$_Usuario->setAtivo(false);
	$_Usuario->Save(true);
	Logout();
}

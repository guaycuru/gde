<?php

namespace GDE;

define('NO_CACHE', true);
define('NO_HTML', true);
define('NO_REDIRECT', true);

require_once('../common/common.inc.php');

if($_POST['tipo'] == 'a') {
	$Aluno = Aluno::Load($_POST['ra']);
	$_Usuario->addFavoritos($Aluno);
	echo ($_Usuario->Save(true) !== false) ? '1' : '0';
} elseif($_POST['tipo'] == 'r') {
	$Aluno = Aluno::Load($_POST['ra']);
	$_Usuario->removeFavoritos($Aluno);
	echo ($_Usuario->Save(true) !== false) ? '1' : '0';
}

<?php

namespace GDE;

define('NO_CACHE', true);
define('NO_HTML', true);
define('NO_REDIRECT', true);

require_once('../common/common.inc.php');

// tipo = l -> Lido
// tipo = r -> Removido
if(isset($_POST['id']) && $_POST['tipo'] == 'l') {
	$Aviso = Aviso::Load($_POST['id']);
	if($Aviso->getID_Usuario() != $_Usuario->getID())
		die();
	$Aviso->setLido($Aviso->getLido() ? false : true);
	$Aviso->Save(true);
}

if(isset($_POST['id']) && $_POST['tipo'] == 'r') {
	$Aviso = Aviso::Load($_POST['id']);
	if($Aviso->getID_Usuario() != $_Usuario->getID())
		die();
	$Aviso->Delete();
}

echo Aviso::QuantidadeAvisos($_Usuario->getID());

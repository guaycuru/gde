<?php

namespace GDE;

define('JSON', true);
require_once('../common/common.inc.php');

$Amigo = UsuarioAmigo::Load($_POST['id']);
if(($Amigo->getID() == null) || ($Amigo->getUsuario()->getID() != $_Usuario->getID()))
	Base::Error_JSON('Erro');
$Amigo->setApelido(Util::Remover_4_Bytes_Chars(trim($_POST['nome'])));
$Amigo->Save_JSON(true);

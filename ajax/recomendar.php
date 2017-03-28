<?php

namespace GDE;

define('JSON', true);
require_once('../common/common.inc.php');

$Rec = new Recomendacao();
$Rec->setChave();
$Rec->setRecomendante($_Usuario);
$Rec->setEmail($_POST['email']);
$Rec->setRA($_POST['ra']);

$erros = array();
if((strlen($_POST['email']) < 2) || (preg_match('/^[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4}$/i', $_POST['email']) == 0))
	$erros[] = "O email digitado &eacute; inv&aacute;lido.";
if($Rec->Existe_Dados() === true)
	$erros[] = "O email ou o RA digitado j&aacute; est&aacute; cadastrado no sistema.";
if(count($erros) > 0) {
	die(Base::To_JSON(array(
		'ok' => false,
		'erros' => $erros
	)));
} else {
	if($Rec->Recomendar($_Usuario, $_POST['mensagem']) === true)
		die(Base::To_JSON(array(
			'ok' => true
		)));
	else
		die(Base::To_JSON(array(
			'ok' => false,
			'erros' => array('N&atilde;o foi poss&iacute;vel enviar a Recomenda&ccedil;&atilde;o.')
		)));
}

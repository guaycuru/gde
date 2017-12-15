<?php

namespace GDE;

define('JSON', true);

require_once('../common/common.inc.php');

if($_Usuario->getAdmin() === false)
	Base::Error_JSON('Acesso negado!');
	
$Colaboracao = ColaboracaoOferecimento::Load($_POST['id']);
if($Colaboracao->getID() == null)
	Base::Error_JSON('Colaboracao nao encontrada!');
if($_POST['tipo'] == 'a') {
	$Colaboracao->setStatus(ColaboracaoOferecimento::STATUS_AUTORIZADA);
	$Colaboracao->Save_JSON();
} else {
	$Colaboracao->setStatus(ColaboracaoOferecimento::STATUS_RECUSADA);
	$Colaboracao->Save_JSON();
}

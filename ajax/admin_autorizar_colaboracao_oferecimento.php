<?php

namespace GDE;

define('JSON', true);

require_once('../common/common.inc.php');

if($_Usuario->getAdmin() === false)
	Base::Error_JSON('Acesso negado!');
	
$Colaboracao = ColaboracaoOferecimento::Load($_POST['id']);
if($Colaboracao->getID() == null)
	Base::Error_JSON('Colaboracao nao encontrada!');
if($_POST['tipo'] == 'a') { // Autorizar
	$Colaboracao->setStatus(ColaboracaoOferecimento::STATUS_AUTORIZADA);
	$Colaboracao->Copiar(false);
	$Colaboracao->Save_JSON();
} else { // Recusar
	$Colaboracao->setStatus(ColaboracaoOferecimento::STATUS_RECUSADA);
	$Colaboracao->Save_JSON();
}

<?php

namespace GDE;

define('JSON', true);

require_once('../common/common.inc.php');

if($_Usuario->getAdmin() === false)
	Base::Error_JSON('Acesso negado!');

if(empty($_POST['mensagem']))
	Base::Error_JSON('Faltando mensagem!');

$tratamento = explode("\n", $_POST['mensagem']);
$msg_tratada = "<ul>\n";
foreach ($tratamento as $linha)
	$msg_tratada = $msg_tratada."<li>".$linha."</li>\n";
$msg_tratada = $msg_tratada."</ul>";
$Acontecimento = new Acontecimento();
$Acontecimento->setData();
$Acontecimento->setTexto($msg_tratada);
$Acontecimento->setTipo(Acontecimento::TIPO_GDE);
$Acontecimento->Save_JSON();

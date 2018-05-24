<?php

namespace GDE;

define('JSON', true);

require_once('../common/common.inc.php');

if($_POST['tp'] == 'a') { // Adicionar
	if(!empty($_POST['id_evento'])) {
		$Evento = Evento::Load($_POST['id_evento']);
		if($Evento->Pode_Alterar($_Usuario) === false)
			Base::Error_JSON('Acesso negado!');
	} else
		$Evento = new Evento();
	$Evento->setUsuario($_Usuario);
	if(!empty($_POST['oferecimento'])) {
		$Oferecimento = Oferecimento::Load($_POST['oferecimento']);
		if($Oferecimento->getID() == null)
			Base::Error_JSON('Oferecimento não encontrado!');
		$Evento->setOferecimento($Oferecimento);
	}
	$Evento->setNome(trim($_POST['nome']));
	$Evento->setTipo($_POST['tipo']);
	if(isset($_POST['local']))
		$Evento->setLocal($_POST['local']);
	if(isset($_POST['descricao']))
		$Evento->setDescricao($_POST['descricao']);
	if((!isset($_POST['ad']) || $_POST['ad'] != '1') && (isset($_POST['hora_inicio'])))
		$hora_inicio = $_POST['hora_inicio'];
	else
		$hora_inicio = "00:00";
	$Inicio = \DateTime::createFromFormat('d/m/Y H:i', $_POST['data_inicio'].' '.$hora_inicio);
	$Evento->setData_Inicio($Inicio);
	if(isset($_POST['data_fim'])) {
		if((!isset($_POST['ad']) || $_POST['ad'] != '1') && (isset($_POST['hora_fim'])))
			$hora_fim = $_POST['hora_fim'];
		else
			$hora_fim = "00:00";
		if($_POST['data_fim'] != "Data Termino") {
			$Fim = \DateTime::createFromFormat('d/m/Y H:i', $_POST['data_fim'].' '.$hora_fim);
			$Evento->setData_Fim($Fim);
		} else
			$Evento->setData_Fim($Inicio);
	}
	$Evento->setDia_Todo($_POST['ad'] == 'true');
	$Evento->Save_JSON(true);
} elseif($_POST['tp'] == 'e') { // Editar
	$Evento = Evento::Load($_POST['id_evento']);
	if($Evento->Pode_Alterar($_Usuario) === false)
		Base::Error_JSON('Acesso negado!');
	$Inicio = \DateTime::createFromFormat('Y-m-d H:i', $_POST['data_inicio']);
	$Evento->setData_Inicio($Inicio);
	$Evento->setData_Fim($_POST['data_fim']);
	if(isset($_POST['local']))
		$Evento->setLocal($_POST['local']);
	if(isset($_POST['ad']))
		$Evento->setDia_Todo($_POST['ad'] == 'true');
	$Evento->Save_JSON(true);
} elseif($_POST['tp'] == 'r') { // Remover
	$Evento = Evento::Load($_POST['id_evento']);
	if($Evento->Pode_Alterar($_Usuario) === false)
		Base::Error_JSON('Acesso negado!');
	$Evento->Delete_JSON(true);
}

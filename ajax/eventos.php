<?php

namespace GDE;

define('JSON', true);

require_once('../common/common.inc.php');

$Inicio = \DateTime::createFromFormat('U', $_POST['start']);
$Fim = \DateTime::createFromFormat('U', $_POST['end']);
$Eventos = Evento::Listar_Por_Usuario_Datas($_Usuario, $Inicio, $Fim);
$saida = array();
foreach($Eventos as $Evento) {
	switch($Evento->getTipo(false)) {
		case 'f':
			$className = 'calendarioFeriado';
			$bgColor = '#A65D53';
			$borderColor = '#A63121';
			break;
		case 'g':
			$className = 'calendarioGraduacao';
			$bgColor = '#7CBFAB';
			$borderColor = '#21A67E';
			break;
		case 'o':
			$className = 'calendarioOutro';
			$bgColor = '#738ABF';
			$borderColor = '#2149A6';
			break;
		case 'p':
			$className = 'calendarioProva';
			$bgColor = '#A6985B';
			$borderColor = '#99661F';
			break;
		case 't':
			$className = 'calendarioTrabalho';
			$bgColor = '#628040';
			$borderColor = '#2C4D08';
			break;
		default:
			$className = 'calendarioGraduacao';
			$bgColor = '#7CBFAB';
			$borderColor = '#21A67E';
			break;
	}	

	$saida[] = array(
		'id' => $Evento->getID(),
		'title' => $Evento->getNome(false),
		'start' => $Evento->getData_Inicio('Y-m-d H:i'),
		'end' => $Evento->getData_Fim('Y-m-d H:i'),
		'className' => $className,
		'backgroundColor' => $bgColor,
		'borderColor' => $borderColor,
		'allDay' => $Evento->getDia_Todo()
	);
}

$Amigos = UsuarioAmigo::Listar_Aniversarios($_Usuario, $Inicio, $Fim);
$className = 'calendarioAniversario';
$bgColor = '#9459B3';
if(!isset($borderColor))
	$borderColor = '';

foreach($Amigos as $Amigo) {
	$saida[] = array(
		'title' => 'Aniversario '.$Amigo->Apelido_Ou_Nome(false),
		'start' => $Inicio->format('Y').$Amigo->getAmigo()->getData_Nascimento('-m-d'),
		'className' => $className,
		'backgroundColor' => $bgColor,
		'borderColor' => $borderColor,
		'allDay' => true,
		'editable' => false
	);
}

echo Base::To_JSON($saida);

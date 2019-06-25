<?php

namespace GDE;

define('NO_CACHE', true);
define('NO_HTML', true);
define('NO_REDIRECT', true);

require_once('../common/common.inc.php');

$Aluno = $_Usuario->getAluno(true);

$Periodo_Selecionado = isset($_POST['periodo']) && ($_POST['periodo'] > 0) ? Periodo::Load($_POST['periodo']) : Periodo::getAtual();
if($Periodo_Selecionado->getID() == null)
	die("Periodo nao cadastrado!\n");

$nivel = (isset($_GET['n'])) ? $_GET['n'][0] : 'G';

$nomeCalendario = $_POST['nomeCalendario'];
$idCalendario = $_POST['idCalendario'];
$datasImportantes = $_POST['datasImportantes'];

// Client da API
$Calendar = new GoogleCalendar;

// Se nao temos o token, deu ruim
if(empty($_SESSION['token'])) {
	die("Sem token");
} else {
	$Calendar->setTokenAcesso('', $_SESSION['token']);
	// Servico da API do Calendar
	$Calendar->setServico();
	// Se precisa criar um calendario
	if($idCalendario == null) {
		$idCalendario = $Calendar->criaCalendario($nomeCalendario);
	}

	$Horario = $Aluno->Monta_Horario($Periodo_Selecionado->getPeriodo(), $nivel);

	try {
		$Calendar->adicionaHorario($idCalendario, $Horario, $Periodo_Selecionado);
		if($datasImportantes) {
			$Calendar->adicionaCalendarioUnicamp($idCalendario, $Periodo_Selecionado);
		}
	} catch(\Exception $E) {
		if(function_exists('exception_handler'))
			exception_handler($E);
		die("Erro");
	}

	// reseta o token
	unset($_SESSION['token']);
}

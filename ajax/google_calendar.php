<?php

namespace GDE;

define('NO_CACHE', true);
define('NO_HTML', true);
define('NO_REDIRECT', true);

require_once('../common/common.inc.php');

$ra = (isset($_POST['ra'])) ? intval($_POST['ra']) : -1;

$Aluno = ($ra > 0) ? Aluno::Load($ra) : $_Usuario->getAluno(true);
$UsuarioAluno = ($Aluno->getUsuario(false) !== null) ? $Aluno->getUsuario(false) : new Usuario();
$pode_ver = $_Usuario->Pode_Ver($UsuarioAluno, 'horario');
if($pode_ver !== true)
	exit;


$Periodo_Selecionado = isset($_POST['periodo']) && ($_POST['periodo'] > 0) ? Periodo::Load($_POST['periodo']) : Periodo::getAtual();
if($Periodo_Selecionado->getID() == null)
	die("Periodo '".$periodo."' nao cadastrado!\n");

$nivel = (isset($_GET['n'])) ? $_GET['n'][0] : 'G';

$nomeCalendario = $_POST['nomeCalendario'];
$idCalendario = $_POST['idCalendario'];
$datasImportantes = $_POST['datasImportantes'];

// Client da API
$Calendar = new GooglCalendar;

// Se nao temos o token, deu ruim
if(empty($_SESSION['token'])) {
	echo "Sem token";
} else {
	$Calendar->setTokenAcesso('', $_SESSION['token']);
	// Servico da API do Calendar
	$Calendar->setServico();
	// Se precisa criar um calendario
	if($idCalendario == null) {
		$idCalendario = $Calendar->criaCalendario($nomeCalendario);
	}

	$Horario = $Aluno->Monta_Horario($Periodo_Selecionado->getPeriodo(), $nivel);

	$Calendar->adicionaHorario($idCalendario, $Horario, $Periodo_Selecionado);
	if($datasImportantes) {
		$Calendar->adicionaCalendarioUnicamp($idCalendario, $Periodo_Selecionado);
	}

	// reseta o token
	unset($_SESSION['token']);
}

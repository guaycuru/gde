<?php

namespace GDE;

define('NO_CACHE', true);
define('NO_HTML', true);
define('NO_REDIRECT', true);
define('NO_DENIAL', true);

require_once('../common/common.inc.php');

if ($_Usuario === null || $_Usuario->getID() == null) {
	http_response_code(401);
	header('Content-Type: text/plain; charset=utf-8');
	echo "Usuario nao autenticado.\n";
	exit;
}

$idPlanejado = 0;
if (isset($_GET['id'])) {
	$idPlanejado = intval($_GET['id']);
} elseif (isset($_GET['id_planejado'])) {
	$idPlanejado = intval($_GET['id_planejado']);
} elseif (isset($_POST['id'])) {
	$idPlanejado = intval($_POST['id']);
} elseif (isset($_POST['id_planejado'])) {
	$idPlanejado = intval($_POST['id_planejado']);
}

$incluirUnicamp = true;
if (isset($_GET['unicamp'])) {
	$incluirUnicamp = ($_GET['unicamp'] === '1' || $_GET['unicamp'] === 'true');
} elseif (isset($_POST['unicamp'])) {
	$incluirUnicamp = ($_POST['unicamp'] === '1' || $_POST['unicamp'] === 'true');
} elseif (isset($_POST['datasImportantes'])) {
	$incluirUnicamp = (bool)$_POST['datasImportantes'];
}

$Exporter = new CalendarExporter();
$siglaPeriodo = 'planejador';

if ($idPlanejado > 0) {
	$Planejado = Planejado::Load($idPlanejado);
	if ($Planejado->getID() == null || $Planejado->Pertence_A($_Usuario) === false) {
		http_response_code(403);
		header('Content-Type: text/plain; charset=utf-8');
		echo "Planejamento nao encontrado ou sem permissao de acesso.\n";
		exit;
	}
	$Periodo = $Planejado->getPeriodo(false) ?: Periodo::getAtual();
	if ($Periodo !== null && $Periodo->getID() !== null) {
		$siglaPeriodo = preg_replace('/[^A-Za-z0-9_-]/', '', $Periodo->getNome_DAC());
	}
	$ics = $Exporter->exportarPlanejado($Planejado, array(
		'incluir_calendario_unicamp' => $incluirUnicamp,
	));
} else {
	$periodoParam = isset($_GET['periodo']) ? intval($_GET['periodo']) : (isset($_POST['periodo']) ? intval($_POST['periodo']) : 0);
	$Periodo_Selecionado = ($periodoParam > 0) ? Periodo::Load($periodoParam) : Periodo::getAtual();
	if ($Periodo_Selecionado === null || $Periodo_Selecionado->getID() == null) {
		http_response_code(404);
		header('Content-Type: text/plain; charset=utf-8');
		echo "Periodo nao cadastrado!\n";
		exit;
	}
	$siglaPeriodo = preg_replace('/[^A-Za-z0-9_-]/', '', $Periodo_Selecionado->getNome_DAC());
	$nivel = isset($_GET['n']) ? $_GET['n'][0] : Disciplina::NIVEL_GRAD;

	$Aluno = $_Usuario->getAluno(false);
	if ($Aluno !== null) {
		$ics = $Exporter->exportarHorarioAluno($Aluno, $Periodo_Selecionado, array(
			'incluir_calendario_unicamp' => $incluirUnicamp,
			'nivel' => $nivel,
		));
	} else {
		$Planejados = null;
		$Planejado = Planejado::Algum($_Usuario, $Periodo_Selecionado->getPeriodo(false), $Planejados);
		$ics = $Exporter->exportarPlanejado($Planejado, array(
			'incluir_calendario_unicamp' => $incluirUnicamp,
		));
	}
}

$modo = isset($_GET['modo']) ? $_GET['modo'] : 'download';
header('Content-Type: text/calendar; charset=utf-8');
if ($modo !== 'raw') {
	header('Content-Disposition: attachment; filename="gde_horarios_' . $siglaPeriodo . '.ics"');
}
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');
echo $ics;
exit;

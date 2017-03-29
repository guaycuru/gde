<?php

namespace GDE;

define('NO_CACHE', true);
define('NO_HTML', true);
define('NO_REDIRECT', true);

header('Content-type: application/json');

require_once('../common/common.inc.php');

if(!isset($_POST['tp']))
	exit();

$res = array();
$total = 0;

if($_POST['tp'] == 1)
	$Disciplinas = Disciplina::Consultar(array("sigla" => urldecode($_POST['q'])), null, $total);
elseif($_POST['tp'] == 2)
	$Disciplinas = Disciplina::Consultar(array("nome" => urldecode($_POST['q'])), null, $total);
else
	$Disciplinas = Disciplina::Consultar(array("sigla" => urldecode($_POST['q']), "nome" => urldecode($_POST['q'])), null, $total, '-1', '-1', 'OR');

foreach($Disciplinas as $Disciplina)
	if($_POST['tp'] == 1)
		$res[] = $Disciplina->getSigla();
	elseif($_POST['tp'] == 2)
		echo $Disciplina->getNome()."\n";
	else
		$res[] = array('sigla' => $Disciplina->getSigla(), 'nome' => $Disciplina->getSigla().' - '.$Disciplina->getNome(false, true));

if($_POST['tp'] != 2)
	echo json_encode(array('ok' => true, 'total' => $total, 'resultados' => $res));

<?php

namespace GDE;

define('JSON', true);

if(isset($_POST['token'])) {
	if(DAC::Validar_Token($_POST['token']) === false)
		die('ERRO');
	define('NO_LOGIN_CHECK', true);
}

require_once('../common/common.inc.php');

$total = 0;
$ret = array();

$Professores = Professor::Consultar_Simples($_POST['q'], null, $total);

foreach($Professores as $Professor) {
	if(isset($_POST['i']))
		$ret[] = array('nome' => $Professor->getNome(true).' ('.(($Professor->getInstituto(false) === null) ? 'Desconhecido' : $Professor->getInstituto()->getSigla(true).' - '.$Professor->getInstituto()->getNome(true)).')', 'id' => $Professor->getID());
	else
		$ret[] = array('nome' => $Professor->getNome(true));
}

echo Base::To_JSON(array(
	'ok' => true,
	'total' => $total,
	'resultados' => $ret
));

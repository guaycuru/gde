<?php

namespace GDE;

define('NO_CACHE', true);
define('NO_LOGIN_CHECK', true);
define("NO_HTML", true);

require_once('../common/common.inc.php');

if(empty($_GET['c']))
	exit;

$catalogo = (!empty($_GET['a'])) ? $_GET['a'] : null;
$Modalidades = Modalidade::Listar('G', $_GET['c'], $catalogo);

if(count($Modalidades) == 0)
	$opcoes = "<option value=\"\">-</option>";
else {
	$opcoes = "";
	if((isset($_GET['o'])) && ($_GET['o'] == 0))
		$opcoes .= "<option value=\"\">Indiferente</option>";
	foreach($Modalidades as $Modalidade)
		$opcoes .= "<option value=\"".$Modalidade->getSigla(true)."\"".((!empty($_GET['s'])) && ($Modalidade->getSigla(false) == $_GET['s'])?" selected=\"selected\"":null).">".$Modalidade->getSigla(true)." - ".$Modalidade->getNome(true)."</option>";
}

echo "<select id=\"modalidade\" name=\"modalidade\">".$opcoes."</select>";

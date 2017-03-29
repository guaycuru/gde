<?php

namespace GDE;

define('NO_CACHE', true);
define('NO_HTML', true);
define('NO_REDIRECT', true);

require_once('../common/common.inc.php');

if(!isset($_POST['c']))
	exit();

if($_POST['c'] < 0)
	$Cardapio = Cardapio::Atual();
else
	$Cardapio = Cardapio::Load($_POST['c']);

$id_anterior = $Cardapio->ID_Anterior();
$id_proximo = $Cardapio->ID_Proximo();

echo ($Cardapio->getID() != null) ? $Cardapio->Formatado()."<br />" : "<strong>N&atilde;o foi poss&iacute;vel carregar o card&aacute;pio...</strong><br />";
if($id_anterior !== false)
	echo "<a href=\"#\" onclick=\"return Cardapio_Muda('".$id_anterior."');\">&laquo;</a>";
if($Cardapio->getID() != null)
	echo ' <a href="'.CONFIG_URL.'ajax/cardapios.php?d='.$Cardapio->getData('Y-m-d').'" id="cardapio_semana">Semana</a>';
if($id_proximo !== false)
	echo " <a href=\"#\" onclick=\"return Cardapio_Muda('".$id_proximo."');\">&raquo;</a>";

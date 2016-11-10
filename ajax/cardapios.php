<?php

define('NO_CACHE', true);
define('NO_HTML', true);
define('NO_REDIRECT', true);

require_once('../common/common.inc.php');

$data = strtotime($_GET['d']);

$ds = date('w', $data);

if($ds == 6)
	$df = 2;
elseif($ds == 0)
	$df = 1;
else
	$df = 1 - $ds;

$data_inicial = date('Y-m-d', mktime(12, 0, 0, date('m', $data), date('d', $data)+$df));
$data_final = date('Y-m-d', mktime(12, 0, 0, date('m', $data), date('d', $data)+$df+4));

$cardapios = Carrega_Cardapios($data_inicial, $data_final);

?>
<table width="95%" align="center" border="1">
<?php

foreach($cardapios as $k => $cardapio) {
	$d = $_GDE['DB']->UserDate($cardapio['data'], "w") + 1;
	if($cardapio['tipo'] == 1)
		echo "\r\n	<tr>\r\n		<td colspan=\"2\" align=\"center\"><strong>".$d."&ordf;-feira (".$_GDE['DB']->UserDate($cardapio['data'], "d/m/Y").")</strong></td>\r\n	</tr><tr>\r\n		<td align=\"center\"><strong>Almo&ccedil;o</strong></td>\r\n		<td align=\"center\"><strong>Jantar</strong></td>\r\n	</tr>\r\n	<tr>";
	echo "\r\n<td>".Formata_Cardapio($cardapio, false)."</td>";
	if($cardapio['tipo'] == 2)
		echo "\r\n	</tr>";
}

?>
</table>
<?php

namespace GDE;

define('NO_CACHE', true);
define('NO_HTML', true);
define('NO_REDIRECT', true);

require_once('../common/common.inc.php');

$data = (!empty($_GET['d'])) ? strtotime($_GET['d']) : time();

$Cardapios = Cardapio::Cardapios_Semana($data);

?>
<table width="95%" align="center" border="1">
<?php

foreach($Cardapios as $k => $Cardapio) {
	if($Cardapio->getTipo(false) == Cardapio::TIPO_ALMOCO)
		echo "\r\n	<tr>\r\n		<td colspan=\"2\" align=\"center\"><strong>".$Cardapio->Dia_Da_Semana()." (".$Cardapio->getData("d/m/Y").")</strong></td>\r\n	</tr><tr>\r\n		<td align=\"center\"><strong>Almo&ccedil;o</strong></td>\r\n		<td align=\"center\"><strong>Jantar</strong></td>\r\n	</tr>\r\n	<tr>";
	echo "\r\n<td>".$Cardapio->Formatado(false)."</td>";
	if($Cardapio->getTipo(false) == Cardapio::TIPO_JANTAR)
		echo "\r\n	</tr>";
}

?>
</table>

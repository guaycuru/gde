<?php

namespace GDE;

define('NO_CACHE', true);
define('NO_HTML', true);
define('NO_REDIRECT', true);

require_once('../common/common.inc.php');

$data = (!empty($_GET['d'])) ? strtotime($_GET['d']) : time();

$Cardapios = Cardapio::Cardapios_Semana($data);

?>
<table align="center" border="1">
<?php

$ultima = null;
foreach($Cardapios as $k => $Cardapio) {
	if($ultima != $Cardapio->getData('U')) {
		if($k > 0) { ?>
	</tr>
		<?php } ?>
	<tr>
		<td colspan="3" align="center"><strong><?= $Cardapio->Dia_Da_Semana(); ?> (<?= $Cardapio->getData("d/m/Y"); ?>)</strong></td>
	</tr>
	<tr>
	<?php } ?>
		<td width="30%">
			<h2><?= $Cardapio->getTipo(true); ?></h2>
			<?= $Cardapio->Formatado(false); ?>
		</td>
	<?php $ultima = $Cardapio->getData('U');
}

?>
	</tr>
</table>

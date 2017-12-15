<?php

namespace GDE;

define('TITULO', 'Admin - Acontecimento');

require_once('../common/common.inc.php');

if($_Usuario->getAdmin() === false)
	die("Voc&ecirc; n&atilde;o tem permiss&atilde;o para acessar esta p&aacute;gina!");
?>

<h1>Acontecimentos</h1><br />
<h2>OBS: Use para postar um acontecimento geral no GDE.</h2>
<form method="post" class="auto-form" action="<?= CONFIG_URL; ?>ajax/admin_acontecimento.php" data-destino="<?= CONFIG_URL; ?>">
<table border="0">
<tr>
	<td>Mensagem:</td>
	<td>
		<textarea name="mensagem" rows="10" cols="50"></textarea>
	</td>
</tr>
<tr>
	<td colspan="2"><input type="submit" name="enviar" value="OK" class="botao_salvar" /></td>
</tr>
</table>
</form>
<?= $FIM; ?>

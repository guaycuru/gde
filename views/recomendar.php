<?php

namespace GDE;

define('TITULO', 'Recomendar o GDE');

require_once('../common/common.inc.php');

$Aluno = (isset($_GET['ra'])) ? Aluno::Load($_GET['ra']) : false;

?>
<h2>Recomendar o GDE</h2>
<form method="post" action="<?= CONFIG_URL; ?>ajax/recomendar.php" target="controle">
	<table border="0">
		<tr>
			<td>RA:</td>
			<td><input type="text" name="ra" value="<?= ($Aluno !== false) ? $Aluno->getRA(true)."\" readOnly=\"readonly\"" : null; ?>" /></td>
		</tr>
		<tr>
			<td>Email:</td>
			<td><input type="text" name="email" /><?= ($Aluno !== false) ? "<i>".$Aluno->getEmailDAC(true)."</i>" : null; ?> <i>Use o email principal do usu&aacute;rio.</i></td>
		</tr>
		<tr>
			<td>Mensagem:</td>
			<td><textarea name="mensagem" cols="50" rows="10"></textarea><br /><textarea cols="50" rows="10" disabled="disabled"><?= Recomendacao::getFinal($_Usuario, 'XXXXXXXXXXXXXXXX'); ?></textarea></td>
		</tr>
		<tr>
			<td colspan="2"><input type="submit" name="enviar" class="botao_enviar" value="Recomendar" /></td>
		</tr>
	</table>
</form>
<?php
echo $FIM;
?>

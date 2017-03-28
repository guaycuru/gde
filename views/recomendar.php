<?php

namespace GDE;

define('TITULO', 'Recomendar o GDE');

require_once('../common/common.inc.php');

$Aluno = (isset($_GET['ra'])) ? Aluno::Load($_GET['ra']) : false;

?>
<script type="text/javascript">
// <![CDATA[
$(document).ready(function() {
	$("#form_recomendacao").submit(function() {
		$.post(CONFIG_URL + 'ajax/recomendar.php', $(this).serialize(), function(res) {
			if((res) && (res.ok)) {
				var msg = 'Sua Recomenda&ccedil;&atilde;o foi enviada com sucesso! O GDE agradece!!!';
				$.guaycuru.confirmacao(msg, CONFIG_URL + "/index/");
			} else {
				var erro = ((res) && (res.erros)) ? res.erros.join(' ') : 'Um erro desconhecido ocorreu. Por favor, tente novamente.';
				$.guaycuru.confirmacao(erro);
			}
		});
		return false;
	});
});
// ]]>
</script>
<h2>Recomendar o GDE</h2>
<form id="form_recomendacao">
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
<?= $FIM; ?>

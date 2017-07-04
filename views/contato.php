<?php

namespace GDE;

define('TITULO', 'Contato');

require_once('../common/common.inc.php');

?>
<script type="text/javascript">
	// <![CDATA[
	$(document).ready(function() {
		$("#form_contato").submit(function() {
			$.post(CONFIG_URL + 'ajax/contato.php', $(this).serialize(), function(res) {
				if((res) && (res.ok)) {
					var msg = 'Sua mensagem foi enviada com sucesso! O GDE agradece!!!';
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
	Antes de enviar sua mensagem, consulte as "<a href="https://github.com/guaycuru/gde/wiki/FAQ">Perguntas Frequentes</a>", talvez voc&ecirc; encontre respostas...<br /><br />
	<h2>Recomendar o GDE</h2>
	<form id="form_contato">
		<table border="0">
			<tr>
				<td>Assunto:</td>
				<td><select name="assunto"><option value="Elogio">Elogio</option><option value="Sugestao">Sugest&atilde;o</option><option value="Duvida">D&uacute;vida</option><option value="Reclamacao">Reclama&ccedil;&atilde;o</option><option value="BUG">Problema / BUG</option></select></td>
			</tr>
			<tr>
				<td>Mensagem:</td>
				<td><textarea name="mensagem" cols="50" rows="10"></textarea></td>
			</tr>
			<tr>
				<td colspan="2"><input type="submit" name="enviar" value=" " alt="Enviar" class="botao_enviar" /></td>
			</tr>
		</table>
	</form>
<?= $FIM; ?>

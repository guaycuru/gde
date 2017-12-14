<?php

namespace GDE;

define('TITULO', 'Contato');

require_once('../common/common.inc.php');

?>
Antes de enviar sua mensagem, por favor consulte as "<a href="https://github.com/guaycuru/gde/wiki/FAQ">Perguntas Frequentes</a>". Se sua dúvida estiver ali, sua mensagem não será respondida!<br /><br />
<h2>Contato</h2>
<form class="auto-form" method="post" action="<?= CONFIG_URL; ?>ajax/contato.php" data-sucesso="Sua mensagem foi enviada com sucesso. O GDE agradece!" data-destino="<?= CONFIG_URL; ?>">
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

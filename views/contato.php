<?php

namespace GDE;

define('TITULO', 'Contato');

require_once('../common/common.inc.php');

?>
	Antes de enviar sua mensagem, consulte as "<a href="<?= CONFIG_URL; ?>faq/">Perguntas Frequentes</a>", talvez voc&ecirc; encontre respostas...<br /><br />
	<h2>Recomendar o GDE</h2>
	<form method="post" action="<?= CONFIG_URL; ?>ajax/contato/" target="controle">
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

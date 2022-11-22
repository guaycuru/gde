<?php

namespace GDE;

define('TITULO', 'Admin - Super P&aacute;gina!!!');

require_once('../common/common.inc.php');

if($_Usuario->getAdmin() === false)
	die("Voc&ecirc; n&atilde;o tem permiss&atilde;o para acessar esta p&aacute;gina!");

$colaboracoes_professores_pendentes = ColaboracaoProfessor::Numero(ColaboracaoProfessor::STATUS_PENDENTE);
$colaboracoes_oferecimentos_pendentes = ColaboracaoOferecimento::Numero(ColaboracaoProfessor::STATUS_PENDENTE);

$erros = iterator_count(new \FilesystemIterator(__DIR__.'/../errors/', \FilesystemIterator::SKIP_DOTS)) - 1;
?>
<script type="text/javascript">
var debug = function(d) {
	$.post("<?= CONFIG_URL; ?>ajax/admin.php", {debug: d}, function(res) {
		if(res && res.ok) {
			alert('Nivel de debug alterado com sucesso!');
			document.location = '<?= CONFIG_URL; ?>admin/';
		} else
			alert('Erro!');
	});
};
$(document).ready(function() {
	$("#botao_atualizar").click(function() {
		$.guaycuru.aguarde();
		var curss = $("#curss").is(':checked') ? 1 : 0;
		var ranks = $("#ranks").is(':checked') ? 1 : 0;
		$.post("<?= CONFIG_URL; ?>ajax/admin.php", {info: 1, curss: curss, ranks: ranks}, function(res) {
			if(res && res.ok) {
				alert('Dados atualizados com sucesso!');
				document.location = '<?= CONFIG_URL; ?>admin/';
			} else
				alert('Erro!');
		});
	});
});
</script>
<?= "<br />DEBUG: ".(((!isset($_SESSION['admin']['debug'])) || ($_SESSION['admin']['debug'] == 0))?"<b>Desativado</b>":"<a href=\"#\" onClick=\"debug(0);\">Desativar</a>")." | ".(((isset($_SESSION['admin']['debug'])) && ($_SESSION['admin']['debug'] == 1))?"<b>Ativado Nivel 1</b>":"<a href=\"#\" onClick=\"debug(1);\">Ativar Nivel 1</a>")." | ".(((isset($_SESSION['admin']['debug'])) && ($_SESSION['admin']['debug'] == 2))?"<b>Ativado Nivel 2</b>":"<a href=\"#\" onClick=\"debug(2);\">Ativar Nivel 2</a>")." | ".(((isset($_SESSION['admin']['debug'])) && ($_SESSION['admin']['debug'] == 3))?"<b>Ativado Nivel 3</b>":"<a href=\"#\" onClick=\"debug(3);\">Ativar Nivel 3</a>"); ?>
<br /><br />
<input type="checkbox" id="info" name="info" value="t" disabled="disabled" checked="checked" />Atualizar Informa&ccedil;&otilde;es<br />
<input type="checkbox" id="ranks" name="ranks" value="t" />Atualizar Rankings - LENTO<br />
<input type="checkbox" id="curss" name="curss" value="t" />Atualizar Cursa&ccedil;&otilde;es e Reprova&ccedil;&otilde;es - BEM LENTO<br />
<input type="button" id="botao_atualizar" class="botao_ok" /><br /><br />
<form method="post" class="auto-form" action="<?= CONFIG_URL; ?>ajax/admin.php" data-sucesso="SU executado com sucesso!" data-destino="<?= CONFIG_URL; ?>">
	SU: <input type="text" name="su">
</form><br /><br />
<table class="tabela_bonyta_branca tabela_busca" style="width: 50%">
	<tr>
		<th>Tipo</th>
		<th>Total</th>
		<th>Pendentes</th>
	</tr>
	<tr>
		<td><label>Colabora&ccedil;&otilde;es Professor</label></td>
		<td><?= ColaboracaoProfessor::Numero(null); ?></td>
		<td><?= (($colaboracoes_professores_pendentes > 0) ? "<a href=\"".CONFIG_URL."admin-colaboracoes-professor\" target=\"_blank\">Autorizar ".$colaboracoes_professores_pendentes."</a>" : "Nada pendente"); ?></td>
	</tr>
	<tr>
		<td><label>Colabora&ccedil;&otilde;es Oferecimento</label></td>
		<td><?= ColaboracaoOferecimento::Numero(); ?></td>
		<td><?= (($colaboracoes_oferecimentos_pendentes > 0) ? "<a href=\"".CONFIG_URL."admin-colaboracoes-oferecimento\" target=\"_blank\">Autorizar ".$colaboracoes_oferecimentos_pendentes."</a>" : "Nada pendente"); ?></td>
	</tr>
</table>
<br /><br />Erros: <a href="<?= CONFIG_URL; ?>admin-erros/" target="_blank"><?= $erros; ?></a>
<br /><br />Result cache dispon&iacute;vel: <?= (RESULT_CACHE_AVAILABLE) ? 'Sim' : 'N&atilde;o'; ?>
<?= $FIM; ?>

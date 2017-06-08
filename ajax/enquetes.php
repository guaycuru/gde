<?php

namespace GDE;

define('NO_CACHE', true);
define('NO_HTML', true);
define('NO_REDIRECT', true);

require_once('../common/common.inc.php');

if(empty($_GET['id']))
	exit();

$Enquete = Enquete::Load($_GET['id']);
$resultado = ((isset($_GET['res'])) || ($Enquete->Ja_Votou($_Usuario)) || ($Enquete->getAtiva() === false));
?>
<script type="text/javascript">
$.tablesorter.addParser({
	id: 'votos',
	is: function(s) { return false; },
	format: function(s) {
		var tmp = s.split(" ");
		return tmp[0];
	},
	type: 'numeric'
});
var resultados = function() {
	$("div.enquete_content").Carregando();
	$("div.enquete_content").load('<?= CONFIG_URL; ?>ajax/enquetes.php?id=<?= $Enquete->getID(); ?>&res');
	return false;
};
$(document).ready(function() {
	$("#ver_resultados").click(resultados);
	$("#atualizar").click(resultados);
});
</script>
<div class="enquete_content" style="width: 640px; height: 480px;">
<?php if(!$resultado) { ?>
<form method="post" class="auto-form" action="<?= CONFIG_URL; ?>ajax/enquete.php" data-destino="<?= CONFIG_URL; ?>" data-sucesso="Voto computado com sucesso! Obrigado!">
<input type="hidden" name="enquete" value="<?= $Enquete->getID(); ?>" />
<?php } ?>
<table border="1" id="table_enquete" class="tabela_bonyta" width="95%">
<thead>
	<tr>
		<td align="center" colspan="<?= ($resultado) ? 3 : 1; ?>"><strong><?= $Enquete->getPergunta(false); ?></strong></td>
	</tr>
<?php if($resultado) { ?>
	<tr>
		<th align="center" width="75%"><strong>Op&ccedil;&atilde;o</strong></td>
		<th align="center" width="15%"><strong>Votos</strong></td>
		<td align="center"><strong>-</strong></td>
	</tr>
</thead>
<tbody>
<?php
	$total_votos = $Enquete->Numero_Votos();
	$total_usuarios = $Enquete->Numero_Usuarios();
	foreach($Enquete->getOpcoes() as $Opcao) {
		$votos = $Opcao->Numero_Votos();
		$porcentagem = $Opcao->Porcentagem(2);
?>
	<tr>
		<td><?= $Opcao->getOpcao(false); ?></td>
		<td><?= $votos; ?> (<?= $porcentagem; ?>%)</td>
		<td><img src="<?= CONFIG_URL; ?>web/images/barra.gif" alt="" width="<?= ceil($porcentagem * 2); ?>" height="12" /></td>
	</tr>
<?php } ?>
</tbody>
<tfoot>
	<tr>
		<td colspan="3" align="center">Total de Votos: <?= $total_votos; ?> (<?= $total_usuarios; ?> Usu&aacute;rios)</td>
	</tr>
</tfoot>
<?php } else { ?>
</thead>
<tbody>
<?php foreach($Enquete->getOpcoes() as $Opcao) { ?>
	<tr>
		<td><input type="<?= (($Enquete->getMax_Votos() > 1) ? "checkbox" : "radio"); ?>" class="opcao" name="<?= (($Enquete->getMax_Votos() > 1) ? "votos[]" : "voto"); ?>" value="<?= $Opcao->getID(); ?>" id="opcao_<?= $Opcao->getID(); ?>" /><label for="opcao_<?= $Opcao->getID(); ?>"><?= $Opcao->getOpcao(false); ?></label></td>
	</tr>
<?php } ?>
</tbody>
<tfoot>
	<tr>
		<td align="center"><input type="submit" name="votar" value=" " class="botao_salvar" /><br />
		<a href="#" id="ver_resultados">Ver Resultados</a></td>
	</tr>
</tfoot>
<?php } ?>
</table>
<?php if(!$resultado) { ?>
</form>
<?php } else { ?>
<div align="center"><a href="#" id="atualizar">Atualizar</a></div>
<?php } ?>
</div>

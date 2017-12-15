<?php

namespace GDE;

define('TITULO', 'Autorizar Colabora&ccedil;&otilde;es');

require_once('../common/common.inc.php');

if($_Usuario->getAdmin() === false)
	die("Voc&ecirc; n&atilde;o tem permiss&atilde;o para acessar esta p&aacute;gina!");

$Pendentes = ColaboracaoOferecimento::Pendentes();

if(count($Pendentes) < 1)
	die('Nenhuma autoriza&ccedil;&atilde;o pendente'.$FIM);

?>
<script language="javascript" type="text/javascript">
// <![CDATA[
$(document).ready(function(){
	$("#menuAccordion").accordion({
		autoHeight: false,
		navigation: true,
		collapsible: true
	});
	
	$("span.ui-icon").css({'display': 'none'});
	
	$("a.autorizar").click(function() {
		var id = $(this).data("id");
		$.post("<?= CONFIG_URL; ?>ajax/admin_autorizar_colaboracao_oferecimento.php", {id: id, tipo: 'a'}, function(res) {
			if(res && res.ok) {
				$("#autorizar_"+id).hide();
				$("#recusar_"+id).hide();
				$("#estado_"+id).text("Autorizado!");
			} else
				$("#estado_" + id).text("Erro!");
		});
		return false;
	});
	
	$("a.recusar").click(function() {
		var id = $(this).data("id");
		$.post("<?= CONFIG_URL; ?>ajax/admin_autorizar_colaboracao_oferecimento.php", {id: id, tipo: 'r'}, function(res) {
			if(res && res.ok) {
				$("#autorizar_"+id).hide();
				$("#recusar_"+id).hide();
				$("#estado_"+id).text("Recusado!");
			} else
				$("#estado_" + id).text("Erro!");
		});
		return false;
	});
});

// ]]>
</script>
<div id="menuAccordion" class="gde_jquery_ui">
<?php
	foreach($Pendentes as $Colaboracao) {
		$campo = $Colaboracao->getCampo(true);
		$valor = $Colaboracao->getValor(true);
		if($Colaboracao->getOferecimento(false) === null)
			continue;
		
?>
	<h3 style="padding: 5px">Colabora&ccedil;&atilde;o Oferecimento</h3>
	<div>
		<table cellspacing="0" class="tabela_bonyta_branca">
			<tr>
				<td width="20%"><strong>Oferecimento:</strong></td>
				<td><a href="<?= CONFIG_URL; ?>oferecimento/<?= $Colaboracao->getOferecimento()->getID(); ?>" target="_blank"><?= $Colaboracao->getOferecimento()->getDisciplina()->getSigla().$Colaboracao->getOferecimento()->getTurma()." - ".$Colaboracao->getOferecimento()->getPeriodo()->getNome(); ?></a></td>
			</tr>
			<tr>
				<td width="20%"><strong>Usuario Colaborador:</strong></td>
				<td><?= $Colaboracao->getUsuario()->getNome_Completo(); ?></td>
			</tr>
			<tr>
				<td width="20%"><strong>Campo:</strong></td>
				<td><?= $campo; ?></td>
			</tr>
			<tr>
				<td width="20%"><strong>Valor:</strong></td>
				<td>
				<?php if($campo == "pagina") { ?>
					<a href="<?= $valor; ?>" target="_blank"><?= $valor; ?></a>
				<?php } else { ?>
					<?= $valor; ?>
				<?php } ?>
				</td>
			</tr>
			<tr>
				<td width="20%"><strong>Data:</strong></td>
				<td><?= $Colaboracao->getData('d/m/Y'); ?></td>
			</tr>
			<tr>
				<td width="20%"><strong>Estado:</strong></td>
				<td><a href="#" class="autorizar" data-id="<?= $Colaboracao->getID() ?>" id="autorizar_<?= $Colaboracao->getID() ?>">Autorizar</a> <a href="#" class="recusar" data-id="<?= $Colaboracao->getID()?>" id="recusar_<?= $Colaboracao->getID() ?>">Recusar</a><label id="estado_<?= $Colaboracao->getID()?>"></label></td>
			</tr>
		</table>
	</div>
<?php } ?>
</div>
<?= $FIM; ?>

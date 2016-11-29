<?php

namespace GDE;

if(isset($_GET['cm'])) {
	require_once('../common/config.inc.php');
	die("<img src='" . CONFIG_URL . "web/images/loading.gif' /> Carregando Mapa...");
}

define('TITULO', 'Sala');

require_once('../common/common.inc.php');

if(empty($_GET['id']))
	exit();

$Sala = Sala::Por_Nome($_GET['id']);
if($Sala === null)
	die('Sala n&atilde;o encontrada...'.$FIM);

$link = null;
$Predio = new Instituto();
$Unidade = new Instituto();
if($Sala->getID_Predio() != null) {
	$Predio = Instituto::Por_ID_Unidade($Sala->getID_Predio());
	$link = $Predio->getLink_Mapa();
}

if($Sala->getID_Unidade() != null) {
	$Unidade = Instituto::Por_ID_Unidade($Sala->getID_Unidade());
	if($link == null)
		$link = $Unidade->getLink_Mapa();
}

$mapa_encontrado = ($link != null);

if($link != null)
	$link = "http://maps.google.com/maps/ms?ie=UTF8&msa=0&msid=207972918742334384558.00049b21f38a1896b2cc5".$link."&z=17&output=embed";
else
	$link = "http://maps.google.com/maps/ms?ie=UTF8&msa=0&msid=207972918742334384558.00049b21f38a1896b2cc5&z=15&output=embed";

?>

<script type="text/javascript">
	// <![CDATA[
	var carregou_horario = false;
	var carregou_mapa = false;
	var Atualizar_Horario = function(periodo) {
		if(!periodo)
			periodo = '';
		$("#tab_horario").Carregando('Carregando Hor&aacute;rio...');
		$.post('<?= CONFIG_URL; ?>ajax/horario.php', {sala: '<?= $Sala->getID(); ?>', p: periodo}, function(data) {
			if(data)
				$("#tab_horario").html(data);
		});
	}
	$(document).ready(function() {
		$('#periodo_horario').live('change', function() {
			Atualizar_Horario($(this).val());
		});
		$("#tabs").tabs({
			show: function(event, ui) {
				if((ui.panel.id == 'tab_horario') && (!carregou_horario)) {
					Atualizar_Horario();
					carregou_horario = true;
				} else if((ui.panel.id == 'tab_mapa') && (!carregou_mapa)) {
					$("#iframe_mapa").attr('src', '<?= $link ?>');
					carregou_mapa = true;
					<?php if (!$mapa_encontrado) { ?>
					if ($("#iframe_mapa_naoachou").length == 0)
						$("#iframe_mapa").before('<div id="iframe_mapa_naoachou" style="text-align: center; margin-bottom: 4px;">A localiza&ccedil;&atilde;o da sala n&atilde;o foi encontrada, por&eacute;m o mapa geral da UNICAMP ser&aacute; mostrado.</div>');
					<?php } ?>
				}
			},
			select: function(event, ui) {
				window.location.hash = ui.tab.hash;
			}
		});
		Tamanho_Abas('tabs');
	});
	// ]]>
</script>

<div id="coluna_esquerda_wrapper">
	<div id="coluna_esquerda">
		<div id="perfil_cabecalho">
			<div id="perfil_mensagem_botoes">
				<div id="perfil_cabecalho_nome">Sala - <?= $Sala->getNome(true); ?></div>
			</div>
		</div>
		<div class="tip" id="sala_tip">Dica: Para acessar diretamente a p&aacute;gina desta sala use <span class="link"><a href="http://gde.ir/s/<?= $Sala->getNome(true); ?>">http://gde.ir/s/<?= $Sala->getNome(true); ?></a></span></div>
		<div id="perfil_abas">
			<div id="tabs">
				<ul>
					<li><a href="#tab_informacoes" class="ativo">Informa&ccedil;&otilde;es</a></li>
					<li><a href="#tab_horario">Hor&aacute;rio</a></li>
					<li><a href="#tab_mapa">Mapa</a></li>
				</ul>
				<div id="tab_informacoes" class="tab_content">
					<table cellspacing="0" class="tabela_bonyta_branca">
						<tr>
							<td width="25%"><b>Sala:</b></td>
							<td><?= $Sala->getNome(true); ?></td></tr>
						</tr>
						<tr>
							<td width="25%"><b>Informa&ccedil;&otilde;es:</b></td>
							<td><a href="http://salas.basico.unicamp.br/salas/buscasala.xhtml?busca=<?= $Sala->getNome(true); ?>" target="_blank">Link</a></td>
						</tr>
						<tr>
							<td width="25%"><b>Lugares:</b></td>
							<td><?= $Sala->getLugares(true); ?></td>
						</tr>
						<tr>
							<td width="25%"><b>Pr&eacute;dio:</b></td>
							<td><?= $Predio->getNome(true) ?></td>
						</tr>
						<tr>
							<td width="25%"><b>Andar:</b></td>
							<td><?= $Sala->getAndar(true); ?></td>
						</tr>
						<tr>
							<td width="25%"><b>Unidade:</b></td>
							<td><?= $Unidade->getNome(true); ?></td>
						</tr>
						<tr>
							<td width="25%"><b>Topologia:</b></td>
							<td><?= $Sala->getTopologia(true); ?></td>
						</tr>
						<tr>
							<td width="25%"><b>Utilizacoes:</b></td>
							<td><?= $Sala->getUtilizacoes(true); ?></td>
						</tr>

					</table>
				</div>
				<div id="tab_horario" class="tab_content">
					<img src="<?= CONFIG_URL; ?>web/images/loading.gif" alt="..." /> Carregando Hor&aacute;rio...
				</div>
				<div id="tab_mapa" class="tab_content">
					<div id="sala_tab_mapa" class="sala_tab_mapa"><iframe class="iframe_mapa" id="iframe_mapa" scrolling="no" frameborder="0" src="<?= CONFIG_URL; ?>sala/?cm" marginwidth="0" marginheight="0"></iframe></div>
				</div>
			</div>
		</div>
	</div>
</div>
<?= $FIM; ?>

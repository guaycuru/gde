<?php

namespace GDE;

define('TITULO', 'Home');

require_once('../common/common.inc.php');

$Amigos = $_Usuario->Amigos();
$Recomendacoes = $_Usuario->Amigos_Recomendacoes(2, 15);
$Autorizacoes = $_Usuario->getAmigos_Pendentes();

// ToDo ?
if(!isset($_SESSION['atualizacoes_last_id']))
	$_SESSION['atualizacoes_last_id'] = 0; //Acontecimento::Ultimo_Id_Por_Data($_SESSION['ultimo_acesso']);
	
?>
<script type="text/javascript" src="<?= CONFIG_URL; ?>web/js/gde.amizades.js?<?= REVISION; ?>"></script>
<script type="text/javascript" src="<?= CONFIG_URL; ?>web/js/gde.atualizacoes.js?<?= REVISION; ?>"></script>
<script type="text/javascript" src="<?= CONFIG_URL; ?>web/js/gde.calendario.js?<?= REVISION; ?>"></script>
<script type="text/javascript" src="<?= CONFIG_URL; ?>web/js/gde.favoritos.js?<?= REVISION; ?>"></script>
<script type="text/javascript" src="<?= CONFIG_URL; ?>web/js/gde.notas.js?<?= REVISION; ?>"></script>
<script type="text/javascript" src="<?= CONFIG_URL; ?>web/js/gde.aviso.js?<?= REVISION; ?>"></script>
<script type="text/javascript">
// <![CDATA[
var carregou_horario = false;
var carregou_calendario = false;
var carregou_notas = false;
var carregou_avisos = false;
var atualizacao_id = '';
var atualizacao_tp = 'u';
var status_padrao = 'Definir status...';
var procurando_amigo = [];
var Cardapio_Muda = function(id) {
	$("#cardapio_conteudo").Carregando('Carregando Card&aacute;pio...<br /><br /><br /><br /><br />');
	$.post("<?= CONFIG_URL; ?>ajax/cardapio.php", { c: id }, function(data) {
		if(data) {
			$("#cardapio_conteudo").html(data);
			$("a#cardapio_semana").fancybox({
				'hideOnContentClick': false,
				'autoDimensions': false,
				'width': '50%',
				'height': '60%'
			});
		}
	});
	return false;
}

var Toggle_Cardapio = function() {
	$("#cardapio_conteudo").toggle("slow");
}

var Atualizar_Horario = function(periodo, nivel) {
	if(!periodo)
		periodo = '';
	if(!nivel)
		nivel = '';
	$("#tab_horario").Carregando('Carregando Matr&iacute;culas e Hor&aacute;rio...');
	$.post('<?= CONFIG_URL; ?>ajax/horario.php', {p: periodo, n: nivel}, function(data) {
		if(data)
			$("#tab_horario").html(data);
	});
}

var Atualizar_Status = function() {
	var status = $("#meu_status").val();
	if((status == status_padrao) || (status == ''))
		return false;
	$("#atualizar_status").unbind('click');
	$("#atualizar_status").text('Salvando...');
	$("#meu_status").addClass('enviando');
	$.post('<?= CONFIG_URL; ?>ajax/ax_acontecimento.php', {tp: 'us', txt: status}, function(data) {
		if(data != 0) {
			$("#meu_status_atual").load('<?= CONFIG_URL; ?>ajax/ax_status.php', {q: 'carregar'});
			$("#limpar_status").show();
			$("#meu_status").Padrao();
			Adicionar_Atualizacao('', data);
		}
		$("#meu_status").removeClass('enviando');
		$("#atualizar_status").text('Salvar');
		$("#atualizar_status").click(Atualizar_Status);
	});
	return false;
}

var Limpar_Status = function() {
	$("#limpar_status").text('Removendo...');
	$.post('<?= CONFIG_URL; ?>ajax/ax_status.php', {q: 'limpar_status'}, function(data) {
		if(data != 0) {
			$("#meu_status_atual").html('');
			$("#limpar_status").hide();
		}
		$("#limpar_status").text('Remover');
	});
	return false;
}

var Adicionar_Amigo_Sugestao = function(id) {
	$.post('<?= CONFIG_URL; ?>ajax/ax_amigo.php', {i: id, tipo: 'a'}, function(data) {
		$.guaycuru.confirmacao("Foi enviado um pedido de autoriza&ccedil;&atilde;o!", null);
		$("#amigo_"+id).hide();
	});
	return false;
}

$(document).ready(function() {
	$("#atualizacoes_mensagens").attr('checked', <?= ($_Usuario->getConfig(true)->getAcontecimentos_Mensagens()) ? 'true' : 'false'; ?>);
	$("#atualizacoes_minhas").attr('checked', <?= ($_Usuario->getConfig(true)->getAcontecimentos_Minhas()) ? 'true' : 'false'; ?>);
	$("#atualizacoes_amigos").attr('checked', <?= ($_Usuario->getConfig(true)->getAcontecimentos_Amigos()) ? 'true' : 'false'; ?>);
	$("#atualizacoes_gde").attr('checked', <?= ($_Usuario->getConfig(true)->getAcontecimentos_GDE()) ? 'true' : 'false'; ?>);
	$("#calendarioOutro").attr('checked', <?= ((!isset($_SESSION['calendario']['calendarioOutro'])) || ($_SESSION['calendario']['calendarioOutro'])) ? 'true' : 'false'; ?>);
	$("#calendarioTrabalho").attr('checked', <?= ((!isset($_SESSION['calendario']['calendarioTrabalho'])) || ($_SESSION['calendario']['calendarioTrabalho'])) ? 'true' : 'false'; ?>);
	$("#calendarioProva").attr('checked', <?= ((!isset($_SESSION['calendario']['calendarioProva'])) || ($_SESSION['calendario']['calendarioProva'])) ? 'true' : 'false'; ?>);
	$("#calendarioFeriado").attr('checked', <?= ((!isset($_SESSION['calendario']['calendarioFeriado'])) || ($_SESSION['calendario']['calendarioFeriado'])) ? 'true' : 'false'; ?>);
	$("#calendarioAniversario").attr('checked', <?= ((!isset($_SESSION['calendario']['calendarioAniversario'])) || ($_SESSION['calendario']['calendarioAniversario'])) ? 'true' : 'false'; ?>);
	$("#calendarioGraduacao").attr('checked', <?= ((!isset($_SESSION['calendario']['calendarioGraduacao'])) || ($_SESSION['calendario']['calendarioGraduacao'])) ? 'true' : 'false'; ?>);

	$("#menuAccordion").accordion({
		autoHeight: false,
		navigation: true,
		collapsible: true
	});
	
	$("#buscar_amigos1").bind({
		click: function() {
			$("#menuAccordion").accordion('activate', $("#accordion_amigos"));
		},
		focus: function() {
			$('#menuAccordion').accordion('option', 'collapsible', false);
		},
		blur: function() {
			$('#menuAccordion').accordion('option', 'collapsible', true);
		},
		keydown: function(e) {
			if(e.keyCode == 32) {
				$(this).val($(this).val()+' ');
				return false;
			}
		}
	});
	
	$("span.ui-icon").css({'display': 'none'});
	
	/*$.post('<?= CONFIG_URL; ?>ajax/ax_previsao.php', {}, function(data){
		$("#previsao_tempo").html(data);
	});*/
	
	$("#tabs").tabs({
		show: function(event, ui) {
			if(ui.panel.id == 'tab_atualizacoes') {
				Atualizar_Atualizacoes('', 'u', false, false);
			}
<?php if(($_Usuario->getRA() > 0) || ($_Usuario->getMatricula() > 0)) { ?>
			else if((ui.panel.id == 'tab_horario') && (!carregou_horario)) {
				Atualizar_Horario('');
				carregou_horario = true;
			}
<?php } if($_Usuario->getRA() > 0) { ?>
			else if((ui.panel.id == 'tab_calendario') && (!carregou_calendario)) {
				Atualizar_Calendario();
				carregou_calendario = true;
				$("#link_novo_evento").fancybox({
					'autoDimensions' : true,
					'hideOnContentClick': false
				});
			}
			else if((ui.panel.id == 'tab_notas') && (!carregou_notas)) {
				Atualizar_Notas();
				carregou_notas = true;
			}
			
			else if((ui.panel.id == 'tab_avisos') && (!carregou_avisos)) {
				Atualizar_Avisos();
				carregou_avisos = true;
			}
<?php } ?>
		},
		select: function(event, ui) {
			window.location.hash = ui.tab.hash;
		}
	});
	Tamanho_Abas('tabs');
	$("#limpar_status").click(Limpar_Status);
	$("#meu_status").Valor_Padrao(status_padrao, 'padrao');
	$("#meu_status, textarea.resposta").live('keyup', function(e) {
		if(e.which == 13 && !e.shiftKey) {
			Atualizar_Status();
			return false;
		}
		if($(this).scrollTop() > 0) {
			var tamanho = $(this).scrollTop() + $(this).height();
			$(this).height((tamanho < 108) ? tamanho : 108);
		}
		if($(this).val().length > 255)
			$(this).val($(this).val().substr(0, 255));
	});
	$("#atualizar_status").click(Atualizar_Status);
	$("#cardapio_botao").click(Toggle_Cardapio);
	if($("#meu_status_atual").html() == "")
		$("#limpar_status").hide();
	$("img.nota_botao_x").live('hover', function(e) {
		if(e.type == 'mouseenter') {
			$(this).attr('src', '<?= CONFIG_URL; ?>web/images/CancelON.png');
		} else {
			$(this).attr('src', '<?= CONFIG_URL; ?>web/images/CancelOFF.png');
		}
	});
	$("img.nota_botao_lapis").live('hover', function(e) {
		if(e.type == 'mouseenter') {
			$(this).attr('src', '<?= CONFIG_URL; ?>web/images/EditON.png');
		} else {
			$(this).attr('src', '<?= CONFIG_URL; ?>web/images/EditOFF.png');
		}
	});
	$("img.nota_botao_save").live('hover', function(e) {
		if(e.type == 'mouseenter') {
			$(this).attr('src', '<?= CONFIG_URL; ?>web/images/SaveON.png');
		} else {
			$(this).attr('src', '<?= CONFIG_URL; ?>web/images/SaveOFF.png');
		}
	});
	$('#periodo_horario').live('change', function() {
		if($('#tab_horario input[type="radio"][name="n"]').length > 0)
			nivel = $('#tab_horario input[type="radio"][name="n"]:checked').val();
		else
			nivel = null;
		Atualizar_Horario($(this).val(), nivel);
	});
	$('#tab_horario input[type="radio"][name="n"]').live('click', function() {
		Atualizar_Horario($('#periodo_horario').val(), $(this).val());
	});
	Cardapio_Muda('-1');
	Atualizar_Favoritos();
	$("input.tipo_atualizacoes").click(function() {
		var id = $(this).attr('id');
		if(id == 'atualizacoes_todas')
			$("input.tipo_atualizacoes").attr('checked', $(this).is(':checked'));
		else
			$("#atualizacoes_todas").attr('checked', false);
		Atualizar_Atualizacoes('', 'u', false, false);
	});
	$("input.tipo_calendario").click(function() {
		$("div."+$(this).attr('id')).toggle();
		$.post('<?= CONFIG_URL; ?>ajax/ax_opcao_calendario.php', {c: $(this).attr('id'), v: ($(this).is(":checked") ? 1 : 0)});
	});
	$("#toggle_menu_requisicoes").click(Amizade_Requisicoes_Toggle);
	$("a.amizade_aceitar").click(Amizade_Aceitar);
	$("a.amizade_ignorar").click(Amizade_Ignorar);
	$(document).everyTime(900000, function() { Cardapio_Muda('-1'); });
	$(window).resize(function() { Tamanho_Abas('tabs'); });
	$('#buscar_amigos1').Procura_Amigo('lista_amigos1');
	$("a#enquete").fancybox({
		'hideOnContentClick': false,
		'autoDimensions': true
	});
<?php
//require_once('../classes/Enquete.inc.php');
//$Enquete = new Enquete(5, $_GDE['DB']);
//if($Enquete->Ja_Votou($_Usuario) === false) { ?>
	//$("a#enquete").click();
<?php //} ?>
	$("#lista_amigos2 div.sliding_top").live("click", function() {
		Adicionar_Amigo_Sugestao(($(this).attr("id").split('_'))[1]);
		return false;
	});
	$("div.recomendacao_amigo").live("mouseenter", function() {
		var div_oculto = $("#div_"+($(this).attr("id").split('_'))[1]);
		if($(div_oculto).is(":hidden")){
			$("#div_"+($(this).attr("id").split('_'))[1]).slideDown();
		}
	});
	$("div.recomendacao_amigo").live("mouseleave", function() {
		var div_oculto = $("#div_"+($(this).attr("id").split('_'))[1]);
		if($(div_oculto).is(":visible")){
			$("#div_"+($(this).attr("id").split('_'))[1]).slideUp();
		}
	});
	$("div[id^='div_']").live("mouseenter", function() {
		if($(this).hasClass("transparente"))
			$(this).removeClass("transparente");
	});
	$("div[id^='div_']").live("mouseleave", function() {
		if(!$(this).hasClass("transparente"))
			$(this).addClass("transparente");
	});
});
// ]]>
</script>
<div id="coluna_esquerda_wrapper">
	<div id="coluna_esquerda">
		<div id="perfil_cabecalho">
			<div id="perfil_foto">
				<a href="<?= CONFIG_URL; ?>perfil/?usuario=<?= $_Usuario->getLogin(); ?>" class="link_foto"><img src="<?= $_Usuario->getFoto(true); ?>" alt="Foto" class="foto_perfil" /></a><br /><a href="EditarPerfil.php" class="link_foto" style="font-size: 14px">Editar Perfil</a>
			</div>
			<div id="perfil_mensagem_botoes">
				<div id="perfil_cabecalho_nome"><?= $_Usuario->getNome_Completo(true); ?></div>
				<div id="perfil_cabecalho_status"><span id="meu_status_atual"><?= $_Usuario->getStatus(true, true); ?></span> <a href="#" id="limpar_status">Remover</a></div>
				<div id="perfil_mensagem">
					<textarea class="arredondado-all" id="meu_status" name="status" rows="1" cols="50"></textarea> <a href="#" id="atualizar_status" class="perfil_link_enviar">Salvar</a>
					<div id="cardapio">
						<div id="cardapio_botao"><img src="<?= CONFIG_URL; ?>web/images/cardapio_icone.gif" alt="Cardapio" /></div>
					</div>
					<div id="cardapio_conteudo" style="width: 260px; float: left; margin-left: 10px"></div>
					<!--<div id="previsao_tempo" style="float: left; margin-left: 20px"></div> -->
				</div>
			</div>
		</div>
		<!-- <div class="tip" id="perfil_tip" style="text-align: center;"><strong>Ajude a escolher o futuro do GDE.</strong> Clique <a href="<?= CONFIG_URL; ?>ajax/ax_enquete.php?id=5" id="enquete">aqui</a> e d&ecirc; sua opini&atilde;o!</div> -->
		<div id="perfil_abas">
			<div id="tabs">
				<ul>
					<li><a href="#tab_atualizacoes" class="ativo">Atualiza&ccedil;&otilde;es</a></li>
<?php if(($_Usuario->getRA() > 0) || ($_Usuario->getMatricula() > 0)) { ?>
					<li><a href="#tab_horario"><?= ($_Usuario->getRA() > 0) ? 'Matr&iacute;culas' : 'Hor&aacute;rio'; ?></a></li>
<?php } if($_Usuario->getRA() > 0) { ?>
					<li><a href="#tab_calendario">Calend&aacute;rio</a></li>
					<li><a href="#tab_notas">Notas</a></li>
					<li><a href="#tab_avisos" id="link_avisos">Avisos</a></li>
<?php } ?>
				</ul>
				<div id="tab_atualizacoes" class="tab_content">
					<div id="atualizacoes_botoes">
						<input type="checkbox" id="atualizacoes_todas" class="tipo_atualizacoes" /><label for="atualizacoes_todas">Tudo</label>
						<input type="checkbox" id="atualizacoes_mensagens" class="tipo_atualizacoes" checked="checked" /><label for="atualizacoes_mensagens">Mensagens</label>
						<input type="checkbox" id="atualizacoes_minhas" class="tipo_atualizacoes" checked="checked" /><label for="atualizacoes_minhas">Minhas Atual.</label>
						<input type="checkbox" id="atualizacoes_amigos" class="tipo_atualizacoes" checked="checked" /><label for="atualizacoes_amigos">Atual. de Amigos</label>
						<input type="checkbox" id="atualizacoes_gde" class="tipo_atualizacoes" checked="checked" /><label for="atualizacoes_gde">Atual. do GDE</label>
					</div>
					<div id="tab_atualizacoes_conteudo">
						<img src="<?= CONFIG_URL; ?>web/images/loading.gif" alt="..." /> Carregando Atualiza&ccedil;&otilde;es...
					</div>
				</div>
<?php if(($_Usuario->getRA() > 0) || ($_Usuario->getMatricula() > 0)) { ?>
				<div id="tab_horario" class="tab_content">
					<img src="<?= CONFIG_URL; ?>web/images/loading.gif" alt="..." /> Carregando <?php if($_Usuario->getRA() > 0) echo 'Matr&iacute;culas e '; ?>Matr&iacute;culas...
				</div>
<?php } if($_Usuario->getRA() > 0) { ?>
				<div id="tab_calendario" class="tab_content">
					<div id="opcaoCalendario" style="text-align: center; margin: 0px 0px 10px 0px">
						<input type="checkbox" id="calendarioFeriado" class="tipo_calendario" checked="checked" /><label for="calendarioFeriado">Feriado</label>
						<input type="checkbox" id="calendarioGraduacao" class="tipo_calendario" checked="checked" /><label for="calendarioGraduacao">Gradua&ccedil;&atilde;o</label>
						<input type="checkbox" id="calendarioProva" class="tipo_calendario" checked="checked" /><label for="calendarioProva">Prova</label>
						<input type="checkbox" id="calendarioTrabalho" class="tipo_calendario" checked="checked" /><label for="calendarioTrabalho">Trabalho</label>
						<input type="checkbox" id="calendarioOutro" class="tipo_calendario" checked="checked" /><label for="calendarioOutro">Outro</label>
						<input type="checkbox" id="calendarioAniversario" class="tipo_calendario" checked="checked" /><label for="calendarioAniversario">Anivers&aacute;rio</label>
					</div>
					<div id="calendar"></div>
					<div style="display: none;">
						<div id="div_novo_evento" style="width: 410px; height: 290px;"></div>
					</div>
					<a href="#div_novo_evento" id="link_novo_evento"></a>
				</div>
				<div id="tab_notas" class="tab_content">
					<img src="<?= CONFIG_URL; ?>web/images/loading.gif" alt="..." /> Carregando Notas...
				</div>
				<div id="tab_avisos" class="tab_content">
					<img src="<?= CONFIG_URL; ?>web/images/loading.gif" alt="..." /> Carregando Avisos...
				</div>
<?php } ?>
			</div>
		</div>
	</div>
</div>
<div id="coluna_direita" class="gde_jquery_ui">
	<div id="menuAccordion">
		<?php if(count($Autorizacoes) > 0) { ?>
		<h3 id="accordion_pendentes" style="padding: 5px">Amizades Pendentes (<span id="span_numero_requisicoes"><?= count($Autorizacoes); ?></span>):</h3>
		<div id="amizades_pendentes">
			<?php foreach($Autorizacoes as $Auth) { ?>
			<div class="div_menu_requisicoes_linha" id="requisicao_amizade_<?= $Auth->getAmigo()->getID(); ?>">
				<div class="requisicao_foto"><img src="<?= $Auth->getUsuario()->getFoto(true, true); ?>" /></div>
				<div class="requisicao_nome"><a href="<?= CONFIG_URL; ?>perfil/?usuario=<?= $Auth->getUsuario()->getLogin(); ?>"><strong><?= $Auth->getUsuario()->getNome_Completo(true); ?></strong></a><br />
					<a href="#" class="amizade_aceitar" id="amizade_aceitar_<?= $Auth->getUsuario()->getID(); ?>"><i>Aceitar</i></a> <a href="#" class="amizade_ignorar" id="amizade_ignorar_<?= $Auth->getUsuario()->getID(); ?>"><i>Ignorar</i></a>
				</div>
			</div>
			<?php } ?>
		</div>
		<?php } ?>
		<h3 id="accordion_amigos"><a href='#' class='accordion_header'>Amigos (<span id="span_numero_amigos1"><?= count($Amigos); ?></span>): <input type="text" id="buscar_amigos1" /></a></h3>		
		<div id="lista_amigos1">
			<div id="amigos_on_1"></div>
			<div id="amigos_off_1"></div>
			<?php
			if(count($Amigos) == 0)
				echo '<div style="margin: 10px 10px">Voc&ecirc; ainda n&atilde;o possui nenhum Amigo...</div>';
			else {
				foreach($Amigos as $Amigo) { ?>
				<div class="amigo" id="amigo_<?= $Amigo->getAmigo()->getID(); ?>">
					<div class="amigo_foto">
						<a href="<?= CONFIG_URL; ?>perfil/?usuario=<?= $Amigo->getAmigo()->getLogin() ?>" class="link_sem_decoracao" title="<?= $Amigo->getAmigo()->getNome(true).' '.$Amigo->getAmigo()->getSobrenome(true) ?>">
							<img src="<?= $Amigo->getAmigo()->getFoto(true, true) ?>" border="0" alt="<?= $Amigo->getAmigo()->getNome(true) ?>" />
						</a>
					</div>
					<div class="amigo_nome">
						<img src="<?= CONFIG_URL; ?>web/images/status_vs.png" class="status_icone status_icone_<?= $Amigo->getAmigo()->getID(); ?>" alt="?" />
						<a href="<?= CONFIG_URL; ?>perfil/?usuario=<?= $Amigo->getAmigo()->getLogin() ?>" class="amigo" title="<?= $Amigo->getAmigo()->getNome_Completo(true) ?>"><?= Util::Limita($Amigo->getApelido(true), 10); ?></a>
					</div>
				</div>
			<?php } } ?>
		</div>
		<h3 id="accordion_favoritos"><a href='#' class='accordion_header'>Favoritos (<span id="span_numero_favoritos">?</span>):</a></h3>
		<div id="lista_favoritos" ><img src="<?= CONFIG_URL; ?>web/images/loading.gif" alt="..." /> Carregando...</div>
		<h3 id="accordion_recomendacoes"><a href='#' class='accordion_header'>Recomenda&ccedil;&otilde;es de Amigos:</a></h3>
		<div id="lista_amigos2" >
			<?php
			if(count($Recomendacoes) == 0)
				echo '<div style="margin: 10px 10px">Voc&ecirc; ainda n&atilde;o possui nenhum Amigo...</div>';
			else {
				foreach($Recomendacoes as $Amigo) { ?>
				<div class="recomendacao_amigo" id="amigo_<?= $Amigo->getID(); ?>">
					<div class="amigo_foto">
						<a href="#" class="link_sem_decoracao">
							<div class="sliding_top transparente ui-corner-bottom" id="div_<?= $Amigo->getID(); ?>"><span>Adicionar</span></div>
						</a>
						<a href="<?= CONFIG_URL; ?>perfil/?usuario=<?= $Amigo->getLogin() ?>" class="link_sem_decoracao" title="<?= $Amigo->getNome(true).' '.$Amigo->getSobrenome(true) ?>">
							<img src="<?= $Amigo->getFoto(true, true) ?>" border="0" alt="<?= $Amigo->getNome(true) ?>" />
						</a>
					</div>
					<div class="amigo_nome">
						<img src="<?= CONFIG_URL; ?>web/images/status_vs.png" class="status_icone status_icone_<?= $Amigo->getID(); ?>" alt="?" />
						<a href="<?= CONFIG_URL; ?>perfil/?usuario=<?= $Amigo->getLogin(); ?>" class="amigo" title="<?= $Amigo->getNome_Completo(true); ?>"><?= $Amigo->getNome(true); ?></a>
					</div>
				</div>
			<?php } } ?>
		</div>
	</div>
</div>
<?= $FIM; ?>

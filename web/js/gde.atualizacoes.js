var atualizacao_maior_id = 0;
var atualizacao_por_pagina = 10;

var Atualizar_Atualizacoes = function(id, mais, novas) {
	var msg = $("#atualizacoes_mensagens").is(':checked') ? '1' : '';
	var min = $("#atualizacoes_minhas").is(':checked') ? '1' : '';
	var am = $("#atualizacoes_amigos").is(':checked') ? '1' : '';
	var gde = $("#atualizacoes_gde").is(':checked') ? '1' : '';
	if(novas) {
		var args = {i: id, st: 0, msg: msg, min: min, am: am, gde: gde, ultimo: atualizacao_maior_id, nvs: 1};
		$.get(CONFIG_URL + 'ajax/acontecimentos.php', args, function(data) {
			if(data) {
				$("#div_tem_atualizacoes").remove();
				$("div.atualizacao_nova_escondida").remove();
				$("#tab_atualizacoes_conteudo").prepend(data);
				$("#tab_atualizacoes_conteudo").prepend("<div id=\"div_tem_atualizacoes\"><a href=\"#\" id=\"link_tem_atualizacoes\">Exibir novas atualiza&ccedil;&otilde;es.</a></div>");
			}
		});
	} else if(mais) {
		$("#atualizacoes_mais").before('<div id="carregando_mais_atualizacoes"><img src="' + CONFIG_URL + 'web/images/loading.gif" />Carregando...</div>');
		var args = {i: id, st: $("div.atualizacao:visible").length, msg: msg, min: min, am: am, gde: gde, ultimo: atualizacao_maior_id, pp: atualizacao_por_pagina, mais: 1};
		$.get(CONFIG_URL + 'ajax/acontecimentos.php', args, function(data) {
			var mais = $(data).find(".atualizacao");
			if(mais.length == 0)
				$("#atualizacoes_mais").remove();
			else {
				mais.each(function() {
					var id = $(this).attr('id').split("_");
					// Evita atualizacoes duplicadas (re-organizacao ou atualizacoes nao carregadas, etc)
					if($("#atualizacao_"+id[1]).length > 0) {
						$("#atualizacao_"+id[1]).remove();
						$("#respostas_"+id[1]).remove();
					}
				});
				$("#atualizacoes_mais").before(data);
				if(mais.length < atualizacao_por_pagina)
					$("#atualizacoes_mais").remove();
			}
			$("#carregando_mais_atualizacoes").remove();
		});
	} else {
		$("#tab_atualizacoes_conteudo").Carregando('Carregando Atualiza&ccedil;&otilde;es...');
		var args = {i: id, st: 0, msg: msg, min: min, am: am, gde: gde, ultimo: -1, pp: atualizacao_por_pagina};
		$("#tab_atualizacoes_conteudo").load(CONFIG_URL + 'ajax/acontecimentos.php?' + $.param(args), function() {
			//$.get(CONFIG_URL + 'ajax/acontecimentos.php', {ui: 1}, function(data) {
				atualizacao_maior_id = parseInt($("#atualizacao_maior_id").html());
			//});
			$("#tab_atualizacoes_conteudo").append('<div id="atualizacoes_mais"><a href="#" id="atualizacoes_mais_link">Mais...</a></div>');
		});
	}
};

var Remover_Atualizacao = function() {
	var ids = $(this).attr('id').split("_");
	var id = ids[1];
	var tmp = $("#remover_"+id).html();
	$("#remover_"+id).html('Aguarde');
	$.post(CONFIG_URL + 'ajax/acontecimento.php', {tp: 'x', id: id}, function(data) {
		if(data && data.ok) {
			$("#atualizacao_"+id).hide("slow");
			$("#respostas_"+id).hide("slow");
		} else
			$("#remover_"+id).html(tmp);
	});
	return false;
};

var Responder_Atualizacao = function() {
	var ids = $(this).attr('id').split("_");
	var id = ids[1];
	var original = (ids[2]) ? ids[2] : id;
	if($("#div_responder_"+id).length > 0)
		return false;
	$("#atualizacao_"+id).append('<div id="div_responder_'+id+'"><textarea id="input_responder_'+id+"_"+original+'" class="resposta"></textarea> <a href="#" id="responder_enviar_'+id+'_'+original+'" class="resposta_link_enviar">Enviar</a> <a href="#" id="responder_cancelar_'+id+'" class="resposta_link_cancelar">Cancelar</a></div>');
	$("#input_responder_"+id+"_"+original).Valor_Padrao('Responder...', 'padrao');
	$("#input_responder_"+id+"_"+original).focus();
	return false;
};

var Responder_Atualizacao_Enviar = function() {
	var ids = $(this).attr('id').split("_");
	var id = ids[2];
	var original = (ids[3]) ? ids[3] : id;
	var txt = $("#input_responder_"+id+"_"+original).val();
	$("a.resposta_link_enviar").hide();
	if((txt == null) || (txt == 'Responder...'))
		return false;
	$("#input_responder_"+id+"_"+original).addClass('enviando');
	$.post(CONFIG_URL + 'ajax/acontecimento.php', {tp: 'um', txt: $("#input_responder_"+id+"_"+original).val(), ori: original}, function(data) {
		if(data && data.ok) {
			$("#respostas_"+original).load(CONFIG_URL + 'ajax/acontecimentos.php?' + $.param({o: original, ultimo: atualizacao_maior_id}) + ' div.atualizacao_resposta', function() {
				// Remove o link de exibir todas as respostas (porque depois que respondo, ja carrego todas
				$("#div_responder_"+id).remove();
				$('#todas_respostas_'+original).parent().remove();
			});
		} else {
			$("#input_responder_"+id+"_"+original).removeClass('enviando');
			$("a.resposta_link_enviar").show();
		}
	});
	return false;
};

var Responder_Atualizacao_Cancelar = function() {
	var ids = $(this).attr('id').split("_");
	var id = ids[2];
	var original = (ids[3]) ? ids[3] : id;
	$("#div_responder_"+id).remove();
	return false;
};

var Adicionar_Atualizacao = function(i, o) {
	$.get(CONFIG_URL + 'ajax/acontecimentos.php', {i: i, o: o}, function(data) {
		$("#tab_atualizacoes_conteudo").prepend(data);
	});
};

var Todas_Respostas_Atualizacao = function() {
	var idf = $(this).attr('id');
	var ids = idf.split("_");
	var id = ids[2];
	var old = $(this).html();
	$(this).text('Aguarde...');
	$.get(CONFIG_URL + 'ajax/acontecimentos.php', {o: id, ultimo: atualizacao_maior_id, rt: 1}, function(data) {
		if(data) {
			$('#'+idf).parent().remove();
			$("#respostas_"+id).html($(data).find('div.atualizacao_resposta'));
		} else
			$(this).html(old);
	});
	return false;
};

$(document).ready(function() {
	$("#tab_atualizacoes").on('click', 'a.atualizacao_responder', Responder_Atualizacao);
	$("#tab_atualizacoes").on('click', 'a.atualizacao_remover', Remover_Atualizacao);
	$("#tab_atualizacoes").on('click', 'a.atualizacao_todas_respostas', Todas_Respostas_Atualizacao);
	$("#tab_atualizacoes").on('click', 'a.resposta_link_enviar', Responder_Atualizacao_Enviar);
	$("#tab_atualizacoes").on('click', 'a.resposta_link_cancelar', Responder_Atualizacao_Cancelar);
	$("#tab_atualizacoes").on('click', '#atualizacoes_mais_link', function() { Atualizar_Atualizacoes(atualizacao_id, true, false); return false; });
	$("#tab_atualizacoes").on('click', '#link_tem_atualizacoes', function() {
		$("#div_tem_atualizacoes").remove();
		// Atualizo o ID aqui pra ficar certo enquanto nao clicou no link de ver as novas...
		$("div.atualizacao_nova_escondida").each(function() {
			var id = $(this).attr('id').split("_");
			atualizacao_maior_id = parseInt($("#atualizacao_maior_id").html());
			var novo_id = $(this).attr('id').replace('nova_', '');
			$("#"+novo_id).remove();
			$(this).attr('id', novo_id);
			$("#"+novo_id).removeClass("atualizacao_nova_escondida");
			$("#"+novo_id).show("slow");
		});
		return false;
	});

	$("#tab_atualizacoes").on('click', 'a.video_youtube', function() {
		var id_video = $(this).attr('id').replace('youtube_', '');
		var largura_maxima = Math.round($("#tab_atualizacoes_conteudo").width()) - 250;
		var altura_maxima = Math.round(largura_maxima * 0.5625) + 26;
		var largura = (largura_maxima > 640) ? 640 : largura_maxima;
		var altura = (altura_maxima > 386) ? 386 : altura_maxima;
		$(this).hide('slow');
		// por um DIV em volta? (facebook)
		$(this).before('<object style="height: '+altura+'px; width: '+largura+'px" id="player_'+id_video+'"><param name="movie" value="http://www.youtube.com/v/'+id_video+'?version=3&rel=0&autoplay=1"><param name="allowFullScreen" value="false"><param name="allowScriptAccess" value="never"><embed src="http://www.youtube.com/v/'+id_video+'?version=3&rel=0&autoplay=1" quality="high" type="application/x-shockwave-flash" allowfullscreen="false" allowScriptAccess="never" width="'+largura+'" height="'+altura+'"></object>');
		$(this).remove();
		return false;
	});
});

var Amizade_Requisicoes_Toggle = function() {
	$("#div_menu_requisicoes").toggle('fast', function() {
		if($(this).is(":visible"))
			$(document).click(function() { $("#div_menu_requisicoes").hide('fast'); return false; });
	});
	return false;
}

var Amizade_Aceitar = function(e) {
	var i = $(this).attr('id').replace('amizade_aceitar_', '');
	$("#amizade_aceitar_"+i).text('Aguarde...');
	$("#amizade_ignorar_"+i).hide();
	$.post(CONFIG_URL + 'ajax/amigo.php', {i: i, tipo: 'h'}, function(res) {
		if(res && res.ok) {
			$("#amizade_aceitar_"+i).text('Pedido aceito!');
			$("#amizade_aceitar_"+i).unbind('click');
			$("#span_numero_requisicoes").text(parseInt($("#span_numero_requisicoes").text())-1);
			if(parseInt($("#span_numero_requisicoes").text()) == 0)
				$("#cabecalho_amigos_requisicoes").hide();
		} else {
			$("#amizade_aceitar_"+i).text('Aceitar');
			$("#amizade_ignorar_"+i).text('Ignorar');
		}
	});
	e.stopPropagation();
	return false;
};

var Amizade_Ignorar = function(e) {
	var i = $(this).attr('id').replace('amizade_ignorar_', '');
	$("#amizade_ignorar_"+i).text('Aguarde...');
	$("#amizade_aceitar_"+i).hide();
	$.post(CONFIG_URL + 'ajax/amigo.php', {i: i, tipo: 'r'}, function(res) {
		if(res && res.ok) {
			$("#amizade_ignorar_"+i).text('Pedido ignorado!');
			$("#amizade_ignorar_"+i).unbind('click');
			$("#span_numero_requisicoes").text(parseInt($("#span_numero_requisicoes").text())-1);
		} else {
			$("#amizade_aceitar_"+i).text('Aceitar');
			$("#amizade_ignorar_"+i).text('Ignorar');
		}
	});
	e.stopPropagation();
	return false;
};

var Adicionar_Amigo = function(id) {
	$.post(CONFIG_URL + 'ajax/amigo.php', {i: id, tipo: 'a'}, function(res) {
		if(res && res.ok) {
			$.guaycuru.confirmacao("Foi enviado um pedido de autoriza&ccedil;&atilde;o de amizade!", null);
			$('#link_amigo').after("<span style=\"font-size: 10px;\">Aguardando Autoriza&ccedil;&atilde;o...</span>")
			$('#link_amigo').hide();
		} else {
			if (res.error)
				var msg = res.error;
			else
				var msg = 'Ele(a) j&aacute; est&aacute; na sua lista de amigos.';
			$.guaycuru.confirmacao(msg);
		}
	});
	return false;
};
var Remover_Amigo = function(id) {
	$.guaycuru.simnao("Tem certeza que deseja remover ele(a) da sua lista de amigos?", function() { 
		$.post(CONFIG_URL + 'ajax/amigo.php', {i: id, tipo: 'r'}, function(res) {
			if(res && res.ok) {
				$.guaycuru.confirmacao("Ele(a) foi removido(a) da sua lista de amigos!", null);
				$('#link_amigo').text('Solicitar Amizade');
				$('#link_amigo').unbind('click');
				$('#link_amigo').click(function() { Adicionar_Amigo(id); return false; });
			} else {
				if (res.error)
					var msg = res.error;
				else
					var msg = 'N&atilde;o foi poss&iacute;vel remover ele(a) da sua lista de amigos.';
				$.guaycuru.confirmacao(msg);
			}
		});
	}, {});
	return false;
};

var Atualizar_Avisos = function() {
	$.post(CONFIG_URL + 'ajax/ax_avisos.php', {}, function(data){
		$("#tab_avisos").html(data);
		});
};

var Atualiza_Avisos_Quantidade = function() {
	$.post(CONFIG_URL + 'ajax/aviso.php', {}, function(data){
		$("#link_avisos").addClass("aviso_destaque");
		$("#link_avisos").html("Avisos ("+data+")");
		if(data == 0){
			$("#link_avisos").removeClass("aviso_destaque");
			$("#link_avisos").attr("title", "Voc\u00EA n\u00E3o possui novos avisos");
		}else if(data == 1)
			$("#link_avisos").attr("title", "Voc\u00EA tem "+data+" novo aviso");
		else
			$("#link_avisos").attr("title", "Voc\u00EA tem "+data+" novos avisos");
	});
};

$(document).ready(function() {
	Atualiza_Avisos_Quantidade();
	marcarLido = function(id) {
		$.post(CONFIG_URL + 'ajax/aviso.php', {id: id, tipo: 'l'}, function(data) {
			Atualiza_Avisos_Quantidade();
			if($("#span_aviso_nome_"+id).hasClass('aviso_nome')) {
				$("#ler_"+id).html('Marcar como n&atilde;o lido');
				$("#span_aviso_nome_"+id).removeClass('aviso_nome');
				$("#span_aviso_nome_"+id).addClass('aviso_nome_lido');
			} else {
				$("#ler_"+id).html('Marcar como lido');
				$("#span_aviso_nome_"+id).removeClass('aviso_nome_lido');
				$("#span_aviso_nome_"+id).addClass('aviso_nome');
			}
		});
	};
	
	apagarAviso = function(indice) {
		$.post(CONFIG_URL + 'ajax/aviso.php', {id: indice, tipo: 'r'}, function(data) {
			Atualiza_Avisos_Quantidade();
			$("#aviso_"+indice).next().hide();
			$("#aviso_"+indice).hide();
		});
	};
});

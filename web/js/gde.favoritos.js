var Adicionar_Favorito = function(ra) {
	$.post(CONFIG_URL + 'ajax/favorito.php', {ra: ra, tipo: 'a'}, function(data) {
		if(data == '1') {
			$("#link_favorito_img").attr("src", "../web/images/star_on.gif");
			$("#link_favorito").attr("title", "Adicionar aos Favoritos");
			$('#link_favorito').unbind('click');
			$('#link_favorito').click(function() { Remover_Favorito(ra); return false; });
		}
	});
};
var Remover_Favorito = function(ra) {
	$.post(CONFIG_URL + 'ajax/favorito.php', {ra: ra, tipo: 'r'}, function(data) {
		if(data == '1') {
			$("#link_favorito_img").attr("src", "../web/images/star_off.gif");
			$("#link_favorito").attr("title", "Remover dos Favoritos");
			$('#link_favorito').unbind('click');
			$('#link_favorito').click(function() { Adicionar_Favorito(ra); return false; });
		}
	});
};
var Atualizar_Favoritos = function() {
	$.post(CONFIG_URL + 'ajax/favoritos.php', {pc: 3}, function(data) {
		if(data == 'null') {
			$("#lista_favoritos").html('');
			$("#lista_favoritos").css({'max-height': '0', 'min-height': '0', 'height': '0', 'border': '0 none', 'margin-bottom': '15px'});
			$("#span_numero_favoritos").text('0');
			$("#accordion_favoritos").hide();
		} else if(data) {
			$("#lista_favoritos").html(data);
			$("#span_numero_favoritos").text($("#lista_favoritos div.amigo").length);
		}
	});
};

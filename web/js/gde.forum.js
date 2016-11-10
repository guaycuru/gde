var Salvar_Resposta_Forum = function(id_forum, topico) {
	var resposta = $("#input_"+id_forum).val();
	var titulo = "";
	if($("#titulo_"+id_forum).length > 0)
		var titulo = $("#titulo_"+id_forum).val();
	$("#titulo_"+id_forum).attr("disabled", true);
	$("#input_"+id_forum).attr("disabled", true);
	$.post('../ajax/ax_forum.php', {id_forum: id_forum, titulo: titulo, text: resposta, topico: topico, act: 'save'}, function(data) {
		$("#input_"+id_forum).attr("disabled", false);
		$("textarea.resposta_forum").Padrao();		
		if(topico == ""){
			$("#titulo_"+id_forum).attr("disabled", false);
			$("input.resposta_forum").Padrao();
		}
		if(data != null){
			if($("#forum_vazio").length > 0){
				$("#forum_vazio").before(data.notas);
				$("#forum_vazio").html(data.corpo);
				$("#forum_vazio").removeAttr("id");
			}else{
				if(topico == null || topico == ''){
					$("div.forum > ul > li:first-child").before("<li>"+data.corpo+"</li>");
				}else{
					$("ul.topico_respostas > li:last-child").after(data.notas+"<li>"+data.corpo+"</li>");
				}
			}
		}
		else alert("Erro ao salvar postagem");
		Zebrar_Postagens();
		Colorir_Votos();
	}, "json");
}

var setaCampos = function() {
	$("textarea.resposta_forum").Valor_Padrao('Descri\u00E7\u00E3o da postagem', 'padrao');
	$("input.resposta_forum").Valor_Padrao('T\u00EDtulo', 'padrao');
}

var Voto_Positivo = function(id_postagem){
	$.post('../ajax/ax_forum.php', {id_postagem: id_postagem, voto: '1', act: 'votar'}, function(data){
		Avisar_Usuario_Voto(data);
	}, "json");
}

var Voto_Negativo = function(id_postagem){
	$.post('../ajax/ax_forum.php', {id_postagem: id_postagem, voto: '-1', act: 'votar'}, function(data){
		Avisar_Usuario_Voto(data);
	}, "json");
}

var Avisar_Usuario_Voto = function(data){
	if(data.valido == 's'){
		$('div[id$="'+data.id_postagem+'"] > span.voto_controles').fadeOut("fast",function(){
			$(this).remove();
		});
		$('div[id$="'+data.id_postagem+'"] > span.voto_resultado').fadeOut("fast",function(){
			$(this).text(data.resultado); 
			Colorir_Votos();
		}).fadeIn("slow");
	}
}
		
var Carregar_Forum = function(id_forum, topico) {
	$("#tab_forum").Carregando('Carregando Forum...');
	$.post('../ajax/ax_forum.php', {id_forum: id_forum, topico: topico, act: 'display'}, function(data) {
		if(data)
			$("#tab_forum").html(data);
		Zebrar_Postagens();
		Colorir_Votos();
	});
}

var Atualizar_Forum = function(id_pai, tipo) {
	$("#tab_forum").Carregando('Carregando Forum...');
	$.post('../ajax/ax_forum.php', {id: id_pai, tipo: tipo, topico: '', act: 'display'}, function(data) {
		if(data)
			$("#tab_forum").html(data);
		Zebrar_Postagens();
		Colorir_Votos();
	});
}

var Remover_Resposta = function(id_postagem) {
	$.post('../ajax/ax_forum.php', {id_postagem: id_postagem, act: 'remove'}, function(data) {
		if(data){
			var li = $("#Postagem_"+id_postagem).parent();
			var nota = $("#voto_painel_"+id_postagem);
			var ul = $(li).parent();
			$(li).html(data);
			if($(ul).children().length > 2){
				$(nota).delay('1500').hide('slow');
				$(li).delay('1500').hide('slow', function(){
					$(li).remove();
					Zebrar_Postagens();
				});
			}else{
				$(nota).hide('slow', function(){ $(this).remove(); });
				$(li).attr('id','forum_vazio');
			}			
		}
	});
}

var Zebrar_Postagens = function(){
	$("ul.zebrado li").removeClass("alt1 alt2");
	$("ul.zebrado li:even").addClass("alt1");
	$("ul.zebrado li:odd").addClass("alt2");
}

var Colorir_Votos = function(){
	$(".voto_color").each(function() {
		var classe = "voto_color ";
		if(parseInt($(this).text()) > 0)
			classe += "voto_pos";
		else if(parseInt($(this).text()) < 0)
			classe += "voto_neg";
		else
			classe += "voto_neutro";
		if($(this).hasClass("voto_resultado"))
			classe += " voto_resultado";
		$(this).attr("class", classe);
	});
	
	$(document).ready(function() {
		setaCampos();
	});
}

var Seguir_Topico = function(id_topico, id_usuario){
	if(!$("#seguir_topico").hasClass('pressed')){
		$("#seguir_topico").attr('disabled', true);
		var image = $("#seguir_topico > img").attr('src');
		$("#seguir_topico > img").attr('src','../web/images/loading2.gif');
		$.post('../ajax/ax_forum.php', {id_topico: id_topico, id_usuario: id_usuario, act: 'seguir'}, function(data){
			$("#seguir_topico > img").attr('src',image);
			$("#seguir_topico").attr('disabled', false);
			$("#seguir_topico").addClass('pressed');
		}, "json");
	} else {
		$("#seguir_topico").attr('disabled', true);
		var image = $("#seguir_topico > img").attr('src');
		$("#seguir_topico > img").attr('src','../web/images/loading2.gif');
		$.post('../ajax/ax_forum.php', {id_topico: id_topico, id_usuario: id_usuario, act: 'unfollow'}, function(data){
			$("#seguir_topico > img").attr('src',image);
			$("#seguir_topico").attr('disabled', false);
			$("#seguir_topico").removeClass('pressed');
		}, "json");
	}
}
var Adicionar_Grupo = function(id_grupo) {
	$.post('../ajax/ax_grupo.php', {id_grupo: id_grupo, tipo: 'a'}, function(data) {
		if(data == '1') {
			$.guaycuru.confirmacao("Voc&ecirc; entrou no grupo!");
			$('#link_grupo').text('Sair do Grupo');
			$('#link_grupo').unbind('click');
			$('#link_grupo').click(function() { Remover_Grupo(id_grupo); });
			Atualizar_Participantes(id_grupo, 1);
		} else {
			$.guaycuru.confirmacao("Aguarde confirma&ccedil;&atilde;o!");
			$('#link_grupo').text('Aguarde...');
			$('#link_grupo').unbind('click');
		}
	});
	return false;
}
var Remover_Grupo = function(id_grupo) {
	$.guaycuru.simnao("Tem certeza que deseja sair deste grupo?", function() { 
		$.post('../ajax/ax_grupo.php', {id_grupo: id_grupo, tipo: 'r'}, function(data) {
			$.guaycuru.confirmacao("Voc&ecirc; saiu do grupo!", null);
			$('#link_grupo').text('Entrar no Grupo');
			$('#link_grupo').unbind('click');
			$('#link_grupo').click(function() { Adicionar_Grupo(id_grupo); });
			Atualizar_Participantes(id_grupo, 1);
		});
	}, {});
	return false;
}
var Adicionar_Moderador = function(id_grupo, id) {
	$.post('../ajax/ax_grupo.php', {id_grupo: id_grupo, id: id, tipo: 'am'}, function(data) {
		$('#participante_'+id).text('Remover como Moderador');
		$('#participante_'+id).unbind('click');
		$('#participante_'+id).click(function() { Remover_Moderador(id_grupo, id); });
	});
	return false;
}
var Remover_Moderador = function(id_grupo, id) {
	$.post('../ajax/ax_grupo.php', {id_grupo: id_grupo, id: id, tipo: 'rm'}, function(data) {
		$('#participante_'+id).text('Adicionar como Moderador');
		$('#participante_'+id).unbind('click');
		$('#participante_'+id).click(function() { Adicionar_Moderador(id_grupo, id); });
	});
	return false;
}
var Remover_Participante = function(id_grupo, id) {
	$.post('../ajax/ax_grupo.php', {id_grupo: id_grupo, id: id, tipo: 'r'}, function(data) {
		if(data == '1') {
			$('#remover_'+id).text('Usu\u00E1rio removido');
			$('#participante_'+id).hide();
		}
	});
	return false;
}
var Atualizar_Participantes = function(id, tipo, edita) {
	$.post("../ajax/ax_grupo_participantes.php", {i: id, pc: 3, tipo: tipo, edita: edita}, function(data) {
		if(tipo == 1) {
			if(data == 'null'){
				$("#lista_participantes").html('<div style="margin: 10px 10px">O grupo ainda n&atilde;o possui nenhum Participante...</div>');
				$("#span_numero_participantes").text($("#lista_participantes div.amigo").length);				
			} else if (data){
				$("#lista_participantes").html(data);
				$("#span_numero_participantes").text($("#lista_participantes div.amigo").length);				
			}
		} else {
			$("#lista_participantes").html(data);
			$("a.remove_moderador").click(function() {
				Remover_Moderador(id, $(this).attr('id').split('_')[1]);
			});
			$("a.adiciona_moderador").click( function() {
				Adicionar_Moderador(id, $(this).attr('id').split('_')[1]);
			});
			$("a.remover_participante").click(function() {
				Remover_Participante(id, $(this).attr('id').split('_')[1]);
	});
		}
	});
}

var Autorizar_Pendente = function(id_grupo, i) {
	$("#pendente_aceitar_"+i).text('Aguarde...');
	$.post('../ajax/ax_grupo.php', {id_grupo: id_grupo, id_usuario: i, tipo: 'at'}, function(data) {
		$("#pendente_aceitar_"+i).hide();
		Atualizar_Participantes(id_grupo, 1, 0);
	});
}

var Recusar_Pendente = function(id_grupo, i) {
	$("#pendente_recusar_"+i).text('Aguarde...');
	$.post('../ajax/ax_grupo.php', {id_grupo: id_grupo, id_usuario: i, tipo: 'rt'}, function(data) {
		$("#pendente_recusar_"+i).hide();
	});
}

var Carregar_Grupos = function(id) {
	$.post("../ajax/ax_grupos.php", {id: id}, function(data) {
		if(data == 'null1') {
			$("#lista_grupos").html('<div style="margin: 10px 10px">Voc&ecirc; n&atilde;o possui nenhum grupo</div>');
			$("#span_numero_grupos").text(0);
		} else if(data == 'null2') {
			$("#lista_grupos").html('<div style="margin: 10px 10px">N&atilde;o possui nenhum grupo</div>');
			$("#span_numero_grupos").text(0);
		} else {
			$("#lista_grupos").html(data);
			$("#span_numero_grupos").text($("#lista_grupos div.amigo").length);
		}
	});
}

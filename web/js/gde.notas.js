var Adicionar_Nota = function(id_oferecimento, isExame) {
		if($("#div_nova_nota_"+id_oferecimento).length > 0)
			return false;
	var tipo;
	if(isExame){
		tipo = 'e';
		var ja_existe = 'n';
		$("#tab_notas_"+id_oferecimento+" > div.notas > div[id*='div_nota']").each(function(){
			var sigla = $(this).children("div.nota_sigla").text();
			sigla = $.trim(sigla);
			if (ja_existe == 'n' && sigla === "Exame")
				ja_existe = 's';
		});
		if (ja_existe == 'n')
			$("#nota_funcoes_"+id_oferecimento).before('<div id="div_nova_nota_'+id_oferecimento+'" class="nota"><input type="text" id="nova_nota_'+id_oferecimento+'_sigla" disabled="disabled" value="Exame" class="input_nota" /><input type="text" id="nova_nota_'+id_oferecimento+'_valor" class="input_nota" /><a href="#" id="salvar_nova_nota_'+id_oferecimento+'"><img src="../web/images/SaveOFF.png" alt="Salvar" title="Salvar" class="nota_botao_save" /></a> <a href="#" id="cancelar_nova_nota_'+id_oferecimento+'"><img src="../web/images/CancelOFF.png" alt="Cancelar" title="Cancelar" class="nota_botao_x"/></a></div>');
	}else{
		tipo = 'n';
		$("#nota_funcoes_"+id_oferecimento).before('<div id="div_nova_nota_'+id_oferecimento+'" class="nota"><input type="text" id="nova_nota_'+id_oferecimento+'_sigla" class="input_nota" /><input type="text" id="nova_nota_'+id_oferecimento+'_valor" class="input_nota" /><input type="text" id="nova_nota_'+id_oferecimento+'_peso" class="input_nota" /> <a href="#" id="salvar_nova_nota_'+id_oferecimento+'"><img src="../web/images/SaveOFF.png" alt="Salvar" title="Salvar" class="nota_botao_save" /></a> <a href="#" id="cancelar_nova_nota_'+id_oferecimento+'"><img src="../web/images/CancelOFF.png" alt="Cancelar" title="Cancelar"  class="nota_botao_x"/></a></div>');
		$("#nova_nota_"+id_oferecimento+"_sigla").Valor_Padrao('Sigla (Ex: P1)', 'padrao');
	}	
	$("#nova_nota_"+id_oferecimento+"_valor").Valor_Padrao('Nota', 'padrao');
	$("#nova_nota_"+id_oferecimento+"_peso").Valor_Padrao('Peso', 'padrao');
	$("#salvar_nova_nota_"+id_oferecimento).click(function() {
		var sigla = $("#nova_nota_"+id_oferecimento+"_sigla").val();
		var nota = $("#nova_nota_"+id_oferecimento+"_valor").val();
		var peso = $("#nova_nota_"+id_oferecimento+"_peso").val();
		$("#salvar_nova_nota_"+id_oferecimento).hide();
		$("#div_nova_nota_"+id_oferecimento+" > input").addClass('enviando');
		$.post('../ajax/ax_nota.php', {tp: tipo, m: id_oferecimento, sigla: sigla, nota: nota, peso: peso}, function(data) {
			if(data != 0) {
				$("#div_nova_nota_"+id_oferecimento).remove();
				data = parseInt(data);
				if(isExame){
					$("#div_media_"+id_oferecimento).after('<div id="div_nota_'+data+'" class="nota"><div class="nota_sigla">'+sigla+' <a href="#" onclick="return Alterar_Nota('+data+', '+id_oferecimento+');"><img src="../web/images/EditOFF.png" alt="Alterar" title="Alterar" class="nota_botao_lapis" /></a> <a href="#" onclick="return Remover_Nota('+data+');"><img src="../web/images/CancelOFF.png" alt="Excluir" title="Excluir"  class="nota_botao_x"/></a></div><div class="nota_texto"><span class="nota_texto">Nota</span>: <span class="nota_valor">'+parseFloat(nota.replace(",",".")).toFixed(2).replace(".",",")+'</span></div></div>');
					$("#funcao_exame_"+id_oferecimento).remove();
				}
				else{
					if($("#div_media_"+id_oferecimento).length >= 1){
						$("#div_media_"+id_oferecimento).before('<div id="div_nota_'+data+'" class="nota"><div class="nota_sigla">'+sigla+' <a href="#" onclick="return Alterar_Nota('+data+', '+id_oferecimento+');"><img src="../web/images/EditOFF.png" alt="Alterar" title="Alterar" class="nota_botao_lapis" /></a> <a href="#" onclick="return Remover_Nota('+data+');"><img src="../web/images/CancelOFF.png" alt="Excluir" title="Excluir"  class="nota_botao_x"/></a></div><div class="nota_texto"><span class="nota_texto">Nota</span>: <span class="nota_valor">'+parseFloat(nota.replace(",",".")).toFixed(2).replace(".",",")+'</span></div><div class="peso_texto"><span class="peso_texto">Peso</span>: <span class="peso_valor">'+parseFloat(peso.replace(",",".")).toFixed(2).replace(".",",")+'</span></div></div>');
					}else{
						$("#nota_funcoes_"+id_oferecimento).before('<div id="div_nota_'+data+'" class="nota"><div class="nota_sigla">'+sigla+' <a href="#" onclick="return Alterar_Nota('+data+', '+id_oferecimento+');"><img src="../web/images/EditOFF.png" alt="Alterar" title="Alterar" class="nota_botao_lapis" /></a> <a href="#" onclick="return Remover_Nota('+data+');"><img src="../web/images/CancelOFF.png" alt="Excluir" title="Excluir"  class="nota_botao_x"/></a></div><div class="nota_texto"><span class="nota_texto">Nota</span>: <span class="nota_valor">'+parseFloat(nota.replace(",",".")).toFixed(2).replace(".",",")+'</span></div><div class="peso_texto"><span class="peso_texto">Peso</span>: <span class="peso_valor">'+parseFloat(peso.replace(",",".")).toFixed(2).replace(".",",")+'</span></div></div>');
					}
				}
				Calcular_Medias(id_oferecimento);
				if($("#link_novo_exame_"+id_oferecimento).length == 0 && !isExame){
					$("#funcao_nota_"+id_oferecimento).after('<div class="notas_funcoes" id="funcao_exame_'+id_oferecimento+'"><a href="#" id="link_novo_exame_'+id_oferecimento+'" onclick="return Adicionar_Nota('+id_oferecimento+', true);">Adicionar Exame</a></div>');
				}
			} else {
				$("#div_nova_nota_"+id_oferecimento+" > input").removeClass('enviando');
				$("#salvar_nova_nota_"+id_oferecimento).show();
			}
		});
		return false;
	});
	$("#cancelar_nova_nota_"+id_oferecimento).click(function() {
		$("#div_nova_nota_"+id_oferecimento).remove();
		return false;
	});
	return false;
}

var Alterar_Nota = function(id, id_oferecimento) {
	var prevSigla = $("#div_nota_"+id+" > div.nota_sigla").text();
	prevSigla = $.trim(prevSigla);
	var prevNota = $("#div_nota_"+id+" > div.nota_texto > span.nota_valor").text();
	var prevPeso = $("#div_nota_"+id+" > div.peso_texto > span.peso_valor").text();
	if (prevPeso != "")
		$("#div_nota_"+id).after('<div id="div_alterar_nota_'+id+'" class="nota"><input type="text" id="alterar_nota_sigla_'+id+'" value="'+prevSigla+'" class="input_nota" /><input type="text" id="alterar_nota_valor_'+id+'" value="'+prevNota+'" class="input_nota" /> <input type="text" id="alterar_nota_peso_'+id+'" value="'+prevPeso+'" class="input_nota" /> <a href="#" id="salvar_alterar_nota_'+id+'"><img src="../web/images/SaveOFF.png" alt="Salvar" title="Salvar" class="nota_botao_save" /></a> <a href="#" id="cancelar_alterar_nota_'+id+'"><img src="../web/images/CancelOFF.png" alt="Cancelar" title="Cancelar"  class="nota_botao_x"/></a></div>');
	else
		$("#div_nota_"+id).after('<div id="div_alterar_nota_'+id+'" class="nota"><input type="text" id="alterar_nota_sigla_'+id+'" value="'+prevSigla+'" disabled="disabled" class="input_nota" /><input type="text" id="alterar_nota_valor_'+id+'" value="'+prevNota+'" class="input_nota" /> <a href="#" id="salvar_alterar_nota_'+id+'"><img src="../web/images/SaveOFF.png" alt="Salvar" title="Salvar" class="nota_botao_save" /></a> <a href="#" id="cancelar_alterar_nota_'+id+'"><img src="../web/images/CancelOFF.png" alt="Cancelar" title="Cancelar"  class="nota_botao_x"/></a></div>');
	$("#div_nota_"+id).hide();
	$("#salvar_alterar_nota_"+id).click(function() {
		$("#salvar_alterar_nota_"+id).text('Salvando...');
		var sigla = $("#alterar_nota_sigla_"+id).val();
		var nota = $("#alterar_nota_valor_"+id).val();
		var peso = $("#alterar_nota_peso_"+id).val();
		$("#div_alterar_nota_"+id+" > input").addClass('enviando');
		var tipo;
		(prevSigla == "Exame")?tipo='e':tipo='n'
		$.post('../ajax/ax_nota.php', {tp: tipo, i: id, m: id_oferecimento, sigla: sigla, nota: nota, peso: peso}, function(data) {
			if(data != 0) {
				$("#div_alterar_nota_"+id).remove();
				$("#div_nota_"+id).show();
				$("#div_nota_"+id+" > div.nota_sigla").replaceWith('<div class="nota_sigla">'+sigla+' <a href="#" onclick="return Alterar_Nota('+id+', '+id_oferecimento+');"><img src="../web/images/EditOFF.png" alt="Alterar" title="Alterar" class="nota_botao_lapis" /></a> <a href="#" id="excluir_nota_'+id+'" onclick="return Remover_Nota('+id+');"><img src="../web/images/CancelOFF.png" alt="Excluir" title="Excluir"  class="nota_botao_x"/></a></div>');
				$("#div_nota_"+id+" > div.nota_texto > span.nota_valor").replaceWith('<span class="nota_valor">'+parseFloat(nota.replace(",",".")).toFixed(2).replace(".",",")+'</span>');
				if (peso)
					$("#div_nota_"+id+" > div.peso_texto > span.peso_valor").replaceWith('<span class="peso_valor">'+parseFloat(peso.replace(",",".")).toFixed(2).replace(".",",")+'</span>');
				Calcular_Medias(id_oferecimento);
			} else {
				$("#div_alterar_nota_"+id+" > input").removeClass('enviando');
				$("#salvar_alterar_nota_"+id).text('Salvar');
			}
		});
		return false;
	})
	$("#cancelar_alterar_nota_"+id).click(function() {
		$("#div_alterar_nota_"+id).remove();
		$("#div_nota_"+id).show();
		return false;
	});
	return false;
}

var Calcular_Medias = function(id_oferecimento) {
	var accNota = 0.0;
	var accPeso = 0.0;
	var exame;
	if($("#tab_notas_"+id_oferecimento+" > div.notas > div[id*='div_nota']").length == 0){
		$("#div_media_"+id_oferecimento).remove();
		$("#link_novo_exame_"+id_oferecimento).remove();
		return false;
	}
	$("#tab_notas_"+id_oferecimento+" > div.notas > div[id*='div_nota']").each(function(){
		var nota = $(this).children("div.nota_texto").children("span.nota_valor").text();
		var peso = $(this).children("div.peso_texto").children("span.peso_valor").text();
		if(peso){
			accNota = accNota + parseFloat(nota.replace(",",".")) * parseFloat(peso.replace(",","."));
			accPeso = accPeso + parseFloat(peso.replace(",","."));
		}else if($.trim($(this).children("div.nota_sigla").text()) == "Exame"){
			exame = parseFloat(nota.replace(",","."));
		}
	});
	var media = (accNota / accPeso);
	var curMedia = $("#div_media_"+id_oferecimento).length;
	if(curMedia >= 1){
		$("#div_media_"+id_oferecimento+" > div.nota_texto > span.media_valor").replaceWith('<span class="media_valor">'+parseFloat(media).toFixed(2).replace(".",",")+'</span>');
	}else{
		$("#nota_funcoes_"+id_oferecimento).before('<div id="div_media_'+id_oferecimento+'" class="nota"><div class="nota_sigla">M&eacute;dia</div><div class="nota_texto"><span class="nota_texto">Nota</span>: <span class="media_valor">'+parseFloat(media).toFixed(2).replace(".",",")+'</span></div></div>');
	}

	if(typeof(exame) != 'undefined'){
		var mediaFinal = (media + exame)/2;
		var curMediaFinal = $("#div_media_final_"+id_oferecimento).length;
		if(curMediaFinal >= 1){
			$("#div_media_final_"+id_oferecimento+" > span.media_valor").replaceWith('<span class="media_valor">'+parseFloat(mediaFinal).toFixed(2).replace(".",",")+'</span>');
		}else{
			$("#nota_funcoes_"+id_oferecimento).before('<div id="div_media_final_'+id_oferecimento+'" class="nota"><div class="media_sigla">M&eacute;dia Final</div><span class="nota_texto">Nota</span>: <span class="media_valor">'+parseFloat(mediaFinal).toFixed(2).replace(".",",")+'</span></div>');
		}
	}else if($("#div_media_final_"+id_oferecimento).length == 1){
		$("#div_media_final_"+id_oferecimento).remove();
	}

	return false;
}

var Remover_Nota = function(id) {
	var id_oferecimento = $("#div_nota_"+id).parent().parent().attr("id").substring(10);
	var numNotas = $("#tab_notas_"+id_oferecimento+" div[id*='div_nota']").length;
	var existsExame = $("#tab_notas_"+id_oferecimento).find("div.nota_sigla:contains('Exame')").length;
	var toDelete = $("#div_nota_"+id+" > div.nota_sigla").text();
	if((numNotas == 2)&&(existsExame == 1)&&($.trim(toDelete) != "Exame")){
		var idExame = $("#tab_notas_"+id_oferecimento+" div[id*='div_nota'] div.nota_sigla:contains('Exame')").parent().attr("id").substring(9);
		Remover_Nota(idExame);
	}
	$("#excluir_nota_"+id).text("Excluindo...");
	$.post('../ajax/ax_nota.php', {tp: 'x', id: id}, function(data) {
		if(data != 0) {
			$("#div_nota_"+id).remove();
			if ($.trim(toDelete) === "Exame") {
				$("#funcao_nota_"+id_oferecimento).after('<div class="notas_funcoes" id="funcao_exame_'+id_oferecimento+'"><a href="#" id="link_novo_exame_'+id_oferecimento+'" onclick="return Adicionar_Nota('+id_oferecimento+', true);">Adicionar Exame</a></div>');
			}
			Calcular_Medias(parseInt(data));
		} else
			$("#excluir_nota_"+id).text("Excluir");
	});
	return false;
}

var Atualizar_Notas = function(periodo) {
	if(!periodo)
		periodo = '';
	$("#tab_notas").Carregando('Carregando Notas...');
	$.post('../ajax/ax_notas.php', {p: periodo}, function(data) {
		if(data) {
			$("#tab_notas").html(data);
			$("#tabs_notas").tabs();
			Tamanho_Abas('tabs_notas');
			Abas_Embaixo();
			$(window).resize(function() { Tamanho_Abas('tabs_notas'); });
			$('#periodo_notas').change(function() {
				Atualizar_Notas($(this).val());
			});
		}
	});
}
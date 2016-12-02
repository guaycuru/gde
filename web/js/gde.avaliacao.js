var avaliacao_valores = ['', '1 - De jeito nenhum', '2 - N&atilde;o muito', '3 - Mais ou menos', '4 - Razoavelmente', '5 - Muito'];
var avaliacao_cores = ['', '#CC0000', '#EE7700', '#FFFF00', '#77EE00', '#00CC00'];

var Carregar_Avaliacoes = function() {
	if($(this).val() != '') {
		var ids = $(this).attr('id').split('_');
		$("#div_avaliacoes_"+ids[1]+"_"+ids[2]).Carregando();
		$("#div_avaliacoes_"+ids[1]+"_"+ids[2]).load('../ajax/avaliacoes.php', {id_professor: ids[1], sigla: $(this).val()}, function() {
			$("#div_avaliacoes_"+ids[1]+"_"+ids[2]+" div.nota_slider").each(function() {
				Criar_Slider($(this));
			});
			$("#div_avaliacoes_"+ids[1]+"_"+ids[2]+" div.nota_slider_fixo").each(function() {
				Criar_Slider_Fixo($(this));
			});
		});
	}
}

var Enviar_Avaliacao = function(link, idp, professor, sigla) {
	if(!professor)
		professor = '';
	if(!sigla)
		sigla = '';
	link.hide();
	link.after('<span id="votando_aguarde_'+idp+'_'+professor+((sigla)?"_"+sigla:"")+'">Aguarde...</span>');
	$.post('../ajax/ax_avaliacao.php', {idp: idp, professor: professor, sigla: sigla.replace('-', ' '), nota: $("#nota_"+idp+"_"+professor+((sigla)?"_"+sigla:"")).slider("value")}, function(data) {
		if(data == 1) {
			$("#votar_nota_"+idp+"_"+professor+((sigla)?"_"+sigla:"")).before("Voto salvo com sucesso!");
			$("#votar_nota_"+idp+"_"+professor+((sigla)?"_"+sigla:"")).remove();
		} else {
			link.show();
			$("#votando_aguarde_"+idp+"_"+professor+((sigla)?"_"+sigla:"")).remove();
		}
	});
}

var Criar_Slider = function(el) {
	var id = el.attr('id');
	el.slider({
		value: 3,
		min: 1,
		max: 5,
		step: 1,
		slide: function(event, ui) {
			$("#span_"+id).html(avaliacao_valores[ui.value]);
			$("#"+id).css('background', avaliacao_cores[ui.value]);
		}
	});
	$("#span_"+id).html(avaliacao_valores[el.slider("value")]);
	$("#"+id).css('background', avaliacao_cores[el.slider("value")]);
}

var Criar_Slider_Fixo = function(el, valor) {
	var id = el.attr('id');
	var valor = $("#span_"+el.attr('id')).text().replace(',', '.') * 100;
	el.slider({
		value: valor,
		min: 100,
		max: 500,
		disabled: true
	});
	$("#"+id).css('background', avaliacao_cores[Math.round(valor / 100)]);
}

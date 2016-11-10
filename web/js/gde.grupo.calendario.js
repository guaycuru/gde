var nEventosCalendario = 0;
var Atualizar_Calendario = function(id) {
	var date = new Date();
	var d = date.getDate();
	var m = date.getMonth();
	var y = date.getFullYear();
	var id_grupo = id;

	var calendar = $('#calendar').fullCalendar({
		header: {
			left: 'today',
			center: 'prev, title, next',
			right: 'month,agendaWeek,agendaDay'
		},
		selectable: true,
		selectHelper: true,
		axisFormat: 'HH(:mm)',
		columnFormat: {
			month: 'ddd',
			week: 'ddd d/M',
			day: 'dddd d/M'
		},
		titleFormat: {
			month: 'MMMM yyyy',
			week: "d MMM [ yyyy]{ '&#8212;' d [ MMM] yyyy}",
			day: 'dddd, d MMM yyyy'
		},
		/* para resolver o problema dos caracteres usamos o valor hexadecimal do caracter */
		dayNames: ['Domingo', 'Segunda', 'Ter\xE7a', 'Quarta', 'Quinta', 'Sexta', 'S\xE1bado'],
		select: function(startDate, endDate, allDay) {
			$("#link_novo_evento").click();
			$("#div_novo_evento").html("Carregando...");
			$("#div_novo_evento").load('../ajax/ax_form_evento_grupo.php', {dti: $.guaycuru.Data(startDate), dtf: $.guaycuru.Data(endDate), hri: $.guaycuru.Hora(startDate), hrf: $.guaycuru.Hora(endDate), ad: allDay}, function() { Carregou_Novo_Evento(allDay, id_grupo); });
			calendar.fullCalendar('unselect');
		},
		
	    events: "../ajax/ax_grupo_eventos.php?id="+id_grupo,

		eventDrop: function(event,dayDelta,minuteDelta,allDay,revertFunc) {
			$.guaycuru.simnao2("Voc&ecirc; tem certeza?", 
				function(){
					$.post('../ajax/ax_grupo_evento.php', {
						id_grupo_evento: event.id, tp: 'e', data_inicio: $.guaycuru.DateTime(event.start), data_fim: $.guaycuru.DateTime(event.start), allDay: allDay},
						function(data) {
							if(data == "Erro") {
								$.guaycuru.confirmacao("Apenas moderadores podem criar / editar eventos");
								revertFunc();
							}
						});
				}, function(){
					revertFunc();
			});
		},
		
		eventResize: function(event,dayDelta,minuteDelta,revertFunc) {
			$.guaycuru.simnao2("Voc&ecirc; tem certeza?", function(){
				$.post('../ajax/ax_grupo_evento.php', {
					id_grupo_evento: event.id, tp: 'e', data_inicio: $.guaycuru.DateTime(event.start), data_fim: $.guaycuru.DateTime(event.end)},
					function(data) {
						if(data == "Erro") {
							$.guaycuru.confirmacao("Apenas moderadores podem criar / editar eventos");
							revertFunc();
						}
					});
			}, function(){
				revertFunc();
			});
		},
		
		eventClick: function(calEvent, jsEvent, view) {
			if(!calEvent.id)
				return;
			$("#link_novo_evento").click();
			$("#div_novo_evento").html("Carregando...");
			$("#div_novo_evento").load('../ajax/ax_form_evento_grupo.php', {'id': calEvent.id}, function() { Carregou_Novo_Evento(false, id_grupo); });
			calendar.fullCalendar('unselect');	
		},
		
		editable: true,
	});
	
}

var Carregou_Novo_Evento = function(allDay, id_grupo) {
	$("#nomeEvento").Valor_Padrao('Clique para adicionar um t\xEDtulo', 'padrao');
	$("#tipoEvento").Valor_Padrao('Tipo do Evento', 'padrao');
	$("#localEvento").Valor_Padrao('Local do Evento', 'padrao');
	$("#descricaoEvento").Valor_Padrao('Descri\xE7\xE3o do Evento', 'padrao');
	$("#data_inicio").Valor_Padrao('Data Inicio', 'padrao');
	$("#hora_inicio").Valor_Padrao('Hora Inicio', 'padrao');
	$("#data_inicio").datepicker({
		beforeShow: function(input, inst) { 
			var newclass = 'gde_jquery_ui'; 
			if (!inst.dpDiv.parent().hasClass('gde_jquery_ui')){ 
				inst.dpDiv.wrap('<div class="'+newclass+'"></div>') 
			} 
		}
	});
	$("#data_fim").Valor_Padrao('Data Termino', 'padrao');
	$("#hora_fim").Valor_Padrao('Hora Termino', 'padrao');
	$("#data_fim").datepicker({
		beforeShow: function(input, inst) { 
			var newclass = 'gde_jquery_ui'; 
			if (!inst.dpDiv.parent().hasClass('gde_jquery_ui')){ 
				inst.dpDiv.wrap('<div class="'+newclass+'"></div>') 
			} 
		}
	});	
		
	$("#check_dia_todo").click(function() {
		$("#hora_inicio").toggle();
		$("#hora_fim").toggle();
		$("#labelEventoInicio").toggle();
		$("#labelEventoTermino").toggle();
	})
	
	if(allDay || $("#check_dia_todo").is(":checked")) {
		$("#labelEventoInicio").hide();
		$("#labelEventoTermino").hide();
		$("#hora_inicio").hide();
		$("#hora_fim").hide();
	}

	$("#novo_evento_salvar").click(function() {
		if($("#nomeEvento").hasClass('padrao') || $("#nomeEvento").val() == '') {
			$.guaycuru.confirmacao("Preencha o nome do evento");
			return false;
		}
		
		if($("#data_inicio").val() > $("#data_fim").val()) {
			$.guaycuru.confirmacao("Data de t&eacute;rmino n&atilde;o &eacute; v&aacute;lido");
			return false;
		}
		
		if($("#tipoEvento").hasClass('padrao')) {
			$("#tipoEvento").val('');
		}
		
		if($("#localEvento").hasClass('padrao')) {
			$("#localEvento").val('');
		}
		
		if($("#descricaoEvento").hasClass('padrao')) {
			$("#descricaoEvento").val('');
		}			

		var data_inicio = $("#data_inicio").val().split("/").reverse().join("-");
		var data_fim = $("#data_fim").val().split("/").reverse().join("-");
		var allDay = true;
		
		if (!$("#check_dia_todo").is(':checked')) {
			data_inicio = data_inicio + " " + $("#hora_inicio").val();
			data_fim = data_fim + " " + $("#hora_fim").val();
			allDay = false;
		}
		$("#novo_evento_salvar").attr('disabled', 'disable');
		$.post('../ajax/ax_grupo_evento.php', {
			id_grupo: id_grupo,
			id_grupo_evento: $("#id_grupo_evento").val(), nome: $("#nomeEvento").val(), tipo: $("#tipoEvento").val(),
			descricao: $("#descricaoEvento").val(), local: $("#localEvento").val(),
			data_inicio: $("#data_inicio").val(), data_fim: $("#data_fim").val(), hora_inicio: $("#hora_inicio").val(), hora_fim: $("#hora_fim").val(),
			tp: 'a', ad: allDay}, function(data) {
				$.fancybox.close();
				if (data == "Erro") {
					$.guaycuru.confirmacao("Apenas moderadores podem criar / editar eventos");
					return false;
				} 
				if ($("#id_grupo_evento").val() != "") {
					var id = $("#id_grupo_evento").val();
					$("#calendar").fullCalendar('removeEvents', parseInt(id));
				}				
				$("#calendar").fullCalendar('addEventSource', {
					events: [
						{
							id  : $("#id_grupo_evento").val(),
							title  : $("#nomeEvento").val(),
							start  : $("#data_inicio").val(),
							end  : $("#data_fim").val(),
							allDay  : ($("#check_dia_todo").is(':checked') ? 'true' : 'false')
						}
					]
				});
				$("#calendar").fullCalendar('refetchEvents');
			}
		)
	});
	
	$("#novo_evento_cancelar").click(function() {
		$.fancybox.close();
	});
	
	$("#excluir_evento").click(function() {
		$.guaycuru.simnao2("Voc&ecirc; tem certeza?",
			function(){
				$.post('../ajax/ax_grupo_evento.php', {id_grupo_evento: $("#id_grupo_evento").val(), tp: 'r'}, function(data) {
					$.fancybox.close();
					if(data == "NaoExisteEvento") {
						$.guaycuru.confirmacao("N&atilde;o existe evento para excluir.");
						return false;
					} else
						$("#calendar").fullCalendar('removeEvents', parseInt($("#id_grupo_evento").val()));
				});
			}, function(){$.fancybox.close();});
	});
}

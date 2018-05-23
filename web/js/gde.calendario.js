var nEventosCalendario = 0;
var Atualizar_Calendario = function() {
	var date = new Date();
	var d = date.getDate();
	var m = date.getMonth();
	var y = date.getFullYear();

	var calendar = $('#calendar').fullCalendar({
		header: {
			left: 'today',
			center: 'prev, title, next',
			right: 'month,agendaWeek,agendaDay'
		},
		weekMode : 'liquid',
		selectable: true,
		selectHelper: true,
		axisFormat: 'HH(:mm)',
		buttonText:{
			today: 'Hoje',
			month: 'M&ecirc;s',
			agendaWeek: 'Semana',
			agendaDay: 'Dia'
		},
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
		dayNamesShort: ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sab'],
		monthNames: ['Janeiro', 'Fevereiro', 'Mar&ccedil;o', 'Abril', 'Maio', 'Junho', 'Julho','Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'],
		select: function(startDate, endDate, allDay) {
			$("#link_novo_evento").click();
			$("#div_novo_evento").html("Carregando...");
			$("#div_novo_evento").load(CONFIG_URL + 'ajax/form_evento.php', {dti: $.guaycuru.Data(startDate), dtf: $.guaycuru.Data(endDate), hri: $.guaycuru.Hora(startDate), hrf: $.guaycuru.Hora(endDate), ad: allDay}, function() { Carregou_Novo_Evento(allDay); });
			calendar.fullCalendar('unselect');
		},
		
		events: {
			url: CONFIG_URL + 'ajax/eventos.php',
			type: 'post'
		},

		eventDrop: function(event,dayDelta,minuteDelta,allDay,revertFunc) {
			$.guaycuru.simnao2("Voc&ecirc; tem certeza?", 
				function(){
					$.post(CONFIG_URL + 'ajax/evento.php', {
						id_evento: event.id, tp: 'e', data_inicio: $.guaycuru.DateTime(event.start), data_fim: $.guaycuru.DateTime(event.end), ad: allDay},
						function(data) {
							if(!data || !data.ok) {
								$.guaycuru.confirmacao(data.error || 'Erro!');
								revertFunc();
							}
						});
				}, function(){
					revertFunc();
			});
		},
		
		eventResize: function(event,dayDelta,minuteDelta,revertFunc) {
			$.guaycuru.simnao2("Voc&ecirc; tem certeza?", function(){
				$.post(CONFIG_URL + 'ajax/evento.php', {
					id_evento: event.id, tp: 'e', data_inicio: $.guaycuru.DateTime(event.start), data_fim: $.guaycuru.DateTime(event.end)},
					function(data) {
						if(!data || !data.ok) {
							$.guaycuru.confirmacao(data.error || 'Erro!');
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
			$("#div_novo_evento").load(CONFIG_URL + 'ajax/form_evento.php', {'id': calEvent.id}, function() { Carregou_Novo_Evento(false); });
			calendar.fullCalendar('unselect');	
		},
		
		eventAfterRender: function(event, element, view) {
			if($("#"+event.className).is(':checked'))
				element.show();
			else
				element.hide();
		},
		
		editable: true
	});
};

var Carregou_Novo_Evento = function(allDay) {
	$("#nomeEvento").Valor_Padrao('Clique para adicionar um t\xEDtulo', 'padrao');
	$("#localEvento").Valor_Padrao('Local do Evento', 'padrao');
	$("#descricaoEvento").Valor_Padrao('Descri\xE7\xE3o do Evento', 'padrao');
	$("#data_inicio").Valor_Padrao('Data Inicio', 'padrao');
	$("#hora_inicio").Valor_Padrao('Hora Inicio', 'padrao');
	$("#data_inicio").datepicker({
		dateFormat: 'dd/mm/yy',
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
		dateFormat: 'dd/mm/yy',
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
	
	if ($("#lembrete").is(':checked')) {
		$("#tipoAviso").show();
		$("#dias_aviso").show();
		$("#aviso1").show();
		$("#aviso2").show();
	}
	else {
		$("#tipoAviso").hide();
		$("#dias_aviso").hide();
		$("#aviso1").hide();
		$("#aviso2").hide();
	}
	
	$("#lembrete").click(function(){
		$("#tipoAviso").toggle();
		$("#dias_aviso").toggle();
		$("#aviso1").toggle();
		$("#aviso2").toggle();
	});
	
	if(allDay || $("#check_dia_todo").is(":checked")) {
		$("#labelEventoInicio").hide();
		$("#labelEventoTermino").hide();
		$("#hora_inicio").hide();
		$("#hora_fim").hide();
	}
	$("#tipoEvento").change(function() {
		var tipo = $(this).val();
		if (tipo == "p" || tipo == "t")
			$("#oferecimentoEvento").show();
		else
			$("#oferecimentoEvento").hide();
	})
	$("#novo_evento_salvar").click(function() {
		if($("#nomeEvento").hasClass('padrao') || $("#nomeEvento").val() == '') {
			$.guaycuru.confirmacao("Preencha o nome do evento");
			return false;
		}
		
		if($("#tipoEvento").val() == 'g' || $("#tipoEvento").val() == 'f') {
			$.guaycuru.confirmacao("Tipo do evento incorreto. Apenas prova e trabalho  s&atilde;o permitidos");
			return false;
		}
		
		var data_inicio = $("#data_inicio").val().split("/").reverse().join("-");
		var data_fim = $("#data_fim").val().split("/").reverse().join("-");
		if(data_inicio > data_fim) {
			$.guaycuru.confirmacao("Data de t&eacute;rmino n&atilde;o &eacute; v&aacute;lido");
			return false;
		}
		
		if($("#localEvento").hasClass('padrao')) {
			$("#localEvento").val('');
		}
		
		if($("#descricaoEvento").hasClass('padrao')) {
			$("#descricaoEvento").val('');
		}			
		
		if($("#lembrete").is(":checked") && $("#dias_aviso").val() <= '0') {
			$.guaycuru.confirmacao("N&uacute;mero de dias para o lembrete inv&aacute;lido.");
			return false;
		}
		
		var nome = $("#nomeEvento").val();
		var data_inicio = $("#data_inicio").val().split("/").reverse().join("-");
		var data_fim = $("#data_fim").val().split("/").reverse().join("-");
		var allDay = true;
		var lembrete = false;
		
		if($("#lembrete").is(':checked')) {
			lembrete = true;
		}
		
		if (!$("#check_dia_todo").is(':checked')) {
			data_inicio = data_inicio + " " + $("#hora_inicio").val();
			data_fim = data_fim + " " + $("#hora_fim").val();
			allDay = false;
		}
		$("#novo_evento_salvar").attr('disabled', 'disable');
		$.post(CONFIG_URL + 'ajax/evento.php', {
			id_evento: $("#id_evento").val(), nome: $("#nomeEvento").val(), tipo: $("#tipoEvento option:selected").val(),
			descricao: $("#descricaoEvento").val(), local: $("#localEvento").val(),
			data_inicio: $("#data_inicio").val(), data_fim: $("#data_fim").val(), hora_inicio: $("#hora_inicio").val(), hora_fim: $("#hora_fim").val(),
			oferecimento: $("#oferecimentoEvento option:selected").val(),
			dias_aviso: $("#dias_aviso").val(),
			tipoAviso: $("#tipoAviso").val(),
			lembrete: lembrete,
			tp: 'a', ad: allDay}, function(data) {
				$.fancybox.close();
				if(!data || !data.ok) {
					$.guaycuru.confirmacao(data.error || 'Erro!');
					return false;
				} 
				if($("#id_evento").val() != "") {
					var id = $("#id_evento").val();
					$("#calendar").fullCalendar('removeEvents', parseInt(id));
				}				
				$("#calendar").fullCalendar('renderEvent', {
							id  : $("#id_evento").val(),
							title  : $("#nomeEvento").val(),
							start  : $("#data_inicio").val(),
							end  : $("#data_fim").val(),
							className  : $("#tipoEvento option:selected").val(),
							allDay  : ($("#check_dia_todo").is(':checked') ? 'true' : 'false')
				});
				$("#calendar").fullCalendar('refetchEvents');
				Atualizar_Avisos();
				Atualiza_Avisos_Quantidade();
			}
		)
	});
	
	$("#novo_evento_cancelar").click(function() {
		$.fancybox.close();
	});
	
	$("#excluir_evento").click(function() {
		if($("#tipoEvento").val() == 'g' || $("#tipoEvento").val() == 'f') {
			$.guaycuru.confirmacao("Voc&ecirc; n&atilde;o pode excluir este evento");
			$.fancybox.close();
		} else {
			$.guaycuru.simnao2("Voc&ecirc; tem certeza?",
				function(){
					$.post(CONFIG_URL + 'ajax/evento.php', {id_evento: $("#id_evento").val(), tp: 'r'}, function(data) {
						if(!data || !data.ok) {
							$.guaycuru.confirmacao(data.error || 'Erro!');
							revertFunc();
						} else {
							$("#calendar").fullCalendar('removeEvents', parseInt($("#id_evento").val()));
							$.fancybox.close();
							Atualizar_Avisos();
							Atualiza_Avisos_Quantidade();
						}
					});
				}, function(){$.fancybox.close();});
		}
	});
};

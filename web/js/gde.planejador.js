var c = 0;
var ce = 0;
var nce = 0;
var id_planejado;
var periodo = '';
var periodo_nome = '';
var periodo_atual = '';
var diasEmNumeros = {dayNamesShort: ['1', '2', '3', '4', '5', '6', '7']};
var numerosEmDias = ['', 'Domingo', 'Segunda-feira', 'Ter\xE7a-feira', 'Quarta-feira', 'Quinta-feira', 'Sexta-feira', 'S\xE1bado'];
var calendario;
var planejador_arvore_tipos = new Object();
var Horarios = new Object();
var Extras = [];
var InicializarPlanejador = function(id) {
	var esconder_aguarde = $.guaycuru.aguarde();
	c = 0;
	ce = 0;
	id_planejado = id;
	Horarios = new Object();
	$("#planejador_creditos").html('0');
	$("#planejador_matriculas").html('');
	for(i = 0; i <= 7; i++) {
		for(j = 0; j <= 23; j++) {
			var h = i+String('00'+j).slice(-2);
			Horarios[h] = {
				ocupado: false,
				Oferecimentos: [],
				Possiveis: []
			}
		}
	}
	$("#planejador_disciplinas").after('<div id="hmenu_all" style="display:none;" class="RMenu">' +
	'<a href="#" id="hidden_hmenu_all" style="display:none;"></a>' +
		'<ul>' +
			'<li id="hmenu_titulo"></li>' +
			'<li id="planejador_extra"><a href="#" id="planejador_extra_link">Adicionar est&aacute;gio / atividade extra-curricular</a></li>' +
			'<li id="buscar_oferecimentos"><a href="#">Pesquisar Oferecimentos</a></li>' +
		'</ul>' +
	'<div>');
	$("#hidden_hmenu_all").showMenu({
		opacity: 0.9,
		width: 290,
		left: true,
		leftEvent: 'mouseup',
		query: "#hmenu_all"
	});
	$.post(CONFIG_URL + 'ajax/planejador.php', {id: id, a: 'c', c: c, pp: periodo, pa: periodo_atual}, function(data) {
		if(data == false) {
			document.location = CONFIG_URL + 'planejador/'+id;
			return;
		} else if(data == 'forbidden') {
			document.location = CONFIG_URL + 'planejador/';
			return;
		}
		c = data.c;
		PlanejadorProcessarPlanejado(data.Planejado);
		PlanejadorProcessarOferecimentos(data.Oferecimentos, false);
		PlanejadorProcessarArvore(data.Arvore);
		PlanejadorProcessarExtras(data.Extras);
		esconder_aguarde();
	});
};
var PlanejadorProcessarPlanejado = function(Planejado) {
	periodo = Planejado.periodo;
	periodo_nome = Planejado.periodo_nome;
	periodo_atual = Planejado.periodo_atual;
	$("#planejador_periodo").html(Planejado.periodo_nome);
	$("#planejador_periodo_atual").html(Planejado.periodo_atual_nome);
	$("#compartilhado_"+Planejado.compartilhado).attr('checked', true);
	$("#simulado_"+Planejado.simulado).attr('checked', true);
	$("#form_planejador_configurar > input.configurar_eliminada").remove();
	$.each(Planejado.Config, function(i, C) {
		var sr = C.sigla.replace(' ', '_');
		$("#form_planejador_configurar").prepend('<input type="checkbox" name="eliminadas[]" class="configurar_eliminada" value="'+C.sigla+'" id="eliminada_'+sr+'"'+((C.eliminada)?' checked="checked"':'')+' /><label for="eliminada_'+sr+'">'+C.sigla+'</label> <input type="checkbox" name="parciais[]" class="configurar_parcial" value="'+C.sigla+'" id="parcial_'+sr+'"'+((C.eliminada && C.parcial)?' checked="checked"':'')+' /><label for="parcial_'+sr+'">Parcialmente (m&eacute;dia entre 3,00 e 5,00)</label><br />');
		$("#parcial_"+sr).click(function() {
			if(($(this).is(':checked')) && ($("#eliminada_"+sr).is(':checked') == false)) {
				$("#eliminada_"+sr).attr('checked', true);
			}
		});
		$("#eliminada_"+sr).click(function() {
			if(($(this).is(':checked') == false) && ($("#parcial_"+sr).is(':checked'))) {
				$("#parcial_"+sr).attr('checked', false);
			}
		});
	});
};
var PlanejadorAdicionarDisciplina = function(Disciplina) {
	if($("#psd_d"+Disciplina.semestre).length == 0) { // Ainda nao tem este semestre
		$("#planejador_disciplinas").append('	<div class="planejador_semestre">'+
'		<div class="planejador_semestre_numero">'+Disciplina.semestre+'</div>'+
'		<div id="psd_d'+Disciplina.semestre+'" class="planejador_semestre_disciplinas"></div>');
	} else if($("#psd_d"+Disciplina.semestre).parent(':hidden').length > 0) { // Semestre ta escondido (N)
		$("#psd_d"+Disciplina.semestre).parent().show();
	}
	if($("#disciplina_"+Disciplina.id).length == 0) { // Ainda nao tem esta disciplina
		var ce = (Disciplina.tem) ? '' : ' psd_vazia';
		var quinzenal = (Disciplina.quinzenal) ? ' (quinzenal)' : '';
		var AA200 = (Disciplina.obs == 'AA200') ? '<br /><strong>Depende de Autoriza&ccedil;&atilde;o (AA200)</strong>' : '';
		$("#psd_d"+Disciplina.semestre).append('<div id="disciplina_'+Disciplina.id+'" class="planejador_semestre_disciplina psd_cor_'+Disciplina.c+ce+'">'+Disciplina.sigla+' ('+Disciplina.creditos+')</div>');
		$("#planejador_disciplinas").after('<div id="menu_'+Disciplina.id+'" style="display:none;" class="RMenu">' +
'	<ul>' +
'		<li>'+Disciplina.nome+quinzenal+AA200+'</li>' +
'		<li><a href="' + CONFIG_URL + 'disciplina/'+Disciplina.id+'" target="_blank">Informa&ccedil;&otilde;es da Disciplina</a></li>' +
'	</ul>' +
'</div>');
		$("#disciplina_"+Disciplina.id).showMenu({
			opacity: 0.9,
			width: 275,
			left: true,
			query: "#menu_"+Disciplina.id
		});
	}
};
var PlanejadorPodeQuinzenais = function(OA, OB) {
	return ((OA.Disciplina.quinzenal === true) && (OB.Disciplina.quinzenal === true));
};
var PlanejadorLiBinds = {
	mouseenter: function() {
		if($(this).data('emcima')) // Previne que aconteca multiplas vezes
			return;
		$(this).data('emcima', true);
		var Oferecimento = $(this).data('Oferecimento');
		if(Oferecimento.fechado)
			return;
		if(Oferecimento.adicionado) {
			calendario.fullCalendar('removeEventSource', Oferecimento.eventSources);
			Oferecimento.eventSources.textColor = '#FFFFFF';
			Oferecimento.eventSources.backgroundColor = '#000000';
		}
		calendario.fullCalendar('addEventSource', Oferecimento.eventSources);
	},
	mouseleave: function() {
		$(this).data('emcima', false);
		var Oferecimento = $(this).data('Oferecimento');
		if(Oferecimento.adicionado) {
			calendario.fullCalendar('removeEventSource', Oferecimento.eventSources);
			Oferecimento.eventSources.textColor = '#000000';
			Oferecimento.eventSources.backgroundColor = Oferecimento.Disciplina.cor;
			calendario.fullCalendar('addEventSource', Oferecimento.eventSources);
			return;
		}
		if(!Oferecimento.fechado) { // Nao adicionei este evento, removo ele
			calendario.fullCalendar('removeEventSource', Oferecimento.eventSources);
		}
	},
	click: function(e) {
		var Oferecimento = $(this).data('Oferecimento');
		e.stopPropagation();
		if(!Oferecimento.possivel) // Nao pode ser adicionado
			return;
		if(Oferecimento.adicionado) // Ja foi adicionado
			PlanejadorRemoverOferecimento(Oferecimento, false, true, false);
		else // Ainda nao foi adicionado
			PlanejadorAdicionarOferecimento(Oferecimento, false, true);
		$(document).click(); // Fecho o menu
	}
};
var PlanejadorProcessarConflitos = function(O, P) {
	// Este ja foi adicionado ou marcado ou eh da mesma disciplina ou eh quinzenal
	if((P.adicionado) || (!P.possivel) || (P.siglan == O.siglan) || (PlanejadorPodeQuinzenais(O, P)))
		return true;
	P.possivel = false;
	P.eventSources.textColor = '#FFFFFF'; // E mudo a cor para preto
	P.eventSources.backgroundColor = '#000000'; // E mudo a cor do texto para branco
};
var PlanejadorProcessarOferecimentos = function(Oferecimentos, atualizar_conflitos) {
	var a = 0;
	var Adicionar = [];
	$.each(Oferecimentos, function(sigla, Dados) {
		PlanejadorAdicionarDisciplina(Dados.Disciplina);
		if(Dados.Disciplina.tem == false) {
			if(Dados.Disciplina.pode)
				var tmp = '<li>Esta Disciplina n&atilde;o ser&aacute; oferecida<br />em '+periodo_nome+'.</li>';
			else if(Dados.Disciplina.obs == 'ja_cursou')
				var tmp = '<li>Voc&ecirc; j&aacute; cursou esta Disciplina.</li>';
			else
				var tmp = '<li>Voc&ecirc; n&atilde;o poder&aacute; cursar esta Disciplina<br />em '+periodo_nome+', pois n&atilde;o cursou um ou mais de seus pr&eacute;-requisitos.</li>';
			$("#menu_"+Dados.Disciplina.id+" ul").append(tmp);
		} else {
			$.each(Dados.Oferecimentos, function(i, O) {
				if(O.fechado)
					O.li = '<a href="#" class="oferecimento_'+O.id+' pso_fechado">'+O.link+' FECHADO!</a>';
				else {
					var classe = (O.viola_reserva) ? ' pso_viola' : '';
					if (O.viola_reserva)
						O.link += ' -  VIOLA RESERVA!';
					O.li = '<a href="#" class="oferecimento_' + O.id + classe + '">' + O.link + '</a>';
					if (!Array.isArray(O.professores) || O.professores.length === 0)
						O.li += '<div>Docente(s) Desconhecido(s)</div>';
					for (p in O.professores) {
						var nome = O.professores[p]['nome'];
						var mediap = O.professores[p]['mediap'];
						var media1 = O.professores[p]['media1'];
						var media2 = O.professores[p]['media2'];
						var media3 = O.professores[p]['media3'];
						O.li += '<div>' + nome + '</div>' +
							'<div class="planejador_estrelas">' +
							'<div class="planejador_estrelas1">Geral<br />' +
							((mediap > 0) ? '<div class="estrelas" style="width: ' + mediap + 'px;">' : '<div class=\"estrelas_nd\">') + '&nbsp;</div>' +
							'</div>' +
							'<div class="planejador_estrelas2">Coer&ecirc;ncia<br />' +
							((media1 > 0) ? '<div class="estrelas" style="width: ' + media1 + 'px;">' : '<div class=\"estrelas_nd\">') + '&nbsp;</div>' +
							'</div>' +
							'<div class="planejador_estrelas3">Aprendizado<br />' +
							((media2 > 0) ? '<div class="estrelas" style="width: ' + media2 + 'px;">' : '<div class=\"estrelas_nd\">') + '&nbsp;</div>' +
							'</div>' +
							'<div class="planejador_estrelas4">Facilidade<br />' +
							((media3 > 0) ? '<div class="estrelas" style="width: ' + media3 + 'px;">' : '<div class=\"estrelas_nd\">') + '&nbsp;</div>' +
							'</div>' +
							'</div>' +
							'<div class="planejador_estrelas_fim"></div>';
					}
				}
				O.Disciplina = Dados.Disciplina;
				$("#menu_"+Dados.Disciplina.id+" ul").append('<li id="li_oferecimento_'+O.id+'" class="planejador_oferecimento">' + O.li + '</li>');
				$("#li_oferecimento_"+O.id).bind(PlanejadorLiBinds);
				$("#li_oferecimento_"+O.id).data('Oferecimento', O);
				$("a.oferecimento_"+O.id).click(function(e) {
					e.stopPropagation();
					$(this).parent().click();
					return false;
				});
				$.each(O.horarios, function(i, h) { // Adiciona este as listas de possiveis
					if(atualizar_conflitos) {
						$.each(Horarios[h].Oferecimentos, function(j, P) {
							PlanejadorProcessarConflitos(P, O);
						});
					}
					Horarios[h].Possiveis.splice(Horarios[h].Possiveis.length, 0, O);
				});
				if(O.adicionado) // Adiciona este Oferecimento caso ele ja tenha sido adicionado no planejador anteriormente
					Adicionar[a++] = O;
			});
		}
	});
	$.each(Adicionar, function(i, O) {
		PlanejadorAdicionarOferecimento(O, true, false);
	});
};
var PlanejadorAdicionarOferecimento = function(Oferecimento, sources, salvar) {
	if(salvar) {
		var tmp = $("#planejador_matriculas").CarregandoL();
		$.post(CONFIG_URL + 'ajax/planejador.php', {a: 'a', id: id_planejado, o: Oferecimento.id}, function(res) {
			tmp.remove();
			if(res.ok == false) {
				//PlanejadorRemoverOferecimento(Oferecimento, true, false);
				window.location.reload();
				return;
			} else {
				if(res.Removido !== false)
					PlanejadorRemoverOferecimento($("#li_oferecimento_"+res.Removido.id).data('Oferecimento'), true, false, true);
				PlanejadorProcessarArvore(res.Arvore);
			}
		});
	}
	if(sources) // Tambem tenho que adicionar os eventos
		calendario.fullCalendar('addEventSource', Oferecimento.eventSources);
	var Disciplina = Oferecimento.Disciplina;
	Oferecimento.adicionado = true;
	$.each(Oferecimento.horarios, function(i, h) { // Para cada horario deste oferecimento
		Horarios[h].ocupado = true;
		Horarios[h].Oferecimentos.splice(Horarios[h].Oferecimentos.length, 0, Oferecimento); // Adiciono este oferecimento ao horario
		$.each(Horarios[h].Possiveis, function(j, P) { // Marco todos os possiveis como impossibilitados
			PlanejadorProcessarConflitos(Oferecimento, P);
		});
	});
	$("a.oferecimento_"+Oferecimento.id).html("Remover "+Oferecimento.link);
	$("#disciplina_"+Oferecimento.Disciplina.id).addClass('psd_adicionada');
	$("#planejador_creditos").html(parseInt($("#planejador_creditos").html()) + parseInt(Disciplina.creditos));
	$("#planejador_matriculas").append('<div id="matricula_'+Oferecimento.id+'" class="matricula_conjunto mtr_cor_'+Oferecimento.Disciplina.c+'"><div class="matricula_sigla">'+Disciplina.sigla+' '+Oferecimento.turma+' ('+Disciplina.creditos+')</div></div>');
	PlanejadorBindMostrarInfo($("#matricula_"+Oferecimento.id), Oferecimento);
	$("#matricula_"+Oferecimento.id).bind('mouseenter mouseleave click', function(e) {
		if(e.type == 'click') {
			PlanejadorRemoverOferecimento(Oferecimento, true, true, false);
			$("#li_oferecimento_"+Oferecimento.id).triggerHandler('mouseleave');
		} else
			$("#li_oferecimento_"+Oferecimento.id).triggerHandler(e);
	});
};
var PlanejadorRemoverOferecimento = function(Oferecimento, sources, salvar, automatico) {
	if(salvar) {
		var tmp = $("#planejador_matriculas").CarregandoL();
		$.post(CONFIG_URL + 'ajax/planejador.php', {id: id_planejado, a: 'r', o: Oferecimento.id}, function(res) {
			tmp.remove();
			if(res.ok == false) {
				//PlanejadorAdicionarOferecimento(Oferecimento, true, false);
				window.location.reload();
				return;
			} else {
				PlanejadorProcessarArvore(res.Arvore);
			}
		});
	}
	if(sources) // Tambem tenho que remover os eventos
		calendario.fullCalendar('removeEventSource', Oferecimento.eventSources);
	$.each(Oferecimento.horarios, function(i, h) { // Para cada horario deste oferecimento
		Horarios[h].ocupado = false;
		for(o in Horarios[h].Oferecimentos) // Remove este oferecimento deste horario
			if(Horarios[h].Oferecimentos[o].id == Oferecimento.id)
				Horarios[h].Oferecimentos.splice(i, 1);
		$.each(Horarios[h].Possiveis, function(j, P) { // Des-impossibilito todos os possiveis
			if(P.possivel)
				return true;
			P.possivel = true;
			P.eventSources.textColor = '#000000';
			P.eventSources.backgroundColor = P.Disciplina.cor;
		});
	});
	$("#matricula_"+Oferecimento.id).remove();
	$("a.oferecimento_"+Oferecimento.id).html(Oferecimento.link);
	$("#planejador_creditos").html(parseInt($("#planejador_creditos").html()) - parseInt(Oferecimento.Disciplina.creditos));
	if(automatico === false)
		$("#disciplina_"+Oferecimento.Disciplina.id).removeClass('psd_adicionada');
	Oferecimento.adicionado = false;
	Oferecimento.possivel = true;
	Oferecimento.eventSources.textColor = '#000000';
	Oferecimento.eventSources.backgroundColor = Oferecimento.Disciplina.cor;
};
var PlanejadorProcessarArvore = function(Arvore) {
	$("#planejador_cp").html(Arvore.cp);
	$("#planejador_cpf").html(Arvore.cpf);
	$("#planejador_integralizacao").html(Arvore.integralizacao);
	$.each(Arvore.tipos, function(s, t) {
		planejador_arvore_tipos[s] = t;
	});
	if((Arvore.cp == '1,0000') && ($("#span_cp1").length == 0))
		$("#planejador_disciplinas").prepend('<span id="span_cp1"><strong>N&atilde;o falta nenhuma Disciplina para voc&ecirc; cursar...</strong><br />Infelizmente o GDE (ainda) n&atilde;o tem suporte<br />a planejamento do <i>futuro fora da Unicamp</i>...<br />Boa sorte! :)<br /><br /><span>');
};
var PlanejadorProcessarExtras = function(Lista) {
	$.each(Lista, function(i, E) {
		var x = -1;
		$.each(Extras, function(j, X) {
			if(X.nome == E.title) {
				x = j;
				return true;
			}
		});
		if(x == -1)
			x = PlanejadorCriarNovoExtra(E.title);
		PlanejadorAdicionarExtra(E, x);
	});
};
var PlanejadorAdicionarExtraPopUp = function(dia, hr1, hr2) {
	$("#extra_dia_da_semana").val(dia);
	$("#extra_horario1").val(hr1+':00');
	$("#extra_horario2").val(hr2+':00');
	$("#link_novo_extra").click();
	return false;
};
var PlanejadorAdicionarExtraPopUpFechar = function(botao) {
	if(botao == 'salvar') {
		if(($("#extra_lista_nomes").val() == '-1') && ($("#extra_novo_nome").val().length < 2)) {
			$.guaycuru.confirmacao('O nome informado &eacute; inv&aacute;lido!');
			return false;
		} else if($("#extra_horario1").val().search(/^[0-2]?\d\:[0-5]\d$/) == -1) {
			$.guaycuru.confirmacao('Formato de hora inicial inv&aacute;lido!');
			return false;
		} else if($("#extra_horario2").val().search(/^[0-2]?\d\:[0-5]\d$/) == -1) {
			$.guaycuru.confirmacao('Formato de hora final inv&aacute;lido!');
			return false;
		} else {
			if($("#extra_lista_nomes").val() == '-1')
				var e = PlanejadorCriarNovoExtra($("#extra_novo_nome").val());
			else
				var e = $("#extra_lista_nomes").val();
			$.post(CONFIG_URL + 'ajax/planejador.php', {id: id_planejado, a: 'ae', c: Extras[e].cor, nome: Extras[e].nome, dia: $("#extra_dia_da_semana").val(), inicio: $("#extra_horario1").val()+':00', fim: $("#extra_horario2").val()+':00'}, function(res) {
				if(res && res.ok)
					PlanejadorAdicionarExtra(res.c ? res.c : '');
				else {
					if (res.error)
						var msg = res.error;
					else
						var msg = 'Ocorreu um erro inesperado. Por favor, tente novamente mais tarde.';
					$.guaycuru.confirmacao(msg);
				}
			});
		}
	}
	$("#extra_novo_nome").val('');
	$("#extra_dia_da_semana").val('');
	$("#extra_horario1").val('');
	$("#extra_horario2").val('');
	$.fancybox.close();
};
var PlanejadorCriarNovoExtra = function(nome) {
	var e = Extras.length;
	if(ce >= nce)
		ce = -1;
	Extras[e] = {
		id: e,
		nome: nome,
		cor: ce++
	};
	$("#extra_lista_nomes").append('<option value="'+e+'">'+nome+'</option>');
	$("#extra_lista_nomes").val(e);
	$("#extra_novo_nome").hide();
	return e;
};
var PlanejadorAdicionarExtra = function(event, e) {
	calendario.fullCalendar('renderEvent', event);
};
var PlanejadorRemoverExtra = function(event) {
	calendario.fullCalendar('removeEvents', event.id);
};
var PlanejadorAdicionarEletiva = function(id, after) {
	if(id == undefined)
		return;
	esconde = $.guaycuru.aguarde();
	$("#sigla_eletiva").val("");
	$.post(CONFIG_URL + 'ajax/planejador.php', {id: id_planejado, a: 'c', c: c, d: id}, function(data) {
		if(data == false) {
			window.location.reload();
			return;
		}
		PlanejadorProcessarOferecimentos(data.Oferecimentos, true);
		if(after)
			after();
		esconde();
	});
};
var PlanejadorBindMostrarInfo = function(elemento, Oferecimento) {
	var DivTip = $("#planejador_divtip");
	elemento.bind({
		mouseenter: function() {
			var ori = elemento.offset();
			var off = {top: ori.top + elemento.outerHeight(), left: ori.left};
			
			if(Oferecimento.total == '-1') {
				Oferecimento.total = '?';
				var qts_amigos = '?';
				$.post(CONFIG_URL + 'ajax/planejador.php', {a: 'cd', id: id_planejado, oid: Oferecimento.id}, function(data) {
					Oferecimento.total = data.total;
					Oferecimento.Amigos = data.Amigos;
					$("#divtip_amigos").html(Oferecimento.Amigos.length);
					$("#divtip_total").html(Oferecimento.total);
					$("#divtip_lista_amigos").html(Oferecimento.Amigos.join(', '));
				});
			} else
				var qts_amigos = Oferecimento.Amigos.length;

			$("#divtip_disciplina").html(Oferecimento.Disciplina.nome);
			$("#divtip_professor").html(Oferecimento.professor);
			$("#divtip_tipo").html(planejador_arvore_tipos[Oferecimento.siglan]);
			$("#divtip_viola").html((Oferecimento.viola_reserva) ? '<strong>Sim</strong>' : 'N&atilde;o');
			$("#divtip_AA200").html((Oferecimento.Disciplina.obs == 'AA200') ? '<strong>Sim</strong>' : 'N&atilde;o');
			$("#divtip_amigos").html(qts_amigos);
			$("#divtip_total").html(Oferecimento.total);
			$("#divtip_lista_amigos").html(Oferecimento.Amigos.join(', '));
			$("#divtip_vagas").html(Oferecimento.vagas);
			
			if(DivTip.is(':visible'))
				clearTimeout(DivTip.data('id_timeout'));
			else
				DivTip.show('fast'); // Mostra
			
			DivTip.offset(off); // Seta a posicao
		},
		mouseleave: function() {
			var id_timeout = setTimeout(function(){
				DivTip.hide('fast');
			}, 300);
			DivTip.data('id_timeout', id_timeout);
		}
	});
};
$(document).ready(function() {
	calendario = $('#planejador_calendario').fullCalendar({
		header: {
			left: '',
			center: '',
			right: ''
		},
		columnFormat: {
			week: "ddd"
		},
		defaultView: 'agendaWeek',
		selectable: true,
		selectHelper: false,
		editable: true,
		allDaySlot: false,
		allDayDefault: false,
		ignoreTimezone: true,
		year: 2003,
		month: 11,
		slotMinutes: 60,
		height: 403,
		//aspectRatio: 0.8,
		firstDay: 1,
		firstHour: 7,
		minTime: 7,
		axisFormat: 'H:mm',
		timeFormat: 'H:mm{ - H:mm}',
		weekends: false,
		weekendDays: [0],
		dayNames: ['Domingo', 'Segunda', 'Ter\xE7a', 'Quarta', 'Quinta', 'Sexta', 'S\xE1bado'],
		dayNamesShort: ['Dom', 'Seg', 'Ter', 'Quar', 'Qui', 'Sex', 'S\xE1b'],
		eventTextColor: '#000000',
		eventClick: function(event, jsEvent) {
			if(!event.editable) { // Somente para oferecimentos...
				var Oferecimento = $("#li_oferecimento_"+event.id).data('Oferecimento');
				PlanejadorRemoverOferecimento(Oferecimento, true, true, false);
			} else {
				$.post(CONFIG_URL + 'ajax/planejador.php', {id: id_planejado, a: 're', ide: event.id.replace('extra_', '')}, function(res) {
					if(res && res.ok)
						PlanejadorRemoverExtra(event);
					else {
						if (res.error)
							var msg = res.error;
						else
							var msg = 'Ocorreu um erro inesperado. Por favor, tente novamente mais tarde.';
						$.guaycuru.confirmacao(msg);
					}
				});
			}
		},
		eventResize: function(event, dayDelta, minuteDelta, revertFunc) {
			if(($.fullCalendar.formatDate(event.start, 'd') != $.fullCalendar.formatDate(event.end, 'd')) && ($.fullCalendar.formatDate(event.end, 'H') != 0))
				revertFunc();
			else {
				$.post(CONFIG_URL + 'ajax/planejador.php', {id: id_planejado, a: 'ee', ide: event.id.replace('extra_', ''), t: 'r', dd: dayDelta, md: minuteDelta}, function(res) {
					if(!res || !res.ok) {
						if (res.error)
							var msg = res.error;
						else
							var msg = 'Ocorreu um erro inesperado. Por favor, tente novamente mais tarde.';
						$.guaycuru.confirmacao(msg);
						revertFunc();
					}
				});
			}
		},
		eventDrop: function(event, dayDelta, minuteDelta, allDay, revertFunc) {
			if(($.fullCalendar.formatDate(event.start, 'd') != $.fullCalendar.formatDate(event.end, 'd')) && ($.fullCalendar.formatDate(event.end, 'H') != 0))
				revertFunc();
			else {
				$.post(CONFIG_URL + 'ajax/planejador.php', {id: id_planejado, a: 'ee', ide: event.id.replace('extra_', ''), t: 'd', dd: dayDelta, md: minuteDelta}, function(res) {
					if(!res || !res.ok) {
						if (res.error)
							var msg = res.error;
						else
							var msg = 'Ocorreu um erro inesperado. Por favor, tente novamente mais tarde.';
						$.guaycuru.confirmacao(msg);
						revertFunc();
					}
				});
			}
		},
		select: function(startDate, endDate, allDay, jsEvent) {
			var d = $.fullCalendar.formatDate(startDate, 'ddd', diasEmNumeros);
			var hr1 = parseInt($.fullCalendar.formatDate(startDate, 'H', diasEmNumeros));
			var hr2 = parseInt($.fullCalendar.formatDate(endDate, 'H', diasEmNumeros)) - 1;
			var foi = new Object;
			
			// Limpa o menu
			$("#hmenu_all li").not("#hmenu_titulo, #planejador_extra, #buscar_oferecimentos").each(function() {
				$(this).remove();
			});
			
			if(hr1 == hr2) {
				$("#hmenu_titulo").html('Oferecimentos para '+numerosEmDias[d]+' &agrave;s '+hr1+':00');
				
				$('#buscar_oferecimentos').show();
			} else {
				$("#hmenu_titulo").html('Oferecimentos para '+numerosEmDias[d]+' das '+hr1+':00 &agrave;s '+(hr2+1)+':00');
				
				//Nao existe busca implementada para um periodo de horarios, logo escondemos a pesquisa
				$('#buscar_oferecimentos').hide();
			}
			
			var qts = 0;
			for(i = hr1; i <= hr2; i++) {
				var h = d + ((i < 10) ? '0' : '') + i;
				$.each(Horarios[h].Possiveis, function(j, P) {
					if(foi[P.id])
						return true;
					foi[P.id] = true;
					qts++;
					$("#planejador_extra").before('<li id="li_hoferecimento_'+P.id+'" class="planejador_oferecimento">' + P.li + '</li>');
					$("#li_hoferecimento_"+P.id).bind('mouseenter mouseleave click', function(e) {
						$("#li_oferecimento_"+P.id).triggerHandler(e);
					});
				});
			}
			if(qts == 0)
				$("#planejador_extra").before('<li style="font-style: italic;">Nenhum oferecimento dispon&iacute;vel para o hor&aacute;rio selecionado.</li>');
			
			$("#planejador_extra").unbind('click');
			$("#planejador_extra").click(function(e) {
				$(document).click();
				PlanejadorAdicionarExtraPopUp(d-1, hr1, hr2+1);
				e.stopPropagation();
				return false;
			});
			
			//Abre a pagina de busca de oferecimentos no horario selecionado
			$("#buscar_oferecimentos").unbind('click');
			$('#buscar_oferecimentos').click(function() {
				linkBusca = 'Busca.php?t=tab_oferecimentos&periodo='+periodo+'&dia='+d+'&horario='+hr1+'&resultados_pagina=20&buscar=+#tab_oferecimentos';
				window.open(linkBusca);
			});
			
			$("#hidden_hmenu_all").triggerHandler(jsEvent); // Abre o menu
		},
		eventAfterRender: function(event, element, view) {
			if(parseInt($.fullCalendar.formatDate(event.start, 'H')) == parseInt($.fullCalendar.formatDate(event.end, 'H')) - 1) {
				element.find('div.fc-event-time').hide();
				element.find('div.fc-event-content').html(event.title).css('font-size', '0.9em');
			}
		}
	});
	$("#toggle_integralizacao").click(function() {
		if($("#planejador_integralizacao").is(':hidden')) {
			$(this).html("Ocultar Integraliza&ccedil;&atilde;o Planejada");
			$("#planejador_integralizacao").show();
		} else {
			$(this).html("Mostrar Integraliza&ccedil;&atilde;o Planejada");
			$("#planejador_integralizacao").hide();
		}
		return false;
	});
	$("#toggle_configurar").click(function() {
		$("#planejador_configurar").toggle();
		return false;
	});
	$("#form_planejador_configurar").submit(function() {
		var aguarde = $.guaycuru.aguarde();
		var c = 0;
		var p = 0;
		var config = [];
		var parciais = [];
		$(this).find("input.configurar_eliminada:checked").each(function(i, e) {
			config[c++] = $(e).val();
		});
		$(this).find("input.configurar_parcial:checked").each(function(i, e) {
			parciais[p++] = $(e).val();
		});
		$.post(CONFIG_URL + 'ajax/planejador.php', {a: 'f', id: id_planejado, 'conf[]': config, 'parciais[]': parciais}, function(data) {
			window.location.reload();
		});
		return false;
	});
	$("#compartilhado_t, #compartilhado_f").click(function() {
		var v = $(this).val();
		var tmp = $(this).CarregandoL();
		$.post(CONFIG_URL + 'ajax/planejador.php', {id: id_planejado, a: 'm', v: v}, function(res) {
			if(res == false)
				$("#compartilhado_"+((v == 'f')?'t':'f')).attr('checked', 'checked');
			tmp.remove();
		});
	});
	$("#simulado_t, #simulado_f").click(function() {
		var v = $(this).val();
		$(this).CarregandoL();
		$.post(CONFIG_URL + 'ajax/planejador.php', {id: id_planejado, a: 's', v: v}, function(res) {
			if(res == false)
				$("#simulado_"+((v == 'f')?'t':'f')).attr('checked', 'checked');
			window.location.reload();
		});
	});
	$("#link_novo_extra").fancybox({
		'autoDimensions' : true,
		'hideOnContentClick': false
	});
	$("#novo_extra_salvar").click(function() {
		PlanejadorAdicionarExtraPopUpFechar('salvar');
	});
	$("#novo_extra_cancelar").click(function() {
		PlanejadorAdicionarExtraPopUpFechar('cancelar');
	});
	$("#visualizar_impressao").click(function() {
		window.open(CONFIG_URL + 'planejador/?idi='+id_planejado, '_blank', 'width=700, height=550, scrollbars=yes');
		return false;
	});
	$("#extra_lista_nomes").change(function() {
		if($(this).val() == '-1')
			$("#extra_novo_nome").show();
		else
			$("#extra_novo_nome").hide();
	});
	$("#sigla_eletiva").Valor_Padrao('Digite a sigla ou o nome da disciplina.', 'padrao').Autocompletar({
		json: CONFIG_URL + 'ajax/disciplinas.php',
		data: {tp: 3},
		idField: 'sigla',
		valField: 'nome',
		delay: 500,
		minLength: 3,
		highlight: true,
		instantaneo: true,
		instantaneo_delay: 100,
		obrigatorio: false,
		maxHeight: '350px',
		change: function(event, ui) {
			if(!ui.item)
				$("#sigla_eletiva").Padrao();
		},
		select: function(event, ui) {
			var id = ui.item.raw.id;
			console.log(id);
			if($("#disciplina_"+id).length == 0)
				PlanejadorAdicionarEletiva(id, function() { $("#sigla_eletiva").Padrao(); });
			else {
				$("#sigla_eletiva").Padrao();
				return false;
			}
		},
		create: function(event, ui) { $("ul.ui-autocomplete").not("div.gde_jquery_ui > ul").wrap('<div class="gde_jquery_ui" />'); }
	});
	$("#planejador_opcoes").buttonset();
	$("#planejador_opcoes input").click(function() {
		var aguarde = $.guaycuru.aguarde();
		var id = $(this).val();
		if(id == 'n') {
			$.post(CONFIG_URL + 'ajax/planejador.php', {a: 'n', pp: periodo, pa: periodo_atual}, function(res) {
				if(res.ok === false)
					window.location.reload();
				else
					document.location = CONFIG_URL + 'planejador/'+res.id;
			});
		} else
			document.location = CONFIG_URL + 'planejador/'+id;
	});
	$(".planejador_excluir").click(function() {
		$.guaycuru.simnao('Tem certeza que deseja excluir esta op&ccedil;&atilde;o?', function() {
			$.post(CONFIG_URL + 'ajax/planejador.php', {a: 'x', id: id_planejado}, function(res) {
				if(res.ok === false)
					window.location.reload();
				else
					document.location = CONFIG_URL + 'planejador/'+res.id;
			});
		});
		return false;
	});
	$("#planejador_divtip").bind({
		mouseenter: function() {
			var DivTip = $(this);
			clearTimeout(DivTip.data('id_timeout'));
		},
		mouseleave: function() {
			var DivTip = $(this);
			var id_timeout = setTimeout(function(){
				DivTip.hide('fast');
			}, 300);
			$(this).data('id_timeout', id_timeout);
		}
	});
	$.guaycuru.tooltip("TT_eletivas", "Eletiva:", "Digite a sigla ou o nome da disciplina eletiva que deseja adicionar.", {});
	$.guaycuru.tooltip("TT_planejador_dicas", "Dicas:", "<ul><li>Clique e arraste o mouse em algum hor&aacute;rio / faixa de hor&aacute;rios para uma lista de poss&iacute;veis oferecimentos, ou para adicionar um est&aacute;gio / atividade extra-curricular.</li><li>A integraliza&ccedil;&atilde;o e o CP / CPF correspondem ao per&iacute;odo planejado, com os dados atuais do planejador.</li><li>Para excluir um oferecimento / est&aacute;gio, clique no mesmo.", {});
});

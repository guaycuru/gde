var mostra_online = true;
var mostra_offline = false;
var n_requisicao = 0;
var id_chat = 0;
var amigos_nicks = [];
var amigos_nomes = [];
var amigos_fotos = [];
var amigos_status = [];
var status_janelas = [];
var nAmigosOn = 0;
var nAmigosOff = 0;
var meu_status = 'd';
var atualiza_lista1 = false;
var atualiza_lista3 = false;

var Iniciar_Chat = function() {
	Atualizar_Chat();
	$("#chatMiguxo").show();
	Atualizar_Coisas(true);
}

var Atualizar_Coisas = function(atualiza_chat) {
	$.post(CONFIG_URL + 'ajax/ax_xml.php', {n: n_requisicao, id_chat: id_chat, ac: (atualiza_chat) ? 1 : 0}, function(data){
		if($(data).find('deslogado').length > 0) {
			document.location = 'VisaoLogin.php';
			return;
		}
		n_requisicao++;
		$(data).find('chat_status').each(function() {
			var status = $(this).text();
			Change_Chat_Image(status);
			meu_status = status;
		});
		
		// Parseando lista de amigos
		$(data).find('amigos').each(function() {
			nAmigosOn = 0;
			nAmigosOff = 0;
			var chatAmigosOnHtml = "";
			var chatAmigosOffHtml = "";
			var chatAmigosOcupadosHtml = "";
			var chatAmigosHtml = "";
			$(this).find('amigo').each(function() { // Informacoes dos meus amigos
				var id = $(this).attr("id");
				if(id == meu_id)
					return true;
				var status = $(this).attr("status");
				if(!amigos_status[id])
					amigos_status[id] = status;
				if(n_requisicao == 1) {
					var nick = $(this).find("nick").text();
					var nome_completo = $(this).find("nome").text();
					var text = $(this).text();
					amigos_nicks[id] = nick;
					amigos_nomes[id] = nome_completo;
					amigos_fotos[id] = $(this).find("foto").text();
					status_janelas[id] = $(this).attr("status_janela");
				} else {
					var nome_completo = amigos_nomes[id];
					var nick = amigos_nicks[id];
				}
				
				// Arrumar icones de status pela pagina inteira
				$("img.status_icone_"+id).attr("src", CONFIG_URL + "web/images/status_" + status + ".png");
				
				if(status == "d" || status == "x" || status == "o")
					nAmigosOn++;
				else
					nAmigosOff++;
				
				// Arrumar lista de amigos, pois agora temos usuarios que entraram/sairam do chat
				if((n_requisicao == 1) || (amigos_status[id] != status)) {
					if(status == "d" || status == "x") { // Amigos disponiveis
						if(n_requisicao == 1) {
							chatAmigosOnHtml += "<div class=\"chat_amigos_lista\" id=\"chat_amigo_" + id + "\" title=\"" + nome_completo + "\"><img src=\"" + CONFIG_URL + "web/images/status_" + status + ".png\" class=\"status_icone status_icone_" + id + "\" alt=\"" + status + "\" />" + nick + "</div>";
							$("#chat_header_nome_" + id + " img").attr("src", CONFIG_URL + "web/images/status_"+status+".png");
						}
						if(amigos_status[id] != status)
							$("#chat_amigos_lista_on_body").append($("#chat_amigo_" + id));
						if(atualiza_lista1)
							$("#amigos_on_1").before($("#amigo_"+id));
						if(atualiza_lista3)
							$("#amigos_on_3").before($("#amigocomum_"+id));
					} else if(status == "o") { // Amigos ocupados
						if(n_requisicao == 1) {
							chatAmigosOcupadosHtml += "<div class=\"chat_amigos_lista\" id=\"chat_amigo_" + id + "\" title=\"" + nome_completo + "\"><img src=\"" + CONFIG_URL + "web/images/status_" + status + ".png\" class=\"status_icone status_icone_" + id + "\" alt=\"" + status + "\" />" + nick + "</div>";
							$("#chat_header_nome_" + id + " img").attr("src", CONFIG_URL + "web/images/status_"+status+".png");
						}
						if(amigos_status[id] != status)
							$("#chat_amigos_lista_on_body").append($("#chat_amigo_" + id));
						if(atualiza_lista1)
							$("#amigos_on_1").before($("#amigo_"+id));
						if(atualiza_lista3)
							$("#amigos_on_3").before($("#amigocomum_"+id));
					} else { // Amigos off
						if(n_requisicao == 1) {
							chatAmigosOffHtml += "<div class=\"chat_amigos_lista\" id=\"chat_amigo_" + id + "\" title=\"" + nome_completo + "\"><img src=\"" + CONFIG_URL + "web/images/status_" + status + ".png\" class=\"status_icone status_icone_" + id + "\" alt=\"" + status + "\" />" + nick + "</div>";
							$("#chat_header_nome_" + id + " img").attr("src", CONFIG_URL + "web/images/status_"+status+".png");
						}
						if(amigos_status[id] != status)
							$("#chat_amigos_lista_off_body").append($("#chat_amigo_" + id));
						if(atualiza_lista1)
							$("#amigos_off_1").before($("#amigo_"+id));
						if(atualiza_lista3)
							$("#amigos_off_3").before($("#amigocomum_"+id));
					}
				}
				amigos_status[id] = status;
			});
			
			$("#linkChat").text("Chat (" + nAmigosOn + ")");
			
			if(n_requisicao == 1) {
				chatAmigosHtml += "<div id=\"chat_amigos_lista_on\"><div id=\"chat_amigos_lista_on_header\"><a href=\"#\" id=\"chat_amigos_lista_on_toggle\"><img id=\"chat_amigos_lista_on_img\" src=\"" + CONFIG_URL + "web/images/botao_menos.png\" alt=\"-\" /> Online (<span id=\"chat_numero_amigos_on\">" + nAmigosOn + "</span>):</a></div><div id=\"chat_amigos_lista_on_body\">";
				chatAmigosHtml += chatAmigosOnHtml;
				chatAmigosHtml += chatAmigosOcupadosHtml;
				chatAmigosHtml += "</div></div>";
				chatAmigosHtml += "<div id=\"chat_amigos_lista_separador\" style=\"margin-bottom: 8px;\"></div>";
				chatAmigosHtml += "<div id=\"chat_amigos_lista_off\"><div id=\"chat_amigos_lista_off_header\"><a href=\"#\" id=\"chat_amigos_lista_off_toggle\"><img id=\"chat_amigos_lista_off_img\" src=\"" + CONFIG_URL + "web/images/botao_menos.png\" alt=\"-\" /> Offline (<span id=\"chat_numero_amigos_off\">" + nAmigosOff + "</span>):</a></div><div id=\"chat_amigos_lista_off_body\">";
				chatAmigosHtml += chatAmigosOffHtml;
				chatAmigosHtml += "</div></div>";
				
				$("#chatAmigos").html(chatAmigosHtml);
				
				if(!mostra_online) {
					$("#chat_amigos_lista_on_body").hide();
					$("#chat_amigos_lista_on_img").attr('alt', '+').attr('src', CONFIG_URL + 'web/images/botao_mais.png');
				} else
					$("#chat_amigos_lista_on_img").attr('alt', '-').attr('src', CONFIG_URL + 'web/images/botao_menos.png');
				
				if(!mostra_offline) {
					$("#chat_amigos_lista_off_body").hide();
					$("#chat_amigos_lista_off_img").attr('alt', '+').attr('src', CONFIG_URL + 'web/images/botao_mais.png');
				} else
					$("#chat_amigos_lista_off_img").attr('alt', '-').attr('src', CONFIG_URL + 'web/images/botao_menos.png');
				
				$("#chat_amigos_lista_on_toggle").click(function() {
					$("#chat_amigos_lista_on_body").toggle();
					mostra_online = !mostra_online;
					if(mostra_online)
						$("#chat_amigos_lista_on_img").attr('alt', '-').attr('src', CONFIG_URL + 'web/images/botao_menos.png');
					else
						$("#chat_amigos_lista_on_img").attr('alt', '+').attr('src', CONFIG_URL + 'web/images/botao_mais.png');
					Atualizar_Chat();
					return false;
				});
				
				$("#chat_amigos_lista_off_toggle").click(function() {
					$("#chat_amigos_lista_off_body").toggle();
					mostra_offline = !mostra_offline;
					if(mostra_offline)
						$("#chat_amigos_lista_off_img").attr('alt', '-').attr('src', CONFIG_URL + 'web/images/botao_menos.png');
					else
						$("#chat_amigos_lista_off_img").attr('alt', '+').attr('src', CONFIG_URL + 'web/images/botao_mais.png');
					Atualizar_Chat();
					return false;
				});
				
			} else {
				$("#chat_numero_amigos_on").html(nAmigosOn);
				$("#chat_numero_amigos_off").html(nAmigosOff);
			}
			
		});
		
		// Receber mensagens do chat
		$(data).find('chat').find('mensagem').each(function() {
			var id = $(this).attr("id");
			var id_usuario_origem = $(this).attr("id_usuario_origem");
			var id_usuario_destino = $(this).attr("id_usuario_destino");
			var apelido = amigos_nicks[id_usuario_origem];
			var mensagem = $.trim($(this).text());
			var foto = amigos_fotos[id_usuario_origem];
			var data_envio = $(this).attr("data_envio");
			var direcao = $(this).attr("direcao");
			if(id_chat < parseInt($(this).attr("id_chat")))
				id_chat = parseInt($(this).attr("id_chat"));
			if(direcao == "r")
				Mensagem_Chat(id, id_usuario_origem, apelido, mensagem, data_envio, "r", foto, false);
			else if(direcao == "e")
				Mensagem_Chat(id, id_usuario_destino, "Eu", mensagem, data_envio, "e", minha_foto_th, false);
			Atualizar_Chat();
		});
		
		// Atualizar acesso
		$(data).find('usuarios_online').each(function() {
			var n_usuarios = $(this).text();
			$("#contador_usuarios_online").html(n_usuarios);
		});
		
		if(n_requisicao == 1) {
			if(meu_status != "z")
				setTimeout(function() { Atualizar_Coisas(true) }, 2000);
			setTimeout(function() { Atualizar_Coisas(false) }, 1000);
		} else {
			if(atualiza_chat) {
				if(meu_status != "z")
					setTimeout(function() { Atualizar_Coisas(true); }, 100);
			} else {
				setTimeout(function() { Atualizar_Coisas(false); }, 10000);
			}
		}
	}).error(function() {
		if(atualiza_chat && meu_status != "z")
			setTimeout(function() { Atualizar_Coisas(true); }, 10000);
		else if(!atualiza_chat)
			setTimeout(function() { Atualizar_Coisas(false); }, 10000);
	});
}

var Atualizar_Chat = function() {
	$("#chatAmigos").height(300);
	var top = ($(window).height() - (($("#chatAmigos").is(":visible")) ? $("#chatAmigos").outerHeight(true) : 0) - $("#chatOpcoes").outerHeight(true));
	if(top < 10)  // Deixa pelo menos 10 pixels de espa�o acima da lista de amigos
	{
		top = 10;
		$("#chatAmigos").css({
			'height': ($(window).height() - $("#chatOpcoes").outerHeight(true) - 10 - 2) + 'px'
		});
	}
	
	$("#chatMiguxo").css({
		//top:  (top - (($("#chatAmigos").is(":visible")) ? 3 : 0)) + 'px',
		top:  (top) + 'px',
		left: ($(window).width() - $("#chatMiguxo").width() - 10) + 'px'
	});
	
	$("#chatStatus").css({
		top: ($(window).height() - 20 - 2) + 'px',
		left: ($(window).width() - $("#chatMiguxo").width() - 10 - $("#chatStatus").width() - 2) + 'px'
	});
	
	var incremento = 0;
	var mais_antigo = 0;
	
	$("div.chat").each(function() {
		var id = ($(this).attr("id").split("_"))[1];
		
		if (!$("#chat_" + id).is(":hidden")) {
			incremento++;
			if(mais_antigo == 0)
				mais_antigo = id;
			
			var posicao_top = $(window).height() - $("#chat_header_" + id).outerHeight();
			if($("#chat_body_" + id).is(":visible"))
				posicao_top -= $("#chat_body_" + id).outerHeight();
			if($("#chat_bottom_" + id).is(":visible"))
				posicao_top -= $("#chat_bottom_" + id).outerHeight();
			var posicao_left = $("#chatMiguxo").offset().left - $("#chat_" + id).outerWidth() * incremento - 10 * incremento;
			if(posicao_left < 0) {
				$("#chat_"+mais_antigo).remove();
				Atualizar_Chat();
				// Empilhar chats minimizados
				return false;
			}
			$("#chat_" + id).css({
				top: (posicao_top) + 'px',
				left: (posicao_left) + 'px'
			});
		}
	});
}

var Cria_Chat = function(id) {
	var chat_html = "<div id=\"chat_" + id + "\" class=\"chat\">";
	chat_html += "<div id=\"chat_header_" + id + "\" class=\"chat_header ui-corner-top\" >";
	chat_html += "<div id=\"chat_header_nome_" + id + "\" class=\"chat_header_nome\">";
	chat_html += $("#chat_amigo_"+id).html();
	chat_html += "</div>";
	chat_html += "<div id=\"chat_header_botoes_" + id + "\" class=\"chat_header_botoes\">";
	chat_html += "<a href=\"#\" id=\"chat_botao_minimizar_" + id + "\" class=\"chat_botao_minimizar\"><img src=\"" + CONFIG_URL + "web/images/chat_minimizar.png\" alt=\"_\" title=\"Minimizar\" /></a> ";  // Botao minimizar
	chat_html += "<a href=\"#\" id=\"chat_botao_fechar_" + id + "\" class=\"chat_botao_fechar\"><img src=\"" + CONFIG_URL + "web/images/chat_fechar.png\" alt=\"X\" title=\"Fechar\" /></a>";  // Botao fechar
	chat_html += "</div>"
	chat_html += "</div>";
	chat_html += "<div id=\"chat_body_" + id + "\" class=\"chat_body\">";
	chat_html += "</div>";
	chat_html += "<div id=\"chat_bottom_" + id + "\" class=\"chat_bottom ui-corner-bottom\">";
	chat_html += "<textarea id=\"chat_input_" + id + "\" class=\"chat_input\"></textarea>";
	chat_html += "</div>";
	chat_html += "</div>";
	
	$("#content").append(chat_html);
	
	if(status_janelas[id] == undefined)
		status_janelas[id] = "o";
	else if(status_janelas[id] == "m") {
		$("#chat_body_" + id).hide();
		$("#chat_bottom_" + id).hide();
	}
	else if (status_janelas[id] == "c") {
		if(n_requisicao <= 1)
			$("#chat_" + id).hide();
	}
	
	$("#chat_botao_minimizar_" + id).click(function() {
		Minimizar_Restaurar_Chat(id);
		return false;
	});
	
	$("#chat_botao_fechar_" + id).click(function() {
		if($("#chat_" + id).length > 0) {
			$("#chat_" + id).hide();
			status_janelas[id] = "c";
			$.post(CONFIG_URL + "ajax/ax_chat.php", {tipo: 'ws', id: id, status: 'c'});
			Atualizar_Chat();
		}
		return false;
	});
	
	$("#chat_input_"+id).click(function() {
		$("#chat_header_" + id).css({
			'background-color': '#B1C6CC'
		});
	});
	
	$("#chat_body_"+id).click(function() {
		$("#chat_header_" + id).css({
			'background-color': '#B1C6CC'
		});
	});
	
	$("#chat_header_nome_" + id).click(function() {
		Minimizar_Restaurar_Chat(id);
		return false;
	});
	
	$("#chat_input_" + id).keypress(function(e) {
		var id_usuario = ($(this).attr("id").split("_"))[2];
		var mensagem = $.trim($(this).val());
		
		if(e.which == 13 && !e.shiftKey) {  // Enter
			$(this).val('');
			if(mensagem == "" || mensagem == null)
				return false;
			var rnd = $.guaycuru.Random(16, 'abcdefghijklmnopqrstuvwxyz');
			Mensagem_Chat(rnd, id, "Eu", mensagem.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;"), null, "e", minha_foto_th, true);
			return false;
		}
		
	});
	
	Atualizar_Chat();
}

var Minimizar_Restaurar_Chat = function(id) {
	$("#chat_body_" + id).toggle();
	$("#chat_bottom_" + id).toggle();
	var status = ($("#chat_body_" + id).is(":visible")) ? "o" : "m";
	
	if(status == "o") {
		$("#chat_header_" + id).css({
			'background-color': '#B1C6CC'
		});
		$("#chat_body_"+id).scrollTop($("#chat_body_"+id).outerHeight());
	}
	
	status_janelas[id] = status;
	$.post(CONFIG_URL + "ajax/ax_chat.php", {tipo: 'ws', id: id, status: status});
	Atualizar_Chat();
	return false;
}

var Mensagem_Enviar = function(id, id_usuario_destino, mensagem) {
	$.post(CONFIG_URL + "ajax/ax_chat.php", {tipo: 'i', id: id, id_usuario_destino: id_usuario_destino, mensagem: mensagem}, function (data) {
		var resposta = data.split('#');
		var id_novo = resposta[0];
		var hora = resposta[1];
		if(id_novo > 0) {
			if($("#chat_mensagem_"+id_novo).length > 0) { // Esta mensagem ja esta na janela! (O ax_xml chegou antes de mim!)
				$("#chat_mensagem_" + id).remove();
				return;
			}
			$("#chat_mensagem_" + id + " img.chat_enviando").remove();
			$("#chat_mensagem_" + id + " span.chat_mensagem_hora").remove();
			$("#chat_mensagem_" + id).attr('id', 'chat_mensagem_' + id_novo);
			$.guaycuru.tooltip("chat_mensagem_" + id_novo, hora, "", {fixed: false, width: 74});
		} else {
			$("#chat_mensagem_" + id + " img.enviando").attr('src', CONFIG_URL + 'web/images/CancelON.png');
		}
		$("#chat_body_"+id_usuario_destino).scrollTop($("#chat_body_"+id_usuario_destino).outerHeight());
	});
}

var Mensagem_Chat = function(id, id_destino, nome_usuario, mensagem, hora, direcao, foto, enviar) {
	if($("#chat_"+id_destino).length > 0) { // Janela ja esta aberta
		if($("#chat_" + id_destino).is(":hidden") && n_requisicao > 1) {
			$("#chat_" + id_destino).show();
			Atualizar_Chat();
			status_janelas[id_destino] = "o";
		}
		if(nome_usuario != null && mensagem != null && direcao != null && foto != null) {
			var html_to_append = "";
			var cria_div = false;
			
			if(hora == null)
				hora = "<img src=\"" + CONFIG_URL + "web/images/loading.gif\" class=\"chat_enviando\" alt=\"...\" />";
			else
				cria_div = true;
				
			if($("#chat_body_" + id_destino + " > div.chat_mensagem:last").attr("direcao") != direcao) { // Mudou de direcao
				html_to_append += "<div class=\"chat_separador\"></div>";
				html_to_append += "<div class=\"chat_mensagem\" direcao=\"" + direcao + "\">";
				html_to_append += "<div class=\"chat_mensagem_foto\"><img class=\"chat_foto\" src=\"" + foto + "\" /></div>";
				html_to_append += "<div class=\"chat_mensagem_texto\"><b>" + nome_usuario + "</b>:</div>";
				html_to_append += "<div id=\"chat_mensagem_" + id + "\" class=\"chat_mensagem_texto\">";
				if (n_requisicao > 1)
					html_to_append += "<span class=\"chat_mensagem_hora\" id=\"chat_mensagem_hora_" + id + "\">" + (cria_div) ? "" : hora + "</span> "
				html_to_append += mensagem + "</div>";
				html_to_append += "</div>";
			} else {
				html_to_append += "<div id=\"chat_mensagem_" + id + "\" class=\"chat_mensagem_texto\">";
				if (n_requisicao > 1)
					html_to_append += "<span class=\"chat_mensagem_hora\" id=\"chat_mensagem_hora_" + id + "\">" + (cria_div) ? "" : hora + "</span> "
				html_to_append += mensagem + "</div>";
			}
			
			if((id > 0) && ($("#chat_mensagem_"+id).length > 0)) // Esta mensagem ja esta na janela!
				return;
			
			if($("#chat_body_" + id_destino + " > div.chat_mensagem:last").attr("direcao") != direcao)
				$("#chat_body_"+id_destino).append(html_to_append);
			else
				$("#chat_body_" + id_destino + " > div.chat_mensagem:last").append(html_to_append);
			
			var bghover = (direcao == "e") ? "#DDDDDD" : "#afdceb";
			$("#chat_mensagem_" + id).mouseover(function() {
				$(this).css({
					'background-color': bghover
				});
			});
			$("#chat_mensagem_" + id).mouseout(function() {
				$(this).css({
					'background-color': 'white'
				});
			});
			
			if (cria_div == true) {
				$.guaycuru.tooltip("chat_mensagem_" + id, hora, "", {fixed: false, width: 74});
			}
			
			if(direcao == "e" && enviar == true)
				Mensagem_Enviar(id, id_destino, mensagem);
			
			$("#chat_body_"+id_destino).scrollTop($("#chat_body_"+id_destino).outerHeight());
			
			if($("#chat_body_"+id_destino).is(":hidden") && n_requisicao > 1) {
				$("#chat_header_"+id_destino).css({
					'background-color': '#AA7777'
				});
			}
				
		}
	} else { // Cria a janela
		Cria_Chat(id_destino);
		Mensagem_Chat(id, id_destino, nome_usuario, mensagem, hora, direcao, foto, enviar);
	}
}

var Toggle_Lista_Amigos = function() {
	if ($("#chatAmigos").is(":hidden"))
		$("#chatOpcoes").removeClass("ui-corner-top");
	else
		$("#chatOpcoes").addClass("ui-corner-top");
	$("#chatAmigos").fadeToggle(function() {
		Atualizar_Chat();
	});
	Atualizar_Chat();
}

var Change_Chat_Image = function(status) {
	if(status == "i")
		status = "off";
	if(status == "x")
		$("#chatOpcoesLink > img").attr("title", "Disponivel (Admin)").attr("alt", "Disponivel (Admin)");
	else if (status == "d")
		$("#chatOpcoesLink > img").attr("title", "Disponivel").attr("alt", "Disponivel");
	else if (status == "o")
		$("#chatOpcoesLink > img").attr("title", "Ocupado").attr("alt", "Ocupado");
	else if (status == "off")
		$("#chatOpcoesLink > img").attr("title", "Invisivel").attr("alt", "Invisivel");
	$("#chatOpcoesLink > img").attr("src", CONFIG_URL + "web/images/status_"+status+".png");
	$("img.status_icone_"+meu_id).each(function() {
		$(this).attr("src", CONFIG_URL + "web/images/status_" + status + ".png");
	});
}

$(document).ready(function(){
	$("#chatOpcoesLink > img").click(function() {
		$("#chatStatus").animate({left: 'toggle'}, 1000);
		return false;
	});
	
	$("div.chatStatusSelect > a").click(function() {
		var status = ($(this).attr("id").split("_"))[2];
		$("#chatOpcoesLink > img").attr("src", CONFIG_URL + "web/images/loading.gif");
		$.post(CONFIG_URL + "ajax/ax_chat_status.php", {set_chat_status: status});
		$("#chatStatus").animate({left: 'toggle'}, 1000);
		Change_Chat_Image(status);
		return false;
	});
	
	$("div.chatStatusSelect > a").mouseover(function() {
		var msg = $(this).find("img").attr("alt");
		$("#linkChat").text(msg + " (" + nAmigosOn +  ")");
	});
	
	$("div.chatStatusSelect > a").mouseout(function() {
		$("#linkChat").text("Chat (" + nAmigosOn +  ")");
	});

	$("div.chat_amigos_lista").live("click", function() {
		var id_destino = ($(this).attr("id").split("_"))[2];
		Mensagem_Chat(null, id_destino, null, null, null, null, false);
		$("#chat_input_"+id_destino).focus();
		$.post(CONFIG_URL + "ajax/ax_chat.php", {tipo: 'ws', id: id_destino, status: 'o'}, function (data) {
		});
		status_janelas[id_destino] = "o";
	
		return false;
	});
	
	$("textarea.chat_input").live("keyup", function(e) {
		var id = ($(this).attr("id").split("_"))[2];
		var mensagem = $.trim($(this).val());
		var foto = minha_foto_th;
		
		if(e.which == 27) {  // ESC
			if ($("#chat_"+id).length > 0) {
				$("#chat_"+id).hide();
				status_janelas[id] = "c";
				$.post(CONFIG_URL + "ajax/ax_chat.php", {tipo: 'ws', id: id, status: 'c'});
				Atualizar_Chat();
			}
			return false;
		}
		
		
	});
	
	$("textarea.chat_input").live("keydown", function(e) {
		if(e.which == 9) { // Tab
			var id_em_foco = $(this).attr("id").split("_")[2];
			var div_a_focar = $("#chat_" + id_em_foco).prev("div.chat");
			if(div_a_focar.length == 0)
				div_a_focar = $("div.chat").last();
			var textarea_a_focar = div_a_focar.find('textarea.chat_input');
			if(textarea_a_focar.is(':hidden'))
				Minimizar_Restaurar_Chat(div_a_focar.attr('id').split("_")[1]);
			textarea_a_focar.focus();
			e.stopPropagation();
			return false;
		}
	});
	
	$("#linkChat").click(function() {
		Toggle_Lista_Amigos();
		return false;
	});
	
	$("#chatOpcoes").click(function() {
		Toggle_Lista_Amigos();
		return false;
	});
	
	atualiza_lista1 = ($("#lista_amigos1").length > 0);
	atualiza_lista3 = ($("#lista_amigos3").length > 0);
	
	Iniciar_Chat();
	$(window).resize(Atualizar_Chat);
	Atualizar_Chat();
});

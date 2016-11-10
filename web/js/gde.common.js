var Tamanho_Abas = function(id) {
	var tamanho_total = $("#"+id).width() - 10;
	var tamanho_usado = 0;
	$("#"+id+" > ul > li").each(function() {
		tamanho_usado += $(this).outerWidth();
	});
	var tamanho_disp = tamanho_total - tamanho_usado;
	if(tamanho_disp > 0){
		var tamanho_acresc = tamanho_disp / $("#"+id+" > ul > li").length;
		$("#"+id+" > ul > li > a").each(function() {
			$(this).width($(this).width()+Math.floor(tamanho_acresc));
		});
	}
	/*var total_abas = $("#"+id+" > ul > li > a").length;
	var total_tamanho = $("#"+id).width() - 10;
	// Em alguns navegadores (mobile) ele nao acha o tamanho...
	var tamanho = Math.floor((1 / total_abas) * total_tamanho) - 2;
	$("#"+id+" > ul > li > a").each(function() {
		$(this).width(tamanho);
	});*/
}

var Abas_Embaixo = function() {
	$(".tabs-bottom .ui-tabs-nav, .tabs-bottom .ui-tabs-nav > *") 
 		.removeClass("ui-corner-all ui-corner-top") 
  		.addClass("ui-corner-bottom");
}

var RegExpAcentos = function(str) {
	return str.replace(/a/gi, '[a\u00E0\u00E1\u00E2\u00E3\u00E4\u00E5]')
		.replace(/c/gi, '[c\u00E7]')
		.replace(/e/gi, '[e\u00E8\u00E9\u00EA\u00EAB]')
		.replace(/i/gi, '[i\u00EC\u00ED\u00EE\u00EF]')
		.replace(/n/gi, '[n\u00F1]')
		.replace(/o/gi, '[o\u00F2\u00F3\u00F4\u00F5\u00F6]')
		.replace(/u/gi, '[u\u00F9\u00FA\u00FB\u00FC]')
		.replace(/y/gi, '[y\u00FD\u00FF]');
}

var LimpaPraRegExp = function(str) {
	return str.replace(/[^a-z0-9 ]/gi, '.');
}

;(function($){
	$.fn.Procura_Amigo = function(lista) {
		this.val('');
		this.Valor_Padrao('Buscar Amigos...', 'padrao');
		this.bind('keyup', function() {
			procurando_amigo[lista] = ($(this).val() != '');
			var procura = new RegExp(RegExpAcentos(LimpaPraRegExp($(this).val())), 'i');
			$('#'+lista+' div.amigo').show();
			$('#'+lista+' div.amigo').each(function() {
				if(($(this).find('a.amigo').attr('title').search(procura) == -1) && ($(this).find('a.amigo').html().search(procura) == -1))
					$(this).hide();
			});
		});
	};
	$.fn.Carregando = function(txt) {
		if(!txt)
			txt = 'Carregando...'
		this.html('<img src="' + CONFIG_URL + 'web/images/loading.gif" alt=" " /> '+txt);
	};
	$.fn.CarregandoL = function(txt) {
		if(!txt)
			txt = ''
		this.after('<img src="' + CONFIG_URL + 'web/images/loading.gif" alt=" " id="carregando_'+this.attr('id')+'" /> '+txt);
		return $("#carregando_"+this.attr('id'));
	};
	$.fn.Autocompletar = function(opcoes) {
		var padroes = {
			'delay': 300,
			'minLength': 3,
			'method': 'post',
			'append': false,
			'query': 'q', // Campo que sera enviado com o valor do input
			'idField': false, // false para usar o valField como ID
			'labelField': false, // false para usar o valField como label
			'valField': false, // false para usar o proprio item
			'cache': true, // Procura por strings inteiras no cache
			'advCache': true, // Procura tambem por substrings no cache, nao recomendavel se a busca tem limite!
			'espacoLike': true, // Uma busca por A D pode retornar A b c D
			'obrigatorio': true, // Eh obrigatorio selecionar um valor do autocomplete
			'instantaneo': false, // Comeca a procurar assim que "sente" a digitacao de uma tecla
			'instantaneo_delay': 100, // Tempo entre consultas no modo instantaneo
			'highlight': false,
			'termFilter': false // Funcao para filtrar o request
		};
		var buscando = false;
		var buscando_instantaneo = false;
		opcoes = $.extend(padroes, opcoes);
		if(opcoes.valField) {
			if(!opcoes.idField)
				opcoes.idField = opcoes.valField;
			if(!opcoes.labelField)
				opcoes.labelField = opcoes.valField;
		}
		var cache = {};
		var source = function(request, response) {
			if((!opcoes.instantaneo) && (buscando))
				return;
			if(opcoes.termFilter) {
				request.term = opcoes.termFilter(request.term);
				if(request.term.length < opcoes.minLength) {
					return;
				}
			}
			if(opcoes.json) {
				if(opcoes.cache) {
					// Procura direto no cache pelo termo inteiro
					if(request.term in cache) {
						response(cache[request.term]);
						return;
					}
					// Procura por substrings de termos ja cacheados
					if(opcoes.advCache) {
						var sdata = [];
						var sdatal = 0;
						for(c in cache) {
							if(c.length <= sdatal) // Soh me interessa o termo do cache com o maior tamanho
								continue;
							var re = new RegExp(LimpaPraRegExp(c), 'i');
							if(request.term.search(re) >= 0) {
								sdata = cache[c];
								sdatal = c.length;
							}
						}
						// Encontrei um termo usavel do cache, preciso limpa-lo e procurar os elementos dele por resultados
						if(sdatal > 0) {
							var data = [];
							var d = 0;
							var term = RegExpAcentos(LimpaPraRegExp(request.term));
							if(opcoes.espacoLike)
								term = term.replace(/ /g, '.+');
							var re = new RegExp(term, 'i');
							for(i in sdata)
								if(sdata[i].label.search(re) >= 0)
									data[d++] = sdata[i];
							response(data);
							return;
						}
					}
				}
				var data = (opcoes.append) ? {} : {'q': request.term};
				buscando = true;
				$.ajax({
					type: opcoes.method,
					url: (opcoes.append) ? opcoes.json + request.term : opcoes.json,
					data: $.extend(opcoes.data, data),
					dataType: 'json',
					success: function(res) {
						if(!res) {
							buscando = false;
							return;
						}
						if(opcoes.valField) {
							var ndata = $.map(res.resultados, function(item) {
								return {
									id: (opcoes.idField && item[opcoes.idField]) ? item[opcoes.idField] : item[opcoes.valField],
									label: (opcoes.labelField && item[opcoes.labelField]) ? item[opcoes.labelField] : item[opcoes.valField],
									value: item[opcoes.valField],
									raw: item
								}
							});
						} else {
							var resultados = (res.resultados) ? res.resultados : res;
							var ndata = $.map(resultados, function(item) {
								return {
									id: item,
									label: item,
									value: item,
									raw: item
								};
							});
						}
						cache[request.term] = ndata;
						response(ndata);
						buscando = false;
						return;
					}
				});
			} else {
				return opcoes.source;
			}
		}
		var meu_padrao = this.val();
		if(opcoes.hiddenField)
			var meu_hidden_padrao = $("#"+opcoes.hiddenField).val();
		var este = this.autocomplete($.extend({}, opcoes, {
			source: source,
			minLength: opcoes.minLength,
			change: function(event, ui) {
				if(!ui.item && opcoes.obrigatorio) {
					$(this).val(meu_padrao);
					if(opcoes.hiddenField)
						$("#"+opcoes.hiddenField).val(meu_hidden_padrao);
				}
				if(opcoes.change)
					return opcoes.change(event, ui);
			},
			select: function(event, ui) {
				if(ui.item) {
					meu_padrao = ui.item.value;
					if(opcoes.hiddenField) {
						$("#"+opcoes.hiddenField).val(ui.item.id);
						meu_hidden_padrao = ui.item.id;
					}
				}
				if(opcoes.select)
					return opcoes.select(event, ui);
			}
		}));
		if(opcoes.highlight) {
			este.data('autocomplete')._renderItem = function(ul, item) {
				if(!opcoes.espacoLike) {
					var term = RegExpAcentos(LimpaPraRegExp(this.term));
					var re = new RegExp(term, 'i');
					var t = item.label.replace(re, "<span class='autocomplete_highlight'>$&</span>");
				} else {
					var terms = RegExpAcentos(LimpaPraRegExp(this.term)).split(' ');
					var re = new RegExp(terms.join('|'), 'ig');
					var t = item.label.replace(re, "<span class='autocomplete_highlight'>$&</span>");
				}
				return $("<li></li>")
				  .data("item.autocomplete", item)
				  .append("<a>" + t + "</a>")
				  .appendTo(ul);
			}
		}
		var id_timeout = false;
		if(opcoes.instantaneo) {
			this.keyup(function(e) {
				if(e.which > 8 && e.which < 46) // Ignora teclas como setas, esc, enter, tab, etc
					return;
				if(buscando_instantaneo) {
					clearTimeout(id_timeout);
					id_timeout = setTimeout(function() { buscando_instantaneo = false; }, opcoes.instantaneo_delay);
				}
				if($(this).val().length >= opcoes.minLength && !buscando_instantaneo && !buscando) {
					buscando_instantaneo = true;
					id_timeout = setTimeout(function() { buscando_instantaneo = false; }, opcoes.instantaneo_delay);
					$(this).autocomplete("search");
				}
			});
		}
		if(opcoes.maxHeight)
			$(".ui-autocomplete").css({'max-height': opcoes.maxHeight, 'overflow-y': 'auto', 'overflow-x': 'hidden', 'padding-right': '20px'});
		return this;
	};
})(jQuery);

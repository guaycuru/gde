(function($) {
	$.fn.showPlanner = function(options) {
		var opts = $.extend({}, $.fn.showPlanner.defaults, options);
		var antiga = new Array();
		var antigo = new Array();
		var antigh = $(this).html();
		var eu = $(this);
		var adicionou = false;
		
		var mostra = function(e){
			if(adicionou)
				return false;
			//if(eu.hasClass('ocupado'))
				//return false;
			for(h in opts.horarios) {
				horario = opts.horarios[h];
				este = $("#"+opts.id+"_"+horario);
				antiga[horario] = este.css("background-color");
				antigo[horario] = este.html();
				cor = (este.hasClass("ocupado")) ? "#FF0000" : opts.cor;
				este.css("background-color", cor);
				este.html(opts.textos[h]);
			}
			return false;
		}
		var volta = function(e) {
			if(!adicionou) {
				for(h in opts.horarios) {
					horario = opts.horarios[h];
					este = $("#"+opts.id+"_"+horario);
					este.css("background-color", antiga[horario]);
					este.html(antigo[horario]);
				}
			}
			//return false;
		}
		var adiciona = function(e) {
			if(adicionou)
				return false;
			for(h in opts.horarios) {
				horario = opts.horarios[h];
				if($("#"+opts.id+"_"+horario).hasClass("ocupado"))
					return false;
			}
			// Remove a outra turma dessa disciplina, caso ja esteja adicionada
			$("td.ocupado:contains('"+opts.disciplina+"')").first().trigger("remover");
			for(h in opts.horarios) {
				horario = opts.horarios[h];
				este = $("#"+opts.id+"_"+horario);
				cor = (este.hasClass("ocupado")) ? "#FF0000" : opts.cor;
				antiga[horario] = cor;
				antigo[horario] = opts.textos[h];
				este.css("background-color", cor);
				este.html(opts.textos[h]);
				este.addClass("ocupado");
				este.addClass("jcontext_disabled");
				este.bind("click", remove);
				este.bind("remover", remove);
			}
			adicionou = true;
			eu.prepend("Remover - ");
			eu.unbind("mouseover", mostra);
			eu.unbind("mouseout", volta);
			opts.adiciona();
			//return false;
		}
		var remove = function(e) {
			if(!adicionou)
				return false;
			for(h in opts.horarios) {
				horario = opts.horarios[h];
				este = $("#"+opts.id+"_"+horario);
				este.css("background-color", "transparent");
				este.html("&nbsp;");
				este.removeClass("ocupado");
				este.removeClass("jcontext_disabled");
				antiga[horario] = este.css("background-color");
				antigo[horario] = este.html();
			}
			eu.html(antigh);
			eu.bind("mouseover", mostra);
			eu.bind("mouseout", volta);
			adicionou = false;
			opts.remove();
			//return false;
		}
		var clicou = function(e) {
			if(!adicionou)
				adiciona(e);
			else
				remove(e);
			$(document).click();
			return false;
		}
		eu.bind("mouseover", mostra);
		eu.bind("mouseout", volta);
		eu.bind("click", clicou);
		//return false;
	};
	
	$.fn.showPlanner.defaults = {
		id: '',
		cor: 'transparent',
		horarios: new Array(),
		textos: new Array(),
		adiciona: function() {},
		remove: function() {}
	};
})(jQuery);
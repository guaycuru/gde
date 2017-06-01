/**
*
* jQ plugin: Custom Functions by Guaycuru
*
* Requires: jquery.popup, jquery.timers
*
*/
jQuery.guaycuru = {

	/**
	* Show a pop-up confirmation with given message and redirecting to given url.
	*
	* @param msg : popup content message
	* @param url : popup redirecting url or false to no redirecting
	* @param options : optional settings, can contain following params:
	*          fromIframe : if called from a (hidden) iframe, set to true
	*          callback1 : function to call after creating popup elements, just before showing it (only called once)
	*          callback2 : function to call just before redirecting to url (only called once)
	*          time : time to wait before redirecting to url or false to never
	* @return jQ
	*/
	confirmacao: function(msg, url, options) {
		
		var settings = jQuery.extend({
			fromIframe: true,
			callback1: function(){},
			callback2: function(){},
			time: 5000,
			nomeFrame: false
		}, options);
		
		var CP = false;
		
		var jQ = (settings.fromIframe) ? parent.jQuery : jQuery;
		
		var continua = function() {
			jQ(document).stopTime("confirmacao_continuar");
			settings.callback2();
			doc = (settings.fromIframe) ? parent.document : document;
			if(url) {
				if(!settings.nomeFrame)
					doc.location = url;
				else
					top.frames[settings.nomeFrame].location = url;
			}
		}
		
		settings.callback1();
		
		/* No need to use parent's jQ */
		if(msg)
			CP = jQuery.popup.show("Aviso", msg+"<br><center><input type=\"button\" value=\"OK\" id=\"confirmacao_ok\" class=\"botao_ok\"></center>", {useParent: settings.fromIframe, bClose: continua, close_button: "confirmacao_ok"});
		else
			continua();
		
		jQ("#confirmacao_ok").focus();
	
		if(settings.time) {
			jQ(document).oneTime(settings.time, "confirmacao_continuar", function() {
				continua();
			});
		}
	
		return jQ;
	
	},
	
	simnao: function(msg, acao, options) {
		
		var settings = jQuery.extend({
			fromIframe: true,
			callback1: function(){},
			callback2: function(){}
		}, options);
		
		var CP = false;
		
		var jQ = (settings.fromIframe) ? parent.jQuery : jQuery;
		
		settings.callback1();
		
		/* No need to use parent's jQ */
		CP = jQuery.popup.show("Confirma&ccedil;&atilde;o", msg+"<br><center><input type=\"button\" value=\"Sim\" id=\"confirmacao_sim\" class=\"botao_sim\"> <input type=\"button\" value=\"N&atilde;o\" id=\"confirmacao_nao\" class=\"botao_nao\"></center>", {useParent: settings.fromIframe, close_button: "confirmacao_nao"});
		
		jQ("#confirmacao_sim").focus();
		
		jQ("#confirmacao_sim").click(acao);
	
		return jQ;
	
	},
	
	simnao2: function(msg, acao_sim, acao_nao, options) {
		
		var settings = jQuery.extend({
			fromIframe: true,
			callback1: function(){},
			callback2: function(){}
		}, options);
		
		var CP = false;
		
		var jQ = (settings.fromIframe) ? parent.jQuery : jQuery;
		
		settings.callback1();
		
		/* No need to use parent's jQ */
		CP = jQuery.popup.show("Confirma&ccedil;&atilde;o", msg+"<br><center><input type=\"button\" value=\"Sim\" id=\"confirmacao_sim\" class=\"botao_sim\"> <input type=\"button\" value=\"N&atilde;o\" id=\"confirmacao_nao\" class=\"botao_nao\"></center>", {useParent: settings.fromIframe, close_button: "confirmacao_sim", close_button2: "confirmacao_nao"});
		
		jQ("#confirmacao_sim").focus();
		
		jQ("#confirmacao_sim").click(acao_sim);
		
		jQ("#confirmacao_nao").click(acao_nao);
	
		return jQ;
	
	},
	
	aguarde: function() {
	
		return jQuery.popup.show("", '<h1 style="text-align: center;"><img src="' + CONFIG_URL + 'web/images/loading.gif" /> Aguarde...</h1>', {returnHide: true});
	
	},
	
	tooltip: function(id, titulo, mensagem, options) {
	
		var settings = jQuery.extend({
			opacity: 0.9,
			width: 200,
			trigger: "hover",
			fixed: true
		}, options);
		
		var pos = $("#"+id).position();
		var left = pos.left+$("#"+id).width()+22;
		
		if(settings.width + left < $(window).width())
			$("body").append("<div id='JT_"+id+"' class='JT' style='width:"+settings.width+"px; display:none'><div class='JT_arrow_left'></div><div class='JT_close_left'>"+titulo+"</div><div class='JT_copy'>"+mensagem+"</div></div>");
		else
			$("body").append("<div id='JT_"+id+"' class='JT' style='width:"+settings.width+"px; display:none'><div class='JT_close_right'>"+titulo+"</div><div class='JT_arrow_right' style='left: "+settings.width+"px;'></div><div class='JT_copy'>"+mensagem+"</div></div>");
		
		$("#"+id).jHelperTip({
			trigger: settings.trigger,
			dC:"#JT_"+id,
			autoClose: false,
			opacity: settings.opacity,
			fixed: settings.fixed,
			width: settings.width
		});
	
	},
	
	changeIt: function() {
	
		$("body").css({"background-image": "url(" + CONFIG_URL + "web/images/bodybg.png)", "background-repeat": "repeat"});
		$("#wrapper").css("background", "transparent");
		
	},
	
	Data: function(date, brasil) {
		if(date == null)
			return "";
		if(brasil == undefined)
			brasil = true;
		var data = "";
		if(brasil) {
			if(date.getDate() < 10)
				data += '0';
			data += date.getDate() + '/';
			if(date.getMonth() < 9)
				data += '0';
			data += (date.getMonth() + 1) + '/';
			data += date.getFullYear();
		} else {
			data += date.getFullYear() + '-';
			if(date.getMonth() < 9)
				data += '0';
			data += (date.getMonth() + 1) + '-';
			if(date.getDate() < 10)
				data += '0';
			data += date.getDate();
		}
		return data;
	},
	
	Hora: function(date) {
		if(date == null)
			return "";
		var hora = "";
		if(date.getHours() < 10)
			hora += '0';
		hora += date.getHours() + ':';
		if(date.getMinutes() < 10)
			hora += '0';
		hora += date.getMinutes();
		return hora;
	},
	
	DateTime: function(date) {
		if(date == null)
			return "";
		var data = "";
		data += date.getFullYear() + '-';
		if(date.getMonth() < 9)
			data += '0';
		data += (date.getMonth() + 1) + '-';
		if(date.getDate() < 10)
			data += '0';
		data += date.getDate() + ' ';
		if(date.getHours() < 10)
			data += '0';
		data += date.getHours() + ':';
		if(date.getMinutes() < 10)
			data += '0';
		data += date.getMinutes();
		return data;
	},
	
	Random: function (tamanho, sChrs) {
		if(!sChrs || sChrs == undefined)
			sChrs = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890';
		var sRnd = '';
		for(var i = 0; i < tamanho; i++)
			sRnd += sChrs.substr(Math.floor(Math.random() * sChrs.length), 1);
		return sRnd;
	}
	
};

;(function($){
	$.fn.Valor_Padrao = function(valor_padrao, classe) {
		if(this.val() == '' || this.val() == valor_padrao) {
			this.val(valor_padrao);
			this.addClass(classe);
		}
		this.bind({
			focus: function() {
				if($(this).hasClass(classe)) {
					$(this).removeClass(classe);
					$(this).val('');
				}
			},
			blur: function() {
				if($(this).val() == '') {
					$(this).val(valor_padrao);
					$(this).addClass(classe);
				}
			}
		});
		return this;
	};
	$.fn.Padrao = function() {
		this.val('');
		this.blur();
		return this;
	};
})(jQuery);

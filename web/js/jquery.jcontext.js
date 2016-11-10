/* Copyright (c) 2008 Kean Loong Tan http://www.gimiti.com/kltan
 * Licensed under the MIT (http://www.opensource.org/licenses/mit-license.php)
 * Name: jContext
 * Version: 1.0 (April 28, 2008)
 * Modified by: Guaycuru (14/05/2009)
 * Requires: jQuery 1.2+
 */
(function($) {
	$.fn.showMenu = function(options) {
		var opts = $.extend({}, $.fn.showMenu.defaults, options);
		$(opts.query).width(opts.width+"px");
		var mostra = function(e) {
			if(e.type != 'mostra_menu') {
				var top = e.pageY;
				var left = (opts.width + e.pageX >= $(document).width())
					? e.pageX - opts.width
					: e.pageX;
			} else {
				var offset = $(this).offset();
				var top = offset.top - $("html").scrollTop();
				var left = offset.left;
			}
			var scroll;
			var height;
			$(opts.query).css("height", "auto");
			var thisHeight = $(opts.query).height();
			var espaco = $(window).height() - (top - $(document).scrollTop());
			if((thisHeight > espaco) && (espaco < ($(window).height() / 3))) { // Pra cima
				var bottom = top;
				if(thisHeight > bottom) { // Muito grande, scroll
					top = $(document).scrollTop();
					height = bottom - top + "px";
					scroll = "auto";
				} else { // Cabe inteiro
					top = bottom - thisHeight;
					height = "auto";
					scroll = "hidden";
				}
			} else { // Pra baixo
				if(thisHeight > espaco) { // Muito grande, scroll
					height = espaco + "px";
					scroll = "auto";
				} else { // Cabe inteiro
					height = "auto";
					scroll = "hidden";
				}
			}
			if($(this).hasClass('jcontext_disabled'))
				return false;
			$(".jcontext_shown").each(function() {
				$(this).hide().removeClass('jcontext_shown');
				if($(this).data('jc_opts').closeCallback)
					$(this).data('jc_opts').closeCallback();
			});
			$(document).bind("contextmenu, "+opts.leftEvent, function(e){
				if($(opts.query).hasClass("jcontext_shown")) {
					$(opts.query).hide();
					$(opts.query).removeClass('jcontext_shown');
					if(opts.closeCallback)
						opts.closeCallback();
				}
			});
			$(opts.query).css({
				top: top+"px",
				left: left+"px",
				position: "absolute",
				opacity: opts.opacity,
				zIndex: opts.zindex,
				width: opts.width+"px",
				height: height,
				overflow: scroll			
			}).show().addClass('jcontext_shown');
			if(e) {
				e.preventDefault();
				e.stopPropagation();
			}
			return false;
		}
		$(this).bind("contextmenu", mostra);
		$(this).bind("mostra_menu", mostra);
		if(opts.left)
			$(this).bind(opts.leftEvent, mostra);
		$(opts.query).data('jc_opts', opts);
		return this;
	};
	
	$.fn.showMenu.defaults = {
		zindex: 2000,
		left: false,
		leftEvent: 'click',
		query: document,
		opacity: 1.0,
		width: 180,
		closeCallback: false
	};
})(jQuery);

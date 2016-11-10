/* Copyright (c) 2008 Kean Loong Tan http://www.gimiti.com/kltan
 * Licensed under the MIT (http://www.opensource.org/licenses/mit-license.php)
 * Copyright notice and license must remain intact for legal use
 * jHelpertip
 * Version: 1.0 (Jun 2, 2008)
 * Requires: jQuery 1.2+
 * Modified by Guaycuru - Hover Fixed option and Prevent TABs
 */
(function($) {
		  
	$.fn.jHelperTip = function(options) {
		// merge users option with default options
		var opts = $.extend({}, $.fn.jHelperTip.defaults, options);
		
		// default actions
		// create a ttC if not found
		if ($(opts.ttC).length == 0)
			$('<div id="'+opts.ttC.slice(1)+'"></div>').appendTo("body");
		
		// create a dC if not found
		if ($(opts.dC).length == 0)
			$('<div id="'+opts.dC.slice(1)+'"></div>').appendTo("body");
			
		// create a aC if not found
		if ($(opts.aC).length == 0)
			$('<div id="'+opts.aC.slice(1)+'"></div>').appendTo("body");
		
		
		// initialize our tooltip and our data container and also the close box
		$(opts.ttC).add(opts.aC).css({
			position: "absolute",
			display: "inline"
		}).hide();
		
		$(opts.dC).hide();
		
		// close the tooltip box
		var closeBox = function(){
			if (opts.source == "attribute")
				$(opts.aC).hide().empty();
			else
				$(opts.ttC).hide().empty();
		};
		
		$(".jHelperTipClose").bind("click", closeBox);
		$(opts.ttC).bind("mouseover",function(){
			$(opts.ttC).show();
			return false;
		});

		// the sources of getting data
		var getData = function(obj,e){
			if (opts.source == "ajax") {
				getPosition(obj,e);
				$(opts.ttC).html('<div><img src="'+opts.loadingImg+'"/> '+opts.loadingText+'</div>').show();
				
				$.ajax({
					type: opts.type,
					url: opts.url,
					data: opts.data,
					success: function(msg){
						$(opts.ttC).html(msg);
						// reInitialize the close controller
						$(".jHelperTipClose").unbind("click", closeBox); 
						$(".jHelperTipClose").bind("click", closeBox);
					}
				});
			}
			
			else if (opts.source == "container"){
				$(opts.ttC).show().empty();
				$(opts.dC).clone(true).show().appendTo(opts.ttC);
			}
			
			if (opts.source == "attribute"){
				$(opts.aC).html($(obj).attr(opts.attrName));
			}
		};
		
		// used to position the tooltip
		var getPosition = function (obj,e){
			conteudo = (opts.source == "attribute")?opts.aC:opts.ttC;
			$(conteudo).width(opts.width+"px");
			$(conteudo).show();
			if(opts.fixed) {
				var pos = $(obj).position();
				var top = pos.top;
				var left = pos.left+$(obj).width()+22;
				if($(conteudo).width() + left > $(window).width())
					var left = pos.left-$(conteudo).width()-10;
			} else {
				var top = e.pageY+opts.topOff;
				var left = e.pageX+opts.leftOff;
			}
			$(conteudo).css({
				top: top,
				left: left,
				opacity: opts.opacity
			});
		};
		
		// prevent containers from appearing in tab orders
		$(this).attr('tabIndex', '-1');
		
		// just close tool tip when not needed usually trigger by anything outside out tooltip target
		if (opts.trigger == "hover") {
			if(opts.fixed) {
				$(this).hover(function(e){
					e.preventDefault();
					getData(this, e);
					getPosition(this, e);
					return false;
				});
			} else {
				$(this).bind("mouseover", function(e){
					e.preventDefault();
					getData(this, e);
					return false;
				});
				$(this).bind("mousemove", function(e){
					getPosition(this, e);
					return false;
				});
			}
			
			$(this).bind("mouseout", function(e){
			    if (opts.source == "attribute")
					$(opts.aC).hide().empty();
				else
					$(opts.ttC).hide().empty();
				return false;
			});
			
			$(this).bind("click", function(e){
				return false;
			});
		}
		
		else if (opts.trigger == "click") {
			$(this).bind("click", function(e){
				getData(this, e);
				getPosition(this, e);
				$(document).bind("click", function(e){
					if (opts.autoClose) {
						if (opts.source == "attribute")
							$(opts.aC).hide().empty();
						else
							$(opts.ttC).hide().empty();
					}
				});
				
				return false;
			});

		}
		
	};
	
	$.fn.jHelperTip.defaults = {
		trigger: "click",
		topOff: -10,
		leftOff: 20,
		width: 200,
		source: "container", /* attribute, container, ajax */
		attrName: '',
		ttC: "#jHelperTipContainer", /* tooltip Container*/
		dC: "#jHelperTipDataContainer", /* data Container */
		aC: "#jHelperTipAttrContainer", /* attr Container */
		opacity:  1.0,
		loadingImg: "ajax-loader.gif",
		loadingText: "Loading...",
		type: "GET", /* data can be inline or CSS selector */
		//url: '',
		//data: '',
		autoClose: true,
		fixed: false
	};
})(jQuery);
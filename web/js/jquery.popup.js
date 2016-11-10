/**
*
* jQ plugin: Popup v1.2 - Modified by Guaycuru (Show from inside a iFrame)
*
* Copyright (c) 2008 Yves Bresson
* www.ybresson.com
*
* Usage example: override defaults main_class
*  var options = {main_class: "myClass anotherClass" }
*  $.popup.show('My title', 'My message', options);
*
*/
jQuery.popup = {

	/**
	* Show a pop-up with given title and message.
	*
	* @param title : popup title
	* @param message : popup content message
	* @param options : optional settings, can contain following params:
	*          convertNLtoBR : if true, will convert new lines (\n) to <br/> in message
	*          postDOM : function to call after creating popup elements, just before showing it (only called once)
	*          simpleAlert : if true, will use javascript's standard alert() function (auto-used if client = iPhone/iPod)
	*          main_class : class names to be added on main popup <div> tag
	*          xxx_id : id to use for popup elements, shouldn't need to modify
	* @return jQ
	*/
	show: function(title, message, options) {

		// create a link to self
		var me = this;

		// define defaults and override with options if available
		var settings = jQuery.extend({
			convertNLtoBR: true,
			postDOM: function(){},
			bClose: function(){},
			simpleAlert: false,
			useParent: false,
			returnHide: false,
			main_class: "",
			main_id: "popup",
			bg_id: "popup_bg",
			title_id: "popup_title",
			msg_id: "popup_message",
			close_id: "popup_close",
			close_button: "",
			close_button2: ""
		}, options);

		var jQ = (settings.useParent) ? parent.jQuery : jQuery;

		if(!me.initialized) {
			// inject needed elements in DOM
			domElements = '<div id="'+settings.bg_id+'"></div>';
			domElements += '<div id="'+settings.main_id+'" class="'+settings.main_class+'">';
			domElements += '<span id="'+settings.title_id+'"></span><a id="'+settings.close_id+'"> </a>';
			domElements += '<div id="'+settings.msg_id+'"></div>';
			domElements += '</div>';
			jQ('body').append(domElements);

			// call given method after DOM has been altered (maybe user wants to attach to elements, or whatever)
			settings.postDOM();

			// setup event handlers
			// popup close by outer click
			jQ('#'+settings.bg_id).click( hidePopup );
			jQ('#'+settings.close_id).click( hidePopup );

			me.initialized = true;
		}

		if(!isIPhone() && !settings.simpleAlert) {
			// convert \n into <br/> if asked to (only in message param)
			if(settings.convertNLtoBR) {
				message = message.replace(/\n/g, "<br/>");
			}
			// prepare popup content
			jQ('#'+settings.title_id).html(title);
			jQ('#'+settings.msg_id).html(message);
			if(settings.close_button) jQ('#'+settings.close_button).click( hidePopup );
			if(settings.close_button2) jQ('#'+settings.close_button2).click( hidePopup );
			// display.. tadaaa!
			showPopup();
		} else {
			alert(message);
		}

		/*
		*
		* private functions (they're included right INTO the main show function)
		*
		*/

		// show popup
		function showPopup() {
			// loads popup only if it is disabled
			if(!me.showing) {
				centerPopup();
				jQ('#'+settings.bg_id).css({"opacity": "0.6"});
				jQ('#'+settings.bg_id).fadeIn("slow");
				jQ('#'+settings.main_id).fadeIn("slow");
				me.showing = true;
			}
		}

		// hide popup
		function hidePopup() {
			// disables popup only if it is enabled
			if(me.showing) {
				jQ('#'+settings.bg_id).fadeOut("normal");
				jQ('#'+settings.main_id).fadeOut("normal");
				settings.bClose();
				me.showing = false;
			}
		}

		// center popup in viewport
		function centerPopup() {
			doc = (settings.useParent) ? parent.document : document;
			// get viewport dimensions
			var cWidth = doc.documentElement.clientWidth;
			var cHeight = doc.documentElement.clientHeight;
			var popupHeight = jQ('#'+settings.main_id).height();
			var popupWidth = jQ('#'+settings.main_id).width();
			// positionning
			jQ('#'+settings.main_id).css({
			"top": cHeight/2-popupHeight/2,
			"left": cWidth/2-popupWidth/2
			});
			// IE6
			jQ(settings.bg_id).css({"height": cHeight});
		}

		// detects if browser is iPhone/iPod Safari
		function isIPhone() {
			if((navigator.userAgent.match(/iPhone/i)) || (navigator.userAgent.match(/iPod/i))) {
				return true;
			}
			return false;
		}

		return (options.returnHide) ? hidePopup : me;

	}, // end show function

	// jQ.fn.name = function(..){...} => call $('selector').name
	// jQ.name = function(..){...} => call $.name
	// jQ.namespace = {name: function(..){...}, .. } => call $.namespace.name
	// inside plugin: use jQ, not $ alias which might not exist

	// popup ready or not
	initialized: false,

	// false = disabled, true = enabled
	showing: false

};  // ';' required or will break if compressed
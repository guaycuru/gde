(function(jQuery) {
	jQuery.fn.watcherkeys = function(o){
		var options = jQuery.extend({
			watchFor : [38,38,40,40,37,39,37,39,66,65],
			callback : function() { }
		}, o);

		var key_accum = [];
		var match = options.watchFor;
	 
		$(document).keyup(function(e){
			len = key_accum.push(e.keyCode ? e.keyCode : e.charCode);
			if(len > match.length)
				key_accum.shift();
				if (key_accum.join('-') == match.join('-')) {
					key_accum = [];
					if (options.callback)
						options.callback($(this));
				}
		});
	}
})(jQuery);
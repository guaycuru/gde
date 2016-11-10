(function($) {

	var Tree = {
		array: new Array(),
		settings : {
			boxWidth: 60,
			boxHeight: 60,
			boxBorder: 2,
			vSpace: 10,
			hSpace: 10,
			lineWidth: 2,
			wheelSpeed: 0
		},
		cont: null
	};
	
	Tree.getConf = function() {
		return Tree.settings;
	};
	
	Tree.recurse = function(data, parent, container) {
		
		//Containres
		var table = $('<table/>').appendTo(container);
		var tbody = $('<tbody/>').appendTo(table);
		var tr = $('<tr/>').appendTo(tbody);
		
		// Array size
		var size = data.length;
		
		$.each(data, function(i){
			var item = this;
			var pos = '';
			if(i == 0) {
				pos = 'first';
			} else if(i == (size-1)) {
				pos = 'last';
			}

			var td = $('<td align="center" class="'+pos+'" />').appendTo(tr);
			var divLeaf = $('<div class="leaf" />').appendTo(td);
			if(parent != 0) {
				var borderTop = $('<div class="border-top" />').appendTo(divLeaf);
				var vBorderTop = $('<div class="vBorder-top" />').appendTo(divLeaf);
				vBorderTop.css({
					height: Tree.settings.vSpace + 'px',
					borderWidth: Tree.settings.lineWidth + 'px',
					margin: '0 auto -'+Tree.settings.vSpace+'px'
				});
				borderTop.css({
					borderWidth: Tree.settings.lineWidth + 'px'
				});
			}
			var content = $('<div class="content"><div>').appendTo(divLeaf);
			
			divLeaf.css({
				marginTop: Tree.settings.vSpace+'px',
				position: 'relative'
			});
			
			content
				.css({
					marginTop: Tree.settings.vSpace+'px',
					height: Tree.settings.boxHeight+'px',
					lineHeight: Tree.settings.boxHeight+'px',
					width: Tree.settings.boxWidth+'px',
					borderWidth: Tree.settings.boxBorder+'px',
					marginLeft:  Tree.settings.hSpace+'px',
					marginRight:  Tree.settings.hSpace+'px'
				})
				.attr('title', item.content)
				.hover(function(){
					$(this).addClass('hover');
				}, function() {
					$(this).removeClass('hover');
				});
			
			if(this.children && this.children.length > 0) {
				var vBorderBottom = $('<div class="vBorder-bottom" />').appendTo(divLeaf);
				vBorderBottom.css({
					height: Tree.settings.vSpace + 'px',
					borderWidth: Tree.settings.lineWidth + 'px',
					margin: '0 auto -'+Tree.settings.vSpace+'px'
				});
				
				Tree.recurse(this.children, this.id, td)
			}
		});
	};
	
	Tree.rebuild = function(options) {
		Tree.cont.empty();
		return Tree.cont.tree(Tree.array, options);
	};
	
	Tree.leafSize = function(delta) {
		Tree.settings.boxHeight = 10*delta;
		Tree.settings.boxWidth = 10*delta;
//		Tree.settings.lineWidth += delta/10;
		Tree.settings.vSpace = delta*2;
		Tree.settings.hSpace = delta;
	};
	
	Tree.resize = function(delta)  {
		Tree.leafSize(delta);
		Tree.rebuild();
	};
		
	$.fn.tree = function(elements, options) {
		
		Tree.settings = $.extend(Tree.settings, options);
		Tree.array = elements;
		Tree.cont = $(this);
		
		Tree.recurse(Tree.array, 0, $(this));

		var borders = $(this).find('div.border-top');
		
		$.each(borders, function(i){
			var self = $(this);
			var tr = self.parent().parent();
			if(tr.hasClass('first')) {
				var offsetX = (tr.width()/2);
				self.css({
					marginLeft: offsetX
				});
			} else if(tr.hasClass('last')) {
				var offsetX = (tr.width()/2);
				self.css({
					marginRight: offsetX
				});
			}
		});
		
		
		// Jquery Tools Mouse Wheel
//		Tree.cont.mousewheel(function(e, delta)  {
//			Tree.leafSize(delta/2);
//			Tree.rebuild();
//			console.log(delta);
//			self.move(delta < 0 ? 1 : -1, Tree.settings.wheelSpeed || 50);
//			return false;
//		});
		

		return Tree;
	};
})(jQuery);
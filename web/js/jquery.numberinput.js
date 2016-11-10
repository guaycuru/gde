/*
 * jQuery Number Input plugin 1.0
 * Released: July 14, 2008
 * Modified by: Guaycuru - Allow TAB, SHIFT, CTRL, ALT, F*, NUMLOCK
 * 
 * Copyright (c) 2008 Chris Winberry
 * Email: M8R-tk5fe51@mailinator.com
 * 
 * 
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * 
 * Dual licensed under the MIT and GPL licenses:
 *   http://www.opensource.org/licenses/mit-license.php
 *   http://www.gnu.org/licenses/gpl.html
 *
 * @license http://www.opensource.org/licenses/mit-license.php
 * @license http://www.gnu.org/licenses/gpl.html
 * @project jquery.numberInput
 */
(function($){
	$.fn.numberInput = function() {
		return this.each(function() {
			$(this).keydown(function(event){
				return KEYS_ALLOWED[event.keyCode] ? true : false;
			});
		});
	};
	var KEYS_ALLOWED = {
		   8 : 'BACKSPACE'
		,  9 : 'TAB'
		, 13 : 'ENTER'
		, 16 : 'SHIFT'
		, 17 : 'CTRL'
		, 18 : 'ALT'
		, 20 : 'CAPS LOCK'
		, 37 : 'LEFT_ARROW'
		, 38 : 'UP_ARROW'
		, 39 : 'RIGHT_ARROW'
		, 40 : 'DOWN_ARROW'
		, 46 : 'DELETE'
		, 48 : 'ZERO'
		, 49 : 'ONE'
		, 50 : 'TWO'
		, 51 : 'THREE'
		, 52 : 'FOUR'
		, 53 : 'FIVE'
		, 54 : 'SIX'
		, 55 : 'SEVEN'
		, 56 : 'EIGHT'
		, 57 : 'NINE'
		, 96 : 'ZERO'
		, 97 : 'ONE'
		, 98 : 'TWO'
		, 99 : 'THREE'
		, 100 : 'FOUR'
		, 101 : 'FIVE'
		, 102 : 'SIX'
		, 103 : 'SEVEN'
		, 104 : 'EIGHT'
		, 105 : 'NINE'
		, 112 : 'F1'
		, 113 : 'F2'
		, 114 : 'F3'
		, 115 : 'F4'
		, 116 : 'F5'
		, 117 : 'F6'
		, 118 : 'F7'
		, 119 : 'F8'
		, 120 : 'F9'
		, 121 : 'F10'
		, 122 : 'F11'
		, 123 : 'F12'
		, 144 : 'Num Lock'
		, 145 : 'Scroll Lock'
	};
})(jQuery);
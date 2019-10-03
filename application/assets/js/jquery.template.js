/*
    Copyright 2018-2019 Cédric Levieux, Parti Pirate

    This file is part of Congressus.

    Congressus is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    Congressus is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with Congressus.  If not, see <https://www.gnu.org/licenses/>.
*/

function replaceData(string, data) {
	for(var key in data) {
		var value = data[key];

		// Simple replacement
		var simpleKey = "\\${" + key + "}";
		var regex = new RegExp(simpleKey, "g");

		string = string.replace(regex, value);
	}
	
	return string;
}

(function($) {
	$.templates = {};

	use = function(selector, options) {
		var templated = [];
		$(selector).each(function() {
			var templateInstance = $(this).clone().removeClass("template").removeAttr("data-template-id");

			var html = templateInstance.prop("outerHTML");

/*
			if (options.data) {
				for(var key in options.data) {
					var value = options.data[key];

					// Simple replacement
					var simpleKey = "\\${" + key + "}";
					var regex = new RegExp(simpleKey, "g");

					html = html.replace(regex, value);
				}
			}
*/
			html = replaceData(html, options.data);

			templated[templated.length] = $(html)[0];
		});

		return $(templated);
	};

	$.fn.template = function(method, options) {

		if (method) {
			var settings = $.extend({}, options);

			switch(method) {
				case "use":
					return use(this, settings);
			}
		}
		else {
			// Init
			return this.each(function() {
				$.templates[$(this).attr("data-template-id")] = $(this).clone().removeClass("template").removeAttr("data-template-id");
			});
		}
	};
} (jQuery));
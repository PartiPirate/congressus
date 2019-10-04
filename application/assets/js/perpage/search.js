/*
    Copyright 2015-2017 Cédric Levieux, Parti Pirate

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
/* global $ */
/* global query */

function highlight(text) {
	$(".text-search").each(function() {
		var re = new RegExp("("+ text +")", "gmi");
		var str = $(this).html();
		var subst = '<span class="highlight">' + query + '</span>';

		var result = str.replace(re, subst);

		$(this).html(result);
	});
}

$(function() {
	highlight(query);
});
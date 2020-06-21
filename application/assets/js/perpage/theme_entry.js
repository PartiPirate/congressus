/*
    Copyright 2015-2020 Cédric Levieux, Parti Pirate

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

function addFreeFixedHandlers() {
	$("#free-theme-enter-btn").click(function(event) {
		event.preventDefault();
		event.stopPropagation();

		const themeId = $(this).data("theme-id");

		$.post("do_free_enter.php", {the_id : themeId, action: "add_member"}, function(data) {
			if (data.ok) {
				if (typeof updateGroupTree != "undefined") {
					updateGroupTree();
					openThemeUrl("theme.php?id=" + themeId);
				}
				else {
					window.location.reload();
				}
			}
		}, "json");
	});

	$("#free-theme-exit-btn").click(function(event) {
		event.preventDefault();
		event.stopPropagation();

		const themeId = $(this).data("theme-id");

		$.post("do_free_enter.php", {the_id : themeId, action: "remove_member"}, function(data) {
			if (data.ok) {
				if (typeof updateGroupTree != "undefined") {
					updateGroupTree();
					openThemeUrl("theme.php?id=" + themeId);
				}
				else {
					window.location.reload();
				}
			}
		}, "json");
	});
}

$(function() {
	addFreeFixedHandlers();
});
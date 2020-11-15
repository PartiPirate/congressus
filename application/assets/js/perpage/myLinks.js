/*
    Copyright 2020 Cédric Levieux, Parti Pirate

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

function updateLinks(areas) {
	$.get(window.location.href, {}, function(data) {

		for(let index = 0; index < areas.length; ++index) {
			const selector = areas[index];
			
			const table = $(data).find(selector);
			const existingTable = $(selector);

			existingTable.children().remove();
			existingTable.append(table.children());
		}

	}, "html");
}

function addLinkFormHandlers() {
	$("#authorize-form").submit(function(e) {
		e.preventDefault();
		e.stopPropagation();

		const form = $(this).serialize();

		$.post("meeting_api.php?method=do_createLink", form, function(data) {
			if (data.ok) {
				updateLinks([".from-links"]);
			}
		}, "json");
	});

	$("#im-authorized-form").submit(function(e) {
		e.preventDefault();
		e.stopPropagation();

		const form = $(this).serialize();

		$.post("meeting_api.php?method=do_createLink", form, function(data) {
			if (data.ok) {
				updateLinks([".to-links"]);
			}
		}, "json");
	});
}

function addLinkButtonHandlers() {
	$(".from-links,.to-links").on("click", ".btn-cancel", function() {
		const form = {};
		form.action = "cancel";
		form.tli_id = $(this).data("id");

		$.post("meeting_api.php?method=do_updateLink", form, function(data) {
			if (data.ok) {
				updateLinks([".from-links", ".to-links"]);
			}
		}, "json");
	});
	$(".from-links").on("click", ".btn-accept", function() {
		const form = {};
		form.action = "accept";
		form.tli_id = $(this).data("id");

		$.post("meeting_api.php?method=do_updateLink", form, function(data) {
			if (data.ok) {
				updateLinks([".from-links"]);
			}
		}, "json");
	});
	$(".from-links").on("click", ".btn-reject", function() {
		const form = {};
		form.action = "reject";
		form.tli_id = $(this).data("id");

		$.post("meeting_api.php?method=do_updateLink", form, function(data) {
			if (data.ok) {
				updateLinks([".from-links"]);
			}
		}, "json");
	});
}

$(function() {
	addLinkButtonHandlers();
	addLinkFormHandlers();
});
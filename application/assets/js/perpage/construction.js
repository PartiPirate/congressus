/*
	Copyright 2018 Cédric Levieux, Parti Pirate

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
    along with Congressus.  If not, see <http://www.gnu.org/licenses/>.
*/

/* global $ */
/* global testBadges */

function renewAgendas(successHandler) {

	$.get(window.location.href, {}, function(htmldata) {

		// Replace chat
		var mainPanel = $(htmldata).find("div#main-panel");

		mainPanel.find(".agenda-entry").each(function() {
			var agendaEntry = $(this);
			var agendaEntryId = agendaEntry.attr("id");
			
			var existingAgendaEntry = $("#" + agendaEntryId);
			
			if (existingAgendaEntry.length) {
				existingAgendaEntry.find(".panel-body").html(agendaEntry.find(".panel-body").html());
			}
			else {
				$("#main-panel .btn-add-point").before(agendaEntry);
			}
		});

		if (successHandler) successHandler();

	}, "text");
	
}

function showAddAgenda() {
	var meetingId = $(this).data("meeting-id");

	$("#save-agenda-modal #meetingIdInput").val(meetingId);
	$("#save-agenda-modal #agendaIdInput").val("");

	$("#save-agenda-modal #titleInput").val("");
	$("#save-agenda-modal #descriptionArea").val("");

	$('#save-agenda-modal').one('shown.bs.modal', function () {
		$("#save-agenda-modal #descriptionArea").keyup();
	});

	$("#save-agenda-modal").modal('show');
}

function saveAgenda(event) {
	var form = $("#save-agenda-modal form");

	$("#save-agenda-modal button").attr("disabled", "disabled");

	$.post("meeting_api.php?method=do_addAgendaPoint", form.serialize(), function(data) {
		renewAgendas(function() {
			// Remove the modal
			$("#save-agenda-modal button").removeAttr("disabled");
			$("#save-agenda-modal").modal('hide');
		});
	}, "json");
}

function addAgendaListeners() {
	$("#main-panel").on("click", ".btn-add-point", showAddAgenda);
	$("body").on("click", ".btn-save-agenda", saveAgenda);
}

$(function() {
	addAgendaListeners();
	
	$("body").on("keyup", "textarea[data-provide=markdown]", function(event) {
		//console.log(event)	
		if (event.key == ":") {
			var position = $(event.target).offset();
			position.top += 20;
			position.left += 10;
			position.caller = this;
			position.removeChar = true;
			$("body").emojioneHelper("show", position);
		}
	});

	$("body").emojioneHelper();
});
/*
    Copyright 2018-2020 Cédric Levieux, Parti Pirate

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
/* global testBadges */

/* global sourceSelectHandler */
/* global sourceUrlHandler */
/* global sourceArticlesHandler */

/* global showConnectInlineModal */
/* global addEventAlert */
/* global save_amendment_done */

var sourceEnabled = true

function renewPropositions(agendaId, successHandler) {

	$.get(window.location.href, {}, function(htmldata) {

		// Replace chat
		var mainPanel = $(htmldata).find("div#main-panel");

		var motionEntry = mainPanel.find(".motion-entry");
		var existingMotionEntry = $(".motion-entry");

		// Change an amendment counter if there is one
		existingMotionEntry.find(".counters").html(motionEntry.find(".counters").html());

		var motions = mainPanel.find("#agenda-entry-" + agendaId + " ul").html();
		$("#agenda-entry-" + agendaId + " ul").html(motions);

		if (successHandler) successHandler();

	}, "text");
	
}

function showAddMotion(event) {
	var meetingId = $(this).data("meeting-id");
	var agendaId = $(this).data("agenda-id");
	
	$("#save-amendment-modal #agendaIdInput").val(agendaId);
	$("#save-amendment-modal #meetingIdInput").val(meetingId);

	$("#save-amendment-modal #titleInput").val("");
	$("#save-amendment-modal #descriptionArea").val("");

	$("#save-amendment-modal #explanationArea").val("");

	if ($("#motion-title").text()) {
		$("#save-amendment-modal #titleInput").val("Amendement pour " + $("#motion-title").text());
		$("#save-amendment-modal #explanationArea").val("Amendement pour " + $("#motion-title").text());
	}

	if ($("textarea#destination").val()) {
		$("#save-amendment-modal #descriptionArea").val($("textarea#destination").val());
	}

	$("#sourceSelect").val("");
	$("#sourceUrlInput").val("");

	$("#sourceUrlDiv").hide();
	$("#sourceTitleDiv").hide();
	$("#sourceContentDiv").hide();

	if (sourceEnabled) {
		$("#sourceSelectDiv").show();
	}
	else {
		$("#sourceSelectDiv").hide();
	}

	$("#save-amendment-modal #sourceArticlesDiv").hide();

	$('#save-amendment-modal').one('shown.bs.modal', function () {
		$("#save-amendment-modal #descriptionArea").keyup();
		$("#save-amendment-modal #explanationArea").keyup();
	});

	$("#save-amendment-modal #titleInput").keyup();

	$("#save-amendment-modal").modal('show');
}

function saveMotion(event) {
	var form = $("#save-amendment-modal form");
	var agendaId = $("#save-amendment-modal #agendaIdInput").val();

	$("#save-amendment-modal button").attr("disabled", "disabled");

	$.post("meeting_api.php?method=do_addConstruction", form.serialize(), function(data) {
		renewPropositions(agendaId, function() {
			// Remove the modal
			$("#save-amendment-modal button").removeAttr("disabled");

//			console.log(data);

			if (data.ko && data.message == "must_be_connected") {
		        showConnectInlineModal();
			}
			else if (data.ok) {
				$("#save-amendment-modal").modal('hide');
			    addEventAlert(save_amendment_done, "success", 5000);
			}
			
		});
	}, "json");
}

function previewMotion(event) {
	var form = $("#save-amendment-modal form");

	$.post("meeting_api.php?method=do_showPreview", form.serialize(), function(data) {
		$("#preview-modal-container").children().remove();
		$("#preview-modal-container").append(data);
		$("#preview-modal").modal('show');
	}, "html");
}

function addAmendmentListeners() {
	$("body").on("click", ".btn-add-motion", showAddMotion);
	$("body").on("click", ".btn-save-motion", saveMotion);
	$("body").on("click", ".btn-preview-motion", previewMotion);
	$("body").on("change", "#save-amendment-modal #sourceSelect", function () { sourceSelectHandler($("#save-amendment-modal"), $(this)); });
	$("body").on("change", "#save-amendment-modal #sourceUrlInput", function () { sourceUrlHandler($("#save-amendment-modal"), $(this)); });
	$("body").on("change", "#save-amendment-modal #sourceArticlesSelect", function () { sourceArticlesHandler($("#save-amendment-modal")); });

	$("body").on("keyup", "#save-agenda-modal #titleInput", function() {
		if ($("#save-agenda-modal #titleInput").val()) {
			$(".btn-save-agenda").removeAttr("disabled");
		}
		else {
			$(".btn-save-agenda").attr("disabled", "disabled");
		}
	});

	$("body").on("keyup", "#save-amendment-modal #titleInput", function() {
		if ($("#save-amendment-modal #titleInput").val()) {
			$(".btn-save-motion").removeAttr("disabled");
		}
		else {
			$(".btn-save-motion").attr("disabled", "disabled");
		}
	});

}

$(function() {
	addAmendmentListeners();
});
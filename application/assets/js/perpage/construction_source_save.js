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

function renewSources(successHandler) {

	$.get(window.location.href, {}, function(htmldata) {

		// Replace chat
		var mainPanel = $(htmldata).find("div#main-panel");

		var motionEntry = mainPanel.find(".motion-entry");
		var existingMotionEntry = $(".motion-entry");

		// Change an source counter if there is one
		existingMotionEntry.find(".counters").html(motionEntry.find(".counters").html());

		// Renew sources

		var sources = mainPanel.find("#sources ul").html();
		$("#sources ul").html(sources);

		if (successHandler) successHandler();

	}, "text");
	
}

function showAddSource(event) {
/*	
	var meetingId = $(this).data("meeting-id");
	var agendaId = $(this).data("agenda-id");
	
	$("#save-source-modal #agendaIdInput").val(agendaId);
	$("#save-source-modal #meetingIdInput").val(meetingId);

	$("#save-source-modal #titleInput").val("");
	$("#save-source-modal #descriptionArea").val("");
*/	
	$("#save-source-modal .modal-title").html(save_source_title);

	$("#save-source-modal #sourceSelect").val("");
	$("#save-source-modal #sourceUrlInput").val("");
	$("#save-source-modal #sourceTitleInput").val("");
	$("#save-source-modal #sourceIdInput").val(0);

	$("#save-source-modal #sourceUrlDiv").hide();
	$("#save-source-modal #sourceTitleDiv").hide();
	$("#save-source-modal #sourceContentDiv").hide();

	$("#save-source-modal #sourceSelectDiv").show();

	$("#save-source-modal #sourceUrlInput").keyup();

	$("#save-source-modal #sourceArticlesDiv").hide();

	$("#save-source-modal .btn-save-source").html(common_create);

	$("#save-source-modal").modal('show');
}

function showUpdateSource(event) {
/*	
	var meetingId = $(this).data("meeting-id");
	var agendaId = $(this).data("agenda-id");
	
	$("#save-source-modal #agendaIdInput").val(agendaId);
	$("#save-source-modal #meetingIdInput").val(meetingId);

	$("#save-source-modal #titleInput").val("");
	$("#save-source-modal #descriptionArea").val("");
*/	
	const source = $(this).parents("li").data("json");
	
	console.log(source);


	$("#save-source-modal .modal-title").html(update_source_title);

	$("#save-source-modal #sourceUrlDiv").hide();
	$("#save-source-modal #sourceTitleDiv").hide();
	$("#save-source-modal #sourceContentDiv").hide();
	$("#save-source-modal #sourceArticlesDiv").hide();

	$("#save-source-modal #sourceSelect").val(source.sou_type).change();
	$("#save-source-modal #sourceUrlInput").val(source.sou_url);
	$("#save-source-modal #sourceTitleInput").val(source.sou_title);
	$("#save-source-modal #sourceIdInput").val(source.sou_id);

	$("#save-source-modal #sourceUrlInput").keyup();
	$("#save-source-modal #sourceUrlInput").change();

	sourceUrlHandler($("#save-source-modal"), $("#save-source-modal #sourceUrlInput"), function() {
		$("#save-source-modal #sourceTitleInput").val(source.sou_title);
		$("#save-source-modal #sourceArticlesSelect").val();
		$("#save-source-modal #sourceContentArea").val(source.sou_content);

		$("#save-source-modal #sourceArticlesSelect option").prop("selected", false);

		const articleIndices = JSON.parse(source.sou_articles);

		for(var index = 0; index < articleIndices.length; ++index) {
			$("#save-source-modal #sourceArticlesSelect option[value='" + articleIndices[index] + "']").prop("selected", true);
		}

//		console.log("callback");
	});

/*
	$("#save-source-modal #sourceSelectDiv").show();
*/

	$("#save-source-modal .btn-save-source").html(common_modify);

	$("#save-source-modal").modal('show');
}

function saveSource(event) {
	
	var form = $("#save-source-modal form");

	$("#save-source-modal button").attr("disabled", "disabled");

	$.post("meeting_api.php?method=do_addSource", form.serialize(), function(data) {
		renewSources(function() {
			// Remove the modal
			$("#save-source-modal button").removeAttr("disabled");

			if (data.ko && data.message == "must_be_connected") {
		        showConnectInlineModal();
			}
			else if (data.ok) {
				$("#save-source-modal").modal('hide');
			    addEventAlert("Votre source a bien été ajoutée", "success", 5000);
			}

		});
	}, "json");
	
}

function addSourceUpdateListeners() {

	$("#sources").on("mouseenter", "li", function() {
		$(this).children(".update-source-btn").show();
	});

	$("#sources").on("mouseleave", "li", function() {
		$(this).children(".update-source-btn").hide();
	});

	$("#sources").on("click", "li .update-source-btn", showUpdateSource);

}

function addSourceListeners() {
	$("body").on("click", ".btn-add-source", showAddSource);
	$("body").on("click", ".btn-save-source", saveSource);
	$("body").on("change", "#save-source-modal #sourceSelect", function () { sourceSelectHandler($("#save-source-modal"), $(this)); });
	$("body").on("change", "#save-source-modal #sourceUrlInput", function () { sourceUrlHandler($("#save-source-modal"), $(this)); });
	$("body").on("change", "#save-source-modal #sourceArticlesSelect", function () { sourceArticlesHandler($("#save-source-modal")); });

	
	$("body").on("keyup", "#save-source-modal #sourceUrlInput", function() {
		if ($("#save-source-modal #sourceUrlInput").val()) {
			$(".btn-save-source").removeAttr("disabled");
		}
		else {
			$(".btn-save-source").attr("disabled", "disabled");
		}
	});
}

$(function() {
	addSourceListeners();
	addSourceUpdateListeners();
});
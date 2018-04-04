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

	$('#save-amendment-modal').one('shown.bs.modal', function () {
		$("#save-amendment-modal #descriptionArea").keyup();
	});

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
			$("#save-amendment-modal").modal('hide');
		});
	}, "json");
}

function sourceSelectHandler() {
	var source = $(this).val();

	switch(source) {
		case "leg_text":
		case "leg_article":
		case "wiki_text":
			$("#sourceUrlDiv").show();

			break;
		default: 
		$("#sourceUrlDiv").hide();
	}
}

function sourceUrlHandler() {
	var source = $("#sourceSelect").val();
	var url = $(this).val();

	if (source == "leg_article") {
		legifranceArticleRequester(url);
	}
	else if (source == "leg_text") {
		legifranceTextRequester(url);
	}
	else if (source == "wiki_text") {
		wikiTextRequester(url);
	}
}

var articles = null;

function legifranceTextRequester(url) {
	$.get("construction/getLegifranceText.php", {"url":url}, function(data) {
		if (data.status = "ok") {
			$("#sourceTitleDiv").show();
			$("#sourceTitleInput").val(data.title);
			$("#sourceContentDiv").show();
			$("#sourceContentArea").val("");
			$("#sourceContentArea").keyup();
//			$("#sourceContentArea").val(data.content);

			articles = data.articles;

			var sourceArticlesSelect = $("#sourceArticlesSelect");
			sourceArticlesSelect.children().remove();

			for(var index = 0; index < articles.length; ++index) {
				var option = $("<option></option>").val(index).text(articles[index].title);
				sourceArticlesSelect.append(option);
			}

			$("#sourceArticlesDiv").show();
		}
		else {
			$("#sourceTitleDiv").hide();
			$("#sourceContentDiv").hide();
			$("#sourceArticlesDiv").hide();
		}
	}, "json");
}

function wikiTextRequester(url) {
	$.get("construction/getWikiText.php", {"url":url}, function(data) {
		if (data.status = "ok") {
			$("#sourceTitleDiv").show();
			$("#sourceTitleInput").val(data.title);
			$("#sourceContentDiv").show();
			$("#sourceContentArea").val("");
			$("#sourceContentArea").keyup();

//			$("#sourceContentArea").val(data.content);

			articles = data.articles;

			var sourceArticlesSelect = $("#sourceArticlesSelect");
			sourceArticlesSelect.children().remove();

			for(var index = 0; index < articles.length; ++index) {
				var option = $("<option></option>").val(index).text(articles[index].title);
				sourceArticlesSelect.append(option);
			}

			$("#sourceArticlesDiv").show();
		}
		else {
			$("#sourceTitleDiv").hide();
			$("#sourceContentDiv").hide();
			$("#sourceArticlesDiv").hide();
		}
	}, "json");
}

function legifranceArticleRequester(url) {
	$.get("construction/getLegifranceArticle.php", {"url":url}, function(data) {
		if (data.status == "ok") {
			$("#sourceTitleDiv").show();
			$("#sourceTitleInput").val(data.title);
			$("#sourceContentDiv").show();
			$("#sourceContentArea").val(data.content);
			$("#sourceContentArea").keyup();
		}
		else {
			$("#sourceTitleDiv").hide();
			$("#sourceContentDiv").hide();
			$("#sourceArticlesDiv").hide();
		}
	}, "json");
}

function sourceArticlesHandler() {
	var content = "";
	var contentSeparator = "";
	
	$("#sourceArticlesSelect option:selected").each(function() {
		var index = $(this).val();
		var article = articles[index];
		
		content += contentSeparator;
		if (article.level) {
			for(var lindex = 0; lindex < article.level; ++lindex) {
				content += "=";
			}
			content += " ";
		}
		content += article.title;
		if (article.level) {
			content += " ";
			for(var lindex = 0; lindex < article.level; ++lindex) {
				content += "=";
			}
		}
		
		if (article.content.trim()) {
			content += "\n\n" + article.content.trim();
		}
		
		contentSeparator = "\n\n";
	});
	
	$("#sourceContentArea").val(content);
	$("#sourceContentArea").keyup();
}

function addAmendmentListeners() {
	$("body").on("click", ".btn-add-motion", showAddMotion);
	$("body").on("click", ".btn-save-motion", saveMotion);
	$("body").on("change", "#sourceSelect", sourceSelectHandler);
	$("body").on("change", "#sourceUrlInput", sourceUrlHandler);
	$("body").on("change", "#sourceArticlesSelect", sourceArticlesHandler)
}

$(function() {
	addAmendmentListeners();
});
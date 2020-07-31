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

/* global $ */
/* global replaceData */

if ((typeof deleteCoAuthorTitle) == "undefined") var deleteCoAuthorTitle = "";
if ((typeof exchangeCoAuthorTitle) == "undefined") var exchangeCoAuthorTitle = "";

function showAuthorship(event) {
	$("#authorship-modal").modal('show');
}

function updateAuthors(successHandler) {

	$.get(window.location.href, {}, function(htmldata) {

		// Replace chat
		var mainPanel = $(htmldata).find("div#main-panel");

		var header = mainPanel.find(".motion-entry .panel-heading").children();
		$(".motion-entry .panel-heading").children().remove();
		$(".motion-entry .panel-heading").append(header);

		var coAuthors = $(htmldata).find(".form-group.co-authors").children();
		$(".form-group.co-authors").children().remove();
		$(".form-group.co-authors").append(coAuthors);

		if (successHandler) successHandler();

	}, "text");
	
}

function addCoAuthor() {
	var form = $("#add-co-author-form");

	$.post("meeting_api.php?method=do_addCoAuthor", form.serialize(), function(data) {
		if (data.ok) {
/*			
			var coAuthorSpan = '<span class="co-author co-author-${cau_id}"> <i class="fa fa-times text-danger delete-co-author-btn" data-co-author-id="${cau_id}" aria-hidden="true" data-toggle="tooltip" data-placement="top"></i> <i class="fa fa-exchange text-primary exchange-co-author-btn" data-co-author-id="${cau_id}" aria-hidden="true" data-toggle="tooltip" data-placement="top"></i> <span class="nickname">${pseudo_adh}</span></span>';
			coAuthorSpan = replaceData(coAuthorSpan, data.coAuthor);
			coAuthorSpan = $(coAuthorSpan);
			coAuthorSpan.find(".delete-co-author-btn").attr("title", deleteCoAuthorTitle);
			coAuthorSpan.find(".exchange-co-author-btn").attr("title", exchangeCoAuthorTitle);

			coAuthorSpan.find('[data-toggle="tooltip"]').tooltip();

			$("#authorship-modal #co-author-container .co-authors").append(coAuthorSpan);
			$("#authorship-modal #co-author-container").show();
			form.find("#userDataInput").val("");
*/			
			updateAuthors();
		}
	}, "json");
}

function deleteCoAuthor() {
	var button = $(this);
	var coAuthorId = button.data("co-author-id");
//	button.parents(".co-author").remove();

	if ($("#authorship-modal #co-author-container .co-author").length == 0) {
		$("#authorship-modal #co-author-container").hide()
	}

	$.post("meeting_api.php?method=do_removeCoAuthor", {coAuthorId: coAuthorId}, function(data) {
		updateAuthors();
	}, "json");
}

function acceptCoAuthor() {
	var button = $(this);
	var coAuthorId = button.data("co-author-id");

/*
	button.parents(".co-author").remove();

	if ($("#authorship-modal #co-author-container .co-author").length == 0) {
		$("#authorship-modal #co-author-container").hide()
	}
*/

	$.post("meeting_api.php?method=do_acceptCoAuthor", {coAuthorId: coAuthorId}, function(data) {
		updateAuthors();
	}, "json");
}

function refuseCoAuthor() {
	var button = $(this);
	var coAuthorId = button.data("co-author-id");

/*
	button.parents(".co-author").remove();

	if ($("#authorship-modal #co-author-container .co-author").length == 0) {
		$("#authorship-modal #co-author-container").hide()
	}
*/

	$.post("meeting_api.php?method=do_refuseCoAuthor", {coAuthorId: coAuthorId}, function(data) {
		updateAuthors();
	}, "json");
}

function askAuthorship() {
	var button = $(this);
	$.post("meeting_api.php?method=do_askCoAuthor", {motionId: $(".motion-entry").data("id"), pointId: $(".motion-entry").data("agendaId"), meetingId: $(".motion-entry").data("meetingId")}, function(data) {
		updateAuthors();
	}, "json");
}

function exchangeAuthors() {
	var button = $(this);
	var coAuthorId = button.data("co-author-id");
	var authorSpan = $(".motion-author");
	var authorNickname = authorSpan.text();
	var authorId = authorSpan.data("author-id");

	$.post("meeting_api.php?method=do_exchangeAuthors", {coAuthorId: coAuthorId}, function(data) {
		var coAuthorSpan = button.parents(".co-author");
		coAuthorSpan.find(".nickname").text(authorNickname);

		updateAuthors();
	}, "json");
}

function addAuthorshipListeners() {
	$("body").on("mouseover", "#motion-authors", function() { $("#show-motion-co-authors-btn").show(); });
	$("body").on("mouseleave", "#motion-authors", function() { $("#show-motion-co-authors-btn").hide(); });
	$("body").on("click", "#show-motion-co-authors-btn", showAuthorship);
	$("body").on("click", "#motion-authors .ask-authoring-btn", askAuthorship);
	$("body").on("click", "#authorship-modal .delete-co-author-btn", deleteCoAuthor);
	$("body").on("click", "#authorship-modal .exchange-co-author-btn", exchangeAuthors);
	$("body").on("click", "#authorship-modal .accept-co-author-btn, .motion-entry .panel-heading .accept-co-author-btn", acceptCoAuthor);
	$("body").on("click", "#authorship-modal .refuse-co-author-btn", refuseCoAuthor);
	$("body").on("click", "#authorship-modal #add-co-author-btn", addCoAuthor);
}

$(function() {
	addAuthorshipListeners();
});
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
/* global replaceData */

function showAuthorship(event) {
	$("#authorship-modal").modal('show');
}

function addCoAuthor() {
	var form = $("#add-co-author-form");

	$.post("meeting_api.php?method=do_addCoAuthor", form.serialize(), function(data) {
		if (data.ok) {
			var coAuthorSpan = '<span class="co-author co-author-${cau_id}"> <i class="fa fa-times text-danger delete-co-author-btn" data-co-author-id="${cau_id}" aria-hidden="true"></i> ${pseudo_adh}</span>';
			coAuthorSpan = replaceData(coAuthorSpan, data.coAuthor);

			$("#authorship-modal #co-author-container .co-authors").append($(coAuthorSpan));
			$("#authorship-modal #co-author-container").show();
			form.find("#userDataInput").val("");
		}
	}, "json");
}

function deleteCoAuthor() {
	var coAuthorId = $(this).data("co-author-id");
	$(this).parents(".co-author").remove();

	if ($("#authorship-modal #co-author-container .co-author").length == 0) {
		$("#authorship-modal #co-author-container").hide()
	}

	$.post("meeting_api.php?method=do_removeCoAuthor", {coAuthorId: coAuthorId}, function(data) {
	}, "json");
}

function addAuthorshipListeners() {
	$("body").on("click", "#show-motion-co-authors-btn", showAuthorship);
	$("body").on("click", "#authorship-modal .delete-co-author-btn", deleteCoAuthor);
	$("body").on("click", "#authorship-modal #add-co-author-btn", addCoAuthor);
/*	
	$("body").on("click", ".btn-save-motion", saveMotion);
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
*/
}

$(function() {
	addAuthorshipListeners();
});
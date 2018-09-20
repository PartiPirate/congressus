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
/* global htmlDiff */
/* global showdown */
/* global emojione */

var setTimeoutId = null;

function renewVotes(successHandler) {

	$.get(window.location.href, {}, function(htmldata) {

		// Replace chat
		var mainPanel = $(htmldata).find("div#main-panel");
		var motionEntry = mainPanel.find(".motion-entry");
		var existingMotionEntry = $(".motion-entry");

		existingMotionEntry.find("#voting-members-panel").html(motionEntry.find("#voting-members-panel"));
		existingMotionEntry.find(".counters").html(motionEntry.find(".counters").html());
		existingMotionEntry.find("#mini-voting-panel").html(motionEntry.find("#mini-voting-panel"));
		existingMotionEntry.find("#voting-panel").html(motionEntry.find("#voting-panel"));

		existingMotionEntry.find('[data-toggle="tooltip"]').tooltip();

		$("#motion-buttons-bar div").removeClass("active").addClass("zero").each(function() {
			if (($(this).find("input").val() * 2.) != 0) {
				$(this).addClass("active").removeClass("zero");
			}
		})

		if (successHandler) successHandler();

	}, "text");
}

function renewChats(types, successHandler) {
	
	$.get(window.location.href, {}, function(htmldata) {

		// Replace chat
		var mainPanel = $(htmldata).find("div#main-panel");

		// go through all types of chat to replace		
		for(var index = 0; index < types.length; ++index) {
			var type = types[index];

			var content = mainPanel.find("li." + type + "-chat");
			var listChats = $("." + type + "-chats ul.list-chats");

			listChats.children().remove();
			listChats.append(content);
			
//			content.each(function() {
//				listChats.append($(this));
//			});
			
			var counter = mainPanel.find("." + type + "-counter .counter").text().trim();
			$("." + type + "-counter .counter").text(counter);

			$("." + type + "-chat.answer-chat textarea[data-provide=markdown]").markdown();
			
			var orderSelect = $("." + type + "-chats .select-order");
			if (orderSelect.length) orderSelect.change();
		}

		var motionEntry = mainPanel.find(".motion-entry");
		var existingMotionEntry = $(".motion-entry");
		existingMotionEntry.find(".counters").html(motionEntry.find(".counters").html());

		if (successHandler) successHandler();

	}, "text");

}

function addChatAdviceListeners() {
	$("body").on("click", ".btn-chat-group button", function(event) {
		event.preventDefault();
		
		var form = {};
		
		form["meetingId"] = $(this).data("meetingId");
		form["chatId"] = $(this).data("chatId");
		form["agendaId"] = $(this).data("agendaId");
		form["advice"] = $(this).data("advice");

		if (!form["advice"]) {
			$("#answer-chat-" + form["chatId"]).toggle();
			$("#answer-chat-" + form["chatId"]).find(".autogrow").keyup();
			
			return;
		}

		$.post("meeting_api.php?method=do_setAdvice", form, function(data) {

			if (data.gamifiedUser.data) testBadges(data.gamifiedUser.data);

			renewChats(["pro", "against"], function() {});

		}, "json");
		
	});

	$("body").on("mouseenter", ".advice-progress-bar .progress", function () {
		$(this).css({height: "inherit"});
	})

	$("body").on("mouseleave", ".advice-progress-bar .progress", function () {
		$(this).css({height: "3px"});
	})
}

function addChatListeners() {
	$(".construction-motion").on("click", ".btn-chat-send", function(event) {
		event.preventDefault();

		var button = $(this);

		button.attr("disabled", "disabled");

		var form = button.parents("form");
		var type = form.find(".chat-type").val();

		var formData = form.serialize();

		$.post("meeting_api.php?method=do_addChat", formData, function(data) {

			testBadges(data.gamifiedUser.data);
			form.find(".chat-text").val("");

			renewChats([type, "all"], function() {
				button.removeAttr("disabled");
			});

		}, "json");

	});
}

function setVotes() {
	var form = $("#motion-buttons-bar form");
	var formData = form.serialize();
	
	$.post("meeting_api.php?method=do_voteConstruction", formData, function(data) {
		renewVotes();
	}, "json");
}

function addMotionListeners() {
	var votingPower = $("#motion-buttons-bar").data("voting-power");

	if (votingPower > 1) {
//		$("body").on("click", "#motion-buttons-bar button", function(event) { event.preventDefault(); });
		$("body").on("change", "#motion-buttons-bar div input", function(event) {
			event.preventDefault();

			var changedInput = $(this);
			var totalScore = 0;

			$("#motion-buttons-bar div.btn-vote input").each(function() {
				if (this == changedInput.get(0)) return;
				
				totalScore -= -$(this).val();
			});

			var localMaxPower = (votingPower - totalScore);

			if (changedInput.val() > localMaxPower) {
				changedInput.val(localMaxPower);
			}

			setVotes();
		});
	}
	else {
		$("body").on("click", "#motion-buttons-bar div.btn-vote", function(event) {
			event.preventDefault();

			$("#motion-buttons-bar div input").val(0);
			
			$(this).find("input").val(1);

			setVotes();
		});
	}
	
	$("body").on("click", "#motion-buttons-bar div.btn-delete-motion", function(event) {
		event.preventDefault();

		var meetingId = $(this).data("meeting-id");
		var pointId =   $(this).data("agenda-point-id");
		var motionId =  $(this).data("motion-id");

		$.post("meeting_api.php?method=do_removeMotion", {meetingId: meetingId, pointId: pointId, motionId: motionId}, function(data) {
			window.location.href = "construction.php?id=" + meetingId;
		}, "json");		
	});
}

function constructChangeScroll(scrollGroup) {
	var scroll = scrollGroup.find(".change-scroll");
	var scroller = scrollGroup.find(".scroller");
	var scrollHeight = scroller.height();

	scrollGroup.find(".change-scroll").height(scrollHeight);
	
	var maxheight = scroller.get(0).scrollHeight;
	var scrollOffset = scroller.get(0).scrollTop;

	scroll.children(".inserted,.deleted").remove();

	scroller.find("del").each(function() {
		var topPosition = $(this).position().top + scrollOffset;
		
		var topPercent = 100. * topPosition / maxheight;
		
		var pix = $("<div class='deleted scroll-to' data-scroll-to='"+topPosition+"'></div>");
		scroll.append(pix);
		pix.css({left: "7px", top: (topPercent * (scrollHeight - 8) / 100) + "px", position: "relative"});
		pix.click(function() {
			scroller.get(0).scrollTop = topPosition;
			scroller.scroll();
		});
	});

	scroller.find("ins").each(function() {
		var topPosition = $(this).position().top + scrollOffset;
		
		var topPercent = 100. * topPosition / maxheight;
		
		var pix = $("<div class='inserted scroll-to' data-scroll-to='"+topPosition+"'></div>");
		scroll.append(pix);
		pix.css({left: "1px", top: (topPercent * (scrollHeight - 8) / 100) + "px", position: "relative"});
		pix.click(function() {
			scroller.get(0).scrollTop = topPosition;
			scroller.scroll();
		});
	});

	scroll.find(".scroll-zone").height(scrollHeight * scrollHeight / maxheight);
}

function createDiff() {
	var sourceText = $("#source").val();
	var motionText = $("#destination").val();
	var explanationText = $("#explanation").val();

//	$("#diff").html(htmlDiff(toMarkdownWithEmoji(sourceText), toMarkdownWithEmoji(motionText)).replace(/[\n\r]/g, '<br>'));
//	$("#motion-description").html(htmlDiff(toMarkdownWithEmoji(sourceText), toMarkdownWithEmoji(motionText), {not_del_shown: true}).replace(/[\n\r]/g, '<br>'));
	$("#diff").html(htmlDiff(sourceText, motionText).replace(/[\n\r]/g, '<br>'));
	$("#motion-description").html(htmlDiff(sourceText, motionText, {not_del_shown: true}).replace(/[\n\r]/g, '<br>'));

	constructChangeScroll($("#motion-description-group"));
	constructChangeScroll($("#diff-group"));

	if (motionText != previousMotionText || explanationText != previousExplanation) {
		$("#save-motion-btn").removeAttr("disabled");
	}
	else {
		$("#save-motion-btn").attr("disabled", "disabled");
	}
}

function addDiffListeners() {
	$(".motion-entry").on("keyup", "textarea", function(event) {
//		console.log(event.char);
		if (event.key && event.key.length > 2 && event.key != "Backspace" && event.key != "Delete") return;

		if (setTimeoutId) {
			clearTimeout(setTimeoutId);
			setTimeoutId = null;
		}
		
		setTimeoutId = setTimeout(createDiff, 500);
	});

	$("#explanation").on("keyup", function(event) {
		var motionText = $("#destination").val();
		var explanationText = $("#explanation").val();

		if (motionText != previousMotionText || explanationText != previousExplanation) {
			$("#save-motion-btn").removeAttr("disabled");
		}
		else {
			$("#save-motion-btn").attr("disabled", "disabled");
		}
	});

	createDiff();
	$("#save-motion-btn").attr("disabled", "disabled");
}

function toMarkdownWithEmoji(source) {
	source = emojione.shortnameToImage(source);

	const regex = /^(=+)([^=]*)(=*)$/gm;
	
	let m;

	var dashes = ["", "#", "##", "###", "####", "#####", "######"];

	while ((m = regex.exec(source)) !== null) {
	    // This is necessary to avoid infinite loops with zero-width matches
	    if (m.index === regex.lastIndex) {
	        regex.lastIndex++;
	    }

	    var search = m[1] + m[2] + m[3];
	    var replace = dashes[m[1].length] + m[2] + dashes[m[3].length];
//		var replace = dashes[m[1].length] + m[2];

		source = source.replace(search, replace);
	}

	var converter = new showdown.Converter();
	source = converter.makeHtml(source);

	return source;
}

function addButtonsListeners() {
	var globalScrollTop = 0;

	$("#show-markdown-btn").click(function() {
		$("#markdown-area").html("");
		
		/* mediawiki markdown transformation to normalized markdown titles */
		var source = $("#destination").val();
		source = toMarkdownWithEmoji(source);

		$("#markdown-area").html(source);

		$("#markdown-area").show();
		$("#markdown-group").show();

		$("#explanation-div #explanation-textarea-div").hide();
		$("#explanation-div #explanation-content-div").show();
		$("#explanation-div #explanation-content-div").html(toMarkdownWithEmoji($("#explanation-div textarea").val()));

		$("#motion-description").hide();
		$("#motion-description-group").hide();
		$("#diff").hide();
		$("#diff-group").hide();
		$("#source").hide();
		$("#destination").hide();

		$(".btn-type-group button").removeClass("active");
		$(this).addClass("active");

		$(".btn-authoring-group").hide();
	});

	$("#show-motion-btn").click(function() {
		$("#motion-description").show();
		$("#motion-description-group").show();
		constructChangeScroll($("#motion-description-group"));

		$("#explanation-div #explanation-textarea-div").hide();
		$("#explanation-div #explanation-content-div").show();
		$("#explanation-div #explanation-content-div").html(toMarkdownWithEmoji($("#explanation-div textarea").val()));

		$("#diff").hide();
		$("#diff-group").hide();
		$("#source").hide();
		$("#destination").hide();
		$("#markdown-area").hide();
		$("#markdown-group").hide();

		$("#motion-description").get(0).scrollTop = globalScrollTop;

		$(".btn-type-group button").removeClass("active");
		$(this).addClass("active");

		$(".btn-authoring-group").hide();
	});

	$("#show-diff-btn").click(function() {
		$("#motion-description").hide();
		$("#motion-description-group").hide();
		$("#explanation-div #explanation-textarea-div").hide();
		$("#explanation-div #explanation-content-div").show();
		$("#explanation-div #explanation-content-div").html(toMarkdownWithEmoji($("#explanation-div textarea").val()));

		$("#diff").show();
		$("#diff-group").show();
		constructChangeScroll($("#diff-group"));
		$("#source").hide();
		$("#destination").hide();
		$("#markdown-area").hide();
		$("#markdown-group").hide();

		$("#diff").get(0).scrollTop = globalScrollTop;

		$(".btn-type-group button").removeClass("active");
		$(this).addClass("active");

		$(".btn-authoring-group").hide();
	});

	$("#show-motion-authoring-btn").click(function() {
		$("#explanation-div #explanation-content-div").hide();
		$("#explanation-div #explanation-textarea-div").show();
		$("#explanation-div textarea").keyup();

		$("#motion-description").hide();
		$("#motion-description-group").hide();
		$("#diff").show();
		$("#diff-group").show();
		constructChangeScroll($("#diff-group"));
		$("#source").show().keyup();
		$("#destination").show().keyup();
		$("#markdown-area").hide();
		$("#markdown-group").hide();

		$("#destination").get(0).scrollTop = globalScrollTop;
		$("#source").get(0).scrollTop = globalScrollTop;
		$("#diff").get(0).scrollTop = globalScrollTop;

		$(".btn-type-group button").removeClass("active");
		$(this).addClass("active");

		$(".btn-authoring-group").show();
	});

	$("#show-both-panels-btn").click(function() {
		$("#source").show();
		$("#destination").addClass("col-md-6").removeClass("col-md-12").show();

		$(".btn-authoring-group button").removeClass("active");
		$(this).addClass("active");
	});

	$("#show-right-panel-btn").click(function() {
		$("#source").hide();
		$("#destination").removeClass("col-md-6").addClass("col-md-12").show();

		$(".btn-authoring-group button").removeClass("active");
		$(this).addClass("active");
	});

	$("#destination,#source,#diff,#motion-description").on("scroll", function() {
		globalScrollTop = this.scrollTop;
		
		$("#destination").get(0).scrollTop = globalScrollTop;
		$("#source").get(0).scrollTop = globalScrollTop;
		$("#diff").get(0).scrollTop = globalScrollTop;
		$("#motion-description").get(0).scrollTop = globalScrollTop;
		
		
		$("#motion-description-group .scroll-zone").css({top: ($("#motion-description").get(0).scrollTop / $("#motion-description").get(0).scrollHeight * $("#motion-description").height()) + "px"});
		$("#diff-group .scroll-zone").css({top: ($("#diff").get(0).scrollTop / $("#diff").get(0).scrollHeight * $("#diff").height()) + "px"});
	});

//	$("#show-motion-btn").click();
	$("#show-markdown-btn").click();

	$(".btn-admin-group").on("click", "#btn-pin", function() {
		var button = $(this);

		var motionId = $(".motion-entry").data("id");
		var property = "mot_pinned";
		var propositionId = 0;
		var value = button.hasClass("active") ? 0 : 1;

		$.post("meeting_api.php?method=do_changeMotionProperty", {motionId: motionId, propositionId: propositionId, property: property, text: value}, function(data) {
			if (value) {
				button.addClass("active");
			}
			else {
				button.removeClass("active");
			}
			button.blur();
		}, "json");
	});
	
}

function addUpdateMotion() {
	$("#save-motion-btn").click(function() {
		var motionId = $(".motion-entry").data("id");
		var property = "mot_description";
		var propositionId = 0;
		previousMotionText = $("#destination").val();

		$.post("meeting_api.php?method=do_changeMotionProperty", {motionId: motionId, propositionId: propositionId, property: property, text: previousMotionText}, function(data) {
			var property = "mot_explanation";
			previousExplanation = $("#explanation").val();

			$.post("meeting_api.php?method=do_changeMotionProperty", {motionId: motionId, propositionId: propositionId, property: property, text: previousExplanation}, function(data) {
				$("#save-motion-btn").attr("disabled", "disabled");
			}, "json");
		}, "json");
	});
}

function animateScrollTo(element) {
    $('html, body').animate({
        scrollTop: element.offset().top
    }, 400);
}

function addGoToTabListeners() {
	$("body").on("click", ".go-to-tab", function(event) {
		event.preventDefault();
		
		var href = $(this).attr("href");

		var scrollToCallback = function() { animateScrollTo($(href)); };

		if ($('.nav-tabs li.active a[href='+href+']').length) {
			scrollToCallback();
		}
		else {
			$('.nav-tabs li a[href='+href+']').one('shown.bs.tab', scrollToCallback);
			$('.nav-tabs li a[href='+href+']').tab('show');
		}
		
	});

	$("body").on("click", ".go-to", function(event) {
		event.preventDefault();
		
		var href = $(this).attr("href");

	    $('html, body').animate({
	        scrollTop: $(href).offset().top
	    }, 400);
	});
}

function showTrashDialog(event) {

	$("#save-trash-modal #explanationArea").val("");
	$('#save-trash-modal').one('shown.bs.modal', function () {
		$("#save-trash-modal #explanationArea").keyup();
	});
//	$("#save-trash-modal .modal-title span").html("TOTO");

	$("#save-trash-modal").modal('show');
}

function saveTrash(event) {
	
	var form = $("#save-trash-modal form");
	var meetingId = form.find("#meetingIdInput").val();

	$("#save-trash-modal button").attr("disabled", "disabled");

	$.post("meeting_api.php?method=do_trashMotion", form.serialize(), function(data) {
		$("#save-trash-modal button").removeAttr("disabled");
		$("#save-trash-modal").modal('hide');

		window.location.href = "construction.php?id=" + meetingId;
	}, "json");

}

function addTrashListeners() {
	$("body").on("click", "#motion-buttons-bar .btn-trash-motion", showTrashDialog);
	$("body").on("click", "#save-trash-modal .btn-trash-motion", saveTrash);
}

function addOpenDebateListeners() {
	$("body").on("click", "#motion-buttons-bar .btn-open-debate-motion", function() {
		$.post("meeting_api.php?method=do_openDebate", {meetingId: $(this).data("meeting-id"), pointId: $(this).data("agenda-point-id"), motionId: $(this).data("motion-id") }, function(data) {
			if (data.ok) {
				renewSources(null);
//				showAlertOK();
			}
			else {
//				showAlertKO();
			}
		}, "json");
	});
}

function addTitleListeners() {
	$("body").on("mouseenter", ".motion-title-wrapper", function() {
		if ($("#motion-title").is(":visible")) {
			$("#update-title-btn").show();
		}	
	});
	$("body").on("mouseleave", ".motion-title-wrapper", function() {
//		if ($("#motion-title").is(":visible")) {
			$("#update-title-btn").hide();
//		}	
	});
	$("body").on("click", "#update-title-btn", function() {
		$("#motion-title, #update-title-btn").hide();
		$("#motion-title-input, #save-title-btn, #cancel-title-btn").show();
		$("#motion-title-input").val($("#motion-title").text());
		$("#motion-title-input").css({"display": "inline-block"});
		$("#motion-title-input").removeAttr("disabled");
	});
	$("body").on("click", "#save-title-btn", function() {
		var motionId = $(".motion-entry").data("id");
		var title = $("#motion-title-input").val();
		
		$.post("meeting_api.php?method=do_changeMotionProperty", {motionId: motionId, propositionId: 0, property: "mot_title", text: title}, function(data) {
			$("#motion-title-input").attr("disabled", "disabled");
			$("#motion-title").text(title);
			$("#motion-title, #update-title-btn").show();
			$("#motion-title-input, #save-title-btn, #cancel-title-btn").hide();
		});
	});
	$("body").on("click", "#cancel-title-btn", function() {
		$("#motion-title, #update-title-btn").show();
		$("#motion-title-input, #save-title-btn, #cancel-title-btn").hide();
	});
}

var previousMotionText = "";
var previousExplanation = "";

$(function() {
	addTrashListeners();
	addChatListeners();
	addMotionListeners();
	addDiffListeners();
	addButtonsListeners();
	addUpdateMotion();
	addChatAdviceListeners();
	addGoToTabListeners();
	addOpenDebateListeners();
	addTitleListeners();

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

	previousMotionText = $("#destination").val();
	previousExplanation = $("#explanation").val();
});
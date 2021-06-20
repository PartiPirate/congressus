/*
    Copyright 2015-2021 Cédric Levieux, Parti Pirate

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
/* global bootbox */
/* global majority_judgement_values */
/* global majority_judgement_translations */
/* global approval_translations */
/* global maybe_translations */
/* global showdown */
/* global areVotesAnonymous */
/* global computeMotion */

/* I18N */

/* global meeting_vote */
/* global meeting_motionVote2 */
/* global emojione */
/* global goDownPoint */

/* global keyupTimeoutId */
/* global setMotionTags */

/* global motion_expired */

var judgmentVoteIsMandatory = false;
var approval_values = [1, 2];
var maybe_values = [1, 2, 3];

keyupTimeoutId = null;

function clearKeyup() {
	if (keyupTimeoutId) {
		clearTimeout(keyupTimeoutId);
		keyupTimeoutId = null;
	}
}

function getUserId() {
	var userId = $(".meeting").data("user-id");

	return userId;
}

function isGuest() {
	var userId = getUserId();

	if (("" + userId).indexOf("G") != -1) {
		return true;
	}

	return false;
}

function hasVotingRight(id) {
	return $("#noticed-people .members li#member-"+id+" .voting.can-vote").length > 0;
}

function getVotingPower(id) {
	var power = 0;

	$("#noticed-people .members li#member-"+id+" .voting").each(function() {
		if($(this).css("display") != "none") {
			power -= - $(this).parent().find(".power").text();
		}
	});

	return power;
}

function hasRight(userId, right) {
	var meeting = $(".meeting").data("json");

	var has = false;

	has |= $(".mee_president_member_id").data("id") == userId;
	has |= $(".mee_secretary_member_id").data("id") == userId;

	if (isGuest) return has;

	if (meeting && meeting["mee_rights"]) {
		for(var index = 0; index < meeting["mee_rights"].length; ++index) {
			has |= meeting["mee_rights"][index] == right;
		}
	}

	return has;
}

function hasWritingRight(id) {
	return hasRight(id, null);
}

function isPresident(id) {
	var hasRight = false;
	hasRight |= $(".mee_president_member_id").data("id") == id;

	return hasRight;
}

function addAgendaPointHandlers() {
	$("#agenda_point .objects").on("mouseenter", "li#description", function(event) {
//		if (hasRight(getUserId(), "handle_agenda") && !$("li#description .Editor-container:visible").length) {
		if (hasRight(getUserId(), "handle_agenda")) {
			$(this).find(".glyphicon-pencil").show();
		}
	});

	$("#agenda_point .objects").on("mouseleave", "li#description", function(event) {
		$(this).find(".glyphicon-pencil").hide();
	});

	$("#agenda_point .objects").on("click", "li#description", function(event) {
		if (!hasRight(getUserId(), "handle_agenda")) {
			return;
		}

		$(this).find(".glyphicon-pencil").hide();

		var descriptionText = $(this).find("p");
		descriptionText.hide();

		$(this).find(".description-editor").show()
		autogrowElement($(this).find(".description-editor textarea").get(0));
		$(this).find(".description-editor textarea").focus();

/*
		$(this).find(".description-editor+div").show();
		$(this).find(".description-editor+div .Editor-editor").focus();
*/		
	});

/*	
	$("#agenda_point .objects").on("click", "li#description", function(event) {
		if (!hasRight(getUserId(), "handle_agenda")) {
			return;
		}
		if ($(this).find(".description-editor+div:visible").length) {
			$(this).find(".description-editor+div .Editor-editor").focus();
			return;
		}

		$(this).find(".glyphicon-pencil").hide();

		var descriptionText = $(this).find("p");

		$(this).find(".description-editor").Editor("setText", descriptionText.html());

		descriptionText.hide();

		$(this).find(".description-editor+div").show();
		$(this).find(".description-editor+div .Editor-editor").focus();
	});
*/	
}

function editorBlurHandler(event) {
	
	//click on emoji, do nothing more than the emoji code
	if ($(".emojionepicker:visible").length) return;

//	console.log(event);

	var description = $("li#description");
	var descriptionEditor = description.find(".description-editor");

	if (descriptionEditor.length == 0) return;
	if (!descriptionEditor.is(":visible")) return;

	var agendaId = $("#agenda_point").data("id");
	var meetingId = $(".meeting").data("id");

	clearKeyup();

/*
	var descriptionEditor = description.find("div.description-editor");

	clearKeyup();
	// update the text into the server
	var newText = descriptionEditor.Editor("getText");
*/

	var newText = descriptionEditor.find("textarea").val();

	$.post("meeting_api.php?method=do_changeAgendaPoint", {meetingId: meetingId, pointId: agendaId, property: "age_description", text: newText}, function(data) {
		description.find("p").html(toMarkdownWithEmoji(newText));
		description.find("p").show();

		descriptionEditor.hide();
	}, "json");
}

function getDescriptionLi(list) {
	var description = list.find("li#description");

	var agendaId = $("#agenda_point").data("id");
	var meetingId = $(".meeting").data("id");

	if (!description.length && agendaId != -1) {
		description = $("<li />", {id: "description", "class": "list-group-item"});

		description.append($("<span />", {"class": "glyphicon glyphicon-pencil", style: "display: none; float: right;"}));
		description.append($("<p />"));
		description.append($("<div></div>", {"class": "description-editor"}));
		description.find("div").append("<textarea class=\"form-control autogrow\" id=\"description-text\" data-provide=\"markdown\" data-hidden-buttons=\"cmdPreview\" rows=\"5\"></textarea>");

		description.find("textarea").markdown().blur(editorBlurHandler);

//		var descriptionEditor = description.find("div.description-editor");
//		descriptionEditor.Editor();

		list.append(description);

		var editor = description.find(".description-editor");
		editor.hide();
/*
		editor.children(".Editor-editor").css({"max-height": "300px", "overflow-y": "scroll"});
*/
		description.hide().fadeIn(400);

		description.find("textarea").keyup(function() {
			clearKeyup();
			keyupTimeoutId = setTimeout(function() {
				var newText = description.find("textarea").val();

				$.post("meeting_api.php?method=do_changeAgendaPoint", {meetingId: meetingId, pointId: agendaId, property: "age_description", text: newText}, function(data) {
				}, "json");
			}, 1500);
		});
	}

	return description;
}

function updateDescription(description, agenda) {
	var descriptionP = description.children("p");
	var descriptionTextarea = description.find("textarea");

	if (descriptionTextarea.is(":visible")) return;

	const newText = toMarkdownWithEmoji(agenda.age_description);

	if (descriptionTextarea.val() != agenda.age_description || descriptionP.html() != newText) {
		descriptionP.html(toMarkdownWithEmoji(agenda.age_description));
		descriptionTextarea.val(agenda.age_description);
	}
}

function setAgendaPoint(point) {
	if (point.ko) return;

	var list = $("#agenda_point ul");

	if ($("#agenda_point .agenda-label").eq(0).text() != point.agenda.age_label) {
		$("#agenda_point .agenda-label").text(point.agenda.age_label);
	}

	var description = getDescriptionLi(list);
	updateDescription(description, point.agenda);
}

function setAgendaMotion(id, motions) {
	var userId = $(".meeting").data("user-id");
	var list = $("#agenda_point ul.objects");
	var motionContainer = list.find("li#motion-" + id);
	var meeting = $(".meeting").data("json");
	var synchroVote = meeting.mee_synchro_vote;

	if (!motionContainer.length) {
		motionContainer = $("li[data-template-id=motion]").template("use", {data: {mot_id: id}});
		motionContainer.find(".motion-actions *").tooltip({placement: "top"});
		motionContainer.find("*").tooltip({placement: "bottom"});
		list.append(motionContainer);

		motionContainer.hide().fadeIn(400);
	}

	var motionActions = motionContainer.find(".motion-actions");

	motionContainer.removeClass("to-delete");

	motionActions.find(".btn").hide();

	var title = motionContainer.find(".motion-title");
	var description = motionContainer.find(".motion-description-text");
	var propositions = motionContainer.find(".motion-propositions");

	var first = true;

	propositions.children(".proposition").addClass("to-delete");

	for(var index = 0; index < motions.length; ++index) {
		var motion = motions[index];
		if (motion.mot_id != id) continue;

		if (first) {
			first = false;

			if (title.text() != motion.mot_title) {
				title.text(motion.mot_title);
			}

			if (description.html() != $("<div />").html(motion.mot_description).html()) {
				description.html(motion.mot_description);
			}

			motionActions.children("button").removeClass("disabled");

			motionContainer.data("status", motion.mot_status);
			motionContainer.data("anonymous", (motion.mot_anonymous && motion.mot_anonymous != "0") ? true : false);
			motionContainer.data("win-limit", motion.mot_win_limit);

			if (!hasRight(getUserId(), "handle_motion")) {
				motionActions.find(".btn-motion-limits.btn-motion-limits").prop("disabled", true);
			}
			else {
				motionActions.find(".btn-motion-limits.btn-motion-limits").prop("disabled", false);
			}

			if (motionContainer.data("tag-ids") != JSON.stringify(motion.mot_tag_ids)) {
				setMotionTags(motion.mot_id, motion.mot_tag_ids);
				motionContainer.data("tag-ids", JSON.stringify(motion.mot_tag_ids));
			}

			switch(motion.mot_status) {
				case "construction":
					motionActions.find(".btn-set-deadline").show();
					motionActions.find(".btn-add-proposition").show();
					motionActions.find(".btn-do-vote").show();
					motionActions.find(".btn-remove-motion").show();
					motionActions.find(".btn-motion-limits").show();
					motionActions.find(".btn-motion-limits").removeClass("active").removeClass("disabled");
					motionActions.find(".btn-motion-anonymous").show();
					motionActions.find(".btn-see-motion-stats").hide();
					motionActions.find(".btn-see-motion-delegations").hide();
					motionContainer.find(".btn-motion-add-tag").show();
					motionContainer.find(".btn-remove-tag").show();
					break;
				case "voting":
					motionActions.find(".btn-set-deadline").show();
					motionActions.find(".btn-do-close").show();
					motionActions.find(".btn-remove-motion").show();
					motionActions.find(".btn-motion-limits").addClass("disabled");
					motionActions.find(".voters").show();
					motionActions.find(".btn-motion-anonymous").show();
					motionActions.find(".btn-see-motion-stats").show();
					motionActions.find(".btn-see-motion-delegations").show();
					motionContainer.find(".btn-motion-add-tag").hide();
					motionContainer.find(".btn-remove-tag").hide();
					break;
				case "resolved":
					motionActions.find(".btn-set-deadline").hide();
					motionActions.find(".btn-motion-limits").addClass("disabled");
					motionActions.find(".voters").show();
					motionActions.find(".btn-motion-anonymous").hide();
					motionActions.find(".btn-see-motion-stats").show();
					motionActions.find(".btn-see-motion-delegations").show();
					motionContainer.find(".btn-motion-add-tag").hide();
					motionContainer.find(".btn-remove-tag").hide();
					break;
				default:
			}

			if (!hasRight(getUserId(), "handle_motion")) {
				motionActions.find(".btn-motion-limits.btn-motion-limits").hide();
				motionContainer.find(".btn-motion-add-tag").hide();
				motionContainer.find(".btn-remove-tag").hide();
				motionActions.find(".btn-set-deadline").hide();
			}
			motionActions.find(".btn-motion-limits.btn-motion-limit-" + motion.mot_win_limit).addClass("active").show();

			if (motion.mot_anonymous && motion.mot_anonymous != "0") {
				motionActions.find(".btn-motion-anonymous").addClass("active");
			}
			else {
				motionActions.find(".btn-motion-anonymous").removeClass("active");
				if (!hasRight(getUserId(), "handle_motion")) {
					motionActions.find(".btn-motion-anonymous").hide();
				}
			}

			const deadlineContainer = motionContainer.find(".deadline-span");

			if (motion.mot_deadline != motionContainer.data("deadline")) {
				motionContainer.data("deadline", motion.mot_deadline);

				if (motion.mot_deadline) {
					deadlineContainer.attr("title", motion.mot_deadline_string);
					deadlineContainer.show();
				}
				else {
					deadlineContainer.hide();
					motionContainer.data("expired", false);
				}
			}

			if (motion.mot_deadline) {
				if (motion.mot_deadline_diff.indexOf("-") == -1) {
					motionContainer.data("expired", true);
					deadlineContainer.html(motion_expired);
					if (!deadlineContainer.hasClass("text-danger")) {
						deadlineContainer.removeClass("text-warning");
						deadlineContainer.removeClass("text-success");
						deadlineContainer.addClass("text-danger");
					}
				}
				else {
					motionContainer.data("expired", false);
					const deadlineDiff = motion.mot_deadline_diff.replace("-","");
					deadlineContainer.text(deadlineDiff);

					if (deadlineDiff < "00:05:00") {
						if (!deadlineContainer.hasClass("text-danger")) {
							deadlineContainer.addClass("text-danger");
							deadlineContainer.removeClass("text-warning");
							deadlineContainer.removeClass("text-success");
						}
					}
					else if (deadlineDiff < "00:30:00") {
						if (!deadlineContainer.hasClass("text-warning")) {
							deadlineContainer.addClass("text-warning");
							deadlineContainer.removeClass("text-success");
							deadlineContainer.removeClass("text-danger");
						}
					}
					else if (!deadlineContainer.hasClass("text-success")) {
						deadlineContainer.addClass("text-success");
						deadlineContainer.removeClass("text-warning");
						deadlineContainer.removeClass("text-danger");
					}
				}
			}

			motionActions.find(".btn-motion-anonymous").prop("disabled", !hasRight(getUserId(), "handle_motion"));
		}

		if (!motion.mpr_id) continue;

		var proposition = propositions.find("#proposition-" + motion.mpr_id);
		if (proposition.length == 0) {
			proposition = $("div[data-template-id=proposition]").template("use", {data: motion});
			proposition.find("*").tooltip({placement: "left"});

			propositions.append(proposition);
			proposition.hide().fadeIn(400);
		}

		if (hasVotingRight(userId) && motion.mot_status == "voting" && synchroVote && !motionContainer.data("expired")) {
			proposition.find("button.btn-vote").show();
		}
		else {
			proposition.find("button.btn-vote").hide();
		}

		proposition.data("neutral", motion.mpr_neutral);
		if (proposition.find(".proposition-label").text() != motion.mpr_label) {
			proposition.find(".proposition-label").text(motion.mpr_label);
		}
		proposition.removeClass("to-delete");
	}

	propositions.children(".to-delete").remove();

	if (hasRight(getUserId(), "handle_motion")) {
//		motionActions.show();
	}
	else {
		motionActions.find(".btn-add-proposition").hide();
		motionActions.find(".btn-do-vote").hide();
		motionActions.find(".btn-remove-motion").hide();
		motionActions.find(".btn-do-close").hide();
//		motionActions.hide();
	}

//	motionContainer.find(".btn-vote").removeAttr("disabled");
}

function setAdvice() {
	var userId = $(".meeting").data("user-id");
	var agendaId = $("#agenda_point").data("id");
	var meetingId = $(".meeting").data("id");

	var button = $(this);

	var chatId = button.data("chat-id");
	var advice = button.data("advice");

	$.post("meeting_api.php?method=do_setAdvice", {meetingId : meetingId, agendaId: agendaId, chatId: chatId, advice: advice}, function(data) {
		if (data.ok && data.gamifiedUser) {
			testBadges(data.gamifiedUser.data);
		}
	}, "json");

}

function addSpeakerChat() {
	var focus = arguments.length > 0 ? arguments[0] : true;
	var userId = $(".btn-add-speaker-chat").data("speaker-id");
	var agendaId = $("#agenda_point").data("id");
	var meetingId = $(".meeting").data("id");
	var startingText = $("#starting-text").val();

	$.get("meeting_api.php?method=do_addChat", {id: meetingId, pointId: agendaId, userId: userId, startingText: startingText}, function(data) {
		setAgendaChat(data.chat.cha_id, [data.chat]);
		if (focus) {
			$("#agenda_point ul.objects li.chat#chat-" + data.chat.cha_id).click();
		}
		if (data.gamifiedUser) testBadges(data.gamifiedUser.data);
		$("#starting-text").val("");
		$("#starting-text").keyup();

		goDownPoint();
	}, "json");
}

function addOwnChat() {
	var focus = arguments.length > 0 ? arguments[0] : true;
	var userId = $(".meeting").data("user-id");
	var agendaId = $("#agenda_point").data("id");
	var meetingId = $(".meeting").data("id");
	var startingText = $("#starting-text").val();

	$.get("meeting_api.php?method=do_addChat", {id: meetingId, pointId: agendaId, userId: userId, startingText: startingText}, function(data) {
		setAgendaChat(data.chat.cha_id, [data.chat]);
		if (focus) {
			$("#agenda_point ul.objects li.chat#chat-" + data.chat.cha_id).click();
		}
		if (data.gamifiedUser) testBadges(data.gamifiedUser.data);
		$("#starting-text").val("");
		$("#starting-text").keyup();

		goDownPoint();
	}, "json");
}

function addOwnTask() {
	var focus = arguments.length > 0 ? arguments[0] : true;

	var targetId = $(".meeting").data("user-id");
	var targetType = "galette_adherents";
	var agendaId = $("#agenda_point").data("id");
	var meetingId = $(".meeting").data("id");
	var startingText = $("#starting-text").val();

	$.get("meeting_api.php?method=do_addTask", {id: meetingId, pointId: agendaId, targetId: targetId, targetType: targetType, startingText: startingText}, function(data) {
		setAgendaTask(data.task.tas_id, [data.task]);
		if (focus) {
			$("#agenda_point ul.objects li.task#task-" + data.task.tas_id).click();
		}
		$("#starting-text").val("");
		$("#starting-text").keyup();

		goDownPoint();
	}, "json");
}

function addConclusion() {
	var focus = arguments.length > 0 ? arguments[0] : true;

	var agendaId = $("#agenda_point").data("id");
	var meetingId = $(".meeting").data("id");
	var startingText = $("#starting-text").val();

	$.get("meeting_api.php?method=do_addConclusion", {id: meetingId, pointId: agendaId, startingText: startingText}, function(data) {
		setAgendaConclusion(data.conclusion.con_id, [data.conclusion]);
		if (focus) {
			$("#agenda_point ul.objects li.conclusion#conclusion-" + data.conclusion.con_id).click();
		}
		$("#starting-text").val("");
		$("#starting-text").keyup();

		goDownPoint();
	}, "json");
}

function addMotion(event) {
	const agendaId = $("#agenda_point").data("id");
	const meetingId = $(".meeting").data("id");
	let startingText = $("#starting-text").val();

	const motionForm = {meetingId: meetingId, pointId: agendaId, startingText: startingText};

	const lineSeparatorIndex = startingText.indexOf("---");

	if (lineSeparatorIndex != -1) {
		const description = startingText.substr(lineSeparatorIndex + 3).trim();
		startingText = startingText.substr(0, lineSeparatorIndex).trim();

		motionForm["startingText"] = startingText;
		motionForm["description"] = description;
	}

	$.get("meeting_api.php?method=do_addMotion", motionForm, function(data) {
		if (data.gamifiedUser) testBadges(data.gamifiedUser.data);

		setAgendaMotion(data.motion.mot_id, [data.motion]);

		$("#agenda_point ul.objects li.motion#motion-" + data.motion.mot_id + " h4").click();

		$("#starting-text").val("");
		$("#starting-text").keyup();

		goDownPoint();
	}, "json");
}

function addYesNoMotion(event) {
	const agendaId = $("#agenda_point").data("id");
	const meetingId = $(".meeting").data("id");
	let startingText = $("#starting-text").val();

	const motionForm = {meetingId: meetingId, pointId: agendaId, startingText: startingText, noProposition: true};

	const lineSeparatorIndex = startingText.indexOf("---");

	if (lineSeparatorIndex != -1) {
		const description = startingText.substr(lineSeparatorIndex + 3).trim();
		startingText = startingText.substr(0, lineSeparatorIndex).trim();

		motionForm["startingText"] = startingText;
		motionForm["description"] = description;
	}

	$.get("meeting_api.php?method=do_addMotion", motionForm, function(data) {
		if (data.gamifiedUser) testBadges(data.gamifiedUser.data);

		var motionId = data.motion.mot_id;
		// Add propositions
		var addPropositionForm = {meetingId: meetingId, pointId: agendaId, motionId: motionId, label: ""};
		var propositions = ["Oui", "Non"];

		for(var index = 0; index < propositions.length; ++index) {
			addPropositionForm.label = propositions[index];
			$.post("meeting_api.php?method=do_addMotionProposition", addPropositionForm, function(data) {
			}, "json");
		}

		setAgendaMotion(data.motion.mot_id, [data.motion]);

		$("#agenda_point ul.objects li.motion#motion-" + data.motion.mot_id + " h4").click();

		$("#starting-text").val("");
		$("#starting-text").keyup();

		goDownPoint();
	}, "json");
}

function setAgendaChat(id, chats) {
	
	var list = $("#agenda_point ul.objects");
	var chatContainer = list.find("li#chat-" + id);

	var memberSelect = null;

	if (!chatContainer.length) {
		chatContainer = $("li[data-template-id=chat]").template("use", {data: {cha_id: id}});
		chatContainer.find("*").tooltip({placement: "left"});
		list.append(chatContainer);

		chatContainer.hide().fadeIn(400);

		var memberSelect = chatContainer.find("select.chat-select-member");
		memberSelect.find(".voting").append($(".president select .voting option").clone());
		memberSelect.find(".unknown").append($(".president select .unknown option").clone());
		memberSelect.find(".noticed").append($(".president select .noticed option").clone());
		memberSelect.find(".connected").append($(".president select .connected option").clone());
	}
	else {
		memberSelect = 	chatContainer.find("select.chat-select-member");
	}

	chatContainer.removeClass("to-delete");

	var avatar = chatContainer.find(".avatar");
	var nickname = chatContainer.find(".chat-nickname");
	var text = chatContainer.find(".chat-text");

	for(var index = 0; index < chats.length; ++index) {
		var chat = chats[index];
		if (chat.cha_id != id) continue;

		if (!memberSelect.is(":visible")) {
			memberSelect.val(chat.cha_member_id);
		}

		if (nickname.text() != chat.mem_nickname) {
			nickname.text(chat.mem_nickname);
		}

		if (avatar.attr("title") != chat.mem_nickname) {
			avatar.attr("title", chat.mem_nickname);
		}

		var url = "getAvatar.php?userId=" + chat.cha_member_id;
		if (avatar.attr("src") != url) {
			avatar.attr("src", url);
		}

		if (text.data("text") != chat.cha_text) {
//			text.html(chat.cha_text.replace(/\n/g, "<br>"));
			text.html(toMarkdownWithEmoji(chat.cha_text));
			text.data("text", chat.cha_text);
		}

		var advices = {};

		advices["thumb_up"] = 0;
		advices["thumb_middle"] = 0;
		advices["thumb_down"] = 0;
		advices["total"] = 0;

		if (typeof chat.advices != "undefined") {
			for(var jndex = 0; jndex < chat.advices.length; ++jndex) {
				var advice = chat.advices[jndex];

				advices[advice.cad_advice]++;
				advices["total"]++;

				if (advice.cad_user_id == getUserId()) {
					chatContainer.find(".btn-advice").each(function() {
						if ($(this).data("advice") == advice.cad_advice) {
							$(this).addClass("disabled").prop("disabled", true);
							/* TODO find a better way */
							var ttid = $(this).attr("aria-describedby");
							if (ttid) {
								$(this).removeAttr("aria-describedby");
								var title = $("#" + ttid).text();
								$("#" + ttid).remove();
								$(this).attr("title", title);
							}
						}
						else {
							$(this).removeClass("disabled").prop("disabled", false);
						}
					});
				}
			}
		}

		var progress = chatContainer.find(".progress");

		if (advices["total"] == 0) {
			progress.hide();
		}
		else {
			progress.show();

			progress.find(".progress-bar").each(function() {
				var percent = advices[$(this).data("advice")] * 100 / advices["total"];
				$(this).css({width: percent + "%"});
//				$(this).attr("title", advices[$(this).data("advice")]);
				$(this).find(".value").text(advices[$(this).data("advice")]);

				if (advices[$(this).data("advice")]) {
					$(this).show();
				}
				else {
					$(this).hide();
				}
			});
		}

		chatContainer.data("json", chat);

		break;
	}
}

function setAgendaTask(id, tasks) {
	var list = $("#agenda_point ul.objects");
	var taskContainer = list.find("li#task-" + id);

	if (!taskContainer.length) {
		taskContainer = $("li[data-template-id=task]").template("use", {data: {tas_id: id}});
		taskContainer.find("*").tooltip({placement: "left"});

		list.append(taskContainer);

		taskContainer.hide().fadeIn(400);
	}

	taskContainer.removeClass("to-delete");

	var text = taskContainer.find(".task-label");

	for(var index = 0; index < tasks.length; ++index) {
		var task = tasks[index];
		if (task.tas_id != id) continue;

		if (text.data("text") != task.tas_label) {
			text.html(task.tas_label.replace(/\n/g, "<br>"));

			text.html(toMarkdownWithEmoji(task.tas_label));
			text.data("text", task.tas_label);
		}

		if (task.tas_finish_datetime) {
			if (task.tas_status == "cancel") {
				taskContainer.addClass("list-group-item-danger");
			}
			else {
				taskContainer.addClass("list-group-item-success");
			}
		}
		else {
			taskContainer.removeClass("list-group-item-success");
			taskContainer.removeClass("list-group-item-danger");
		}

		break;
	}
}

function setAgendaConclusion(id, conclusions) {
	var list = $("#agenda_point ul.objects");
	var conclusionContainer = list.find("li#conclusion-" + id);

	if (!conclusionContainer.length) {
		conclusionContainer = $("li[data-template-id=conclusion]").template("use", {data: {con_id: id}});
		conclusionContainer.find("*").tooltip({placement: "left"});

		list.append(conclusionContainer);

		conclusionContainer.hide().fadeIn(400);
	}

	conclusionContainer.removeClass("to-delete");

	var text = conclusionContainer.find(".conclusion-text");

	for(var index = 0; index < conclusions.length; ++index) {
		var conclusion = conclusions[index];
		if (conclusion.con_id != id) continue;

		if (text.data("text") != conclusion.con_text) {
			text.data("text", conclusion.con_text)
			text.html(toMarkdownWithEmoji(conclusion.con_text));
		}

		break;
	}
}

function setAgendaObject(object, data) {
	if (object.type == "motion") {
		setAgendaMotion(object.id, data.motions);
	}
	else if (object.type == "chat") {
		setAgendaChat(object.id, data.chats);
	}
	else if (object.type == "conclusion") {
		setAgendaConclusion(object.id, data.conclusions);
	}
	else if (object.type == "task") {
		setAgendaTask(object.id, data.tasks);
	}
}

var absoluteRequestId = null;

function _updateAgendaPoint(meetingId, agendaId, absolute) {
	if (!agendaId) return;

	var requestId = new Date();
	requestId = requestId.getTime();

	if (absolute) {
		absoluteRequestId = requestId;
	}

//	$.get("meeting_api.php?method=do_getAgendaPoint", {id: meetingId, pointId: agendaId, requestId: requestId}, function(data) {
	m_getAgendaPoint(meetingId, agendaId, requestId, function(data) {
		if (data.ko) {
			$("#agenda_point").hide();
			return;
		}

		if (absoluteRequestId && data.requestId != absoluteRequestId) return;

		$("#agenda_point").show();
		var previousId = $("#agenda_point").data("id");
		var list = $("#agenda_point ul");

		if (previousId != agendaId) {
			list.children().remove();
			$("#agenda_point").data("id", agendaId);

			// We got back on the top of the objects - better navigation
			$("#agenda_point .objects").get(0).scrollTop = 0;
		}

		var points = $("#meeting-agenda li");
		points.each(function(index) {

			if ($(this).data("id") == agendaId) {
				$(".btn-previous-point").show();
				$(".btn-next-point").show();
				$(".btn-go-down").show();

				if (index == 0) {
					$(".btn-previous-point").addClass("disabled");
				}
				else {
					$(".btn-previous-point").removeClass("disabled");
				}

				if (index == points.length -1) {
					$(".btn-next-point").addClass("disabled");
				}
				else {
					$(".btn-next-point").removeClass("disabled");
				}
			}
		});

		absoluteRequestId = null;

		$("#agenda_point li.motion, #agenda_point li.chat, #agenda_point li.conclusion, #agenda_point li.task").addClass("to-delete");

		setAgendaPoint(data);

		for(var index = 0; index < data.agenda.age_objects.length; ++index) {
			var object = data.agenda.age_objects[index];
			if (object.motionId) {
				object.type = "motion";
				object.id = object.motionId;
			}
			else if (object.chatId) {
				object.type = "chat";
				object.id = object.chatId;
			}
			else if (object.conclusionId) {
				object.type = "conclusion";
				object.id = object.conclusionId;
			}
			else if (object.taskId) {
				object.type = "task";
				object.id = object.taskId;
			}

			setAgendaObject(object, data);
		}

		$("#agenda_point .motion .proposition").each(function() {
			var proposition = $(this);
			var motion = proposition.parents(".motion");

			addVotes(data.votes, proposition, motion);
		});

//		console.log("Call");
		$("#agenda_point .motion").each(function() {
			var motion = $(this);
			computeMotion(motion);
		});
//		console.log("End call");

		$("#agenda_point .panel-footer.action-footer button").removeClass("disabled");
		$("templates *").removeClass("to-delete");
		$("li.to-delete").remove();

		initObject();
		initObject = function() {};
	});
}

function updateAgendaPoint() {
	if (absoluteRequestId) return;

	var meetingId = $(".meeting").data("id");
	var agendaId = $("#agenda_point").data("id");

	_updateAgendaPoint(meetingId, agendaId, false);
}

function showAgendaPoint(event) {
	event.preventDefault();
	event.stopPropagation();

	$("#agenda_point #agenda-members-container").children().remove();

	var meetingId = $(".meeting").data("id");
	var agendaId = $(this).data("id");

	_updateAgendaPoint(meetingId, agendaId, true);
}

function addVotes(votes, proposition, motion) {
	var propositionId = proposition.data("id");

	for(var index = 0; index < votes.length; ++index) {
		var vote = votes[index];

		if (vote.vot_motion_proposition_id != propositionId) continue;

		var voteLi = proposition.find("ul.vote-container li#vote-" + vote.vot_id);
		if (!voteLi.length) {
			voteLi = $("li[data-template-id=vote]").template("use", {data: vote});
			voteLi.find("*").tooltip({placement: "left"});

			proposition.find("ul").append(voteLi);
		}

		voteLi.data("memberId", vote.mem_id);
		voteLi.attr("data-power", vote.vot_power);
		voteLi.data("power", vote.vot_power);

		if (voteLi.find(".nickname").text() != vote.mem_nickname) {
			voteLi.find(".nickname").text(vote.mem_nickname);	
		}

		var motionWinLimit = motion.find(".btn-motion-limits.active").attr("value") - 0;
		
		if (motionWinLimit == -2) {
			for(var jmIndex = 0; jmIndex < majority_judgement_values.length; ++jmIndex) {
				var jmValue = majority_judgement_values[jmIndex];
				if (jmValue == vote.vot_power && vote.vot_power != 0) {
					if (voteLi.find(".power").text() != majority_judgement_translations[jmIndex]) {
						voteLi.find(".power").text(majority_judgement_translations[jmIndex]);
					}
					break;
				}
			}
		}
		else if (motionWinLimit == -3) {
			for(var jmIndex = 0; jmIndex < approval_values.length; ++jmIndex) {
				var jmValue = approval_values[jmIndex];
				if (jmValue == vote.vot_power && vote.vot_power != 0) {
					if (voteLi.find(".power").text() != approval_translations[jmIndex]) {
						voteLi.find(".power").text(approval_translations[jmIndex]);
					}
					break;
				}
			}
		}
		else if (motionWinLimit == -4) {
			for(var jmIndex = 0; jmIndex < maybe_values.length; ++jmIndex) {
				var jmValue = maybe_values[jmIndex];
				if (jmValue == vote.vot_power && vote.vot_power != 0) {
					if (voteLi.find(".power").text() != maybe_translations[jmIndex]) {
						voteLi.find(".power").text(maybe_translations[jmIndex]);
					}
					break;
				}
			}
		}
		else if (voteLi.find(".power").text() != vote.vot_power) {
			voteLi.find(".power").text(vote.vot_power);
		}

		if (vote.mem_id != getUserId() && areVotesAnonymous(motion)) {
			voteLi.hide();
		}
		else  if (vote.vot_power != 0) {
			voteLi.show();
		}
		else {
			voteLi.hide();
		}
	}

//	computeMotion(proposition.parents(".motion"));
}

function retrievePreviousVotes(motion, propositionsHolder) {
	var userId = $(".meeting").data("user-id");
	var votes = motion.find(".vote[data-member-id=" + userId + "]").sort(function(a,b) { return $(b).data("power") - $(a).data("power"); });

	if (!votes.length) return;

	votes.each(function() {
		var proposition = propositionsHolder.find(".proposition[data-proposition-id="+$(this).data("proposition-id")+"]");
		proposition.detach();
		propositionsHolder.append(proposition);
	});
}

function setSchulzeOrderStyle(propositionsHolder) {
	var propositionHolders = propositionsHolder.children();

	var index = 0;
	propositionHolders.each(function() {
		index++;

		$(this).removeClass("btn-success").removeClass("btn-warning").removeClass("btn-danger").removeClass("btn-default");
		$(this).find(".btn-up").removeAttr("disabled");
		$(this).find(".btn-down").removeAttr("disabled");

		if (index == 1) {
			$(this).addClass("btn-success");
			$(this).find(".btn-up").attr("disabled", "disabled");
		}
		else if (index == propositionHolders.length) {
			$(this).addClass("btn-danger");
		}
		else if (index == Math.floor((propositionHolders.length + 1) / 2)) {
			$(this).addClass("btn-warning");
		}
		else {
			$(this).addClass("btn-default");
		}

		if (index == propositionHolders.length) {
			$(this).find(".btn-down").attr("disabled", "disabled");
		}
	});
}

function retrieveJMPreviousVotes(motion, propositionsHolder) {
	var userId = $(".meeting").data("user-id");
	var votes = motion.find(".vote[data-member-id=" + userId + "]").sort(function(a,b) { return $(b).data("power") - $(a).data("power"); });

	votes.each(function() {
		var proposition = propositionsHolder.find(".proposition[data-id="+$(this).data("proposition-id")+"]");

		var votePower = $(this).data("power");

		var jmProposition = proposition.find("*[data-power="+votePower+"]");
		jmProposition.addClass("active");

		proposition.data("power", jmProposition.data("power"));
		proposition.css({background: $(jmProposition).css("background-color")});
	});
	
	if (judgmentVoteIsMandatory) {
		propositionsHolder.find(".proposition").each(function() {
			if ($(this).find(".judgement.active").length > 0) return;
			$(this).find(".judgement").eq(0).click();
		});
	}
}

function vote(event) {
	event.stopPropagation();

	var userId = $(".meeting").data("user-id");
	var motion = $(this).parents(".motion");
	var motionWinLimit = motion.find(".btn-motion-limits.active").attr("value") - 0;
	var proposition = $(this).parents(".proposition");
	var maxPower = getVotingPower(userId);

	var dialog;

	if (motionWinLimit >= 0) {
		motion.find(".proposition .vote[data-member-id="+userId+"]").each(function() {
			if ($(this).data("proposition-id") != proposition.data("id")) {
				maxPower -= $(this).data("power");
			}
		});

		dialog = $("form[data-template-id=vote-form]").template("use", {data: {mpr_label: proposition.find(".proposition-label").text(),
																		vot_power: maxPower}});

		dialog.find("*").tooltip({placement: "left"});

		bootbox.dialog({
	        title: meeting_motionVote2 + " \"" + motion.find(".motion-title").text() + "\"",
	        message: dialog,
	        buttons: {
	            success: {
	                label: meeting_vote,
	                className: "btn-primary",
	                callback: function () {
                		var dialog = $(this);

                		var power = dialog.find(".power").val();
                		if (power > maxPower) return;

						m_vote(motion.data("id"), proposition.data("id"), power, function(data) {
                			if (data.ok) {
                				addVotes([data.vote], proposition, motion);
								if (data.gamifiedUser) testBadges(data.gamifiedUser.data);
								computeMotion(motion);
                			}
                		});
/*
                		$.post("meeting_api.php?method=do_vote", {"motionId": motion.data("id"),
                										"propositionId": proposition.data("id"),
                										"power": power}, function(data) {
                			if (data.ok) {
                				addVotes([data.vote], proposition, motion);
								if (data.gamifiedUser) testBadges(data.gamifiedUser.data);
								computeMotion(motion);
                			}
                		}, "json");
*/                		
                    }
	            },
	            close: {
	                label: common_close,
	                className: "btn-default",
	                callback: function () {
                    }
                }
	        },
	        className: "not-large-dialog"
		});
	}
	else if (motionWinLimit == -2 || motionWinLimit == -3 || motionWinLimit == -4) {

		if (motionWinLimit == -2) {
			dialog = $("form[data-template-id=majority-judgment-form]").template("use", {data: {}});
		}
		else if (motionWinLimit == -3) {
			dialog = $("form[data-template-id=approval-form]").template("use", {data: {}});
		}
		else if (motionWinLimit == -4) {
			dialog = $("form[data-template-id=maybe-form]").template("use", {data: {}});
		}

		var propositions = motion.find(".proposition");
		var propositionsHolder = dialog.find(".propositions");

		propositions.each(function() {
			if (motionWinLimit == -2) {
				var propositionHolder = $("div[data-template-id=judgementProposition]").template("use", {data: {mpr_label: $(this).find(".proposition-label").text(), mpr_id: $(this).data("id")}});
			}
			else if (motionWinLimit == -3) {
				var propositionHolder = $("div[data-template-id=approvalProposition]").template("use", {data: {mpr_label: $(this).find(".proposition-label").text(), mpr_id: $(this).data("id")}});
			}
			else if (motionWinLimit == -4) {
				var propositionHolder = $("div[data-template-id=maybeProposition]").template("use", {data: {mpr_label: $(this).find(".proposition-label").text(), mpr_id: $(this).data("id")}});
			}

			propositionHolder.find(".judgement").click(function() {
				propositionHolder.find(".judgement").removeClass("active");
				$(this).addClass("active");
	
				propositionHolder.data("power", $(this).data("power"));
				propositionHolder.css({background: $(this).css("background-color")});
			});

			propositionsHolder.append(propositionHolder);
		});

		retrieveJMPreviousVotes(motion, propositionsHolder);

		dialog.find("*").tooltip({placement: "left"});

		bootbox.dialog({
	        title: meeting_motionVote2 + " \"" + motion.find(".motion-title").text() + "\"",
	        message: dialog,
	        buttons: {
	            success: {
	                label: meeting_vote,
	                className: "btn-primary",
	                callback: function () {
                		var dialog = $(this);
						var propositionsHolder = dialog.find(".propositions");
						var propositionHolders = propositionsHolder.find(".proposition");

						var index = 0;

						propositionHolders.each(function() {

	                		var proposition = motion.find(".proposition[data-id="+$(this).data("id")+"]");
							var propositionJM = $(this);

/*
							var form = {"motionId": motion.data("id"),
										"propositionId": proposition.data("id"),
										"power": propositionJM.data("power")};
*/
							m_vote(motion.data("id"), proposition.data("id"), propositionJM.data("power"), function(data) {
	                			if (data.ok) {
	                				addVotes([data.vote], proposition, motion);
									if (data.gamifiedUser) testBadges(data.gamifiedUser.data);
									computeMotion(motion);
	                			}
	                		});
/*
	                		$.post("meeting_api.php?method=do_vote", form, function(data) {
	                			if (data.ok) {
	                				addVotes([data.vote], proposition, motion);
									if (data.gamifiedUser) testBadges(data.gamifiedUser.data);
									computeMotion(motion);
	                			}
	                		}, "json");
*/
							++index;

						});
                    }
	            },
	            close: {
	                label: common_close,
	                className: "btn-default",
	                callback: function () {
                    }
                }
	        },
	        className: "not-large-dialog"
		});


	}
	else {
		dialog = $("form[data-template-id=schulze-form]").template("use", {data: {}});
		var propositions = motion.find(".proposition");
		var propositionsHolder = dialog.find(".propositions");

		propositions.each(function() {
			var propositionHolder = $("<div class=\"btn btn-default col-md-12 proposition\" style=\"margin-bottom: 2px; \"><button class='btn btn-up btn-xs pull-left' style='background-color: inherit;'><i class=\"fa fa-arrow-up\" aria-hidden=\"true\"></i></button><span class='proposition-label'></span><button class='btn btn-down btn-xs pull-right' style='background-color: inherit;'><i class=\"fa fa-arrow-down\" aria-hidden=\"true\"></i></button></div>");
			propositionHolder.find(".proposition-label").text($(this).find(".proposition-label").text());
			propositionHolder.attr("data-proposition-id", $(this).data("id"));

			propositionsHolder.append(propositionHolder);
		});

		propositionsHolder.sortable({
			"axis": "y",
			"helper": "clone",
			"sort": function() {
				setSchulzeOrderStyle(propositionsHolder);
			},
			"stop": function() {
				setSchulzeOrderStyle(propositionsHolder);
			}
		});

		propositionsHolder.find(".btn-up").click(function(e) {
			e.preventDefault();
			e.stopImmediatePropagation();

			var proposition = $(this).parent(".proposition");
			var prevProposition = proposition.prev();

			proposition.detach();
			proposition.insertBefore(prevProposition);

			setSchulzeOrderStyle(propositionsHolder);
		});
		propositionsHolder.find(".btn-down").click(function(e) {
			e.preventDefault();
			e.stopImmediatePropagation();

			var prevProposition = $(this).parent(".proposition");
			var proposition = prevProposition.next();

			proposition.detach();
			proposition.insertBefore(prevProposition);

			setSchulzeOrderStyle(propositionsHolder);
		});

		retrievePreviousVotes(motion, propositionsHolder);
		setSchulzeOrderStyle(propositionsHolder);

		dialog.find("*").tooltip({placement: "left"});

		bootbox.dialog({
	        title: meeting_motionVote2 + " \"" + motion.find(".motion-title").text() + "\"",
	        message: dialog,
	        buttons: {
	            success: {
	                label: meeting_vote,
	                className: "btn-primary",
	                callback: function () {
                		var dialog = $(this);
						var propositionsHolder = dialog.find(".propositions");
						var propositionHolders = propositionsHolder.find(".proposition");

						var index = 0;

						propositionHolders.each(function() {

	                		var power = (propositionHolders.length - index) * maxPower;
	                		var proposition = motion.find(".proposition[data-id="+$(this).data("proposition-id")+"]");

							m_vote(motion.data("id"), proposition.data("id"), power, function(data) {
	                			if (data.ok) {
	                				addVotes([data.vote], proposition, motion);
									if (data.gamifiedUser) testBadges(data.gamifiedUser.data);
									computeMotion(motion);
	                			}
	                		});
/*
	                		$.post("meeting_api.php?method=do_vote", {"motionId": motion.data("id"),
	                										"propositionId": proposition.data("id"),
	                										"power": power}, function(data) {
	                			if (data.ok) {
	                				addVotes([data.vote], proposition, motion);
									if (data.gamifiedUser) testBadges(data.gamifiedUser.data);
									computeMotion(motion);
	                			}
	                		}, "json");
*/
							++index;

						});
                    }
	            },
	            close: {
	                label: common_close,
	                className: "btn-default",
	                callback: function () {
                    }
                }
	        },
	        className: "not-large-dialog"
		});
	}

}

function addMotionProposition(event) {
	event.preventDefault();

	var button = $(this);

	var motionId = button.parents(".motion").data("id");

	var meetingId = $(".meeting").data("id");
	var pointId = $("#agenda_point").data("id");

	button.parent().children("button").addClass("disabled");

	if (!hasRight(getUserId(), "handle_motion")) return;

	$.post("meeting_api.php?method=do_addMotionProposition", {meetingId: meetingId, pointId: pointId, motionId: motionId}, function(data) {
	}, "json");
}

function removeMotion(event) {
	event.preventDefault();

	var button = $(this);

	var motionId = button.parents(".motion").data("id");
	var meetingId = $(".meeting").data("id");
	var pointId = $("#agenda_point").data("id");

	var motionTitle = button.parents(".motion").find(".motion-title").text();

	button.parent().children("button").addClass("disabled");

	if (!hasRight(getUserId(), "handle_motion")) return;

	bootbox.setLocale("fr");
	bootbox.confirm(meeting_motionDelete + " \"" + motionTitle + "\" ?", function(result) {
		if (result) {
			$.post("meeting_api.php?method=do_removeMotion", {meetingId: meetingId, pointId: pointId, motionId: motionId}, function(data) {
			}, "json");
		}
	});
}

function changeMotionStatus(event) {
	event.preventDefault();

	var button = $(this);

	var motion = button.parents(".motion");
	var motionId = motion.data("id");
	var meetingId = $(".meeting").data("id");
	var pointId = $("#agenda_point").data("id");

	var status = button.hasClass("btn-do-vote") ? "voting" : "resolved";

	button.parent().children("button").addClass("disabled");

	if (!hasRight(getUserId(), "handle_motion")) return;

	$.post("meeting_api.php?method=do_changeMotionStatus", {meetingId: meetingId, pointId: pointId, motionId: motionId, status: status}, function(data) {
		dumpMotion(motion);
	}, "json");
}

function addTaskConlusion(task, status) {
	var agendaId = $("#agenda_point").data("id");

	if (!agendaId) return;

	let message = "";
	const taskLabel = task.children(".task-label").text();

	if (status == "finish") {
		message = meeting_taskEnd_conclusion.replace("{taskTitle}", taskLabel);
	}
	else if (status == "cancel") {
		message = meeting_taskCancel_conclusion.replace("{taskTitle}", taskLabel);
	}

	if (message) {
		$("#starting-text").val(message);
	}

	addConclusion(false);
}

function addTaskHandlers() {

	$("#tasks-list").on("mouseenter", "li.task", function(event) {
		if (!hasWritingRight(getUserId())) return;

		$(this).find(".btn-link-task").show();

		if ($(this).hasClass("list-group-item-success")) return;
		if ($(this).hasClass("list-group-item-danger")) return;

		$(this).find(".btn-cancel-task").show();
		$(this).find(".btn-finish-task").show();
	});

	$("#tasks-list").on("mouseleave", "li.task", function(event) {
		$(this).find(".btn-cancel-task").hide();
		$(this).find(".btn-finish-task").hide();
		$(this).find(".btn-link-task").hide();
	});

	$("#agenda_point ul.objects").on("mouseenter", "li.task", function(event) {
		if (!hasWritingRight(getUserId())) return;

		$(this).find(".glyphicon-pencil").show();
		$(this).find(".btn-remove-task").show();
	});

	$("#agenda_point ul.objects").on("mouseleave", "li.task", function(event) {
		$(this).find(".glyphicon-pencil").hide();
		$(this).find(".btn-remove-task").hide();
	});

	$("#agenda_point ul.objects").on("click", "li.task .btn-remove-task", function(event) {
		if (!hasWritingRight(getUserId())) return;

		var agendaId = $("#agenda_point").data("id");
		var meetingId = $(".meeting").data("id");
		var task = $(this).parents(".task");
		var taskId = task.data("id");

		bootbox.setLocale("fr");
		bootbox.confirm(meeting_taskDelete + " \"" + task.children(".task-label").text() + "\" ?", function(result) {
			if (result) {
				$.post("meeting_api.php?method=do_removeTask", {
					meetingId: meetingId,
					pointId: agendaId,
					taskId: taskId
				}, function(data) {}, "json");
			}
		});
	});

	$("#agenda_point ul.objects").on("click", "li.task", function(event) {
		if (!hasWritingRight(getUserId())) return;

		if ($(event.target).hasClass("glyphicon")) return;
		if ($(event.target).hasClass("task-select-member")) return;

		if ($(this).find("textarea").length) {
			$(this).find("textarea").focus();
			return;
		}

		var textarea = $("<textarea />", {"style": "width: 100%; max-height: 200px;", "class": "autogrow"});
		var taskText = $(this).find(".task-label");
		var taskId = $(this).data("id");

		textarea.text(taskText.data("text"));
		textarea.blur(function() {
			clearKeyup();
			// update the text into the server
			var newText = textarea.val();

			$.post("meeting_api.php?method=do_changeTask", {taskId: taskId, property: "tas_label", text: newText}, function(data) {
				taskText.data("text", newText);
				taskText.html(toMarkdownWithEmoji(newText));
				taskText.show();
				textarea.remove();
			}, "json");
		});

		taskText.after(textarea);
		taskText.hide();

		textarea.keyup();
		textarea.keyup(function() {
			clearKeyup();
			keyupTimeoutId = setTimeout(function() {
				var newText = textarea.val();

				$.post("meeting_api.php?method=do_changeTask", {taskId: taskId, property: "tas_label", text: newText}, function(data) {
				}, "json");
			}, 1500);
		});

		textarea.focus();
	});

	$("#tasks-list").on("click", "li.task .btn-finish-task", function(event) {
		if (!hasWritingRight(getUserId())) return;

		var button = $(this);
		var task = $(this).parents(".task");
		var taskId = task.data("id");
		var agendaId = task.data("agenda-id");
		var meetingId = task.data("meeting-id");

		bootbox.setLocale("fr");
		bootbox.confirm(meeting_taskEnd + " \"" + task.children(".task-label").text() + "\" ?", function(result) {
			if (result) {
				button.attr("disabled", "disabled");
				$(".ui-tooltip").remove();

				$.post("meeting_api.php?method=do_finishTask", {
					meetingId: meetingId,
					pointId: agendaId,
					taskId: taskId,
					status: "finish"
				}, function(data) {
					task.addClass("list-group-item-success");
					addTaskConlusion(task, "finish");
				}, "json");
			}
		});
	});

	$("#tasks-list").on("click", "li.task .btn-cancel-task", function(event) {
		if (!hasWritingRight(getUserId())) return;

		var button = $(this);
		var task = $(this).parents(".task");
		var taskId = task.data("id");
		var agendaId = task.data("agenda-id");
		var meetingId = task.data("meeting-id");

		bootbox.setLocale("fr");
		bootbox.confirm(meeting_taskCancel + " \"" + task.children(".task-label").text() + "\" ?", function(result) {
			if (result) {
				button.attr("disabled", "disabled");
				$(".ui-tooltip").remove();

				$.post("meeting_api.php?method=do_finishTask", {
					meetingId: meetingId,
					pointId: agendaId,
					taskId: taskId,
					status: "cancel"
				}, function(data) {
					task.addClass("list-group-item-danger");
					addTaskConlusion(task, "cancel");
				}, "json");
			}
		});
	});
}

function addChatHandlers() {
	$("#agenda_point ul.objects").on("mouseenter", "li.chat", function(event) {

		if (Number.isInteger(getUserId())) {
			$(this).find(".btn-thumb-up").show();
			$(this).find(".btn-thumb-middle").show();
			$(this).find(".btn-thumb-down").show();
		}

		if (!hasWritingRight(getUserId())) return;

		$(this).find(".glyphicon-pencil").show();
		$(this).find(".btn-remove-chat").show();
	});

	$("#agenda_point ul.objects").on("mouseleave", "li.chat", function(event) {
		$(this).find(".glyphicon-pencil").hide();
		$(this).find(".btn-remove-chat").hide();
		$(this).find(".btn-thumb-up").hide();
		$(this).find(".btn-thumb-middle").hide();
		$(this).find(".btn-thumb-down").hide();
	});

	$("#agenda_point ul.objects").on("mouseenter", "li.chat .chat-member", function(event) {
		if (!hasWritingRight(getUserId())) return;

		if (!$(this).find(".chat-select-member:visible").length) {
			$(this).find(".update-chat-member-btn").show();
		}
	});

	$("#agenda_point ul.objects").on("mouseleave", "li.chat .chat-member", function(event) {
		$(this).find(".update-chat-member-btn").hide();
	});

	$("#agenda_point ul.objects").on("click", "li.chat .chat-member .cancel-chat-member-btn", function(event) {
		event.preventDefault();
		event.stopImmediatePropagation();

		var chatMemberSpan = $(this).parents(".chat-member");

		chatMemberSpan.find(".update-chat-member-btn").show();
		chatMemberSpan.find(".cancel-chat-member-btn").hide();
		chatMemberSpan.find(".chat-nickname").show();
		chatMemberSpan.find(".avatar").show();
		chatMemberSpan.find(".chat-select-member").hide();
	});

	$("#agenda_point ul.objects").on("click", "li.chat .chat-member .update-chat-member-btn", function(event) {
		event.preventDefault();
		event.stopImmediatePropagation();

		var chatMemberSpan = $(this).parents(".chat-member");

		chatMemberSpan.find(".chat-nickname").hide();
		chatMemberSpan.find(".avatar").hide();
		chatMemberSpan.find(".chat-select-member").show();
		chatMemberSpan.find(".update-chat-member-btn").hide();
		chatMemberSpan.find(".cancel-chat-member-btn").show();
	});

	$("#agenda_point ul.objects").on("change", "li.chat select.chat-select-member", function(event) {
		var chat = $(this).parents(".chat").data("json");
		var userId = $(this).val();

		var form = {chatId: chat.cha_id};
		if (userId.substring(0, 1) == "G") {
			form["property"] = "cha_guest_id";
			form["text"] = userId.substring(1);
		}
		else {
			form["property"] = "cha_member_id";
			form["text"] = userId;
		}

		var chatMemberSpan = $(this).parents(".chat-member");

		chatMemberSpan.find(".update-chat-member-btn").show();
		chatMemberSpan.find(".cancel-chat-member-btn").hide();
		chatMemberSpan.find(".chat-nickname").show();
		chatMemberSpan.find(".avatar").show();
		chatMemberSpan.find(".chat-select-member").hide();

		$.post("meeting_api.php?method=do_changeChat", form, function(data) {
		}, "json");
	});

	$("#agenda_point ul.objects").on("click", "li.chat .btn-remove-chat", function(event) {
		if (!hasWritingRight(getUserId())) return;

		var agendaId = $("#agenda_point").data("id");
		var meetingId = $(".meeting").data("id");
		var chat = $(this).parents(".chat");
		var chatId = chat.data("id");

		bootbox.setLocale("fr");
		bootbox.confirm(meeting_chatDelete + " \"" + chat.children(".chat-text").text() + "\" ?", function(result) {
			if (result) {
				$.post("meeting_api.php?method=do_removeChat", {
					meetingId: meetingId,
					pointId: agendaId,
					chatId: chatId
				}, function(data) {}, "json");
			}
		});
	});

	$("#agenda_point ul.objects").on("click", "li.chat", function(event) {
		if ($(event.target).hasClass("glyphicon")) return;
		if ($(event.target).hasClass("chat-select-member")) return;

		if ($(this).find("textarea").length) {
			$(this).find("textarea").focus();
			return;
		}

		var textarea = $("<textarea />", {"style": "width: 100%; max-height: 200px;", "class": "autogrow"});
		var chatText = $(this).find(".chat-text");
		var chatId = $(this).data("id");

		textarea.text(chatText.data("text"));
		textarea.blur(function() {
			clearKeyup();
			// update the text into the server
			var newText = textarea.val();

			$.post("meeting_api.php?method=do_changeChat", {chatId: chatId, property: "cha_text", text: newText}, function(data) {
				chatText.data("text", newText);
				chatText.html(newText.replace(/\n/g, "<br>"));
				chatText.show();
				textarea.remove();
			}, "json");
		});

		chatText.after(textarea);
		chatText.hide();

		textarea.keyup();
		textarea.keyup(function() {
			clearKeyup();
			keyupTimeoutId = setTimeout(function() {
				var newText = textarea.val();

				$.post("meeting_api.php?method=do_changeChat", {chatId: chatId, property: "cha_text", text: newText}, function(data) {
				}, "json");
			}, 1500);
		});

		textarea.focus();
	});
}

function addConclusionHandlers() {
	$("#agenda_point ul.objects").on("mouseenter", "li.conclusion", function(event) {
		if (!hasRight(getUserId(), "handle_conclusion")) return;

		$(this).find(".glyphicon-pencil").show();
		$(this).find(".btn-remove-conclusion").show();
	});

	$("#agenda_point ul.objects").on("mouseleave", "li.conclusion", function(event) {
		$(this).find(".glyphicon-pencil").hide();
		$(this).find(".btn-remove-conclusion").hide();
	});

	$("#agenda_point ul.objects").on("click", "li.conclusion .btn-remove-conclusion", function(event) {
		if (!hasRight(getUserId(), "handle_conclusion")) return;

		var agendaId = $("#agenda_point").data("id");
		var meetingId = $(".meeting").data("id");
		var conclusion = $(this).parents(".conclusion");
		var conclusionId = conclusion.data("id");

		bootbox.setLocale("fr");
		bootbox.confirm(meeting_conclusionDelete + " \"" + conclusion.children(".conclusion-text").text() + "\" ?", function(result) {
			if (result) {
				$.post("meeting_api.php?method=do_removeConclusion", {
					meetingId: meetingId,
					pointId: agendaId,
					conclusionId: conclusionId
				}, function(data) {}, "json");
			}
		});
	});

	$("#agenda_point ul.objects").on("click", "li.conclusion", function(event) {
		if ($(event.target).hasClass("glyphicon")) return;

		if ($(this).find("textarea").length) {
			$(this).find("textarea").focus();
			return;
		}

		var textarea = $("<textarea />", {"style": "width: 100%; max-height: 200px;", "class": "autogrow"});
		var conclusionText = $(this).find(".conclusion-text");
		var conclusionId = $(this).data("id");

		textarea.text(conclusionText.data("text"));
		textarea.blur(function() {
			clearKeyup();
			// update the text into the server
			var newText = textarea.val();

			$.post("meeting_api.php?method=do_changeConclusion", {conclusionId: conclusionId, text: newText}, function(data) {
				conclusionText.html(toMarkdownWithEmoji(newText));
				conclusionText.data("text", newText);
				conclusionText.show();
				textarea.remove();
			}, "json");
		});

		conclusionText.after(textarea);
		conclusionText.hide();

		textarea.keyup();
		textarea.keyup(function() {
			clearKeyup();
			keyupTimeoutId = setTimeout(function() {
				var newText = textarea.val();

				$.post("meeting_api.php?method=do_changeConclusion", {conclusionId: conclusionId, text: newText}, function(data) {
				}, "json");
			}, 1500);
		});

		textarea.focus();
	});
}

function updateTasks() {
	var meetingId = $(".meeting").data("id");

	$.post("meeting_api.php?method=do_getTasks", {meetingId : meetingId}, function(data) {
		if (data.ok) {
			if (data.tasks.length) {
				$("#tasks .tasks-counter").show();
			}
			else {
				$("#tasks .tasks-counter").hide();
			}

			$("#tasks-list li").addClass("to-remove");

			for(var index = 0; index < data.tasks.length; ++index) {
				var task = data.tasks[index];

				var taskLi = $("#tasks-list li[data-id=" + task.tas_id + "]");
				if (taskLi.length == 0) {
					taskLi = $("li[data-template-id=old-task]").template("use", {data: {tas_id: task.tas_id, tas_agenda_id: task.tas_agenda_id, tas_meeting_id: task.tas_meeting_id}});

					taskLi.find("*").tooltip({placement: "right"});
					$("#tasks-list").append(taskLi);
				}

				if (task.tas_informations) {
					taskLi.children(".task-information").remove();
					
					for(var jndex = 0; jndex < task.tas_informations.length; ++jndex) {
						const information = task.tas_informations[jndex].information;
						
						const taskInformationDiv = $("div[data-template-id=task-information]").template("use", {data: information});
						taskLi.append(taskInformationDiv);
					}
				}


				taskLi.removeClass("to-remove");

				taskLi.find(".task-label").html(toMarkdownWithEmoji(task.tas_label));
			}

			$("#tasks-list li.to-remove").remove();

			$("#tasks .tasks-counter").text($("#tasks-list > li").length);
		}
	}, "json");
}

//function addVideoHandlers() {
//	$("body").on("click", "#videoDock .reductor", function() {
////		if ($("#videoDock .dock").height() > 0) {
////			$("#videoDock .dock").height(0);
////			$("#videoDock .reductor").css({cursor: "s-resize"});
////		}
////		else {
////			$("#videoDock .dock").height(120);
////			$("#videoDock .reductor").css({cursor: "n-resize"});
////		}
//
//		$("#videoDock .dock").animate({height: "toggle"}, 400, function() {
//			if (!$("#videoDock .dock").is(":visible")) {
//				$("#videoDock .reductor").css({cursor: "s-resize"});
//			}
//			else {
//				$("#videoDock .reductor").css({cursor: "n-resize"});
//			}
//		});
//	});
//}

function testMeetingReady() {
	if (!isPeopleReady) return;
	if (!isAgendaReady) return;

	$("#start-meeting-modal").modal("hide");
}

function showSetMotionDeadlineModal() {
	$("#set-motion-deadline-modal input").val("");

	const motionContainer = $(this).parents(".motion");

	const motionId = motionContainer.data("id");
	const agendaId = $("#agenda_point").data("id");

	const deadline = motionContainer.data("deadline");
	if (deadline) {
		const deadlineParts = deadline.split(" ");
		$("#set-motion-deadline-modal input#motion-deadline-date").val(deadlineParts[0]);
		$("#set-motion-deadline-modal input#motion-deadline-time").val(deadlineParts[1]);
	}

	$("#set-motion-deadline-modal input[name=motionId]").val(motionId);
	$("#set-motion-deadline-modal input[name=agendaId]").val(agendaId);

	$("#set-motion-deadline-modal button.btn-set-motion-deadline").removeAttr("disabled");
	$("#set-motion-deadline-modal").modal("show");
}

function saveMotionDeadline() {
	$("#set-motion-deadline-modal button.btn-set-motion-deadline").attr("disabled", "disabled");

	const form = $("#set-motion-deadline-modal form");
	let deadline = $("#set-motion-deadline-modal input#motion-deadline-date").val();
	deadline += " " + $("#set-motion-deadline-modal input#motion-deadline-time").val();
	const myForm = {motionId: $("#set-motion-deadline-modal input[name=motionId]").val(), propositionId: 0, property: "mot_deadline", text: deadline};

	$.post("meeting_api.php?method=do_changeMotionProperty", myForm, function(data) {
		$("#set-motion-deadline-modal").modal("hide");
	}, "json");
}

$(function() {
	$("#set-motion-deadline-modal").on("click", "button.btn-set-motion-deadline", saveMotionDeadline);
	$("#agenda_point").on("click", "button.btn-set-deadline", showSetMotionDeadlineModal);
	
	$("#agenda_point").on("click", ".motion button.btn-vote", vote);
	$("#agenda_point").on("click", "button.btn-add-speaker-chat", addSpeakerChat);
	$("#agenda_point").on("click", "button.btn-add-chat", addOwnChat);
	$("#agenda_point").on("click", "button.btn-add-task", addOwnTask);
	$("#agenda_point").on("click", "button.btn-add-conclusion", addConclusion);

	$("#agenda_point").on("click", "a.lnk-add-motion", addMotion);
	$("#agenda_point").on("click", "a.lnk-add-yes-no-motion", addYesNoMotion);

	$("#agenda_point").on("click", "button.btn-advice", setAdvice);

	$("#agenda_point ul.objects").on("click", ".btn-do-vote, .btn-do-close", changeMotionStatus);
	$("#agenda_point ul.objects").on("click", ".btn-remove-motion", removeMotion);
	$("#agenda_point ul.objects").on("click", ".btn-add-proposition", addMotionProposition);

	addChatHandlers();
	addTaskHandlers();
	addConclusionHandlers();
	addAgendaPointHandlers();

//	addVideoHandlers();

	var getTasksTimer = $.timer(updateTasks);
	getTasksTimer.set({ time : 60000, autostart : true });

	updateTasks();
	updateAgendaPoint();


	$(".resizable").resizable({
		animate: true
	});

});

// Framatalk

function setFramatalkPosition(position) {

	var leftSpace = $(".breadcrumb").offset();
	var videoWidth = leftSpace.left - 20;
	var videoTop = leftSpace.top;

	var css = {"top": videoTop + "px", "width": videoWidth + "px"};

	if (position == "left") {
		css["height"] = videoWidth + "px";
	}

	$("#framatalk").css(css);

}

function meetingAutogrowEvent() {
	parent = $(window);
	if ($(this).parents(".list-group objects").length) {
		parent = $(this).parents(".list-group objects").get(0);
	}

	var maxHeight = parent.height() - 50;
	var currentScroll = this.scrollTop;

	$(this).css({"height": "auto"});

	var currentHeight = $(this).height();
	var currentContentHeight = this.scrollHeight;

	var offset= 0;

	if ($(this).hasClass("grown")) {
			offset = 2;
	}
	else {
			offset = 4;
		$(this).addClass("grown")
	}

//		if (this.tagName == "TEXTAREA") {
	currentHeight += offset;
	currentContentHeight += offset;
//		}

	if (currentContentHeight >= currentHeight) {
		var newHeight = Math.min(currentContentHeight, maxHeight);
		$(this).height(newHeight);
	}

	this.scrollTop = currentScroll;
}

function addEmojiHelper() {
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

	var converter = new showdown.Converter({tables: true, strikethrough: true});
	source = converter.makeHtml(source);

	return source;
}

function addMeetingLabelHandlers() {
	$(".breadcrumb .active input").hide();

	$(".breadcrumb .active").hover(function() {
		if (hasWritingRight($(".meeting").data("user-id")) && $(this).find(".read-data").is(":visible")) {
			$(this).find(".update-btn").show();
		}
	}, function() {
		$(this).find(".update-btn").hide();
	});

	$(".breadcrumb .active").find(".update-btn").click(function() {
		var text = $(this).parents(".breadcrumb .active").find(".read-data").text();
		$(this).parents(".breadcrumb .active").find("input").val(text);

		$(this).parents(".breadcrumb .active").find(".read-data").hide();
		$(this).parents(".breadcrumb .active").find(".update-btn").hide();
		$(this).parents(".breadcrumb .active").find("input").show();
		$(this).parents(".breadcrumb .active").find(".save-btn").show();
		$(this).parents(".breadcrumb .active").find(".cancel-btn").show();
	});

	$(".breadcrumb .active").find(".cancel-btn").click(function() {
		$(this).parents(".breadcrumb .active").find("input").hide();
		$(this).parents(".breadcrumb .active").find(".read-data").show();
		$(this).parents(".breadcrumb .active").find(".save-btn").hide();
		$(this).parents(".breadcrumb .active").find(".cancel-btn").hide();
		$(this).parents(".breadcrumb .active").find(".update-btn").show();
	});

	$(".breadcrumb .active").find(".save-btn").click(function() {
		var text = $(this).parents(".breadcrumb .active").find("input").val();
		$(this).parents(".breadcrumb .active").find(".read-data").text(text);

		$(this).parents(".breadcrumb .active").find("input").hide();
		$(this).parents(".breadcrumb .active").find(".read-data").show();
		$(this).parents(".breadcrumb .active").find(".save-btn").hide();
		$(this).parents(".breadcrumb .active").find(".cancel-btn").hide();
		$(this).parents(".breadcrumb .active").find(".update-btn").show();

		var meetingId = $(".meeting").data("id");

		$.post("meeting_api.php?method=do_changeMeeting", {meetingId: meetingId, property: "mee_label", text: text},
				function(data) {}, "json");

	});
}

function addStartingTextHandler() {
	
	var startingKeyupTimeoutId = null;

	function clearStartingKeyup() {
		if (startingKeyupTimeoutId) {
			clearTimeout(startingKeyupTimeoutId);
			startingKeyupTimeoutId = null;
		}
	}

	$("#starting-text").keyup(function() {
		var meetingId = $(".meeting").data("id");
		var agendaId = $("#agenda_point").data("id");

		var startingText = $(this).val();

		if (startingKeyupTimeoutId) {
			// reset the timeout
			clearStartingKeyup();
		}
		else {
			// set en event, starting typing
			var event = {meetingId: meetingId, event: "user_start_typing", pointId: agendaId};
			console.log(event);
			$.post("meeting_api.php?method=do_addEvent", event, function(data) {}, "json");
		}

		startingKeyupTimeoutId = setTimeout(function() {
			// set en event, stop typing
			var event = {meetingId: meetingId, event: "user_stop_typing", pointId: agendaId};
			console.log(event);
			$.post("meeting_api.php?method=do_addEvent", event, function(data) {}, "json");
			clearStartingKeyup();
		}, 30000);

		if (startingText) {
			$("#starting-text-buttons button, #starting-text-buttons .btn-add-motion").removeAttr("disabled").removeClass("disabled");
		}
		else {
			$("#starting-text-buttons button, #starting-text-buttons .btn-add-motion").attr("disabled", "disabled").addClass("disabled");
			// set en event, stop typing
			var event = {meetingId: meetingId, event: "user_stop_typing", pointId: agendaId};
			console.log(event);
			$.post("meeting_api.php?method=do_addEvent", event, function(data) {}, "json");
			clearStartingKeyup();
		}
	});

	$("#starting-text").keyup();
}

function addMeetingInfoPanelsHandler() {
	$(".show-info-panels-btn").click(function() {
		if ($(this).hasClass("active")) {
			$(this).removeClass("active");
			$("#meeting-info-panels").hide(200);
		}
		else {
			$(this).addClass("active");
			$("#meeting-info-panels").show(200);
		}
	});
}

function addMeetingSpeekingPanelsHandler() {
	$(".show-speaking-btn").click(function() {
		if ($(this).hasClass("active")) {
			$(this).removeClass("active");
			$(".president-panels").hide(200);
		}
		else {
			$(this).addClass("active");
			$(".president-panels").show(200);
		}
	});
}

function addMeetingQuorumHanbdlers() {
	$(".show-quorum-modal-btn").click(function() {
		if (hasWritingRight($(".meeting").data("user-id"))) {
			
			$("#set-quorum-modal").one("shown.bs.modal", function() {
				$("#quorum-formula-area").keyup();
			});

			$("#set-quorum-modal").modal("show");
		}
	});

	$(".btn-set-quorum").click(function() {
		let form = {meetingId: $("#set-quorum-modal form input[name=meetingId]").val(), text: $("#set-quorum-modal form #quorum-formula-area").val(), property: "mee_quorum"};
		$.post("meeting_api.php?method=do_changeMeeting", form, function(data) {
			$("#set-quorum-modal").modal("hide");
		}, "json");
	});

	$(".quorum-container").hover(function() {
		if (hasWritingRight($(".meeting").data("user-id"))) {
			$(this).find(".update-btn").show();
		}
	}, function() {
		$(this).find(".update-btn").hide();
	});
	
	$(".quorum-keyword").click(function(event) {
		event.preventDefault();
		$("#quorum-formula-area").val($("#quorum-formula-area").val() + $(this).html());
	});
}

function addSynchroHandlers() {
	$(".synchro-vote").hover(function() {
		if (hasWritingRight($(".meeting").data("user-id")) && $(this).find(".read-data").is(":visible")) {
			$(this).find(".update-btn").show();
		}
	}, function() {
		$(this).find(".update-btn").hide();
	});

	$(".synchro-vote").find(".update-btn").click(function() {
		$(this).parents(".synchro-vote").find(".read-data").hide();
		$(this).parents(".synchro-vote").find(".update-btn").hide();
		$(this).parents(".synchro-vote").find("select").show();
//		$(this).parents(".synchro-vote").find(".save-btn").show();
		$(this).parents(".synchro-vote").find(".cancel-btn").show();
	});
	$(".synchro-vote").find(".cancel-btn").click(function() {
		$(this).parents(".synchro-vote").find("select").hide();
//		$(this).parents(".breadcrumb .active").find(".read-data").show();
//		$(this).parents(".breadcrumb .active").find(".save-btn").hide();
		$(this).parents(".synchro-vote").find(".cancel-btn").hide();
		$(this).parents(".synchro-vote").find(".update-btn").show();
	});
	$(".synchro-vote").find("select").change(function() {
		var meetingId = $(".meeting").data("id");
		var text = $(".synchro-vote").find("select option:selected").val();
		$(".synchro-vote").find("select").hide();
		$(this).parents(".synchro-vote").find(".cancel-btn").hide();
		$(this).parents(".synchro-vote").find(".update-btn").show();

		$.post("meeting_api.php?method=do_changeMeeting", {meetingId: meetingId, property: "mee_synchro_vote", text: text},
				function(data) {}, "json");
	})
}

$(function() {
	setFramatalkPosition("left");
	addEmojiHelper();
	
	addMeetingLabelHandlers();
	
	addStartingTextHandler();
	addMeetingInfoPanelsHandler();
	addMeetingSpeekingPanelsHandler();
	addMeetingQuorumHanbdlers();
	addSynchroHandlers();

/*	
	$("body").on("keyup", "textarea.autogrow, div.autogrow", meetingAutogrowEvent);
	$("body").on("focus", "textarea.autogrow, div.autogrow", meetingAutogrowEvent);
*/	
});

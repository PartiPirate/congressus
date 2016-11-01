/*
	Copyright 2015 Cédric Levieux, Parti Pirate

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

function hasVotingRight(id) {
	return $("#noticed-people .members li#member-"+id+" .voting.can-vote").length > 0;
}

function getVotingPower(id) {
	return $("#noticed-people .members li#member-"+id+" .power:visible").text();
}

function hasRight(userId, right) {
	var meeting = $(".meeting").data("json");

	var has = false;

	has |= $(".mee_president_member_id").data("id") == userId;
	has |= $(".mee_secretary_member_id").data("id") == userId;
	
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
	hasRight = false;
	hasRight |= $(".mee_president_member_id").data("id") == id;

	return hasRight;
}

function addAgendaPointHandlers() {
	$("#agenda_point .objects").on("mouseenter", "li#description", function(event) {
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
		if ($(this).find(".description-editor+div:visible").length) {
			$(this).find(".description-editor+div .Editor-editor").focus();
			return;
		}

		var descriptionText = $(this).find("p");

		$(this).find(".description-editor").Editor("setText", descriptionText.html());

		descriptionText.hide();

		$(this).find(".description-editor+div").show();
		$(this).find(".description-editor+div .Editor-editor").focus();
	});
}

function editorBlurHandler(event) {
	var description = $("li#description");
	var editor = description.find(".description-editor+div");
	
	if (editor.length == 0) return;
	if (!editor.is(":visible")) return;

	var agendaId = $("#agenda_point").data("id");
	var meetingId = $(".meeting").data("id");
	
	var descriptionEditor = description.find("div.description-editor");
	
	clearKeyup();
	// update the text into the server
	var newText = descriptionEditor.Editor("getText");

	$.post("meeting/do_changeAgendaPoint.php", {meetingId: meetingId, pointId: agendaId, property: "age_description", text: newText}, function(data) {
		description.find("p").html(newText);
		description.find("p").show();

		editor.hide();
	}, "json");
}

function getDescriptionLi(list) {
	var description = list.find("li#description");

	if (!description.length) {
		description = $("<li />", {id: "description", "class": "list-group-item"});

		description.append($("<p />"));
		description.append($("<div />", {"class": "description-editor"}));
		description.append($("<span />", {"class": "glyphicon glyphicon-pencil", style: "display: none;"}));

		var descriptionEditor = description.find("div.description-editor");
		descriptionEditor.Editor();

		var editor = description.find(".description-editor+div");
		editor.hide();

		list.append(description);

		description.hide().fadeIn(400);

		var agendaId = $("#agenda_point").data("id");
		var meetingId = $(".meeting").data("id");

		editor.find("*[contenteditable=true]").keyup(function() {
			clearKeyup();
			keyupTimeoutId = setTimeout(function() {
				var newText = descriptionEditor.Editor("getText");

				$.post("meeting/do_changeAgendaPoint.php", {meetingId: meetingId, pointId: agendaId, property: "age_description", text: newText}, function(data) {
				}, "json");
			}, 1500);
		});
	}

	return description;
}

function updateDescription(description, agenda) {
	var descriptionP = description.children("p");

	if (descriptionP.html() != $("<div />").html(agenda.age_description).html()) {
		descriptionP.html(agenda.age_description);
	}
}

function setAgendaPoint(point) {
	var list = $("#agenda_point ul");
	
	if ($("#agenda_point .agenda-label").text() != point.agenda.age_label) { 
		$("#agenda_point .agenda-label").text(point.agenda.age_label);
	}

	var description = getDescriptionLi(list);
	updateDescription(description, point.agenda);
}

function setAgendaMotion(id, motions) {
	var userId = $(".meeting").data("user-id");
	var list = $("#agenda_point ul.objects");
	var motionContainer = list.find("li#motion-" + id);
	var motionActions = motionContainer.find(".motion-actions");

	if (!motionContainer.length) {
		motionContainer = $("li[data-template-id=motion]").template("use", {data: {mot_id: id}});
		motionContainer.find(".motion-actions *").tooltip({placement: "top"});
		motionContainer.find("*").tooltip({placement: "bottom"});
		list.append(motionContainer);

		motionContainer.hide().fadeIn(400);
	}

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
			motionContainer.data("anonymous", motion.mot_anonymous);
			motionContainer.data("win-limit", motion.mot_win_limit);

			switch(motion.mot_status) {
				case "construction":
					motionActions.find(".btn-add-proposition").show();
					motionActions.find(".btn-do-vote").show();
					motionActions.find(".btn-remove-motion").show();

					motionActions.find(".btn-motion-limits").show();
					motionActions.find(".btn-motion-limits").removeClass("active").removeClass("disabled");
					motionActions.find(".btn-motion-anonymous").show();
					break;
				case "voting":
					motionActions.find(".btn-do-close").show();
					motionActions.find(".btn-remove-motion").show();
					motionActions.find(".btn-motion-limits").addClass("disabled");
					motionActions.find(".voters").show();
					motionActions.find(".btn-motion-anonymous").show();
					break;
				case "resolved":
					motionActions.find(".btn-motion-limits").addClass("disabled");
					motionActions.find(".voters").show();
					motionActions.find(".btn-motion-anonymous").hide();
					break;
				default:
			}

			
			motionActions.find(".btn-motion-limits.btn-motion-limit-" + motion.mot_win_limit).addClass("active").show();
			
			if (motion.mot_anonymous) {
				motionActions.find(".btn-motion-anonymous").addClass("active");
			}
			else {
				motionActions.find(".btn-motion-anonymous").removeClass("active");
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

		if (hasVotingRight(userId) && motion.mot_status == "voting") {
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
}

function setAdvice() {
	var userId = $(".meeting").data("user-id");
	var agendaId = $("#agenda_point").data("id");
	var meetingId = $(".meeting").data("id");
	
	var button = $(this);
	
	var chatId = button.data("chat-id");
	var advice = button.data("advice");

	$.post("meeting_api.php?method=do_setAdvice", {meetingId : meetingId, agendaId: agendaId, chatId: chatId, advice: advice}, function(data) {
		if (data.ok) {
		}
	}, "json");

}

function addOwnChat() {
	var userId = $(".meeting").data("user-id");
	var agendaId = $("#agenda_point").data("id");
	var meetingId = $(".meeting").data("id");

	$.get("meeting/do_addChat.php", {id: meetingId, pointId: agendaId, userId: userId}, function(data) {
		setAgendaChat(data.chat.cha_id, [data.chat]);
		$("#agenda_point ul.objects li.chat#chat-" + data.chat.cha_id).click();
	}, "json");
}

function addConclusion() {
	var agendaId = $("#agenda_point").data("id");
	var meetingId = $(".meeting").data("id");

	$.get("meeting/do_addConclusion.php", {id: meetingId, pointId: agendaId}, function(data) {
		setAgendaConclusion(data.conclusion.con_id, [data.conclusion]);
		$("#agenda_point ul.objects li.conclusion#conclusion-" + data.conclusion.con_id).click();
	}, "json");
}

function addMotion(event) {
	var agendaId = $("#agenda_point").data("id");
	var meetingId = $(".meeting").data("id");

	$.get("meeting/do_addMotion.php", {meetingId: meetingId, pointId: agendaId}, function(data) {
		setAgendaMotion(data.motion.mot_id, [data.motion]);
		$("#agenda_point ul.objects li.motion#motion-" + data.motion.mot_id + " h4").click();
	}, "json");
}

function setAgendaChat(id, chats) {
	var list = $("#agenda_point ul.objects");
	var chatContainer = list.find("li#chat-" + id);

	if (!chatContainer.length) {
		chatContainer = $("li[data-template-id=chat]").template("use", {data: {cha_id: id}});
		chatContainer.find("*").tooltip({placement: "left"});
		list.append(chatContainer);

		chatContainer.hide().fadeIn(400);
	}

	chatContainer.removeClass("to-delete");

	var nickname = chatContainer.find(".chat-nickname");
	var text = chatContainer.find(".chat-text");

	for(var index = 0; index < chats.length; ++index) {
		var chat = chats[index];
		if (chat.cha_id != id) continue;

		if (nickname.text() != chat.mem_nickname) {
			nickname.text(chat.mem_nickname);
		}
		
		if (text.text() != chat.cha_text) {
			text.text(chat.cha_text);
		}

		var advices = {};

		advices["thumb_up"] = 0;
		advices["thumb_down"] = 0;
		advices["total"] = 0;
		
		for(var jndex = 0; jndex < chat.advices.length; ++jndex) {
			var advice = chat.advices[jndex];
			
			advices[advice.cad_advice]++;
			advices["total"]++;

			if (advice.cad_user_id == getUserId()) {
				chatContainer.find(".btn-advice").each(function() {
					if ($(this).data("advice") == advice.cad_advice) {
						$(this).addClass("disabled").prop("disabled", true);
					}
					else {
						$(this).removeClass("disabled").prop("disabled", false);
					}
				});
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
				$(this).attr("title", advices[$(this).data("advice")]);
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

		if (text.text() != task.tas_label) {
			text.text(task.tas_label);
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

		if (text.text() != conclusion.con_text) {
			text.text(conclusion.con_text);
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

	$.get("meeting/do_getAgendaPoint.php", {id: meetingId, pointId: agendaId, requestId: requestId}, function(data) {

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
				if (index == 0) {
					$(".btn-previous-point").hide();
				}
				else {
					$(".btn-previous-point").show();
				}

				if (index == points.length -1) {
					$(".btn-next-point").hide();
				}
				else {
					$(".btn-next-point").show();
				}
			}
		});

		absoluteRequestId = null;

		$("li.motion,li.chat,li.conclusion").addClass("to-delete");

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

		$("#agenda_point .panel-footer button").removeClass("disabled");
		$("templates *").removeClass("to-delete");
		$("li.to-delete").remove();

		initObject();
		initObject = function() {};
	}, "json");
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

		voteLi.find(".nickname").text(vote.mem_nickname);
		voteLi.data("memberId", vote.mem_id);
		voteLi.find(".power").text(vote.vot_power);
		
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

	computeMotion(proposition.parents(".motion"));
}

function vote(event) {
	event.stopPropagation();

	var userId = $(".meeting").data("user-id");
	var motion = $(this).parents(".motion");
	var proposition = $(this).parents(".proposition");
	var maxPower = getVotingPower(userId);

	motion.find(".proposition .vote[data-member-id="+userId+"]").each(function() {
		if ($(this).data("proposition-id") != proposition.data("id")) {
			maxPower -= $(this).find(".power").text();
		}
	});

	var dialog = $("form[data-template-id=vote-form]").template("use", {data: {mpr_label: proposition.find(".proposition-label").text(),
																		vot_power: maxPower}});
	dialog.find("*").tooltip({placement: "left"});

	bootbox.dialog({
        title: "Voter la motion \"" + motion.find(".motion-title").text() + "\"",
        message: dialog,
        buttons: {
            success: {
                label: "Voter",
                className: "btn-primary",
                callback: function () {
                		var dialog = $(this);

                		var power = dialog.find(".power").val();

                		$.post("meeting/do_vote.php", {"motionId": motion.data("id"),
                										"propositionId": proposition.data("id"),
                										"power": power}, function(data) {
                			if (data.ok) {
                				addVotes([data.vote], proposition, motion);
                			}
                		}, "json");
                    }
                },
            close: {
                label: "Fermer",
                className: "btn-default",
                callback: function () {

                    }
                }
        },
        className: "not-large-dialog"
	});
}

function addMotionProposition(event) {
	event.preventDefault();

	var button = $(this);

	var motionId = button.parents(".motion").data("id");

	var meetingId = $(".meeting").data("id");
	var pointId = $("#agenda_point").data("id");

	button.parent().children("button").addClass("disabled");

	if (!hasRight(getUserId(), "handle_motion")) return;

	$.post("meeting/do_addMotionProposition.php", {meetingId: meetingId, pointId: pointId, motionId: motionId}, function(data) {
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
	bootbox.confirm("Supprimer la motion \"" + motionTitle + "\" ?", function(result) {
		if (result) {
			$.post("meeting/do_removeMotion.php", {meetingId: meetingId, pointId: pointId, motionId: motionId}, function(data) {
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

	$.post("meeting/do_changeMotionStatus.php", {meetingId: meetingId, pointId: pointId, motionId: motionId, status: status}, function(data) {
		dumpMotion(motion);
	}, "json");
}

function addChatHandlers() {
	$("#agenda_point ul.objects").on("mouseenter", "li.chat", function(event) {
		
		if (Number.isInteger(getUserId())) {
			$(this).find(".btn-thumb-up").show();
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
		$(this).find(".btn-thumb-down").hide();
	});

	$("#agenda_point ul.objects").on("mouseenter", "li.chat .chat-member", function(event) {
		if (!hasWritingRight(getUserId())) return;

		$(this).find(".chat-select-member").show();
		$(this).find(".chat-nickname").hide();
	});

	$("#agenda_point ul.objects").on("mouseleave", "li.chat .chat-member", function(event) {
		$(this).find(".chat-nickname").show();
		$(this).find(".chat-select-member").hide();
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

		$.post("meeting/do_changeChat.php", form, function(data) {
		}, "json");
	});

	$("#agenda_point ul.objects").on("click", "li.chat .btn-remove-chat", function(event) {
		if (!hasWritingRight(getUserId())) return;

		var agendaId = $("#agenda_point").data("id");
		var meetingId = $(".meeting").data("id");
		var chat = $(this).parents(".chat");
		var chatId = chat.data("id");

		bootbox.setLocale("fr");
		bootbox.confirm("Supprimer le chat \"" + chat.children(".chat-text").text() + "\" ?", function(result) {
			if (result) {
				$.post("meeting/do_removeChat.php", {
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

		var textarea = $("<textarea />", {"style": "width: 100%;"});
		var chatText = $(this).find(".chat-text");
		var chatId = $(this).data("id");

		textarea.text(chatText.text());
		textarea.blur(function() {
			clearKeyup();
			// update the text into the server
			var newText = textarea.val();

			$.post("meeting/do_changeChat.php", {chatId: chatId, property: "cha_text", text: newText}, function(data) {
				chatText.text(newText);
				chatText.show();
				textarea.remove();
			}, "json");
		});

		textarea.keyup(function() {
			clearKeyup();
			keyupTimeoutId = setTimeout(function() {
				var newText = textarea.val();

				$.post("meeting/do_changeChat.php", {chatId: chatId, property: "cha_text", text: newText}, function(data) {
				}, "json");
			}, 1500);
		});

		chatText.after(textarea);
		chatText.hide();

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
		bootbox.confirm("Supprimer la conclusion \"" + conclusion.children(".conclusion-text").text() + "\" ?", function(result) {
			if (result) {
				$.post("meeting/do_removeConclusion.php", {
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

		var textarea = $("<textarea />", {"style": "width: 100%;"});
		var conclusionText = $(this).find(".conclusion-text");
		var conclusionId = $(this).data("id");

		textarea.text(conclusionText.text());
		textarea.blur(function() {
			clearKeyup();
			// update the text into the server
			var newText = textarea.val();

			$.post("meeting/do_changeConclusion.php", {conclusionId: conclusionId, text: newText}, function(data) {
				conclusionText.text(newText);
				conclusionText.show();
				textarea.remove();
			}, "json");
		});

		textarea.keyup(function() {
			clearKeyup();
			keyupTimeoutId = setTimeout(function() {
				var newText = textarea.val();

				$.post("meeting/do_changeConclusion.php", {conclusionId: conclusionId, text: newText}, function(data) {
				}, "json");
			}, 1500);
		});

		conclusionText.after(textarea);
		conclusionText.hide();

		textarea.focus();
	});
}

function addMotionHandlers() {
	$("#agenda_point ul.objects").on("mouseenter", ".motion h4,.proposition,.motion-description", function(event) {
		if (!hasRight(getUserId(), "handle_motion")) return;
		if ($(this).parents(".motion").data("status") == "resolved") return;

		$(this).find(".glyphicon-pencil").show();
		$(this).find("button.btn-remove-proposition").show();
	});

	$("#agenda_point ul.objects").on("mouseleave", ".motion h4,.proposition,.motion-description", function(event) {
		$(this).find(".glyphicon-pencil").hide();
		$(this).find("button.btn-remove-proposition").hide();
	});

	$("#agenda_point ul.objects").on("click", ".proposition button.btn-remove-proposition", function(event) {
		if (!hasRight(getUserId(), "handle_motion")) return;
		if ($(this).parents(".motion").data("status") == "resolved") return;

		var agendaId = $("#agenda_point").data("id");
		var meetingId = $(".meeting").data("id");
		var motionId = $(this).parents(".motion").data("id");

		var proposition = $(this).parents(".proposition");

		var propositionId = proposition.data("id");

		bootbox.setLocale("fr");
		bootbox.confirm("Supprimer la proposition \"" + proposition.children(".proposition-label").text() + "\" ?", function(result) {
			if (result) {
				$.post("meeting/do_removeMotionProposition.php", {
					meetingId: meetingId,
					pointId: agendaId,
					motionId: motionId,
					propositionId: propositionId
				}, function(data) {}, "json");
			}
		});
	});

	$("#agenda_point ul.objects").on("click", ".motion h4,.proposition", function(event) {
		// Click on vote button intercepted
		if ($(event.target).hasClass("btn")) return;
		if ($(event.target).hasClass("glyphicon")) return;

		if (!hasRight(getUserId(), "handle_motion")) return;

		if ($(this).find("input").length) {
			$(this).find("input").focus();
			return;
		}

		var input = $("<input />", {"class": "form-control", "style": "width: 200px; display: inline-block;"});
		var propertyText = $(this).find(".motion-title,.proposition-label");

		var motionId = $(this).parents(".motion").data("id");
		var property = "mot_title";
		var propositionId = 0;

		if ($(this).hasClass("proposition")) {
			property = "mpr_label";
			propositionId = $(this).data("id");
			input.addClass("pull-left");
			input.addClass("input-xs");
		}
		else {
			input.addClass("input-sm");
		}

		input.val(propertyText.text());
		input.blur(function() {
//			return;
			clearKeyup();
			// update the text into the server
			var newText = input.val();

			$.post("meeting/do_changeMotionProperty.php", {motionId: motionId, propositionId: propositionId, property: property, text: newText}, function(data) {
				propertyText.text(newText);
				propertyText.show();
				input.remove();
			}, "json");
		});

		input.keyup(function() {
//			return;
			clearKeyup();
			keyupTimeoutId = setTimeout(function() {
				var newText = input.val();

				$.post("meeting/do_changeMotionProperty.php", {motionId: motionId, propositionId: propositionId, property: property, text: newText}, function(data) {
				}, "json");
			}, 1500);
		});

		propertyText.after(input);
		propertyText.hide();

		input.focus();
	});

	$("#agenda_point ul.objects").on("click", ".btn-motion-anonymous", function(event) {
		if (!hasRight(getUserId(), "handle_motion")) return;

		var button = $(this);
		button.addClass("disabled");

		button.toggleClass("active");
		var checked = button.hasClass("active");
		
		var motionId = $(this).parents(".motion").data("id");
		var property = "mot_anonymous";
		var propositionId = 0;
		var newText = checked ? 1 : 0;

		$.post("meeting/do_changeMotionProperty.php", {motionId: motionId, propositionId: propositionId, property: property, text: newText}, function(data) {
		}, "json");
	});

	$("#agenda_point ul.objects").on("click", ".btn-motion-limits", function(event) {
		if (!hasRight(getUserId(), "handle_motion")) return;

		$(this).parents(".motion").find(".btn-motion-limits").addClass("disabled");

		var motionId = $(this).parents(".motion").data("id");
		var property = "mot_win_limit";
		var propositionId = 0;
		var newText = $(this).val();

		$.post("meeting/do_changeMotionProperty.php", {motionId: motionId, propositionId: propositionId, property: property, text: newText}, function(data) {
		}, "json");
	});

	$("#agenda_point ul.objects").on("click", ".motion-description", function(event) {
		if (!hasRight(getUserId(), "handle_motion")) return;

		if ($(this).find("textarea").length) {
			$(this).find("textarea").focus();
			return;
		}

		var input = $("<textarea />", {"class": "form-control", "style": "width: 100%;"});
		var propertyText = $(this).find(".motion-description-text");

		var motionId = $(this).parents(".motion").data("id");
		var property = "mot_description";
		var propositionId = 0;

		input.text(propertyText.text());
		input.blur(function() {
//			return;
			clearKeyup();
			// update the text into the server
			var newText = input.val();

			$.post("meeting/do_changeMotionProperty.php", {motionId: motionId, propositionId: propositionId, property: property, text: newText}, function(data) {
				propertyText.text(newText);
				propertyText.show();
				input.remove();
			}, "json");
		});

		input.keyup(function() {
//			return;
			clearKeyup();
			keyupTimeoutId = setTimeout(function() {
				var newText = input.val();

				$.post("meeting/do_changeMotionProperty.php", {motionId: motionId, propositionId: propositionId, property: property, text: newText}, function(data) {
				}, "json");
			}, 1500);
		});

		propertyText.after(input);
		propertyText.hide();

		input.focus();
	});
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

$(function() {
//	$("#start-meeting-modal").modal({
//		  keyboard: false
////		  ,
////		  show: true
//		});
//	$("#start-meeting-modal").modal("show");
	
//	$("ul.objects").click(function(event) {
//		event.stopPropagation();
//	});
//	
//	$("html").on("click", "*", function(event) {
//		var description = $("ul.objects");
//
////		if (description.contains($(this))) {
//		if ($.contains(description.get(0), this)) {
//			return;
//		}
//
//		editorBlurHandler(event);
//	});
	
	var getAgendaPointTimer = $.timer(updateAgendaPoint);
	getAgendaPointTimer.set({ time : 1000, autostart : true });

	$("#agenda_point").on("click", ".motion button.btn-vote", vote);
	$("#agenda_point").on("click", "button.btn-add-chat", addOwnChat);
	$("#agenda_point").on("click", "button.btn-add-conclusion", addConclusion);
	$("#agenda_point").on("click", "button.btn-add-motion", addMotion);
	$("#agenda_point").on("click", "button.btn-advice", setAdvice); 

	$("#agenda_point ul.objects").on("click", ".btn-do-vote, .btn-do-close", changeMotionStatus);
	$("#agenda_point ul.objects").on("click", ".btn-remove-motion", removeMotion);
	$("#agenda_point ul.objects").on("click", ".btn-add-proposition", addMotionProposition);

	addChatHandlers();
	addConclusionHandlers();
	addMotionHandlers();
	addAgendaPointHandlers();

//	addVideoHandlers();

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

$(function() {
	setFramatalkPosition("left");
});
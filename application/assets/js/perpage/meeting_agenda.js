/*
    Copyright 2015-2017 Cédric Levieux, Parti Pirate

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
/* global moment */
/* global hasRight */
/* global hasWritingRight */
/* global getUserId */
/* global testBadges */
/* global bootbox */
/* global clearKeyup */
/* global keyupTimeoutId */
/* global toMarkdownWithEmoji */
/* global resizeWindow */

var preventAgendaHandling = false;
var AGENDA_MAX_HEIGHT = 300;

function addAgendaHandlers() {
	$("#meeting-agenda").on("mouseenter", "li", function(event) {
		var button = $("#meeting-agenda button.btn-agenda-mode");

		if (hasRight(getUserId(), "handle_agenda") && button.hasClass("btn-success")) {
			$(this).children(".glyphicon-pencil").show();
			$(this).children("button").show();
		}
	});
	$("#meeting-agenda").on("mouseleave", "li", function(event) {
		$(this).children(".glyphicon-pencil").hide();
		$(this).children("button").hide();
	});

	$("#meeting-agenda").on("mouseenter", ".panel-heading", function(event) {
		if (hasRight(getUserId(), "handle_agenda")) {
			$(this).children("button.btn-agenda-mode, button.btn-add-point").show();
		}
	});
	$("#meeting-agenda").on("mouseleave", ".panel-heading", function(event) {
		$(this).children("button.btn-agenda-mode, button.btn-add-point").hide();
	});

	$("#meeting-agenda").on("click", "button.btn-remove-point", function(event) {
		event.stopPropagation();

		if (hasRight(getUserId(), "handle_agenda")) {
			var pointId = $(this).data("id");
			var meetingId = $(".meeting").data("id");

			bootbox.setLocale("fr");
			bootbox.confirm("Supprimer le point \""+$(this).siblings("a").text()+"\" ?", function(result) {
				if (result) {
					$.post("meeting_api.php?method=do_removeAgendaPoint", {meetingId: meetingId, pointId: pointId}, function(data) {
						$("#agenda-" + pointId).remove();
					}, "json");
				}
			});
		}
	});

	$("#meeting-agenda").on("click", "button.btn-add-point", function(event) {
		event.stopPropagation();

		if (hasRight(getUserId(), "handle_agenda")) {
			var meetingId = $(".meeting").data("id");
			var parentId = $(this).data("parent-id") ? $(this).data("parent-id") : null;

			var adder = $(this).siblings("ul");
			if (!adder.length) {
				adder = $(this).parent().siblings("ul");
			}

			$.post("meeting_api.php?method=do_addAgendaPoint", {meetingId: meetingId, parentId: parentId}, function(data) {
				var agendaLi = getAgendaLi(data.agenda);
				adder.append(agendaLi);

				agendaLi.click();

				if (data.gamifiedUser) testBadges(data.gamifiedUser.data);
			}, "json");
		}
	});

	$("#meeting-agenda").on("click", "button.btn-agenda-mode", function(event) {
		event.stopPropagation();

		if (hasRight(getUserId(), "handle_agenda")) {
			var button = $("#meeting-agenda button.btn-agenda-mode");
			if (button.hasClass("btn-success")) {
				button.removeClass("btn-success").addClass("btn-warning");
				button.find(".glyphicon").removeClass("glyphicon-pencil").addClass("glyphicon-book");

				$("#meeting-agenda ul").sortable("disable");
			}
			else {
				button.addClass("btn-success").removeClass("btn-warning");
				button.find(".glyphicon").addClass("glyphicon-pencil").removeClass("glyphicon-book");

				$("#meeting-agenda ul").sortable("enable");
			}
		}
	});

	$("#meeting-agenda").on("click", "button.btn-restore-point", function(event) {
		event.stopPropagation();
		
		$("#meeting-agenda button.btn-maximize-point").show();
		$("#meeting-agenda button.btn-restore-point").hide();
		$("#meeting-agenda #agenda-points-list").css({"max-height": AGENDA_MAX_HEIGHT + "px", "overflow-y": "scroll"});
		
		resizeWindow();
	});

	$("#meeting-agenda").on("click", "button.btn-maximize-point", function(event) {
		event.stopPropagation();
		
		$("#meeting-agenda button.btn-maximize-point").hide();
		$("#meeting-agenda button.btn-restore-point").show();
		$("#meeting-agenda #agenda-points-list").css({"max-height": "", "overflow-y": "auto"});

		resizeWindow();
	});


	$("#meeting-agenda").on("click", "li", function(event) {

		if ($(event.target).hasClass("agenda-link")) return;
		if ($(event.toElement).hasClass("agenda-link")) return;
		if ($(event.toElement).hasClass("btn")) return;

		if ($(this).find("input").length) {
			$(this).find("input").focus();
			return;
		}

		if (!hasRight(getUserId(), "handle_agenda")) return;

		if (!$(".btn-agenda-mode").hasClass("btn-success")) return;

		var input = $("<input />", {"class": "form-control", "style": "display: inline-block;"});
		var propertyText = $(this).children("a");

		var pointId = $(this).data("id");
		var property = "age_label";
		var meetingId = $(".meeting").data("id");

		input.val(propertyText.text());
		input.blur(function() {
//			return;
			clearKeyup();
			// update the text into the server
			var newText = input.val();

			$.post("meeting_api.php?method=do_changeAgendaPoint", {meetingId: meetingId, pointId: pointId, property: property, text: newText}, function(data) {
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

				$.post("meeting_api.php?method=do_changeAgendaPoint", {meetingId: meetingId, pointId: pointId, property: property, text: newText}, function(data) {
				}, "json");
			}, 1500);
		});

		propertyText.after(input);
		propertyText.hide();

		input.focus();
	});
}

function changeAgendaOrder(event, ui) {
	if (hasWritingRight(getUserId())) {
		preventAgendaHandling = true;

		var item = {age_id: ui.item.data("id"), age_parent_id : ui.item.parent().data("parent-id")};
		
		var order = 0;
		
		if (ui.item.next().length && ui.item.prev().length) {
			order = ui.item.next().data("order") + ui.item.prev().data("order");
			order -= (order % 2);
			order /= 2;
		}
		else if (ui.item.next().length) {
			order = ui.item.next().data("order");
			order -= (order % 2);
			order /= 2;
		}

		item["age_order"] = order;

		ui.item.data("order", item.age_order);
		ui.item.data("parent-id", item.age_parent_id);

//		console.log(item);
		
		var meetingId = $(".meeting").data("id");

		$.post("meeting_api.php?method=do_changeAgendaPoint", {meetingId: meetingId, pointId: item.age_id, property: "order", text: JSON.stringify(item)}, function(data) {
			preventAgendaHandling = false;
		}, "json");
	}
}

function getAgendaLi(agenda) {
	var agendaLi = $("li[data-template-id=agenda-point]").template("use", {data: agenda});
	agendaLi.find("*").tooltip({placement: "left"});

	var title = agendaLi.children("a");

	// Just in case
	title.text(agenda.age_label);

	agendaLi.find("ul").sortable(getAgendaPointListSorterOptions());

	return agendaLi;
}

function addAgenda(agendas, parent, parentId) {
	for(var index = 0; index < agendas.length; ++index) {
		var agenda = agendas[index];
		if (agenda.age_parent_id != parentId) continue;

		var agendaLi = parent.children("#agenda-" + agenda.age_id);

		if (agendaLi.length) {
			if (agendaLi.data("parent-id") != agenda.age_parent_id || agendaLi.data("order") != agenda.age_order) {
				agendaLi.remove();
				agendaLi = parent.children("#agenda-" + agenda.age_id);
			}
		}

		if (!agendaLi.length) {
			agendaLi = getAgendaLi(agenda);

			parent.append(agendaLi);
		}

		var ul = agendaLi.children("ul");
		var title = agendaLi.children("a");
		var toVote = agendaLi.children(".to-vote");

		agendaLi.removeClass("to-delete");

		if (agenda.age_number_of_motions && agenda.age_number_of_motions != "0") {
			toVote.show();
		}
		else {
			toVote.hide();
		}

		if (title.text() != agenda.age_label) {
			title.text(agenda.age_label);
		}

		addAgenda(agendas, ul, agenda.age_id);

		var addPointLi = ul.children("li.add-point-li").detach();
		ul.append(addPointLi);
	}

	if ($("#meeting-agenda input").length == 0) {
		var sortLi = function(a, b) {
			if ($(b).data('order') == $(a).data('order')) return 0;
			
		    return ($(b).data('order')) < ($(a).data('order')) ? 1 : -1;
		};
		parent.children().sort(sortLi).appendTo(parent);
	}

	if ($("#agenda-points-list").height() > AGENDA_MAX_HEIGHT) {
		$("#meeting-agenda .btn-restore-point").show();
	}

	resizeWindow();
}

function updateMeeting(meeting) {
	$(".meeting").data("json", meeting);

	$("#meeting-status-panel button:not(.request-speaking,.btn-local-anonymous,.btn-scrutineer-mode), #meeting-status-panel .panel-body>span,#meeting-status-panel a.export-link,#meeting-status-panel br.export-br").hide();
	$(".navbar-nav li.export-divider,.navbar-nav li.export-li").hide();

	switch (meeting.mee_status) {
		case "construction":
			$("#meeting-state-panel").addClass("panel-info").removeClass("panel-primary").removeClass("panel-success").removeClass("panel-warning").removeClass("panel-danger");
			$("#meeting-status-panel button.btn-template-meeting").show();
			$("#meeting-status-panel button.btn-delete-meeting").show();
			$("#meeting-status-panel button.btn-waiting-meeting").show();
			break;
		case "waiting":
			$("#meeting-state-panel").removeClass("panel-info").addClass("panel-primary").removeClass("panel-success").removeClass("panel-warning").removeClass("panel-danger");
			$("#meeting-status-panel button.btn-open-meeting").show();
			$("#meeting-status-panel button.btn-delete-meeting").show();
			break;
		case "open":
			$("#meeting-state-panel").removeClass("panel-info").removeClass("panel-primary").addClass("panel-success").removeClass("panel-warning").removeClass("panel-danger");
			$("#meeting-status-panel button.btn-close-meeting").show();
			break;
		case "closed":
			$("#meeting-state-panel").removeClass("panel-info").removeClass("panel-primary").removeClass("panel-success").addClass("panel-warning").removeClass("panel-danger");
			$("#meeting-status-panel span.closed-meeting").show();
			$("#meeting-status-panel a.export-link").show();
			$("#meeting-status-panel button.export-link").show();
			$("#meeting-status-panel br.export-br").show();
			$(".navbar-nav li.export-divider,.navbar-nav li.export-li").show();
			break;
		case "template":
			$("#meeting-state-panel").removeClass("panel-info").removeClass("panel-primary").removeClass("panel-success").addClass("panel-warning").removeClass("panel-danger");
			break;
		case "deleted":
			$("#meeting-state-panel").removeClass("panel-info").removeClass("panel-primary").removeClass("panel-success").removeClass("panel-warning").addClass("panel-danger");
			break;
	}

	$("#meeting-status-panel .btn-vote-meeting").show();

	$("#meeting_rights_list input").prop("checked", false);
	for(var index = 0; index < meeting["mee_rights"].length; ++index) {
		$("input[value=" + meeting["mee_rights"][index] + "]").prop("checked", true);
	}

	if (!$("#synchro-vote-select").is(":visible")) {
		$(".synchro-vote-option").hide();
		$(".synchro-vote-" + meeting.mee_synchro_vote).show();
	}
	$(".synchro-vote select").val(meeting.mee_synchro_vote);

	if ($("#location .location-type").text() != meeting.loc_type) {
		$("#location .location-type").text(meeting.loc_type);
	}

	if (meeting.loc_type == "discord") {
		$("#location-discord").show();
		$("#location-mumble").hide();

		if ($("#location-discord .discord-text a").text() != meeting.loc_discord_text_channel) {
			$("#location-discord .discord-text a").attr("href", meeting.loc_discord_text_link).text(meeting.loc_discord_text_channel);
		}

		if ($("#location-discord .discord-vocal a").text() != meeting.loc_discord_vocal_channel) {
			$("#location-discord .discord-vocal a").attr("href", meeting.loc_discord_vocal_link).text(meeting.loc_discord_vocal_channel);
		}
	}
	else if (meeting.loc_type == "mumble") {
		$("#location-discord").hide();
		$("#location-mumble").show();
	}
	else {
		$("#location-discord").hide();
		$("#location-mumble").hide();
	}

	if (!hasWritingRight(getUserId())) {
		$("#meeting-status-panel button.btn-template-meeting").hide();
		$("#meeting-status-panel button.btn-waiting-meeting").hide();
		$("#meeting-status-panel button.btn-open-meeting").hide();
		$("#meeting-status-panel button.btn-close-meeting").hide();
		$("#meeting-status-panel button.btn-delete-meeting").hide();
//		$("#meeting-status-panel span.closed-meeting").hide();
		$("#send_discourse").hide();
		$("#send_wiki").hide();
	} else {
		$("#send_discourse").show();
		$("#send_wiki").show();
	}

	if (meeting.mee_datetime) {
		var date = moment(meeting.mee_datetime, "YYYY-MM-DD HH:mm:ss").toDate();

		$(".mee_start .span-date").text(moment(date).format("DD/MM/YYYY"));
		$(".mee_start .span-time").text(moment(date).format("HH:mm"));

		if (!$(".mee_start .input-date").is(":visible")) {
			$(".mee_start .input-date").val(moment(date).format("YYYY-MM-DD"));
		}
		if (!$(".mee_start .input-time").is(":visible")) {
			$(".mee_start .input-time").val(moment(date).format("HH:mm"));
		}
	}

	if (meeting.mee_end_datetime) {
		var date = moment(meeting.mee_end_datetime, "YYYY-MM-DD HH:mm:ss").toDate();

		$(".mee_finish .span-date").text(moment(date).format("DD/MM/YYYY"));
		$(".mee_finish .span-time").text(moment(date).format("HH:mm"));

		if (!$(".mee_finish .input-date").is(":visible")) {
			$(".mee_finish .input-date").val(moment(date).format("YYYY-MM-DD"));
		}
		if (!$(".mee_finish .input-time").is(":visible")) {
			$(".mee_finish .input-time").val(moment(date).format("HH:mm"));
		}
	}

	if (meeting.mee_start_time) {
		var date = moment(meeting.mee_start_time, "YYYY-MM-DD HH:mm:ss").toDate();

		$(".mee_start .span-date").text(moment(date).format("DD/MM/YYYY"));
		$(".mee_start .span-time").text(moment(date).format("HH:mm"));
	}

	if (meeting.mee_finish_time) {
		var date = moment(meeting.mee_finish_time, "YYYY-MM-DD HH:mm:ss").toDate();

		$(".mee_finish .span-date").text(moment(date).format("DD/MM/YYYY"));
		$(".mee_finish .span-time").text(moment(date).format("HH:mm"));
	}

	$(".agenda-link").removeClass("secretary-reading").removeClass("personal-reading");
	if (meeting.mee_secretary_agenda_id) {
		$(".agenda-link#agenda-link-" + meeting.mee_secretary_agenda_id).addClass("secretary-reading");
	}
	if ($("#agenda_point").data("id")) {
		$(".agenda-link#agenda-link-" + $("#agenda_point").data("id")).addClass("personal-reading");
	}
}

function updateAgenda() {
	if (preventAgendaHandling) {
//		console.log("I won't do that, cause sorting is occuring");
		return;
	}
	
	var meetingId = $(".meeting").data("id");
	$.get("meeting_api.php?method=do_getAgenda", {id: meetingId}, function(data) {
		var parent = $("#meeting-agenda>ul");
		parent.find("li").addClass("to-delete");
//		parent.children().remove();

		addAgenda(data.agendas, parent, null);

		var addPointLi = parent.children("li.add-point-li").detach();
		parent.append(addPointLi);

		parent.find("li.add-point-li").removeClass("to-delete");
		parent.find("li.to-delete").remove();

		updateMeeting(data.meeting);

		if (hasWritingRight(getUserId())) {
			$("#meeting_rights").show();
		}
		else {
			$("#meeting_rights").hide();
		}

		initAgenda();
		initAgenda = function() {};

		isAgendaReady = true;
		testMeetingReady();

	}, "json");
}

function addMeetingHandlers() {
	$(".synchro-vote select").hide();

	$("#meeting-status-panel .btn-delete-meeting").click(function() {
		if (!hasWritingRight(getUserId())) return;
		var meetingId = $(".meeting").data("id");
		$.post("meeting_api.php?method=do_changeMeeting", {meetingId: meetingId, property: "mee_status", text: "deleted"},
				function(data) {}, "json");
	});

	$("#meeting-status-panel .btn-template-meeting").click(function() {
		if (!hasWritingRight(getUserId())) return;
		var meetingId = $(".meeting").data("id");
		$.post("meeting_api.php?method=do_changeMeeting", {meetingId: meetingId, property: "mee_status", text: "template"},
				function(data) {}, "json");
	});

	$("#meeting-status-panel .btn-waiting-meeting").click(function() {
		if (!hasWritingRight(getUserId())) return;
		var meetingId = $(".meeting").data("id");
		$.post("meeting_api.php?method=do_changeMeeting", {meetingId: meetingId, property: "mee_status", text: "waiting"},
				function(data) {}, "json");
	});

	$("#meeting-status-panel .btn-open-meeting").click(function() {
		if (!hasWritingRight(getUserId())) return;
		var meetingId = $(".meeting").data("id");
		$.post("meeting_api.php?method=do_changeMeeting", {meetingId: meetingId, property: "mee_status", text: "open"},
				function(data) {}, "json");
	});

	$("#meeting-status-panel .btn-close-meeting").click(function() {
		if (!hasWritingRight(getUserId())) return;

		var meetingId = $(".meeting").data("id");
		$.post("meeting_api.php?method=do_changeMeeting", {meetingId: meetingId, property: "mee_status", text: "closed"},
				function(data) {}, "json");
	});

	$("#meeting_rights_list").on("click", "input", function() {
		if (!hasWritingRight(getUserId())) return;

		var meetingId = $(".meeting").data("id");
		var rights = [];
		$("#meeting_rights_list input:checked").each(function() {
			rights[rights.length] = $(this).val();
		});

		$.post("meeting_api.php?method=do_changeRights", {meetingId: meetingId, "rights[]": rights, "empty": rights.length ? null : "empty"},
				function(data) {}, "json");
	});
}

function goDownPoint() {
	var agendaObjects = $(".objects")[0];
	agendaObjects.scrollTop = agendaObjects.scrollHeight;

	var body = $("body")[0];
	body.scrollTop = body.scrollHeight;

	var html = $("html")[0];
	html.scrollTop = html.scrollHeight;

	window.scrollTop = window.scrollHeight;
}

function showNextPoint() {

	var meetingId = $(".meeting").data("id");
	var agendaId = $("#agenda_point").data("id");
	var nextPointId = null;
	var currentPoint = null;

	var points = $("#meeting-agenda li");
	points.each(function(index) {
		if ($(this).data("id") == agendaId) {
			currentPoint = $(this);
			return;
		}

		if (currentPoint) {
			nextPointId = $(this).data("id");
			currentPoint = null;
		}
	});

	if (nextPointId) {
		$("#agenda_point #agenda-members-container").children().remove();
		_updateAgendaPoint(meetingId, nextPointId, true);
	}
}

function showPreviousPoint() {

	var meetingId = $(".meeting").data("id");
	var agendaId = $("#agenda_point").data("id");
	var previousPointId = null;
	var currentPoint = null;

	var points = $("#meeting-agenda li");
	points.each(function(index) {
		if (!currentPoint && $(this).data("id") == agendaId) {
			currentPoint = $(this);
			return;
		}

		if (!currentPoint) {
			previousPointId = $(this).data("id");
		}
	});

	if (previousPointId) {
		$("#agenda_point #agenda-members-container").children().remove();
		_updateAgendaPoint(meetingId, previousPointId, true);
	}
}

function checkAgendaMembers() {
	var now = new Date().getTime();
	$("#agenda_point #agenda-members-container").children().each(function() {
		var lastTimestamp = $(this).data("last-timestamp");
		
		if ((now - lastTimestamp * 1000) > 61000) $(this).remove();
	})
}

function showAddAgendaFromModal(event) {
	event.stopPropagation();
	
	var parentAgendaId = $(this).data("parent-id");
	var parentAgendaLabel = $(this).parent().children("a").text();

	$("#add-agenda-from-modal #parentAgendaIdInput").val(parentAgendaId);
	$("#add-agenda-from-modal #parentAgendaLabel").text(parentAgendaLabel);
//	

	$.get("meeting_api.php?method=do_getMeetings", {}, function(data) {
		if (data.ok) {
			var constructionGroup = $("#add-agenda-from-modal #construction-group");
			var meetingGroup = $("#add-agenda-from-modal #meeting-group");

			// clear meetings
			constructionGroup.children().remove();
			meetingGroup.children().remove();

			for(var index = data.meetings.length - 1; index >=0 ; --index) {
				var meeting = data.meetings[index];

				var option = $("<option></option>");
				option.val(meeting.mee_id);
				option.text(meeting.mee_label);
				option.data("meeting", meeting);

				if (meeting.mee_type == "construction") {
					constructionGroup.append(option);
				}
				else if (meeting.mee_type == "meeting") {
					meetingGroup.append(option);
				}
			}

			$("#add-agenda-from-modal #titleInput").val("");
			$("#add-agenda-from-modal #descriptionArea").val("");
			$("#add-agenda-from-modal #descriptionDiv").hide();
			$("#add-agenda-from-modal #firstChatArea").val("");
			$("#add-agenda-from-modal #firstChatDiv").hide();
			$("#add-agenda-from-modal #no-motion-btn").click();
			
			$("#add-agenda-from-modal #meetingSelect #no-meeting").removeAttr("disabled");
			$("#add-agenda-from-modal #meetingSelect").val(0).change();
			$("#add-agenda-from-modal #meetingSelect #no-meeting").attr("disabled", "disabled");
			// $("#add-agenda-from-modal #meetingSelect").change();

			// Then show the modal
			$("#add-agenda-from-modal").modal();
		}
	}, "json");


}

function changeMeetingHandler() {
	var meetingId = $("#add-agenda-from-modal #meetingSelect").val();
	var agendaSelect = $("#add-agenda-from-modal #agendaSelect");

	$("#add-agenda-from-modal #motionSelectDiv").hide();
	$("#add-agenda-from-modal #agendaSelectDiv").hide();
	agendaSelect.children().remove();

	if (!meetingId) return;

	var meeting = $("#add-agenda-from-modal #meetingSelect option:selected").data("meeting");
	$("#add-agenda-from-modal #titleInput").val(meeting.mee_label);
	$("#add-agenda-from-modal #descriptionArea").val("").keyup();
	$("#add-agenda-from-modal #descriptionDiv").hide();
	$("#add-agenda-from-modal #firstChatArea").val("").keyup();
	$("#add-agenda-from-modal #firstChatDiv").hide();

	$.get("meeting_api.php?method=do_getAgenda", {id: meetingId}, function(data) {
		var option = $("<option value=\"\" disabled=\"disabled\" selected=\"selected\"></option>");
		option.data("agenda", agenda);

		agendaSelect.append(option);

		for(var index = 0; index < data.agendas.length; ++index) {
			var agenda = data.agendas[index];

			if (agenda.age_label.indexOf("amendments") == 0) continue;

			var option = $("<option></option>");
			option.val(agenda.age_id);
			option.text(agenda.age_label);
			option.data("agenda", agenda);

			agendaSelect.append(option);
		}

		if (data.agendas.length) {
			$("#add-agenda-from-modal #agendaSelectDiv").show();
		}
	}, "json")
}

function changeAgendaHandler() {
	var meetingId = $("#add-agenda-from-modal #meetingSelect").val();
	var agendaId = $("#add-agenda-from-modal #agendaSelect").val();
	var motionSelect = $("#add-agenda-from-modal #motionSelect");

	var agenda = $("#add-agenda-from-modal #agendaSelect option:selected").data("agenda");
	$("#add-agenda-from-modal #titleInput").val(agenda.age_label);
	$("#add-agenda-from-modal #descriptionDiv").show();
	$("#add-agenda-from-modal #descriptionArea").val(agenda.age_description).keyup();
	$("#add-agenda-from-modal #firstChatArea").val("").keyup();
	$("#add-agenda-from-modal #firstChatDiv").hide();

	$("#add-agenda-from-modal #motionSelectDiv").hide();
	motionSelect.children().remove();
	
	$.get("meeting_api.php?method=do_getAgendaPoint", {id: meetingId, pointId: agendaId, requestId: 0}, function(data) {
		var option = $("<option value=\"\" disabled=\"disabled\" selected=\"selected\"></option>");
		option.data("motion", motion);

		motionSelect.append(option);

		var motionIds = {};

		for(var index = 0; index < data.motions.length; ++index) {
			var motion = data.motions[index];

			if (motionIds[motion.mot_id]) continue;
			
			motionIds[motion.mot_id] = motion.mot_id;

			if (motion.mot_trashed) continue;
			
			var option = $("<option></option>");
			option.val(motion.mot_id);
			option.text(motion.mot_title);
			option.data("motion", motion);

			motionSelect.append(option);
		}

		if (data.motions.length) {
			$("#add-agenda-from-modal #motionSelectDiv").show();
		}
	}, "json");
}

function changeMotionHandler() {
	var meetingId = $("#add-agenda-from-modal #meetingSelect").val();
	var agendaId = $("#add-agenda-from-modal #agendaSelect").val();
	var motionId = $("#add-agenda-from-modal #motionSelect").val();

	var motion = $("#add-agenda-from-modal #motionSelect option:selected").data("motion");
	$("#add-agenda-from-modal #titleInput").val(motion.mot_title);
	$("#add-agenda-from-modal #descriptionDiv").show();
	$("#add-agenda-from-modal #descriptionArea").val(motion.mot_description).keyup();
	
	if (motion.mot_explanation) {
		$("#add-agenda-from-modal #firstChatDiv").show();
		$("#add-agenda-from-modal #firstChatArea").val(motion.mot_explanation).keyup();
		$("#add-agenda-from-modal #firstChatAuthorInput").val(motion.mot_author_id);
	}
	else {
		$("#add-agenda-from-modal #firstChatArea").val("").keyup();
		$("#add-agenda-from-modal #firstChatDiv").hide();
		$("#add-agenda-from-modal #firstChatAuthorInput").val("");
	}
}

function addAgendaFromSaveHandler() {
	var addAgendaPointForm = {meetingId: 0, parentId: 0, title: "", description: ""};

	addAgendaPointForm.meetingId = $("#add-agenda-from-modal #meetingIdInput").val();
	addAgendaPointForm.parentId = $("#add-agenda-from-modal #parentAgendaIdInput").val();

	addAgendaPointForm.title = $("#add-agenda-from-modal #titleInput").val();
	addAgendaPointForm.description = toMarkdownWithEmoji($("#add-agenda-from-modal #descriptionArea").val()); // TODO remove that when well be in full markdown

	//	console.log(addAgendaPointForm);
	//	add agenda point
	$.post("meeting_api.php?method=do_addAgendaPoint", addAgendaPointForm, function(data) {
		if (data.ok) {
			var agendaId = data.agenda.age_id;
	
			// add motion call
			var addMotionCall = function() {
				// There is a motion to create ?
				if ($("#add-agenda-from-modal #motion-btn-group button.active").val() != "none") {
					var addMotionForm = {meetingId: addAgendaPointForm.meetingId, pointId: agendaId, startingText: "", description: ""};
					addMotionForm.startingText =  $("#add-agenda-from-modal #motionTitleArea").val();
					addMotionForm.description =  $("#add-agenda-from-modal #motionDescriptionArea").val();
					addMotionForm.noProposition = true;

					// add motion
					$.post("meeting_api.php?method=do_addMotion", addMotionForm, function(data) {
						if (data.ok) {
							var motionId = data.motion.mot_id;
							// Add propositions
							var addPropositionForm = {meetingId: addAgendaPointForm.meetingId, pointId: agendaId, motionId: motionId, label: ""};
							var propositions = defaultPropositions[$("#add-agenda-from-modal #motion-btn-group button.active").val()];
							for(var index = 0; index < propositions.length; ++index) {
								addPropositionForm.label = propositions[index];
								$.post("meeting_api.php?method=do_addMotionProposition", addPropositionForm, function(data) {
								}, "json");
							}
						}
					}, "json");
				}
				else {
				}
			};
	
			if ($("#add-agenda-from-modal #firstChatArea").val()) {
				var addFirstChatForm = {id: addAgendaPointForm.meetingId, pointId: agendaId, userId: 0, startingText: ""};
				addFirstChatForm.userId =  $("#add-agenda-from-modal #firstChatAuthorInput").val();
				addFirstChatForm.startingText =  $("#add-agenda-from-modal #firstChatArea").val();

				// add first chat
				$.post("meeting_api.php?method=do_addChat", addFirstChatForm, function(data) {
					if (data.ok) {
						addMotionCall();
					}
				}, "json");
			}
			else {
				addMotionCall();
			}
		}
		else {
			// Bad request
		}
	}, "json");

	// Then close the modal
	$("#add-agenda-from-modal").modal("hide");
}

function addAgendaFromHandlers() {
	$("body").on("click", ".btn-add-point-from", showAddAgendaFromModal);
	$("body").on("change", "#add-agenda-from-modal #meetingSelect", changeMeetingHandler);
	$("body").on("change", "#add-agenda-from-modal #agendaSelect", changeAgendaHandler);
	$("body").on("change", "#add-agenda-from-modal #motionSelect", changeMotionHandler);
	$("body").on("change", "#add-agenda-from-modal #motionWrapperSelect", function() {
		var selectedOption = $("#add-agenda-from-modal #motionWrapperSelect option:selected");

		var motionTitle = selectedOption.data("title");
		var motionDescription = selectedOption.data("description");

		var motionReplace = selectedOption.data("replace");

		if (motionReplace) {
			for(var index = 0; index < motionReplace.length; ++index) {
				var replace = motionReplace[index];
				
				var value = "";
				switch(replace.val) {
					case "title":
						value = $("#add-agenda-from-modal #titleInput").val();
						break;
					case "parentAgendaLabel":
						value = $("#add-agenda-from-modal #parentAgendaLabel").text();
						break;
				}

				value = value.replace(/\"/g,"");

				for(var jndex = 0; jndex < replace.rmv.length; ++jndex) {
					var rmv = replace.rmv[jndex];

					var startIndex = -1;
					while((startIndex = value.toLocaleLowerCase().indexOf(rmv.toLocaleLowerCase())) != -1) {
						var endIndex = startIndex + rmv.length;
						
						value = (startIndex == 0 ? "" : value.substring(0, startIndex)) + value.substring(endIndex);
					}
				}

				value = value.trim();

				motionTitle = motionTitle.replace(replace.src, value);
				motionDescription = motionDescription.replace(replace.src, value);
			}
		}
		
		$("#add-agenda-from-modal #motionTitleArea").val(motionTitle).keyup();
		$("#add-agenda-from-modal #motionDescriptionArea").val(motionDescription).keyup();
	});

	$('#add-agenda-from-modal').on('shown.bs.modal', function () {
		$("#add-agenda-from-modal #descriptionArea").val("").keyup();
		$("#add-agenda-from-modal #descriptionDiv").hide();
		$("#add-agenda-from-modal #firstChatArea").val("").keyup();
		$("#add-agenda-from-modal #firstChatDiv").hide();
	});

	$("body").on("click", "#add-agenda-from-modal #motion-btn-group button", function() {
		$("#add-agenda-from-modal #motion-btn-group button").removeClass("active").removeClass("btn-success").addClass("btn-default");
		$(this).addClass("active").addClass("btn-success").removeClass("btn-default");
		
		if ($(this).val() == "none") {
			$("#add-agenda-from-modal #motionWrapperSelectDiv").hide();
			$("#add-agenda-from-modal #motionTitleDiv").hide();
			$("#add-agenda-from-modal #motionDescriptionDiv").hide();
		}
		else {
			$("#add-agenda-from-modal #motionWrapperSelectDiv").show();
			$("#add-agenda-from-modal #motionTitleDiv").show();
			$("#add-agenda-from-modal #motionDescriptionDiv").show();
			$("#add-agenda-from-modal #motionTitleArea").keyup();
			$("#add-agenda-from-modal #motionDescriptionArea").keyup();
		}
	});

	$("body").on("click", "#add-agenda-from-modal .btn-add-agenda-from", addAgendaFromSaveHandler);
	
}

function getAgendaPointListSorterOptions() {
	return {connectWith: "#meeting-agenda ul", placeholder: "agenda-placeholder", start: function() { preventAgendaHandling = true; }, stop: function() { preventAgendaHandling = false;}, update: changeAgendaOrder };
}

$(function() {
	$(".meeting .row").on("click", "a.agenda-link", showAgendaPoint);
	$("body").on("click", ".btn-next-point", showNextPoint);
	$("body").on("click", ".btn-previous-point", showPreviousPoint);
	$("body").on("click", ".btn-go-down", goDownPoint);

	$("#agenda-points-list").sortable(getAgendaPointListSorterOptions());

	addAgendaFromHandlers()
	addAgendaHandlers();
	addMeetingHandlers();

	updateAgenda();
});

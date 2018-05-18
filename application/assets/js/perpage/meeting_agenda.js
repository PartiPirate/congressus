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
			$(this).children("button").show();
		}
	});
	$("#meeting-agenda").on("mouseleave", ".panel-heading", function(event) {
		$(this).children("button").hide();
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

				testBadges(data.gamifiedUser.data);
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
			}
			else {
				button.addClass("btn-success").removeClass("btn-warning");
				button.find(".glyphicon").addClass("glyphicon-pencil").removeClass("glyphicon-book");
			}
		}
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

function getAgendaLi(agenda) {
	var agendaLi = $("li[data-template-id=agenda-point]").template("use", {data: agenda});
	agendaLi.find("*").tooltip({placement: "left"});

	var title = agendaLi.children("a");

	// Just in case
	title.text(agenda.age_label);

	return agendaLi;
}

function addAgenda(agendas, parent, parentId) {
	for(var index = 0; index < agendas.length; ++index) {
		var agenda = agendas[index];
		if (agenda.age_parent_id != parentId) continue;

		var agendaLi = parent.children("#agenda-" + agenda.age_id);

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
}

function updateMeeting(meeting) {
	$(".meeting").data("json", meeting);

	$("#meeting-status-panel button:not(.request-speaking,.btn-local-anonymous), #meeting-status-panel .panel-body>span,#meeting-status-panel a.export-link,#meeting-status-panel br.export-br").hide();
	switch (meeting.mee_status) {
		case "construction":
			$("#meeting-state-panel").addClass("panel-info").removeClass("panel-primary").removeClass("panel-success").removeClass("panel-warning").removeClass("panel-danger");
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
	
	$(".synchro-vote-option").hide();
	$(".synchro-vote-" + meeting.mee_synchro_vote).show();
	$(".synchro-vote select").val(meeting.mee_synchro_vote);

	if (!hasWritingRight(getUserId())) {
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

	$(".agenda-link").removeClass("secretary-reading");
	if (meeting.mee_secretary_agenda_id) {
		$(".agenda-link#agenda-link-" + meeting.mee_secretary_agenda_id).addClass("secretary-reading");
	}
}

function updateAgenda() {
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
		_updateAgendaPoint(meetingId, previousPointId, true);
	}
}

$(function() {
	$(".meeting .row").on("click", "a.agenda-link", showAgendaPoint);
	$("body").on("click", ".btn-next-point", showNextPoint);
	$("body").on("click", ".btn-previous-point", showPreviousPoint);

	addAgendaHandlers();
	addMeetingHandlers();

	updateAgenda();
});

function addAgendaHandlers() {
	$("#meeting-agenda").on("mouseenter", "li", function(event) {
		if (hasRight(getUserId(), "handle_agenda")) {
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
					$.post("meeting/do_removeAgendaPoint.php", {meetingId: meetingId, pointId: pointId}, function(data) {
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

			$.post("meeting/do_addAgendaPoint.php", {meetingId: meetingId, parentId: parentId}, function(data) {
				var agendaLi = getAgendaLi(data.agenda);
				adder.append(agendaLi);

				agendaLi.click();
			}, "json");
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

			$.post("meeting/do_changeAgendaPoint.php", {meetingId: meetingId, pointId: pointId, property: property, text: newText}, function(data) {
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

				$.post("meeting/do_changeAgendaPoint.php", {meetingId: meetingId, pointId: pointId, property: property, text: newText}, function(data) {
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
			$("#meeting-status-panel button.btn-delete-meeting").show();
			$("#meeting-status-panel button.btn-waiting-meeting").show();
			break;
		case "waiting":
			$("#meeting-status-panel button.btn-open-meeting").show();
			$("#meeting-status-panel button.btn-delete-meeting").show();
			break;
		case "open":
			$("#meeting-status-panel button.btn-close-meeting").show();
			break;
		case "closed":
			$("#meeting-status-panel span.closed-meeting").show();
			$("#meeting-status-panel a.export-link").show();
			$("#meeting-status-panel br.export-br").show();
			break;
	}
	
	$("#meeting_rights_list input").prop("checked", false);
	for(var index = 0; index < meeting["mee_rights"].length; ++index) {
		$("input[value=" + meeting["mee_rights"][index] + "]").prop("checked", true);
	}

	if (!hasWritingRight(getUserId())) {
		$("#meeting-status-panel button.btn-waiting-meeting").hide();
		$("#meeting-status-panel button.btn-open-meeting").hide();
		$("#meeting-status-panel button.btn-close-meeting").hide();
		$("#meeting-status-panel button.btn-delete-meeting").hide();
//		$("#meeting-status-panel span.closed-meeting").hide();
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
	$.get("meeting/do_getAgenda.php", {id: meetingId}, function(data) {
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
	$("#meeting-status-panel .btn-delete-meeting").click(function() {
		if (!hasWritingRight(getUserId())) return;
		var meetingId = $(".meeting").data("id");
		$.post("meeting/do_changeMeeting.php", {meetingId: meetingId, property: "mee_status", text: "deleted"},
				function(data) {}, "json");
	});

	$("#meeting-status-panel .btn-waiting-meeting").click(function() {
		if (!hasWritingRight(getUserId())) return;
		var meetingId = $(".meeting").data("id");
		$.post("meeting/do_changeMeeting.php", {meetingId: meetingId, property: "mee_status", text: "waiting"},
				function(data) {}, "json");
	});

	$("#meeting-status-panel .btn-open-meeting").click(function() {
		if (!hasWritingRight(getUserId())) return;
		var meetingId = $(".meeting").data("id");
		$.post("meeting/do_changeMeeting.php", {meetingId: meetingId, property: "mee_status", text: "open"},
				function(data) {}, "json");
	});

	$("#meeting-status-panel .btn-close-meeting").click(function() {
		if (!hasWritingRight(getUserId())) return;

		var meetingId = $(".meeting").data("id");
		$.post("meeting/do_changeMeeting.php", {meetingId: meetingId, property: "mee_status", text: "closed"},
				function(data) {}, "json");
	});
	
	$("#meeting_rights_list").on("click", "input", function() {
		if (!hasWritingRight(getUserId())) return;

		var meetingId = $(".meeting").data("id");
		var rights = [];
		$("#meeting_rights_list input:checked").each(function() {
			rights[rights.length] = $(this).val();
		});
		
		$.post("meeting/do_changeRights.php", {meetingId: meetingId, "rights[]": rights, "empty": rights.length ? null : "empty"},
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
	var getAgendaTimer = $.timer(updateAgenda);
	getAgendaTimer.set({ time : 5000, autostart : true });

	$(".meeting .row").on("click", "a.agenda-link", showAgendaPoint);
	$("body").on("click", ".btn-next-point", showNextPoint);
	$("body").on("click", ".btn-previous-point", showPreviousPoint);

	addAgendaHandlers();
	addMeetingHandlers();

	updateAgenda();
});
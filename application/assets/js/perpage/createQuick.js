/*
    Copyright 2020 Cédric Levieux, Parti Pirate

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
/* global moment */

function checkButtonRemoveDates() {
	const removeDateButtons = $(".btn-remove-date");
	removeDateButtons.show();

	if (removeDateButtons.length < 2) {
		removeDateButtons.hide();
	}
}

function checkButtonRemoveTimes() {
	const removeTimeButtons = $(".btn-remove-time");
	removeTimeButtons.show();

	if (removeTimeButtons.length < 2) {
		removeTimeButtons.hide();
	}
}

function checkButtonRemovePropositions() {
	const removePropositionButtons = $(".btn-remove-proposition");
	removePropositionButtons.show();

	if (removePropositionButtons.length < 2) {
		removePropositionButtons.hide();
	}
}

function addMotionHandlers() {
	$(".btn-motion-type").on("click", ".btn", function() {
		$(".btn-motion-type .btn").removeClass("active");

		$(this).addClass("active");

		if ($(this).hasClass("btn-dates")) {
			$(".dates").show();
			$(".propositions").hide();
		}
		else if ($(this).hasClass("btn-propositions")) {
			$(".dates").hide();
			$(".propositions").show();
		}

		$(".well-types").hide();
	});

	$("#motionLimitsButtons").on("click", ".btn", function() {
		$("#motionLimitsButtons .btn").removeClass("active");

		$(this).addClass("active");
	});
}

function addPropositionHandlers() {
	$("body").on("click", ".btn-add-proposition", function() {
		const proposition = $(this).parents(".proposition");
		proposition.clone().insertAfter(proposition);

		checkButtonRemovePropositions();
	});
	
	$("body").on("click", ".btn-remove-proposition", function() {
		const proposition = $(this).parents(".proposition");
		proposition.remove();

		checkButtonRemovePropositions();
	});
}

function addDateTimeTableHandlers() {
	$(".dates").css({maxWidth: $(".breadcrumb").width() + "px"});

	$("body").on("click", ".btn-add-date", function() {

		// on every line of the table
		$(this).parents("table").find("tr").each(function() {
			const currentTr = $(this);

			const tds = currentTr.find("th, td");
			const td = tds.eq(tds.length - 2);
			const newTd = td.clone();

			newTd.insertAfter(td);

			let date = newTd.find("input").val();
			if (date) {
				date = moment(date).add(1, "days").format("YYYY-MM-DD");
				newTd.find("input").val(date);
			}
		});

		checkButtonRemoveDates();
	});

	$("body").on("click", ".btn-remove-date", function() {
		const currentTh = $(this).parents("th");

		var index = -1;
		currentTh.parents("tr").children().each(function(currentIndex) {
			if (this == currentTh.get(0)) {
				index = currentIndex;
			}
		});

//		console.log(index);

		$(this).parents("table").find("tr").each(function() {
			const currentTr = $(this);

			currentTr.children().eq(index).remove();
		});


		checkButtonRemoveDates();
	});

	$("body").on("click", ".btn-add-time", function() {
		const lastTr = $(this).parents("table").find("tbody tr").last();
		const newLastTr = lastTr.clone()

		newLastTr.insertAfter(lastTr);

		let time = newLastTr.find("input").first().val();
//		console.log(time);
		if (time) {
			time = moment("1970-01-01 " + time).add(1, "hours").format("HH:mm");
			newLastTr.find("input").first().val(time);
		}

		checkButtonRemoveTimes();
	});

	$("body").on("click", ".btn-remove-time", function() {
		const tr = $(this).parents("tr");

		tr.remove();

		checkButtonRemoveTimes();
	});
}

function openMeeting(meetingId) {
	$.post("meeting_api.php?method=do_changeMeeting", {meetingId: meetingId, property: "mee_status", text: "open"}, function(data) {
		window.location.href = meetingUrl;
	}, "json");
}

function openMotion(meetingId, agendaId, motionId) {
	$.post("meeting_api.php?method=do_changeMotionStatus", {meetingId: meetingId, pointId: agendaId, motionId: motionId, status: "voting"}, function(data) {
		openMeeting(meetingId);
	}, "json");
}

function createPropositions(meetingId, agendaId, motionId, propositions) {
	let index = 1;
	const createProposition = function(data) {
		if (index == propositions.length) {
			openMotion(meetingId, agendaId, motionId);
			return;
		}

		const proposition = propositions[index];
		const addPropositionForm = {label: proposition, meetingId: meetingId, pointId: agendaId, motionId: motionId};

		index++;

		$.post("meeting_api.php?method=do_addMotionProposition", addPropositionForm, createProposition, "json");
	};
	createProposition(null);
}

function createMotion(meetingId, agendaId, propositions) {
	const title = $("#mee_label").val();
	const description = $("#mot_description").val();


	const motionForm = {meetingId: meetingId, pointId: agendaId, startingText: title, description: description};
	motionForm["label"] = propositions[0];
    motionForm["mot_win_limit"] = $(".btn-motion-limits.active").val();

	$.post("meeting_api.php?method=do_addMotion", motionForm, function(data) {
		const motionId = data.motion.mot_id;

		createPropositions(meetingId, agendaId, motionId, propositions);
	}, "json");
}

function createAgendaPoint(meetingId, propositions) {
	const title = $("#mee_label").val();

	$.post("meeting_api.php?method=do_addAgendaPoint", {meetingId: meetingId, parentId: null, title: title}, function(data) {
		const agendaId = data.agenda.age_id;

		createMotion(meetingId, agendaId, propositions);
	}, "json");
}

var meetingUrl = null;

function createMeetingHandler() {
	const createForm = {ajax: true, quick: true};

    createForm["mee_label"] = $("#mee_label").val();
    createForm["mee_class"] = "";
    createForm["mee_meeting_type_id"] = 3;
    createForm["mee_type"] = "meeting";

	const now = moment();
	const endDatetime = moment($("#mee_end_date").val() + " " + $("#mee_end_time").val());

	const duration = Math.ceil(endDatetime.diff(now) / 60000);

    createForm["mee_date"] = now.format("YYYY-MM-DD");
    createForm["mee_time"] = now.format("HH:mm:ss");
    createForm["mee_expected_duration"] = duration;

	createForm["loc_type"] = "";
	createForm["loc_extra"] = "";

	createForm["not_target_id"] = $("#not_target_id").val();
    createForm["not_target_type"] = $("#not_target_type").val();
	createForm["not_voting"] = 1;

	const propositions = [];

	let type = null;

	if ($(".btn-motion-type .btn-dates.active").length) {
		const dateThs = $(".dates table thead tr").children();
		const timeTrs = $(".dates table tbody tr");
		type = "dates";

		dateThs.each(function(index) {
			if (index == 0) return;
			if (index == dateThs - 1) return;

			const date = $(this).find("input").val();

			if (!date) return;

			timeTrs.each(function() {
				const timeTds = $(this).children();
				const time = timeTds.eq(0).find("input").val();
				const datetime = date + " " + time;
				const checkbox = timeTds.eq(index).find("input");

				if (checkbox.is(":checked")) {
					propositions.push(datetime);
				}
			});
		});
	}
	else if ($(".btn-motion-type .btn-propositions.active").length) {
		type = "propositions";

		$(".propositions .proposition-input").each(function() {
			const proposition = $(this).val();

			if (proposition) propositions.push(proposition);
		})
	}


//	console.log(createForm);

	let errorCount = 0;

	if (!propositions.length) {
		errorCount++;

		if (type == "dates") {
			$("#no-date-error-alert").show().delay(5000).fadeOut(1000, function() {
			});
		}
		else if (type == "propositions") {
			$("#no-proposition-error-alert").show().delay(5000).fadeOut(1000, function() {
			});
		}
		else {
			$("#no-type-error-alert").show().delay(5000).fadeOut(1000, function() {
			});
		}
	}

	if (!createForm["mee_label"]) {
		$("#label-error-alert").show().delay(5000).fadeOut(1000, function() {
		});

		errorCount++;
	}

	if (!duration) {
		$("#date-error-alert").show().delay(5000).fadeOut(1000, function() {
		});

		errorCount++;
	} 
	else if (duration < 60) {

		errorCount++;
	}
	
	if (errorCount) {
		$(".btn-create").prop("disabled", false);
		return;
	}

	$.post("meeting/do_createMeeting.php", createForm, function(data) {
		const meetingId = data.meeting.mee_id;
		meetingUrl = data.url;

		createAgendaPoint(meetingId, propositions);

	}, "json");
}

function addCreationProcessHandlers() {
	$(".btn-create").click(function(event) {
		event.stopImmediatePropagation();
		event.preventDefault();

		createMeetingHandler();
	});;
}

// Notice handling
$(function() {
	var targetChangeHandler = function(type) {
		if (type != "con_external") {
			$(".not_mails").show();
			$(".mails").hide();
			$("#not_target_id option, #not_target_id optgroup").hide();
			$("#not_target_id option." + type + ", #not_target_id optgroup." + type).show();
			$("#not_target_id option").removeAttr("selected");
			$("#not_target_id option." + type).eq(0).attr("selected", "selected");
		}
		else {
			$(".not_mails").hide();
			$(".mails").show();
		}
	};

	$("body").on("change", "#not_target_type", function() {
		var type = $(this).val();
		targetChangeHandler(type);
	});

	$("body #not_target_type").change();
	
	if ($("body #not_target_type option").length < 2) {
		$(".notice-primary-sources").hide();
	}
});

$(function() {
	addMotionHandlers();
	addDateTimeTableHandlers();
	addPropositionHandlers();
	addCreationProcessHandlers();

	checkButtonRemoveDates();
	checkButtonRemoveTimes();
	checkButtonRemovePropositions();
});
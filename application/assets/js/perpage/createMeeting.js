/*
	Copyright 2015-2018 Cédric Levieux, Parti Pirate

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

function isDateValid(str) {
	var d = moment(str,'YYYY-MM-DD');
	if(d == null || !d.isValid()) return false;

	return true;
}

function isTimeValid(str) {
	var d = moment(str,'HH:mm');
	if(d == null || !d.isValid()) return false;

	return true;
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
});


function addTabListeners() {
	$(".show-notice").click(function() {
		$('.nav-tabs li:eq(1) a').tab('show');
	});

	$(".show-agenda").click(function() {
		$('.nav-tabs li:eq(2) a').tab('show');
	});
	$('.nav-tabs li:eq(2) a').on('shown.bs.tab', function (e) {
		$(".autogrow").keyup();
	});
	$(".show-location").click(function() {
		$('.nav-tabs li:eq(3) a').tab('show');
	});
}

function addCopyListeners() {
	$("#mee_id").change(function() {
		var meetingId = $("#mee_id").val();
		$("#mee_type,.show-notice").prop("disabled", meetingId != -1);
		$(".copy-meeting-btn").prop("disabled", meetingId == -1);

		$("#mee_type,.show-notice").prop("disabled", meetingId != -1);

		if (meetingId && meetingId != -1) {
			$("#creation-step-tabs li:not(':eq(0)')").hide();
			var meetingOption = $("#mee_id option:selected");
			var type = meetingOption.parents("optgroup").data("type");

			if ($("#mee_label").val() == "") {
				$("#mee_label").val(meetingOption.text());
			}

			$("#mee_type").val(type).change();
		}
		else {
			$("#creation-step-tabs li:not(':eq(0)')").show();
		}
	});
	
	$(".copy-meeting-btn").click(function(event) {
		event.preventDefault();

		$("#create-meeting-form").find(".form-group").removeClass("has-success").removeClass("has-warning").removeClass("has-error");

		var errorCount = 0;

		errorCount += isDateValid($("#mee_date").val()) ? 0 : 1;
		errorCount += isTimeValid($("#mee_time").val()) ? 0 : 1;

		if (errorCount) {

			$("#mee_date").parents(".form-group").addClass("has-error");

			$('.nav-tabs li:eq(0) a').tab('show');

			$("#date-time-error-alert").show().delay(5000).fadeOut(1000, function() {
			});
		}
		else {
			$("#mee_date").parents(".form-group").addClass("has-success");
		}

		if ($("#mee_label").val() == "") {
			$("#mee_label").parents(".form-group").addClass("has-error");

			$('.nav-tabs li:eq(0) a').tab('show');

			$("#label-error-alert").show().delay(5000).fadeOut(1000, function() {
			});

			errorCount++;
		}
		else {
			$("#mee_label").parents(".form-group").addClass("has-success");
		}

		if (!errorCount) {
			var meetingForm = {};
			
			meetingForm["mee_label"] = $("#mee_label").val();
			meetingForm["mee_type"]  = $("#mee_type").val();
			meetingForm["mee_date"]  = $("#mee_date").val();
			meetingForm["mee_time"]  = $("#mee_time").val();
			meetingForm["mee_expected_duration"] = $("#mee_expected_duration").val();

			meetingForm["mee_id"] = $("#mee_id").val();
			
			meetingForm["ajax"] = true;

			// submit
			$.post("meeting_api.php?method=do_copyMeeting", meetingForm, function(data) {
				if (data.ok) {
					window.location.href = data.url;
				}
			}, "json");
		}


	});
}

function addCreateListener() {
	$("#create-meeting-form").submit(function(event) {

		$("#create-meeting-form").find(".form-group").removeClass("has-success").removeClass("has-warning").removeClass("has-error");

		var errorCount = 0;

		errorCount += isDateValid($("#mee_date").val()) ? 0 : 1;
		errorCount += isTimeValid($("#mee_time").val()) ? 0 : 1;

		if (errorCount) {

			$("#mee_date").parents(".form-group").addClass("has-error");

			$('.nav-tabs li:eq(0) a').tab('show');

			$("#date-time-error-alert").show().delay(5000).fadeOut(1000, function() {
			});

			event.preventDefault();
		}
		else {
			$("#mee_date").parents(".form-group").addClass("has-success");
		}

		if ($("#mee_label").val() == "") {
			$("#mee_label").parents(".form-group").addClass("has-error");

			$('.nav-tabs li:eq(0) a').tab('show');

			$("#label-error-alert").show().delay(5000).fadeOut(1000, function() {
			});

			event.preventDefault();
		}
		else {
			$("#mee_label").parents(".form-group").addClass("has-success");
		}

		if ($("#not_target_id").val() == "0") {
			$("#not_target_id").parents(".form-group").addClass("has-warning");
		}
		else {
			$("#not_target_id").parents(".form-group").addClass("has-success");
		}
	});
}

$(function() {
	addCreateListener();
	addCopyListeners();
	addTabListeners();

	$("body").on("change", "#mee_type", function() {
		var type = $("#mee_type").val();

		$(".type-explanation").hide();

		$(".type-" + type).show();
	});
	$("#mee_id").change();
	$("#mee_type").change();
});
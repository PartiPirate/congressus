/*
    Copyright 2014-2015 Cédric Levieux, Jérémy Collot, ArmagNet

    This file is part of OpenTweetBar.

    OpenTweetBar is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    OpenTweetBar is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with OpenTweetBar.  If not, see <http://www.gnu.org/licenses/>.
*/

var timers = {};

function changeStatus(data, field) {
	if (data.ok && !data.exist) {
		$("#" + field).addClass("glyphicon-ok");
		$("#" + field).removeClass("glyphicon-remove");
		$("#" + field).parents(".has-feedback").addClass("has-success");
		$("#" + field).parents(".has-feedback").removeClass("has-error");

	}
	else {
		$("#" + field).removeClass("glyphicon-ok");
		$("#" + field).addClass("glyphicon-remove");
		$("#" + field).parents(".has-feedback").removeClass("has-success");
		$("#" + field).parents(".has-feedback").addClass("has-error");
	}
	$("#" + field).show();

	verifyAll();
}

function verify_mail() {
	var value = $("#xxxInput").val().trim();
    var mailRegExp = new RegExp("^[A-Z0-9._%+-]+@[A-Z0-9.-]+\\.[A-Z]{2,4}$");
	$("#mailHelp").hide();

	if (!value) {
		changeStatus({ko: "ko"}, "mailStatus");
		$("#mailHelp").html(mypreferences_validation_mail_empty);
		$("#mailHelp").show();
	}
	else if (mailRegExp.test(value.toUpperCase()) === false) {
		changeStatus({ko: "ko"}, "mailStatus");
		$("#mailHelp").html(mypreferences_validation_mail_not_valid);
		$("#mailHelp").show();
	}
    else {
		$.post("do_userDataExist.php", {field: "mail", value: $("#xxxInput").val()}, function(data) {
			changeStatus(data, "mailStatus");
			if (data.ok && data.exist) {
				$("#mailHelp").html(mypreferences_validation_mail_already_taken);
				$("#mailHelp").show();
			}
		}, "json");
	}
}

function verifyAll() {
	var numberOfKos =  $(".glyphicon-remove:visible").length;

	if (!$("#xxxInput").val()) numberOfKos++;

	if (numberOfKos) {
		$('#savePreferencesButton').attr("disabled", "disabled");
	}
	else {
		$('#savePreferencesButton').removeAttr("disabled");
	}
}

function responseHandler(data) {
	if (data.ok) {
		$("#ok_operation_successAlert").show().delay(2000).fadeOut(1000);
		if (data.password) {
			$("#userOldPasswordInput").val(data.password);
			$("#userPasswordInput").val("");

			if ($("#userLanguageInput").val() != userLanguage) {
				window.location.reload(true);
			}
		}
	}
	else {
		$("#" + data.message + "Alert").show().delay(2000).fadeOut(1000);
	}
}

$(function() {
	$("#userLoginInput, #xxxInput").keyup(function() {
		var field = $(this).attr("id");

		switch(field) {
			case "userLoginInput":
				field = "login";
				break;
			case "xxxInput":
				field = "mail";
				break;
		}

		var timerHandler = timers[$(this).attr("id")];
		if (timerHandler) clearTimeout(timerHandler);

		timers[$(this).attr("id")] = setTimeout(eval("verify_" + field), 500);
	});

	$('#savePreferencesButton').click(function (e) {
		e.preventDefault();

		var myform = 	{
							password: $("#userPasswordInput").val(),
							confirmation: $("#userConfirmationInput").val(),
							old: $("#userOldInput").val(),
//							xxx: $("#xxxInput").val().trim(),
//							language: $("#userLanguageInput").val(),
//							notification: $("#userNotificationInput").val()
						};

		$.post("do_mypreferences.php", myform, responseHandler, "json");
	});

	$("#userNotificationButtons button").click(function(e) {
		$("#userNotificationButtons button").removeClass("active");
		$(this).addClass("active");
		$("#userNotificationInput").val($(this).val());
	});

	$("#userLanguageButtons button").click(function(e) {
		$("#userLanguageButtons button").removeClass("active");
		$(this).addClass("active");
		$("#userLanguageInput").val($(this).val());
	});
});
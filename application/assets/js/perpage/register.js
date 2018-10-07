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

function responseHandler(data) {
	if (data.ok) {
		$("#ok_operation_successAlert").show().delay(2000).fadeOut(1000);
		$("#formPanel").hide();
		$("#successPanel").show();
	}
	else {
		$("#" + data.message + "Alert").show().delay(2000).fadeOut(1000);
	}
}

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

function verify_login() {
	$("#userLoginHelp").hide();
	var value = $("#userLoginInput").val().trim();

	if (!value) {
		changeStatus({ko: "ko"}, "userLoginStatus");
		$("#userLoginHelp").html(register_validation_user_empty);
		$("#userLoginHelp").show();
	}
	else {
		$.post("do_userDataExist.php", {field: "login", value: value}, function(data) {
			changeStatus(data, "userLoginStatus");
			if (data.ok && data.exist) {
				$("#userLoginHelp").html(register_validation_user_already_taken);
				$("#userLoginHelp").show();
			}
		}, "json");
	}
}

function verify_mail() {
	var value = $("#xxxInput").val().trim();
    var mailRegExp = new RegExp("^[A-Z0-9._%+-]+@[A-Z0-9.-]+\\.[A-Z]{2,4}$");
	$("#mailHelp").hide();

	if (!value) {
		changeStatus({ko: "ko"}, "mailStatus");
		$("#mailHelp").html(register_validation_mail_empty);
		$("#mailHelp").show();
	}
	else if (mailRegExp.test(value.toUpperCase()) === false) {
		changeStatus({ko: "ko"}, "mailStatus");
		$("#mailHelp").html(register_validation_mail_not_valid);
		$("#mailHelp").show();
	}
    else {
		$.post("do_userDataExist.php", {field: "mail", value: $("#xxxInput").val()}, function(data) {
			changeStatus(data, "mailStatus");
			if (data.ok && data.exist) {
				$("#mailHelp").html(register_validation_mail_already_taken);
				$("#mailHelp").show();
			}
		}, "json");
	}
}

function verify_passwords() {
	if ($("#userPasswordInput").val()) {
		changeStatus({ok: "ok", exists: false}, "passwordStatus");
	}
	else {
		changeStatus({ko: "ko"}, "passwordStatus");
	}

	if ($("#userPasswordInput").val() == $("#userConfirmationInput").val()) {
		changeStatus({ok: "ok", exists: false}, "confirmationStatus");
	}
	else {
		changeStatus({ko: "ko"}, "confirmationStatus");
	}
}

function verifyAll() {

	var numberOfKos =  $(".glyphicon-remove:visible").length;

	if (!$("#userLoginInput").val()) numberOfKos++;
	if (!$("#xxxInput").val()) numberOfKos++;

	if (numberOfKos) {
		$('#registerButton').attr("disabled", "disabled");
	}
	else {
		$('#registerButton').removeAttr("disabled");
	}
}

$(function() {
	$("#cgvInput").click(function(event) {
		if ($("#cgvInput").attr("checked")) {
			$("#cgvInput").removeAttr("checked");
		}
		else {
			$("#cgvInput").attr("checked", "checked");
		}
	});

	$("#userPasswordInput, #userConfirmationInput").keyup(function() {
		verify_passwords();
//		verifyAll();
	});

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

	$('#registerButton').click(function (e) {
		e.preventDefault();

		var myform = 	{
							login: $("#userLoginInput").val().trim(),
							xxx: $("#xxxInput").val().trim(),
							mail: $("#mail").val(),
							cgv: $("#cgvInput").attr("checked") ? "badboy" : "okgirls",
							password: $("#userPasswordInput").val(),
							confirmation: $("#userConfirmationInput").val(),
							language: $("#userLanguageInput").val(),
							notification: $("#userNotificationInput").val()
						};

		$.post("do_register.php", myform, responseHandler, "json");
	});

	$("#userLanguageButtons button").click(function(e) {
		$("#userLanguageButtons button").removeClass("active");
		$(this).addClass("active");
		$("#userLanguageInput").val($(this).val());
	});

	$("#userNotificationButtons button").click(function(e) {
		$("#userNotificationButtons button").removeClass("active");
		$(this).addClass("active");
		$("#userNotificationInput").val($(this).val());
	});

	verifyAll();
});
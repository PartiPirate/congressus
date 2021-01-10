/*
    Copyright 2014-2021 Cédric Levieux, Parti Pirate

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
/* global gamifiedUser */

function testBadges(user) {
//	console.log(user);

	if (typeof user == "undefined") return;
	if (typeof user["badges"] == "undefined") return;

//	console.log(user.badges);

	var hasUnnoticed = false;

	for(var index = 0; index < user.badges.length; ++index) {
		if (!user.badges[index].noticed) {
			hasUnnoticed = true;
			break;
		}
	}
	
	if (hasUnnoticed) {
		$("#mybadgesInfoSpan").removeClass("hidden");
		$("#mybadgesLi").addClass("bg-info");
	}
	else {
//		$("#mybadgesInfoSpan").addClass("hidden");
//		$("#mybadgesLi").removeClass("bg-info");
	}
}

$(function() {
	if (typeof gamifiedUser == "undefined")	return;
	
	testBadges(gamifiedUser);
});

$(function() {
	$("#rememberMe").click(function(event) {
		if ($(this).attr("checked")) {
			$(this).removeAttr("checked");
		}
		else {
			$(this).attr("checked", "checked");
		}
	});

//	$("#loginLink, #connectButton").click(function(event) {
//		event.stopPropagation();
//		event.preventDefault();
//
//		$("#loginForm").show();
//	});

	$("#loginForm").mouseleave(function(event) {
		$("#loginForm").hide();
	});

	$("#loginForm #loginButton").click(function(event) {
		event.stopPropagation();
		event.preventDefault();

		var myform = {
			login : $("#loginInput").val(),
			password : $("#passwordInput").val(),
			rememberMe : $("#rememberMe").attr("checked") ? 1 : 0
		};

		$.post("do_login.php", myform, function(data) {
			$("#loginForm").hide();
			if (data.ok) {
				window.location.reload(true);
			} else {
				$("#" + data.message + "Alert").parents(".container").show();
				$("#" + data.message + "Alert").show().delay(2000).fadeOut(1000, function() {
					$(this).parents(".container").hide();
				});
			}
		}, "json");
	});

	$(".logoutLink").click(function(event) {
		event.stopPropagation();
		event.preventDefault();

		var myform = {};

		$.post("do_logout.php", myform, function(data) {
			if (data.ok) {
				window.location.reload(true);
			} else {
			}
		}, "json");
	});
});

$(function() {
	$("body").on("change", ".viewable-group-checkbox", function() {
		const checkedGroups = [];

		$(".viewable-group-checkbox:checked").each(function() {
			checkedGroups[checkedGroups.length] = {type: $(this).data("type"), id: $(this).data("id")};
		});
		
		$.post("do_set_viewable_groups.php", {"viewable_groups": checkedGroups}, function(data) {
		}, "json");
	});
});
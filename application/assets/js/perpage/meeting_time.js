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

function startingMeetingHandler() {
	var meeting = $(".meeting").data("json");

	if (meeting.mee_status != "waiting") return;

	// Test time

	if (false) {
		if (!meeting.mee_president_member_id) {
			// Show "choose a president"
		}
		else if (isPresident(getUserId())) {
			// Show "start meeting ?"
		}
	}
}

function addTimeHandlers() {
	$(".mee_start .date-control,.mee_start .time-control").hover(function(event) {
			var meeting = $(".meeting").data("json");

			if (meeting.mee_status != "construction") return;
			if (!hasWritingRight(getUserId())) return;

			$(this).children("input").show();
			$(this).children("span").hide();
		},
		function(event) {
			$(this).children("span").show();
			$(this).children("input").hide();
		}
	);

	$(".mee_start .date-control, .mee_start .time-control").on("change", "input", function(event) {
		var meetingId = $(".meeting").data("id");

		var parent = $(this).parents(".datetime-control");

		var date = parent.find(".date-control input").val();
		var time = parent.find(".time-control input").val();

		var mee_datetime = date + " " + time;

		$.post("meeting_api.php?method=do_changeMeeting", {meetingId: meetingId, property: "mee_datetime", text: mee_datetime},
				function(data) {}, "json");
	});
}

$(function() {
	var startingTimer = $.timer(startingMeetingHandler);
	startingTimer.set({ time : 60000, autostart : true });

	addTimeHandlers();
});
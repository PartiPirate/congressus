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
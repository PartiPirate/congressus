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

var lastEventTimestamp = 0;

function computeEventPositions() {
	var margin = 5;
	var currentPosition = 60;

	$(".congressus-event").each(function() {
		var eventAlert = $(this);

		eventAlert.css({"bottom" : currentPosition + "px"});

		currentPosition += (eventAlert.height() + margin + 16);

	});
}

function getEventText(event) {
	if (event.text) return event.text;

	var text = "";

	if (event.type == "speak_request") {
		var userNickname = $("li#member-"+event.options.userId+" .member-nickname").eq(0).text();
		text = "<i><b>" + userNickname + "</b></i> " + meeting_speakingAsk;
	}
	else if (event.type == "speak_set") {
		var userNickname = $("li#member-"+event.options.userId+" .member-nickname").eq(0).text();
		text = "<i><b>" + userNickname + "</b></i> " + meeting_speaking;
	}
	else if (event.type == "speak_renounce") {
		var userNickname = $("li#member-"+event.options.userId+" .member-nickname").eq(0).text();
		text = "<i><b>" + userNickname + "</b></i>" + meeting_speakingRenounce;
	}
	else if (event.type == "join") {
		var userNickname = $("li#member-"+event.options.userId+" .member-nickname").eq(0).text();
		if (userNickname) {
			text = "<i><b>" + userNickname + "</b></i> " + meeting_arrival;
		}
		else {
			text = "<i><b>Guest</b></i> " + meeting_arrival;
		}
	}
	else if (event.type == "left") {
		var userNickname = $("li#member-"+event.options.userId+" .member-nickname").eq(0).text();
		if (userNickname) {
			text = "<i><b>" + userNickname + "</b></i> " + meeting_left;
		}
		else {
			text = "<i><b>Guest</b></i> " + meeting_left;
		}
	}

	return text;
}

function showEvent(event) {

	var eventClass = "success";

	switch(event.type) {
		case "motion_add":
		case "motion_to_vote":
		case "join":
			eventClass = "success";
			break;
		case "motion_remove":
		case "motion_close_vote":
		case "left":
			eventClass = "danger";
			break;
		case "speak_renounce":
			eventClass = "warning";
			break;
		default:
			eventClass = "info";
			break;
	}

	var text = getEventText(event);

	var eventAlert = $("<p style='width: 350px; height: 55px; z-index: 1000; position: fixed; right: 10px;' class='congressus-event form-alert simply-hidden bg-" + eventClass + "'>" + text + "</p>");
	var body = $("body");
	body.append(eventAlert);

	computeEventPositions();

	eventAlert.show().delay(5000).fadeOut(1000, function() {
		$(this).remove();
		computeEventPositions();
	});
}

function getEvents() {
	var meetingId = $(".meeting").data("id");

	$.post("meeting_api.php?method=do_getEvents", {meetingId : meetingId}, function(data) {
		if (data.ok) {
			for(var index = 0; index < data.events.length; ++index) {
				var event = data.events[index];

				if (event.timestamp <= lastEventTimestamp) continue;

				showEvent(event);
			}

			if (data.events.length > 0) {
				lastEventTimestamp = data.events[data.events.length - 1].timestamp;
			}
		}
	}, "json");
}

$(function() {
//	var getAgendaPointTimer = $.timer(updateAgendaPoint);
//	getAgendaPointTimer.set({ time : 1500, autostart : true });
});

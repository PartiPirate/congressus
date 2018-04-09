/*
	Copyright 2018 Cédric Levieux, Parti Pirate

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

function renewMeetings(statuses) {
    $.get(window.location.href, {}, function(htmldata) {
//		var mainPanel = $(htmldata).find("div.meeting-container");

        for(var index = 0; index < statuses.length; ++index) {
            var status = statuses[index];
            
            $(".meeting-container #" + status).children().remove();
            $(".meeting-container #" + status).append($(htmldata).find("div#" + status).children());
            
            $("ul a[href=#"+status+"]").html($(htmldata).find("ul a[href=#"+status+"]").html());
        }

	}, "text");
}

function changeStatus(meetingId, status, toStatus) {
	$.post("meeting_api.php?method=do_changeMeeting", {meetingId: meetingId, property: "mee_status", text: toStatus},
			function(data) {
                renewMeetings([status, toStatus]);   
			}, "json");
}

function addButtonListeners() {
    $(".meeting-container").on("click", ".btn-waiting-meeting", function() {
        $(this).attr("disabled", "disabled");
        var meetingId = $(this).data("meetingId");
        var status = $(this).data("status");
        changeStatus(meetingId, status, "waiting");
    });

    $(".meeting-container").on("click", ".btn-delete-meeting", function() {
        $(this).attr("disabled", "disabled");
        var meetingId = $(this).data("meetingId");
        var status = $(this).data("status");
        changeStatus(meetingId, status, "deleted");
    });

    $(".meeting-container").on("click", ".btn-open-meeting", function() {
        $(this).attr("disabled", "disabled");
        var meetingId = $(this).data("meetingId");
        var status = $(this).data("status");
        changeStatus(meetingId, status, "open");
    });

    $(".meeting-container").on("click", ".btn-close-meeting", function() {
        $(this).attr("disabled", "disabled");
        var meetingId = $(this).data("meetingId");
        var status = $(this).data("status");
        changeStatus(meetingId, status, "closed");
    });
}

$(function() {
    addButtonListeners();
});
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
/* global hasWritingRight */
/* global getUserId */

function mumbleLink(loc_channel) {
	var mumble_url ="mumble://" + mumble_server + "/" + loc_channel + "?title=" + mumble_title + "&version=" + mumble_version + " (" + loc_channel + ")";
	$("#loc_extra").empty().append(mumble_url);
}

$(function() {

//	mumbleLink($("#loc_channel").val());
	$("#loc_extra_group").hide();
	$("#loc_discord_form").hide();

	$("body").on('change', '#loc_type', function() {
		if($("#loc_type").val()=="mumble"){
			$("#loc_discord_form").hide();
			$("#loc_channel_form").show();
			$("#loc_channel").val('Accueil Taverne');
			mumbleLink($("#loc_channel").val());
			$('#loc_extra_group').hide();
		}
		else if($("#loc_type").val()=="discord"){
			$("#loc_channel_form").hide();
			$("#loc_discord_form").show();
			$("#loc_text_channel").val('discussion');
	//		discordsLink($("#loc_channel").val());
			$('#loc_extra_group').hide();
		}
		else {
			$("#loc_discord_form").hide();
			$("#loc_channel_form").hide();
			$("#loc_channel").val('AFK');
			$("#loc_extra").empty();
			$('#loc_extra_group').show();
		}
	
	});
	$("#loc_type").change();
	
	$("body").on('change', '#loc_channel', function() {
		mumbleLink($("#loc_channel").val());
	});
	
	$("#location").hover(function() {
		if (hasWritingRight(getUserId())) {
			$(this).find(".update-btn").show();
		}
	}, function() {
		$(this).find(".update-btn").hide();
	});

	$("#location .update-btn").click(function() {
		$("#change-location-modal").modal("show");
		
		var type = $("#location").data("type");
		
		$("#loc_type").val(type).change();
		
		if (type == "discord") {
			$("#loc_discord_text_channel").val($("#location-discord .discord-text").text().trim());
			$("#loc_discord_vocal_channel").val($("#location-discord .discord-vocal").text().trim());
		}
	});
	
	$("body").on("click", ".btn-save-location", function() {
		var type = $("#loc_type").val();
		var extra = $("#loc_extra").val();
		
		var channel = "";
		
		if (type == "discord") {
			var textChannel = $("#loc_discord_text_channel").val();
			var vocalChannel = $("#loc_discord_vocal_channel").val();

			channel = textChannel + "," + vocalChannel;
		}
		
		var meetingId = $(".meeting").data("id");
		var text = JSON.stringify({type: type, extra: extra, channel: channel});

		$.post("meeting_api.php?method=do_changeMeeting", {meetingId: meetingId, property: "mee_location", text: text}, function(data) {
		$("#change-location-modal").modal("hide");
		});
	});

});
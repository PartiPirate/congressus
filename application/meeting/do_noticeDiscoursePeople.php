<?php /*
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
    along with Congressus.  If not, see <https://www.gnu.org/licenses/>.
*/

if (!isset($api)) exit();

require_once("engine/utils/NotifierUtils.php");
require_once("engine/utils/EventStackUtils.php");

$meetingId = $arguments["meetingId"];
$memcacheKey = "do_getPeople_$meetingId";

$memcache = openMemcacheConnection();

$connection = openConnection();

$locationBo = LocationBo::newInstance($connection, $config);
$meetingBo = MeetingBo::newInstance($connection, $config);
$noticeBo = NoticeBo::newInstance($connection, $config);

$meeting = $meetingBo->getById($meetingId);

if (!$meeting) {
	echo json_encode(array("ko" => "ko", "message" => "meeting_does_not_exist"));
	exit();
}

// TODO Compute the key // Verify the key

if (false) {
	echo json_encode(array("ko" => "ko", "message" => "meeting_not_accessible"));
	exit();
}

$meeting_date = "inconnu";
$meeting_time = "inconnu";
$start = getDateTime($meeting["mee_datetime"]);

$meeting_date = @$start->format(lang("date_format"));
$meeting_time = @$start->format(lang("time_format"));

$meeting_date = str_replace("{date}", $meeting_date, lang("datetime_format", false));
$meeting_date = str_replace("{time}", $meeting_time, $meeting_date);

$notices = $noticeBo->getByFilters(array("not_meeting_id" => $meeting[$meetingBo->ID_FIELD]));

$data = array();
$membersToNotice = array();

$meetingLink = $config["server"]["base"] . "meeting.php?id=" . $meeting[$meetingBo->ID_FIELD];

$locations = $locationBo->getByFilters(array("loc_meeting_id" => $meeting[$meetingBo->ID_FIELD], "loc_principal" => 1));
if (count($locations)) {
	$location = $locations[0];
}
else {
	$location = array("loc_type" => "unknown", "loc_extra" => "");
}

$body = lang("notice_mail_content", false);
$body = str_replace("{meeting_label}", $meeting["mee_label"], $body);
$body = str_replace("{meeting_date}", $meeting_date, $body);
$body = str_replace("{location_type}", strtolower(lang("loc_type_" . $location["loc_type"])), $body);

$altBody = $body;
$altBody = str_replace("{meeting_link}", $meetingLink, $altBody);
//$altBody = str_replace("{location_extra}", $location["loc_extra"], $altBody);

if ($location["loc_type"] == "mumble") {
	$location["loc_extra"] = "<a href='" . $location["loc_extra"] . "'>" . $location["loc_extra"] . "</a>";
}
else if ($location["loc_type"] == "discord") {
	include("config/discord.structure.php");

	echo $location["loc_channel"];
	echo "\n";

	list($discord_text_channel, $discord_vocal_channel) = explode(",", $location["loc_channel"]);
	
	$discord_text_link = @$discord_text_channels[$discord_text_channel];
	$discord_vocal_link = @$discord_vocal_channels[$discord_vocal_channel];
	
	$location["loc_extra"] = "Texte : <a href='$discord_text_link' target='_blank'>$discord_text_channel</a> ";
	$location["loc_extra"] .= "Voix : <a href='$discord_vocal_link' target='_blank'>$discord_vocal_channel</a>";
}

$altBody = str_replace("{location_extra}", $location["loc_extra"], $altBody);

//echo $altBody;


foreach($notices as $notice) {
	if ($notice["not_noticed"] == 1) continue;

	foreach($config["modules"]["groupsources"] as $groupSourceKey) {
		$groupSource = GroupSourceFactory::getInstance($groupSourceKey);

    	if ($groupSource->getGroupKey() != $notice["not_target_type"]) continue;
    	
    	$empty = array();
    	
    	$groupSource->updateNotice($meeting, $notice, $empty, $empty);

//		print_r($notice);

		if ($notice["not_contact_type"] == "discourse_category" || $notice["not_contact_type"] == "discourse_category") {
			notify($notice["not_contact_type"], 
						array($notice["not_contact"]), 
						array("templates/meeting_notification/notification_subject.php", "templates/meeting_notification/notification_message.php"), 
						array("meeting_label" => $meeting["mee_label"], "meeting_date" => "$meeting_date", "message" => $altBody));
		}

	}

//	$notice["not_noticed"] = "1";
//	$noticeBo->save($notice);
}

addEvent($meetingId, EVENT_NOTICE_SENT, "Convocation envoyée");
$data["ok"] = "ok";

$memcache->delete($memcacheKey);

echo json_encode($data, JSON_NUMERIC_CHECK);
?>
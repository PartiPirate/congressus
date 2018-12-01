<?php /*
	Copyright 2015 Cédric Levieux, Parti Pirate

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
    along with Congressus.  If age, see <http://www.gnu.org/licenses/>.
*/

if (!isset($api)) exit();

include_once("config/database.php");
include_once("config/memcache.php");
require_once("engine/utils/SessionUtils.php");
require_once("engine/bo/MeetingBo.php");
require_once("engine/bo/MeetingRightBo.php");
require_once("engine/bo/AgendaBo.php");
require_once("engine/utils/DateTimeUtils.php");

$meetingId = intval($_REQUEST["id"]);
$memcacheKey = "do_getAgenda_$meetingId";

$memcache = openMemcacheConnection();
$json = $memcache->get($memcacheKey);

if (!$json) {
	$connection = openConnection();

	$meetingBo = MeetingBo::newInstance($connection, $config);
	$meetingRightBo = MeetingRightBo::newInstance($connection, $config);
	$agendaBo = AgendaBo::newInstance($connection, $config);

	$meeting = $meetingBo->getById($meetingId, true);

	if (!$meeting) {
		echo json_encode(array("ko" => "ko", "message" => "meeting_does_not_exist"));
	}

	// TODO Compute the key // Verify the key

	if (false) {
		echo json_encode(array("ko" => "ko", "message" => "meeting_not_accessible"));
	}

//	print_r($meeting);

	$data = array();

	$agendas = $agendaBo->getByFilters(array("age_meeting_id" => $meeting[$meetingBo->ID_FIELD], "with_count_motions" => true));

	$end = getDateTime($meeting["mee_datetime"]);
	$duration = new DateInterval("PT" . ($meeting["mee_expected_duration"] ? $meeting["mee_expected_duration"] : 60) . "M");
	$meeting["mee_end_datetime"] = $end->add($duration);
	$meeting["mee_end_datetime"] = $meeting["mee_end_datetime"]->format("Y-m-d H:i:s");

	$meeting["mee_rights"] = array();
	
//	error_log("Meeting id : $meetingId");
//	error_log("User : $meetingId");

//	error_log("Meeting : " . print_r($meeting, true));
	
	$rights = $meetingRightBo->getByFilters(array("mri_meeting_id" => $meeting[$meetingBo->ID_FIELD]));
	foreach($rights as $right) {
		$meeting["mee_rights"][] = $right["mri_right"];
	}

	if ($meeting["loc_type"] == "discord") {
		include("config/discord.structure.php");

		list($discord_text_channel, $discord_vocal_channel) = explode(",", $meeting["loc_channel"]);

		$discord_text_link = @$discord_text_channels[$discord_text_channel];
		$discord_vocal_link = @$discord_vocal_channels[$discord_vocal_channel];

		$meeting["loc_discord_text_channel"]	= $discord_text_channel;
		$meeting["loc_discord_vocal_channel"]	= $discord_vocal_channel;
		$meeting["loc_discord_text_link"]		= $discord_text_link;
		$meeting["loc_discord_vocal_link"]		= $discord_vocal_link;
	}

	$data["meeting"] = $meeting;
	$data["agendas"] = $agendas;

	$data["ok"] = "ok";

	$json = json_encode($data, JSON_NUMERIC_CHECK);

	if (!$memcache->replace($memcacheKey, $json, MEMCACHE_COMPRESSED, 5)) {
		$memcache->set($memcacheKey, $json, MEMCACHE_COMPRESSED, 5);
	}
}
else {
	$data = json_decode($json, true);
	$data["cached"] = true;
}

echo json_encode($data, JSON_NUMERIC_CHECK);
?>
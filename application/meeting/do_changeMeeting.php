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
require_once("engine/utils/SessionUtils.php");
require_once("engine/bo/MeetingBo.php");
require_once("engine/bo/LocationBo.php");
require_once("engine/utils/DateTimeUtils.php");

require_once("engine/utils/LogUtils.php");
addLog($_SERVER, $_SESSION, null, $_POST);

$connection = openConnection();
$userId = SessionUtils::getUserId($_SESSION);

$meetingBo = MeetingBo::newInstance($connection, $config);
$locationBo = LocationBo::newInstance($connection, $config);

$meeting = $meetingBo->getById($_REQUEST["meetingId"]);
$meetingId = $meeting["mee_id"];

if (!$meeting) {
	echo json_encode(array("ko" => "ko", "message" => "meeting_does_not_exist"));
	exit();
}

// TODO Compute the key // Verify the key

if (false) {
	echo json_encode(array("ko" => "ko", "message" => "meeting_not_accessible"));
	exit();
}

if (($userId != $meeting["mee_president_member_id"]) && ($userId != $meeting["mee_secretary_member_id"])) {
	echo json_encode(array("ko" => "ko", "message" => "meeting_not_accessible"));
	exit();
}

if ($_REQUEST["property"] == "mee_location") {
    $location = $locationBo->getByFilters(array("loc_meeting_id" => $meetingId));
    if (count($location)) {
        $text = json_decode($_REQUEST["text"], true);

        $location = $location[0];
        $location["loc_type"] = $text["type"];
        $location["loc_channel"] = $text["channel"];
        $location["loc_extra"] = $text["extra"];

        $locationBo->save($location);
    }
}
else {

    if ($_REQUEST["property"] == "mee_expecting_end_time") {
        $startTime = getDateTime($meeting["mee_datetime"]);
        $endTime = getDateTime($_REQUEST["text"]);
        
        $expectedDuration = round(($endTime->getTimestamp() -  $startTime->getTimestamp()) / 60);
        $meeting["mee_expected_duration"] = $expectedDuration;
    }
    else {
        $meeting[$_REQUEST["property"]] = $_REQUEST["text"];
    }
    
    $date = getNow();
    
    if ($_REQUEST["property"] == "mee_status" && $_REQUEST["text"] == "open") {
    	$meeting["mee_start_time"] = $date->format("Y-m-d H:i:s");
    }
    else if ($_REQUEST["property"] == "mee_status" && $_REQUEST["text"] == "closed") {
    	$meeting["mee_finish_time"] = $date->format("Y-m-d H:i:s");
    }
    
    $meetingBo->save($meeting);
}

$memcacheKey = "do_getAgenda_$meetingId";
$memcache = openMemcacheConnection();
$memcache->delete($memcacheKey);

$data["ok"] = "ok";

echo json_encode($data, JSON_NUMERIC_CHECK);
?>

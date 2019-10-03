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
    along with Congressus.  If not, see <https://www.gnu.org/licenses/>.
*/

if (!isset($api)) exit();

include_once("config/database.php");
include_once("config/memcache.php");
require_once("engine/utils/SessionUtils.php");
require_once("engine/bo/MeetingBo.php");
require_once("engine/bo/PingBo.php");
require_once("engine/utils/EventStackUtils.php");
require_once("engine/utils/DateTimeUtils.php");

require_once("engine/utils/LogUtils.php");
addLog($_SERVER, $_SESSION, null, $_POST);

$connection = openConnection();

$meetingBo = MeetingBo::newInstance($connection, $config);
$pingBo = PingBo::newInstance($connection, $config);

$meetingId = $_REQUEST["id"];
$meeting = $meetingBo->getById($meetingId);

if (!$meeting) {
	echo json_encode(array("ko" => "ko", "message" => "meeting_does_not_exist"));
}

// TODO Compute the key // Verify the key

if (false) {
	echo json_encode(array("ko" => "ko", "message" => "meeting_not_accessible"));
}

$userId = SessionUtils::getUserId($_SESSION);
$guestId = null;

$ping = array("pin_meeting_id" => $meeting[$meetingBo->ID_FIELD]);

if (!$userId) {
	$ping["pin_guest_id"] = $_SESSION["guestId"];
	$ping["pin_nickname"] = $_SESSION["guestNickname"];
}
else {
	$ping["pin_member_id"] = $userId;
}

$now = getNow();

$ping["pin_datetime"] = $now->format("Y-m-d H:i:s");

$previousPings = $pingBo->getByFilters($ping);

//error_log("Number of pings : " . count($previousPings));

//print_r($previousPings);

if (count($previousPings)) {
	$previousPing = $previousPings[0];
	$ping[$pingBo->ID_FIELD] = $previousPing[$pingBo->ID_FIELD];
	
	$now = getNow();
	$lastPing = getDateTime($previousPing["pin_datetime"]);
	
	$diff = $now->getTimestamp() -  $lastPing->getTimestamp();
	
	if ($diff > 60) {
		addEvent($meetingId, EVENT_JOIN, "", array("userId" => $userId ? $userId : "G" . $_SESSION["guestId"]));
	}
}
else {
	// first time
	addEvent($meetingId, EVENT_JOIN, "", array("userId" => $userId ? $userId : "G" . $_SESSION["guestId"]));
}

$pingBo->save($ping);

$data["ping"] = $ping;
$data["ok"] = "ok";

echo json_encode($data, JSON_NUMERIC_CHECK);
?>
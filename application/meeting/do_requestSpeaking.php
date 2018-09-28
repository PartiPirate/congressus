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

require_once("config/database.php");
require_once("config/memcache.php");
require_once("engine/utils/SessionUtils.php");
require_once("engine/bo/MeetingBo.php");
require_once("engine/bo/PingBo.php");
require_once("engine/utils/EventStackUtils.php");
require_once("engine/utils/DateTimeUtils.php");

require_once("engine/utils/LogUtils.php");
addLog($_SERVER, $_SESSION, null, $_POST);

$meetingId = $_REQUEST["id"];
$memcacheKey = "do_getPeople_$meetingId";

$memcache = openMemcacheConnection();

$connection = openConnection();

$meetingBo = MeetingBo::newInstance($connection, $config);
$pingBo = PingBo::newInstance($connection, $config);

$meeting = $meetingBo->getById($_REQUEST["id"]);

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
	$pingUserId = "G" . $_SESSION["guestId"];
}
else {
	$ping["pin_member_id"] = $userId;
	$pingUserId = $userId;
}

$now = getNow();
$ping["pin_datetime"] = $now->format("Y-m-d H:i:s");

$previousPings = $pingBo->getByFilters($ping);

//error_log("Number of pings : " . count($previousPings));

if (count($previousPings)) {
	$ping[$pingBo->ID_FIELD] = $previousPings[0][$pingBo->ID_FIELD];
	$ping["pin_speaking_request"] = $previousPings[0]["pin_speaking_request"];
}

if ($ping["pin_speaking_request"]) {
	$ping["pin_speaking_request"] = 0;
	addEvent($meetingId, EVENT_SPEAK_RENOUNCE, "", array("userId" => $pingUserId));
}
else {
	$ping["pin_speaking_request"] = time();
	addEvent($meetingId, EVENT_SPEAK_REQUEST, "", array("userId" => $pingUserId));
}

$pingBo->save($ping);

$memcache->delete($memcacheKey);

$data["ok"] = "ok";

echo json_encode($data, JSON_NUMERIC_CHECK);
?>
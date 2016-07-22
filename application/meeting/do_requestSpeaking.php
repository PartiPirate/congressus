<?php /*
	Copyright 2014 Cédric Levieux, Jérémy Collot, ArmagNet

	This file is part of OpenTweetBar.

    OpenTweetBar is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    OpenTweetBar is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with OpenTweetBar.  If not, see <http://www.gnu.org/licenses/>.
*/
session_start();

$path = "../";
set_include_path(get_include_path() . PATH_SEPARATOR . $path);

include_once("config/database.php");
include_once("config/memcache.php");
require_once("engine/utils/SessionUtils.php");
require_once("engine/bo/MeetingBo.php");
require_once("engine/bo/PingBo.php");
require_once("engine/utils/EventStackUtils.php");

$meetingId = $_REQUEST["id"];
$memcacheKey = "do_getPeople_$meetingId";

$memcache = openMemcacheConnection();

$connection = openConnection();

$meetingBo = MeetingBo::newInstance($connection);
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

$now = new DateTime();
$ping["pin_datetime"] = $now->format("Y-m-d H:i:s");

$previousPings = $pingBo->getByFilters($ping);

//error_log("Number of pings : " . count($previousPings));

if (count($previousPings)) {
	$ping[$pingBo->ID_FIELD] = $previousPings[0][$pingBo->ID_FIELD];
	$ping["pin_speaking_request"] = $previousPings[0]["pin_speaking_request"];
}

if ($ping["pin_speaking_request"]) {
	$ping["pin_speaking_request"] = 0;
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
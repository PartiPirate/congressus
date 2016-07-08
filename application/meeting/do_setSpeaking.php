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

$meetingId = $_REQUEST["meetingId"];
$memcacheKey = "do_getPeople_$meetingId";

$memcache = openMemcacheConnection();

$connection = openConnection();

$meetingBo = MeetingBo::newInstance($connection);
$pingBo = PingBo::newInstance($connection, $config);

$meeting = $meetingBo->getById($_REQUEST["meetingId"]);

if (!$meeting) {
	echo json_encode(array("ko" => "ko", "message" => "meeting_does_not_exist"));
	exit();
}

// TODO Compute the key // Verify the key

if (false) {
	echo json_encode(array("ko" => "ko", "message" => "meeting_not_accessible"));
	exit();
}

$userId = $_REQUEST["userId"];

$pings = $pingBo->getByFilters(array("pin_meeting_id" => $meeting[$meetingBo->ID_FIELD], "pin_speaking" => 1));
foreach ($pings as $ping) {
	$myping = array($pingBo->ID_FIELD => $ping[$pingBo->ID_FIELD]);
	$myping["pin_speaking"] = 0;
	$pingBo->save($myping);
}

$filters = array("pin_meeting_id" => $meeting[$meetingBo->ID_FIELD]);

if (substr($userId, 0, 1) == "G") {
	$filters["pin_guest_id"] = substr($userId, 1);
}
else {
	$filters["pin_member_id"] = $userId;
}

$pings = $pingBo->getByFilters($filters);
foreach ($pings as $ping) {
	$myping = array($pingBo->ID_FIELD => $ping[$pingBo->ID_FIELD]);
	$myping["pin_speaking"] = 1;
	$myping["pin_speaking_request"] = 0;

	$pingBo->save($myping);
}

$memcache->delete($memcacheKey);

$data["ok"] = "ok";

echo json_encode($data, JSON_NUMERIC_CHECK);
?>
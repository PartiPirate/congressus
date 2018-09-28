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

require_once("engine/utils/LogUtils.php");
addLog($_SERVER, $_SESSION, null, $_POST);

$meetingId = $_REQUEST["meetingId"];

$memcache = openMemcacheConnection();

$connection = openConnection();

$meetingBo = MeetingBo::newInstance($connection, $config);
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

//print_r($meeting);

$meetingId = $meeting[$meetingBo->ID_FIELD];
$memcacheKey = "do_getPeople_$meetingId";

$pings = $pingBo->getByFilters(array("pin_meeting_id" => $meetingId, "pin_speaking" => 1));

//print_r($pings);

foreach ($pings as $ping) {
	
//	print_r($ping);
    if (SessionUtils::getUserId($_SESSION) == $meeting["mee_president_member_id"]) {
    }
    else if (SessionUtils::getUserId($_SESSION) == $meeting["mee_secretary_member_id"]) {
    }
    else if (SessionUtils::getUserId($_SESSION) == $ping["pin_member_id"]) {
    }
    else if (isset($_SESSION["guestId"]) && ($_SESSION["guestId"] == $ping["pin_member_id"])) {
    }
    else {
    	echo json_encode(array("ko" => "ko", "message" => "president_or_actor_only"));
    	exit();
    }

	$myping = array($pingBo->ID_FIELD => $ping[$pingBo->ID_FIELD]);
	$myping["pin_speaking_time"] = $ping["pin_speaking_time"] + intval($_REQUEST["speakingTime"]);
	$myping["pin_speaking"] = 0;

	$pingBo->save($myping);
}

$memcache->delete($memcacheKey);

$data["ok"] = "ok";

echo json_encode($data, JSON_NUMERIC_CHECK);
?>
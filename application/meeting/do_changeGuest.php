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

require_once("engine/utils/LogUtils.php");
addLog($_SERVER, $_SESSION, null, $_POST);

$meetingId = $_REQUEST["meetingId"];
$memcacheKey = "do_getAgenda_$meetingId";

$memcache = openMemcacheConnection();

$connection = openConnection();

$meetingBo = MeetingBo::newInstance($connection, $config);
$pingBo = PingBo::newInstance($connection, $config);

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

$ping = $pingBo->getByFilters(array("pin_meeting_id" => $meeting[$meetingBo->ID_FIELD], "pin_guest_id" => $_SESSION["guestId"]));

// Set default "guest" nickname if text is empty 
if (!$_REQUEST["text"]) {
	$_REQUEST["text"] = "Guest";
}

if (count($ping)) {
	$ping = array($pingBo->ID_FIELD => $ping[0][$pingBo->ID_FIELD]);
	$ping["pin_nickname"] = $_REQUEST["text"];
	$_SESSION["guestNickname"] = $_REQUEST["text"];

	$pingBo->save($ping);
}

$memcache->delete($memcacheKey);

$data["ok"] = "ok";

echo json_encode($data, JSON_NUMERIC_CHECK);
?>
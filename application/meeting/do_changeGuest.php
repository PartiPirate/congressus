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
require_once("engine/utils/SessionUtils.php");
require_once("engine/bo/MeetingBo.php");
require_once("engine/bo/PingBo.php");

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

$ping = $pingBo->getByFilters(array("pin_meeting_id" => $meeting[$meetingBo->ID_FIELD], "pin_guest_id" => $_SESSION["guestId"]));

if (count($ping)) {
	$ping = array($pingBo->ID_FIELD => $ping[0][$pingBo->ID_FIELD]);
	$ping["pin_nickname"] = $_REQUEST["text"];
	$_SESSION["guestNickname"] = $_REQUEST["text"];

	$pingBo->save($ping);
}

$data["ok"] = "ok";

echo json_encode($data, JSON_NUMERIC_CHECK);
?>
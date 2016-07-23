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

$connection = openConnection();

$meetingBo = MeetingBo::newInstance($connection);

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

$meeting[$_REQUEST["property"]] = $_REQUEST["text"];

// TODO pb timezone
$date = new DateTime();
//$date->setTimezone('Europe/Paris');
$date->add(new DateInterval('PT2H'));

if ($_REQUEST["property"] == "mee_status" && $_REQUEST["text"] == "open") {
	$meeting["mee_start_time"] = $date->format("Y-m-d H:i:s");
}
else if ($_REQUEST["property"] == "mee_status" && $_REQUEST["text"] == "closed") {
	$meeting["mee_finish_time"] = $date->format("Y-m-d H:i:s");
}

$meetingBo->save($meeting);

$data["ok"] = "ok";

echo json_encode($data, JSON_NUMERIC_CHECK);
?>
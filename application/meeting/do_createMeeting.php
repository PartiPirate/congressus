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
require_once("engine/bo/LocationBo.php");
require_once("engine/bo/MeetingBo.php");

$userId = SessionUtils::getUserId($_SESSION);
if (!$userId) {
	exit();
}

$connection = openConnection();

$locationBo = LocationBo::newInstance($connection, $config);
$meetingBo = MeetingBo::newInstance($connection);

$meeting = array();
$meeting["mee_label"] = $_REQUEST["mee_label"];
$meeting["mee_class"] = $_REQUEST["mee_class"];
$meeting["mee_secretary_member_id"] = $userId;
$meeting["mee_meeting_type_id"] = $_REQUEST["mee_meeting_type_id"];
$meeting["mee_datetime"] = $_REQUEST["mee_date"] . ' ' . $_REQUEST["mee_time"];
$meeting["mee_expected_duration"] = $_REQUEST["mee_expected_duration"];

$meetingBo->save($meeting);

$data["ok"] = "ok";
$data["meeting"] = $meeting;

$location = array();
$location["loc_meeting_id"] = $meeting[$meetingBo->ID_FIELD];
$location["loc_type"] = $_REQUEST["loc_type"];
$location["loc_extra"] = $_REQUEST["loc_extra"];
$location["loc_principal"] = 1;

$locationBo->save($location);

if (isset($_REQUEST["ajax"])) {
	echo json_encode($data, JSON_NUMERIC_CHECK);
}
else {
	header("Location: ../meeting.php?id=" . $meeting[$meetingBo->ID_FIELD]);
}
?>
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
require_once("engine/bo/AgendaBo.php");
require_once("engine/bo/MeetingBo.php");
require_once("engine/bo/MotionBo.php");
require_once("engine/utils/EventStackUtils.php");

$memcache = openMemcacheConnection();

$connection = openConnection();

$agendaBo = AgendaBo::newInstance($connection);
$meetingBo = MeetingBo::newInstance($connection);
$motionBo = MotionBo::newInstance($connection);

$meetingId = $_REQUEST["meetingId"];
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

$pointId = $_REQUEST["pointId"];
$agenda = $agendaBo->getById($pointId);

if (!$agenda || $agenda["age_meeting_id"] != $meeting[$meetingBo->ID_FIELD]) {
	echo json_encode(array("ko" => "ko", "message" => "agenda_point_not_accessible"));
	exit();
}

$motion = $motionBo->getById($_REQUEST["motionId"]);

if (!$motion || $motion["mot_agenda_id"] != $agenda[$agendaBo->ID_FIELD]) {
	echo json_encode(array("ko" => "ko", "message" => "motion_not_accessible"));
	exit();
}

$motionTitle = $motion["mot_title"];

$motion = array($motionBo->ID_FIELD => $motion[$motionBo->ID_FIELD]);
$motion["mot_deleted"] = 1;

$motionBo->save($motion);

$agenda["age_objects"] = json_decode($agenda["age_objects"]);
$newObjects = array();
foreach($agenda["age_objects"] as $index => $object) {
	if (!isset($object->motionId) || $object->motionId != $motion[$motionBo->ID_FIELD]) {
//		unset($agenda["age_objects"][$index]);
//		break;
		$newObjects[] = $object;
	}
}

$agenda["age_objects"] = json_encode($newObjects);

$agendaBo->save($agenda);

$data["ok"] = "ok";

$memcacheKey = "do_getAgendaPoint_$pointId";
$memcache->delete($memcacheKey);

addEvent($meetingId, EVENT_MOTION_REMOVE, "La motion \"$motionTitle\" a été retiré du point \"".$agenda["age_label"]."\"");

$data["event"] = "La motion \"$motionTitle\" a été retiré du point \"".$agenda["age_label"]."\"";

echo json_encode($data, JSON_NUMERIC_CHECK);
?>
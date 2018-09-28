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
    along with Congressus.  If not, see <http://www.gnu.org/licenses/>.
*/

if (!isset($api)) exit();

require_once("config/database.php");
require_once("config/memcache.php");
require_once("engine/utils/SessionUtils.php");
require_once("engine/bo/AgendaBo.php");
require_once("engine/bo/ChatBo.php");
require_once("engine/bo/MeetingBo.php");
require_once("engine/bo/MotionBo.php");
require_once("engine/bo/VoteBo.php");
require_once("engine/utils/EventStackUtils.php");

require_once("engine/utils/LogUtils.php");
addLog($_SERVER, $_SESSION, null, $_POST);

$memcache = openMemcacheConnection();

$connection = openConnection();

$agendaBo = AgendaBo::newInstance($connection, $config);
$meetingBo = MeetingBo::newInstance($connection, $config);
$motionBo = MotionBo::newInstance($connection, $config);

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

$motionWinLimit = $motion["mot_win_limit"];
$motionTitle = $motion["mot_title"];

$motion = array($motionBo->ID_FIELD => $motion[$motionBo->ID_FIELD]);
$motion["mot_status"] = $_REQUEST["status"];

$motionBo->save($motion);

if ($motion["mot_status"] == "voting") {
	// add, if necessary the NSPP vote
	if ($motionWinLimit >= 0) {
		$proposition = array("mpr_motion_id" => $motion[$motionBo->ID_FIELD]);
		$proposition["mpr_label"] = "NSPP";
		$proposition["mpr_neutral"] = 1;
	
		$motionBo->saveProposition($proposition);
	}

	addEvent($meetingId, EVENT_MOTION_TO_VOTE, "La motion \"".$motionTitle."\" dans le point \"".$agenda["age_label"]."\" est mise au vote");
}
else if ($motion["mot_status"] == "resolved") {
	addEvent($meetingId, EVENT_MOTION_CLOSE_VOTE, "La motion \"".$motionTitle."\" dans le point \"".$agenda["age_label"]."\" est fermée au vote");
	// TODO compute result
}

$data["ok"] = "ok";

$memcacheKey = "do_getAgendaPoint_$pointId";
$memcache->delete($memcacheKey);

echo json_encode($data, JSON_NUMERIC_CHECK);
?>
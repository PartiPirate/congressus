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

include_once("config/database.php");
include_once("config/memcache.php");
require_once("engine/utils/SessionUtils.php");
require_once("engine/bo/AgendaBo.php");
require_once("engine/bo/ChatBo.php");
require_once("engine/bo/MeetingBo.php");
require_once("engine/bo/MotionBo.php");
require_once("engine/bo/VoteBo.php");
require_once("engine/utils/EventStackUtils.php");

if (!SessionUtils::getUserId($_SESSION)) {
	echo json_encode(array("ko" => "ko", "message" => "must_be_connected"));
	exit();
}

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

$userId = SessionUtils::getUserId($_SESSION);

$pointId = $_REQUEST["pointId"];
$agenda = $agendaBo->getById($pointId);

if (!$agenda || $agenda["age_meeting_id"] != $meeting[$meetingBo->ID_FIELD]) {
	echo json_encode(array("ko" => "ko", "message" => "agenda_point_not_accessible"));
	exit();
}

$agenda["age_objects"] = json_decode($agenda["age_objects"]);

$motion = array();
$motion["mot_agenda_id"] = $agenda[$agendaBo->ID_FIELD];
$motion["mot_title"] = isset($_REQUEST["startingText"]) ? $_REQUEST["startingText"] : "Titre de la motion";
$motion["mot_description"] = isset($_REQUEST["description"]) ? $_REQUEST["description"] : "Description de la motion";
$motion["mot_type"] = "yes_no";
$motion["mot_status"] = "construction";
$motion["mot_deleted"] = "0";
$motion["mot_author_id"] = $userId;

$motionBo->save($motion);

$agenda["age_objects"][] = array("motionId" => $motion[$motionBo->ID_FIELD]);
$agenda["age_objects"] = json_encode($agenda["age_objects"]);

$agendaBo->save($agenda);

$data["ok"] = "ok";
$data["motion"] = $motion;

if ($gamifierClient) {
    $events = array();
    $events[] = createGameEvent($userId, GameEvents::CREATE_MOTION);

    $addEventsResult = $gamifierClient->addEvents($events);
    
    $data["gamifiedUser"] = $addEventsResult;
}

$memcacheKey = "do_getAgendaPoint_$pointId";
$memcache->delete($memcacheKey);

addEvent($meetingId, EVENT_MOTION_ADD, "Une nouvelle motion dans le point \"".$agenda["age_label"]."\"");

echo json_encode($data, JSON_NUMERIC_CHECK);
?>
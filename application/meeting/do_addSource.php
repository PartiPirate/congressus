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
    along with Congressus.  If not, see <https://www.gnu.org/licenses/>.
*/

if (!isset($api)) exit();

include_once("config/database.php");
include_once("config/memcache.php");
require_once("engine/utils/SessionUtils.php");
require_once("engine/bo/AgendaBo.php");
require_once("engine/bo/ChatBo.php");
require_once("engine/bo/MeetingBo.php");
require_once("engine/bo/MotionBo.php");
require_once("engine/bo/SourceBo.php");
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
$sourceBo = SourceBo::newInstance($connection, $config);

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

// Get motion
$motion = $motionBo->getById($_REQUEST["motionId"]);
if (!$motion) {
	echo json_encode(array("ko" => "ko", "message" => "motion_does_not_exist"));
	exit();
}

$source = array();
$source["sou_title"] = $_REQUEST["sourceTitle"];
$source["sou_is_default_source"] = 0;
$source["sou_url"] = $_REQUEST["sourceUrl"];
if (isset($_REQUEST["sourceArticles"])) {
    $source["sou_articles"] = json_encode($_REQUEST["sourceArticles"]);
}
else {
    $source["sou_articles"] = "[]";
}
$source["sou_content"] = $_REQUEST["sourceContent"];
$source["sou_type"] = $_REQUEST["sourceType"];
$source["sou_motion_id"] = $motion["mot_id"];

$sourceBo->save($source);

// Add it to the agenda
$agenda["age_objects"][] = array("sourceId" => $source[$sourceBo->ID_FIELD]);

$agenda["age_objects"] = json_encode($agenda["age_objects"]);

$agendaBo->save($agenda);

$data["ok"] = "ok";

if ($gamifierClient) {
    $events = array();

    $addEventsResult = $gamifierClient->addEvents($events);
    
    $data["gamifiedUser"] = $addEventsResult;
}

$memcacheKey = "do_getAgendaPoint_$pointId";
$memcache->delete($memcacheKey);

echo json_encode($data, JSON_NUMERIC_CHECK);
?>
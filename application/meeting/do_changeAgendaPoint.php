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
require_once("engine/bo/ChatAdviceBo.php");
require_once("engine/bo/ConclusionBo.php");
require_once("engine/bo/MeetingBo.php");
require_once("engine/bo/MotionBo.php");
require_once("engine/bo/TaskBo.php");
require_once("engine/bo/VoteBo.php");
require_once("engine/utils/EventStackUtils.php");
require_once("engine/utils/MeetingAPI.php");

if (!SessionUtils::getUserId($_SESSION)) {
	echo json_encode(array("ko" => "ko", "message" => "must_be_connected"));
	exit();
}

require_once("engine/utils/LogUtils.php");
addLog($_SERVER, $_SESSION, null, $_POST);

$meetingId = $_REQUEST["meetingId"];
$memcacheKey = "do_getAgenda_$meetingId";

$memcache = openMemcacheConnection();

$connection = openConnection();

$meetingBo = MeetingBo::newInstance($connection, $config);
$agendaBo = AgendaBo::newInstance($connection, $config);

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

$agenda = $agendaBo->getById($_REQUEST["pointId"]);

if (!$agenda || $agenda["age_meeting_id"] != $meeting[$meetingBo->ID_FIELD]) {
	echo json_encode(array("ko" => "ko", "message" => "agenda_point_not_accessible"));
	exit();
}

//print_r($agenda);

if ($_REQUEST["property"] == "order") {
    $object = json_decode($_REQUEST["text"], true);
    $agenda["age_order"] = $object["age_order"];
    $agenda["age_parent_id"] = $object["age_parent_id"];
    
    if (!$agenda["age_order"]) $agenda["age_order"] = time();
    if (!$agenda["age_parent_id"] || $agenda["age_parent_id"] == "null") $agenda["age_parent_id"] = null;
}
else {
    $agenda[$_REQUEST["property"]] = $_REQUEST["text"];
}

//print_r($agenda);

$agendaBo->save($agenda);

$memcache->delete($memcacheKey);

$api = new MeetingAPI($connection, $config);
$userId = SessionUtils::getUserId($_SESSION);
$requestId = -1;

$response = $api->getAgendaPoint($meetingId, $agenda["age_id"], $userId, $requestId);

$data["ok"] = "ok";

echo json_encode($response, JSON_NUMERIC_CHECK);
?>
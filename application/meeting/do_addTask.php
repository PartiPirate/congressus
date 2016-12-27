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
require_once("engine/bo/TaskBo.php");
require_once("engine/bo/MeetingBo.php");
require_once("engine/utils/DateTimeUtils.php");

if (!SessionUtils::getUserId($_SESSION)) {
	echo json_encode(array("ko" => "ko", "message" => "must_be_connected"));
	exit();
}

require_once("engine/utils/LogUtils.php");
addLog($_SERVER, $_SESSION, null, $_POST);

$memcache = openMemcacheConnection();

$connection = openConnection();

$agendaBo = AgendaBo::newInstance($connection);
$taskBo = TaskBo::newInstance($connection, $config);
$meetingBo = MeetingBo::newInstance($connection);

$meeting = $meetingBo->getById($_REQUEST["id"]);

if (!$meeting) {
	echo json_encode(array("ko" => "ko", "message" => "meeting_does_not_exist"));
}

// TODO Compute the key // Verify the key

if (false) {
	echo json_encode(array("ko" => "ko", "message" => "meeting_not_accessible"));
}

$pointId = $_REQUEST["pointId"];
$agenda = $agendaBo->getById($pointId);

if (!$agenda || $agenda["age_meeting_id"] != $meeting[$meetingBo->ID_FIELD]) {
	echo json_encode(array("ko" => "ko", "message" => "agenda_point_not_accessible"));
}

$agenda["age_objects"] = json_decode($agenda["age_objects"]);

$data = array();

$task = array();
$task["tas_agenda_id"] = $agenda[$agendaBo->ID_FIELD];
$task["tas_label"] = "";

$now = getNow();
$task["tas_start_datetime"] = $now->format("Y-m-d H:i:s");

$targetId = $_REQUEST["targetId"];
$targetType = $_REQUEST["targetType"];

$task["tas_target_id"] = $targetId;
$task["tas_target_type"] = $targetType;

$taskBo->save($task);
$task = $taskBo->getById($task[$taskBo->ID_FIELD]);

// $task["mem_id"] = $task["id_adh"] ? $task["id_adh"] : "G" . $task["chat_guest_id"];
// $task["mem_nickname"] = $task["pin_nickname"] ? $task["pin_nickname"] : $task["pseudo_adh"];

foreach($task as $key => $value) {
	if (substr($key, 0, 4) != "tas_" && substr($key, 0, 4) != "mem_") {
		unset($task[$key]);
	}
}

$data["ok"] = "ok";
$data["task"] = $task;

$agenda["age_objects"][] = array("taskId" => $task[$taskBo->ID_FIELD]);
$agenda["age_objects"] = json_encode($agenda["age_objects"]);

$agendaBo->save($agenda);

$memcacheKey = "do_getAgendaPoint_$pointId";
$memcache->delete($memcacheKey);

echo json_encode($data, JSON_NUMERIC_CHECK);
?>
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
require_once("engine/bo/TaskBo.php");
require_once("engine/bo/MeetingBo.php");
require_once("engine/utils/DateTimeUtils.php");
require_once("engine/bo/NoticeBo.php");
require_once("engine/bo/PingBo.php");
require_once("engine/bo/GroupBo.php");
require_once("engine/bo/ThemeBo.php");
require_once("engine/utils/MeetingAPI.php");
require_once("engine/utils/QuorumUtils.php");


if (!SessionUtils::getUserId($_SESSION)) {
	echo json_encode(array("ko" => "ko", "message" => "must_be_connected"));
	exit();
}

require_once("engine/utils/LogUtils.php");
addLog($_SERVER, $_SESSION, null, $_POST);

$memcache = openMemcacheConnection();

$connection = openConnection();

$agendaBo = AgendaBo::newInstance($connection, $config);
$taskBo = TaskBo::newInstance($connection, $config);
$meetingBo = MeetingBo::newInstance($connection, $config);
$groupBo = GroupBo::newInstance($connection, $config);
$themeBo = ThemeBo::newInstance($connection, $config);

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
$task["tas_label"] = isset($_REQUEST["startingText"]) ? $_REQUEST["startingText"] : "";

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

$agenda["age_objects"][] = array("taskId" => $task[$taskBo->ID_FIELD]);
$agenda["age_objects"] = json_encode($agenda["age_objects"]);

$agendaBo->save($agenda);

$connectUserId = SessionUtils::getUserId($_SESSION);
$api = new MeetingAPI($connection, $config);
$response = $api->getPeople($meeting["mee_id"], $connectUserId);

$notices = $response["notices"];

$configurations = array();

foreach($notices as $notice) {
    $groups = array();

    if ($notice["not_target_type"] == "dlp_themes") {
        // search parent
        $groups = $groupBo->getGroups(array("the_id" => $notice["not_target_id"]));
    }
    else if ($notice["not_target_type"] == "dlp_groups") {
        $group = $groupBo->getGroup($notice["not_target_id"]);

        $groups[] = $group;
    }

    foreach($groups as $group) {
        // Add the configuration from the group
        if ($group["gro_tasker_type"] && $group["gro_tasker_project_id"]) {
            $configurations[] = array("type" => $group["gro_tasker_type"], "projectId" => $group["gro_tasker_project_id"]);
        }

        foreach($group["gro_themes"] as $theme) {
            // Add the configuration from the theme
            if (isset($theme["the_tasker_type"]) && $theme["the_tasker_type"] && $theme["the_tasker_project_id"]) {
                $configurations[] = array("type" => $theme["the_tasker_type"], "projectId" => $theme["the_tasker_project_id"]);
            }
        }
    }
}

// Handle hooks
if (count($configurations)) {
    $taskInformations = array();

	$directoryHandler = dir("task_hooks/");

	while(($fileEntry = $directoryHandler->read()) !== false) {
		if($fileEntry != '.' && $fileEntry != '..' && strpos($fileEntry, ".php")) {
			require_once("task_hooks/" . $fileEntry);
		}
	}
	$directoryHandler->close();

	foreach($taskHooks as $taskHook) {
        foreach($configurations as $configuration) {
            if ($configuration["type"] == $taskHook->getType()) {
                $taskInformation = array("description" => $task["tas_label"], "subject" => "#" . $task["tas_id"]);
                
                $information = $taskHook->createTask($taskInformation, $configuration["projectId"]);
                $taskInformations[] = $information;
            }
            break;
        }
	}

	$task["tas_informations"] = json_encode($taskInformations);
    $taskBo->save($task);
}
// End of handle hooks

$data["task"] = $task;




$memcacheKey = "do_getAgendaPoint_$pointId";
$memcache->delete($memcacheKey);

echo json_encode($data, JSON_NUMERIC_CHECK);
?>
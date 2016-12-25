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

session_start();

$path = "../";
set_include_path(get_include_path() . PATH_SEPARATOR . $path);

include_once("config/database.php");
include_once("config/memcache.php");
require_once("engine/utils/SessionUtils.php");
require_once("engine/bo/AgendaBo.php");
require_once("engine/bo/TaskBo.php");
require_once("engine/bo/MeetingBo.php");

if (!SessionUtils::getUserId($_SESSION)) {
	echo json_encode(array("ko" => "ko", "message" => "must_be_connected"));
	exit();
}

require_once("engine/utils/LogUtils.php");
addLog($_SERVER, $_SESSION, null, $_POST);

$memcache = openMemcacheConnection();

$connection = openConnection();

//$agendaBo = AgendaBo::newInstance($connection);
$taskBo = TaskBo::newInstance($connection, $config);
//$meetingBo = MeetingBo::newInstance($connection);

$taskId = $_REQUEST["taskId"];

$task = $taskBo->getById($taskId);

$data = array("ok" => "ok");

if ($task) {
	$task = array($taskBo->ID_FIELD => $taskId);
	$task[$_REQUEST["property"]] = $_REQUEST["text"];

// 	if ($_REQUEST["property"] == "tas_member_id") {
// 		$task["tas_guest_id"] = 0;
// 	}
// 	else if ($_REQUEST["property"] == "tas_guest_id") {
// 		$task["tas_member_id"] = 0;
// 	}

	$taskBo->save($task);

	$data["task"] = $task;
	
	$pointId = $task["tas_agenda_id"];
	$memcacheKey = "do_getAgendaPoint_$pointId";
	$memcache->delete($memcacheKey);
}

echo json_encode($data, JSON_NUMERIC_CHECK);
?>
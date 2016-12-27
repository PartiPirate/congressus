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
require_once("engine/bo/MeetingBo.php");

require_once("engine/utils/LogUtils.php");
addLog($_SERVER, $_SESSION, null, $_POST);

$meetingId = $_REQUEST["meetingId"];
$memcacheKey = "do_getAgenda_$meetingId";

$memcache = openMemcacheConnection();

$connection = openConnection();

$meetingBo = MeetingBo::newInstance($connection);
$agendaBo = AgendaBo::newInstance($connection);

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

$data = array();

$agenda = array("age_meeting_id" => $meeting[$meetingBo->ID_FIELD]);
$agenda["age_order"] = time();
$agenda["age_active"] = 0;
$agenda["age_expected_duration"] = 0;
$agenda["age_label"] = "Nouveau point";
$agenda["age_objects"] = "[]";
$agenda["age_description"] = "Pas de description";
if (isset($_REQUEST["parentId"]) && $_REQUEST["parentId"]) {
	// TODO verify if the parent is in the same meeting
	$agenda["age_parent_id"] = $_REQUEST["parentId"];
}

$memcache->delete($memcacheKey);

$agendaBo->save($agenda);

$data["agenda"] = $agenda;
$data["ok"] = "ok";

echo json_encode($data, JSON_NUMERIC_CHECK);
?>
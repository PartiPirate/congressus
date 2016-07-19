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
require_once("engine/bo/ConclusionBo.php");
require_once("engine/bo/MeetingBo.php");

$memcache = openMemcacheConnection();

$connection = openConnection();

$agendaBo = AgendaBo::newInstance($connection);
$conclusionBo = ConclusionBo::newInstance($connection, $config);
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

$conclusion = array();
$conclusion["con_agenda_id"] = $agenda[$agendaBo->ID_FIELD];
$conclusion["con_text"] = "";

$conclusionBo->save($conclusion);
$conclusion = $conclusionBo->getById($conclusion[$conclusionBo->ID_FIELD]);

$data["ok"] = "ok";
$data["conclusion"] = $conclusion;

$agenda["age_objects"][] = array("conclusionId" => $conclusion[$conclusionBo->ID_FIELD]);
$agenda["age_objects"] = json_encode($agenda["age_objects"]);

$agendaBo->save($agenda);

$memcacheKey = "do_getAgendaPoint_$pointId";
$memcache->delete($memcacheKey);

echo json_encode($data, JSON_NUMERIC_CHECK);
?>
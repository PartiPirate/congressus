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
require_once("engine/utils/SessionUtils.php");
require_once("engine/bo/AgendaBo.php");
require_once("engine/bo/ChatBo.php");
require_once("engine/bo/ConclusionBo.php");
require_once("engine/bo/MeetingBo.php");
require_once("engine/bo/MotionBo.php");
require_once("engine/bo/VoteBo.php");

$connection = openConnection();

$agendaBo = AgendaBo::newInstance($connection);
$chatBo = ChatBo::newInstance($connection, $config);
$conclusionBo = ConclusionBo::newInstance($connection, $config);
$meetingBo = MeetingBo::newInstance($connection);
$motionBo = MotionBo::newInstance($connection);
$voteBo = VoteBo::newInstance($connection, $config);

$meeting = $meetingBo->getById($_REQUEST["id"]);

if (!$meeting) {
	echo json_encode(array("ko" => "ko", "message" => "meeting_does_not_exist"));
}

// TODO Compute the key // Verify the key

if (false) {
	echo json_encode(array("ko" => "ko", "message" => "meeting_not_accessible"));
}


$agenda = $agendaBo->getById($_REQUEST["pointId"]);

if (!$agenda || $agenda["age_meeting_id"] != $meeting[$meetingBo->ID_FIELD]) {
	echo json_encode(array("ko" => "ko", "message" => "agenda_point_not_accessible"));
}

$agenda["age_objects"] = json_decode($agenda["age_objects"]);

$data = array();

$data["agenda"] = $agenda;

$data["motions"] = $motionBo->getByFilters(array("mot_agenda_id" => $agenda[$agendaBo->ID_FIELD]));
$data["chats"] = $chatBo->getByFilters(array("cha_agenda_id" => $agenda[$agendaBo->ID_FIELD]));

foreach($data["chats"] as $index => $chat) {
	$data["chats"][$index]["mem_id"] = $chat["id_adh"] ? $chat["id_adh"] : "G" . $chat["cha_guest_id"];
//	$data["chats"][$index]["mem_nickname"] = $chat["pin_nickname"] ? $chat["pin_nickname"] : $chat["pseudo_adh"];
	$data["chats"][$index]["mem_nickname"] = htmlspecialchars(utf8_encode($chat["pin_nickname"] ? $chat["pin_nickname"] : ($chat["pseudo_adh"] ? $chat["pseudo_adh"] : $chat["nom_adh"] . ' ' . $chat["prenom_adh"])), ENT_SUBSTITUTE);

	foreach($chat as $key => $value) {
		if (substr($key, 0, 4) != "cha_" && substr($key, 0, 4) != "mem_") {
			unset($data["chats"][$index][$key]);
		}
	}
}

$data["votes"] = $voteBo->getByFilters(array("mot_agenda_id" => $agenda[$agendaBo->ID_FIELD]));

foreach($data["votes"] as $index => $vote) {
	$data["votes"][$index]["mem_id"] = $vote["id_adh"];
	$data["votes"][$index]["mem_nickname"] = htmlspecialchars(utf8_encode($vote["pseudo_adh"] ? $vote["pseudo_adh"] : $vote["nom_adh"] . ' ' . $vote["prenom_adh"]), ENT_SUBSTITUTE);


	foreach($vote as $key => $value) {
		if (substr($key, 0, 4) != "vot_" && substr($key, 0, 4) != "mpt_" && substr($key, 0, 4) != "mot_" && substr($key, 0, 4) != "mem_") {
			unset($data["votes"][$index][$key]);
		}
	}
}

$data["conclusions"] = $conclusionBo->getByFilters(array("con_agenda_id" => $agenda[$agendaBo->ID_FIELD]));

$data["requestId"] = $_REQUEST["requestId"];
$data["ok"] = "ok";

echo json_encode($data, JSON_NUMERIC_CHECK);
?>
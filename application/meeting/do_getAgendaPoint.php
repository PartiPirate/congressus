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
require_once("engine/bo/ChatAdviceBo.php");
require_once("engine/bo/ConclusionBo.php");
require_once("engine/bo/MeetingBo.php");
require_once("engine/bo/MotionBo.php");
require_once("engine/bo/TaskBo.php");
require_once("engine/bo/VoteBo.php");
require_once("engine/utils/EventStackUtils.php");

$memcache = openMemcacheConnection();

$connection = openConnection();

$agendaBo = AgendaBo::newInstance($connection);
$chatBo = ChatBo::newInstance($connection, $config);
$chatAdviceBo = ChatAdviceBo::newInstance($connection);
$conclusionBo = ConclusionBo::newInstance($connection, $config);
$meetingBo = MeetingBo::newInstance($connection);
$motionBo = MotionBo::newInstance($connection);
$taskBo = TaskBo::newInstance($connection, $config);
$voteBo = VoteBo::newInstance($connection, $config);

$meetingId = $_REQUEST["id"];
$pointId = $_REQUEST["pointId"];

$meeting = $meetingBo->getById($meetingId);

if (!$meeting) {
	echo json_encode(array("ko" => "ko", "message" => "meeting_does_not_exist"));
	exit();
}

if (SessionUtils::getUserId($_SESSION) && SessionUtils::getUserId($_SESSION) == $meeting["mee_secretary_member_id"]) {
	if ($meeting["mee_secretary_agenda_id"] != $_REQUEST["pointId"]) {
		$meeting["mee_secretary_agenda_id"] = $_REQUEST["pointId"];
		$meetingBo->save($meeting);
		
		$memcacheKey = "do_getAgenda_$meetingId";
		$memcache->delete($memcacheKey);
		
		addEvent($meetingId, EVENT_SECRETARY_READS_ANOTHER_POINT, "Le secrétaire de séance vient de changer de point");
	}
}

// TODO Compute the key // Verify the key

if (false) {
	echo json_encode(array("ko" => "ko", "message" => "meeting_not_accessible"));
	exit();
}

$memcacheKey = "do_getAgendaPoint_$pointId";
$json = $memcache->get($memcacheKey);

if (!$json) {
	$agenda = $agendaBo->getById($pointId);
	
	if (!$agenda || $agenda["age_meeting_id"] != $meeting[$meetingBo->ID_FIELD]) {
		echo json_encode(array("ko" => "ko", "message" => "agenda_point_not_accessible"));
	}
	
	$agenda["age_objects"] = json_decode($agenda["age_objects"]);
	
	$data = array();
	
	$data["agenda"] = $agenda;
	
	$data["motions"] = $motionBo->getByFilters(array("mot_agenda_id" => $agenda[$agendaBo->ID_FIELD]));
	$data["chats"] = $chatBo->getByFilters(array("cha_agenda_id" => $agenda[$agendaBo->ID_FIELD]));

	$chatAdvices = $chatAdviceBo->getByFilters(array("cad_agenda_id" => $agenda[$agendaBo->ID_FIELD]));

	foreach($data["chats"] as $index => $chat) {
		$data["chats"][$index]["mem_id"] = $chat["id_adh"] ? $chat["id_adh"] : "G" . $chat["cha_guest_id"];
	//	$data["chats"][$index]["mem_nickname"] = $chat["pin_nickname"] ? $chat["pin_nickname"] : $chat["pseudo_adh"];
		$data["chats"][$index]["mem_nickname"] = htmlspecialchars(utf8_encode($chat["pin_nickname"] ? $chat["pin_nickname"] : ($chat["pseudo_adh"] ? $chat["pseudo_adh"] : $chat["nom_adh"] . ' ' . $chat["prenom_adh"])), ENT_SUBSTITUTE);
	
 		foreach($chat as $key => $value) {
 			if (substr($key, 0, 4) != "cha_" && substr($key, 0, 4) != "mem_") {
 				unset($data["chats"][$index][$key]);
 			}
			
 			$data["chats"][$index]["advices"] = array();
 			foreach($chatAdvices as $advice) {
 				if ($advice["cad_chat_id"] != $chat["cha_id"]) continue;

 				$data["chats"][$index]["advices"][] = $advice;
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

	$data["tasks"] = $taskBo->getByFilters(array("tas_agenda_id" => $agenda[$agendaBo->ID_FIELD]));
	
	$data["conclusions"] = $conclusionBo->getByFilters(array("con_agenda_id" => $agenda[$agendaBo->ID_FIELD]));
	
	$data["requestId"] = $_REQUEST["requestId"];
	$data["ok"] = "ok";
}
else {
	$data = json_decode($json, true);
	$data["cached"] = true;
}

echo json_encode($data, JSON_NUMERIC_CHECK);
?>
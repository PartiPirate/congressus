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
require_once("engine/bo/MeetingBo.php");
require_once("engine/bo/ChatBo.php");

$memcache = openMemcacheConnection();

$connection = openConnection();

$agendaBo = AgendaBo::newInstance($connection);
$meetingBo = MeetingBo::newInstance($connection);
$chatBo = ChatBo::newInstance($connection, $config);

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

$pointId = $_REQUEST["pointId"];
$agenda = $agendaBo->getById($pointId);

if (!$agenda || $agenda["age_meeting_id"] != $meeting[$meetingBo->ID_FIELD]) {
	echo json_encode(array("ko" => "ko", "message" => "agenda_point_not_accessible"));
	exit();
}

$chat = $chatBo->getById($_REQUEST["chatId"]);

if (!$chat || $chat["cha_agenda_id"] != $agenda[$agendaBo->ID_FIELD]) {
	echo json_encode(array("ko" => "ko", "message" => "chat_not_accessible"));
	exit();
}

$chat = array($chatBo->ID_FIELD => $chat[$chatBo->ID_FIELD]);
$chat["cha_deleted"] = 1;

$chatBo->save($chat);

$agenda["age_objects"] = json_decode($agenda["age_objects"]);
$newObjects = array();
foreach($agenda["age_objects"] as $index => $object) {
	if (!isset($object->chatId) || $object->chatId != $chat[$chatBo->ID_FIELD]) {
//		unset($agenda["age_objects"][$index]);
//		break;
		$newObjects[] = $object;
	}
}

$agenda["age_objects"] = json_encode($newObjects);

$agendaBo->save($agenda);

$data["ok"] = "ok";

$memcacheKey = "do_getAgendaPoint_$pointId";
$memcache->delete($memcacheKey);

echo json_encode($data, JSON_NUMERIC_CHECK);
?>
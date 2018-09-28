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

require_once("config/database.php");
require_once("config/memcache.php");
require_once("engine/utils/SessionUtils.php");
require_once("engine/bo/AgendaBo.php");
require_once("engine/bo/ChatBo.php");
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

$agendaBo = AgendaBo::newInstance($connection, $config);
$chatBo = ChatBo::newInstance($connection, $config);
$meetingBo = MeetingBo::newInstance($connection, $config);

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

$chat = array();
$chat["cha_agenda_id"] = $agenda[$agendaBo->ID_FIELD];
$chat["cha_text"] = isset($_REQUEST["startingText"]) ? $_REQUEST["startingText"] : "";
$chat["cha_type"] = isset($_REQUEST["type"]) ? $_REQUEST["type"] : "neutral";

if (isset($_REQUEST["motionId"])) {
	$chat["cha_motion_id"] = intval($_REQUEST["motionId"]);
}

if (isset($_REQUEST["parentId"])) {
	$chat["cha_parent_id"] = intval($_REQUEST["parentId"]);
}

$now = getNow();
$chat["cha_datetime"] = $now->format("Y-m-d H:i:s");

$userId = $_REQUEST["userId"];
if (substr($userId, 0, 1) == "G") {
	$chat["cha_guest_id"] = substr($userId, 1);
}
else {
	$chat["cha_member_id"] = $userId;
}

$chatBo->save($chat);
$chat = $chatBo->getById($chat[$chatBo->ID_FIELD]);

$chat["mem_id"] = $chat["id_adh"] ? $chat["id_adh"] : "G" . $chat["chat_guest_id"];
$chat["mem_nickname"] = $chat["pin_nickname"] ? $chat["pin_nickname"] : $chat["pseudo_adh"];

foreach($chat as $key => $value) {
	if (substr($key, 0, 4) != "cha_" && substr($key, 0, 4) != "mem_") {
		unset($chat[$key]);
	}
}

$data["ok"] = "ok";
$data["chat"] = $chat;

if ($gamifierClient) {
	$events = array();
    $events[] = createGameEvent($userId, GameEvents::HAS_CHATED);

	$addEventsResult = $gamifierClient->addEvents($events);

    $data["gamifiedUser"] = $addEventsResult;
}

$agenda["age_objects"][] = array("chatId" => $chat[$chatBo->ID_FIELD]);
$agenda["age_objects"] = json_encode($agenda["age_objects"]);

$agendaBo->save($agenda);

$memcacheKey = "do_getAgendaPoint_$pointId";
$memcache->delete($memcacheKey);

echo json_encode($data, JSON_NUMERIC_CHECK);
?>
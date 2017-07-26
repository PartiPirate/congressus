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

require_once("engine/utils/SessionUtils.php");
require_once("engine/bo/AgendaBo.php");
require_once("engine/bo/ChatBo.php");
require_once("engine/bo/ChatAdviceBo.php");
require_once("engine/bo/MeetingBo.php");
require_once("engine/utils/DateTimeUtils.php");

$memcache = openMemcacheConnection();

$connection = openConnection();

$agendaBo = AgendaBo::newInstance($connection, $config);
$chatBo = ChatBo::newInstance($connection, $config);
$meetingBo = MeetingBo::newInstance($connection, $config);
$chatAdviceBo = ChatAdviceBo::newInstance($connection, $config);

$userId = SessionUtils::getUserId($_SESSION);

if (!$userId) {
	echo json_encode(array("ko" => "ko", "message" => "must_be_connected"));
}

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

$pointId = $_REQUEST["agendaId"];
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

$advice = array();
$advice["cad_chat_id"] = $chat[$chatBo->ID_FIELD];
$advice["cad_user_id"] = $userId;

$advices = $chatAdviceBo->getByFilters($advice);
if (count($advices)) {
	$advice[$chatAdviceBo->ID_FIELD] = $advices[0][$chatAdviceBo->ID_FIELD];
}

$advice["cad_advice"] = $_REQUEST["advice"];

$chatAdviceBo->save($advice);

$memcacheKey = "do_getAgendaPoint_$pointId";
$memcache->delete($memcacheKey);

$data = array();
$data["ok"] = "ok";
$data["advice"] = $advice;

if ($gamifierClient) {
    $events = array();
    
    $userEventId = GameEvents::HAS_APPROVED;
    $targetEventId = GameEvents::IS_APPROVED;

    if ($advice["cad_advice"] != "thumb_up") {
	    $userEventId = GameEvents::HAS_REPROVED;
	    $targetEventId = GameEvents::IS_REPROVED;
    }

    $events[] = createGameEvent($userId, $userEventId);
    if (isset($chat["cha_member_id"]) && $chat["cha_member_id"]) {
    	$events[] = createGameEvent($chat["cha_member_id"], $targetEventId);
    }

    $addEventsResult = $gamifierClient->addEvents($events);

    $data["gamifiedUser"] = $addEventsResult;
}

echo json_encode($data, JSON_NUMERIC_CHECK);
?>
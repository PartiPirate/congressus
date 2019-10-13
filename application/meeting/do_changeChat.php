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
require_once("engine/bo/ChatBo.php");
require_once("engine/bo/MeetingBo.php");

if (!SessionUtils::getUserId($_SESSION)) {
	echo json_encode(array("ko" => "ko", "message" => "must_be_connected"));
	exit();
}

require_once("engine/utils/LogUtils.php");
addLog($_SERVER, $_SESSION, null, $_POST);

$memcache = openMemcacheConnection();

$connection = openConnection();

//$agendaBo = AgendaBo::newInstance($connection, $config);
$chatBo = ChatBo::newInstance($connection, $config);
//$meetingBo = MeetingBo::newInstance($connection, $config);

$chatId = $_REQUEST["chatId"];

$chat = $chatBo->getById($chatId);

if ($chat) {
	$pointId = $chat["cha_agenda_id"];
	
	$chat = array($chatBo->ID_FIELD => $chatId);
	$chat[$_REQUEST["property"]] = $_REQUEST["text"];

	if ($_REQUEST["property"] == "cha_member_id") {
		$chat["cha_guest_id"] = 0;
	}
	else if ($_REQUEST["property"] == "cha_guest_id") {
		$chat["cha_member_id"] = 0;
	}

	$chatBo->save($chat);

	$memcacheKey = "do_getAgendaPoint_$pointId";
	$memcache->delete($memcacheKey);
}

$data = array("ok" => "ok");

echo json_encode($data, JSON_NUMERIC_CHECK);
?>
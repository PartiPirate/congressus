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
require_once("engine/utils/SessionUtils.php");
require_once("engine/bo/MeetingBo.php");
require_once("engine/bo/NoticeBo.php");

require_once("engine/utils/LogUtils.php");
addLog($_SERVER, $_SESSION, null, $_POST);

$connection = openConnection();

$meetingBo = MeetingBo::newInstance($connection, $config);
$noticeBo = NoticeBo::newInstance($connection, $config);

$meeting = $meetingBo->getById($_REQUEST["not_meeting_id"]);

if (!$meeting) {
	echo json_encode(array("ko" => "ko", "message" => "meeting_does_not_exist"));
	exit();
}

// TODO Compute the key // Verify the key

if (false) {
	echo json_encode(array("ko" => "ko", "message" => "meeting_not_accessible"));
	exit();
}

$notice = array();
$notice["not_meeting_id"] = $meeting[$meetingBo->ID_FIELD];
$notice["not_target_type"] = $_REQUEST["not_target_type"];
if ($notice["not_target_type"] != "con_external") {
	$notice["not_target_id"] = $_REQUEST["not_target_id"];
	$notice["not_voting"] = isset($_REQUEST["not_voting"]) ? 1 : 0;
}
else {
	$notice["not_external_mails"] = $_REQUEST["not_external_mails"];
	$notice["not_voting"] = 0;
}

$noticeBo->save($notice);

$data["ok"] = "ok";
$data["notice"] = $notice;

echo json_encode($data, JSON_NUMERIC_CHECK);
?>
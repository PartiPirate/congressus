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
require_once("engine/bo/NoticeBo.php");
require_once("engine/bo/MeetingBo.php");

require_once("engine/utils/LogUtils.php");
addLog($_SERVER, $_SESSION, null, $_POST);

$meetingId = $_REQUEST["meetingId"];
$memcacheKey = "do_getAgenda_$meetingId";

$memcache = openMemcacheConnection();

$connection = openConnection();

$meetingBo = MeetingBo::newInstance($connection);
$noticeBo = NoticeBo::newInstance($connection);

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

$notice = $noticeBo->getById($_REQUEST["noticeId"]);

if (!$notice || $notice["not_meeting_id"] != $meeting[$meetingBo->ID_FIELD]) {
	echo json_encode(array("ko" => "ko", "message" => "notice_point_not_accessible"));
	exit();
}

$memcache->delete($memcacheKey);

$noticeBo->delete($notice);

$data["ok"] = "ok";

echo json_encode($data, JSON_NUMERIC_CHECK);
?>
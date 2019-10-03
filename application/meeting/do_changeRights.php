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
require_once("engine/bo/MeetingBo.php");
require_once("engine/bo/MeetingRightBo.php");

require_once("engine/utils/LogUtils.php");
addLog($_SERVER, $_SESSION, null, $_POST);

$meetingId = $_REQUEST["meetingId"];
$memcacheKey = "do_getAgenda_$meetingId";

$memcache = openMemcacheConnection();

$connection = openConnection();

$meetingBo = MeetingBo::newInstance($connection, $config);
$meetingRightBo = MeetingRightBo::newInstance($connection, $config);

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

if ($meeting["mee_president_member_id"] != SessionUtils::getUserId($_SESSION) && $meeting["mee_secretary_member_id"] != SessionUtils::getUserId($_SESSION)) {
	echo json_encode(array("ko" => "ko", "message" => "meeting_not_enough_rights"));
	exit();
}

$data = array();
$rights = $_REQUEST["rights"];

if (count($rights) || $_REQUEST["empty"] == "empty") {
	
	// Delete old rights
	$meetingRights = $meetingRightBo->getByFilters(array("mee_meeting_id" => $meeting[$meetingBo->ID_FIELD]));
	foreach($meetingRights as $meetingRight) {
		$meetingRightBo->delete($meetingRight);
	}
	
	// Add new rights
	foreach($rights as $right) {
		$meetingRight = array("mri_meeting_id" => $meetingId, "mri_right" => $right);
		$meetingRightBo->save($meetingRight);
	}
	
	$memcache->delete($memcacheKey);
}

$data["ok"] = "ok";

echo json_encode($data, JSON_NUMERIC_CHECK);
?>
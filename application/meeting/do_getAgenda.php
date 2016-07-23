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
require_once("engine/bo/MeetingBo.php");
require_once("engine/bo/MeetingRightBo.php");
require_once("engine/bo/AgendaBo.php");

$meetingId = $_REQUEST["id"];
$memcacheKey = "do_getAgenda_$meetingId";

$memcache = openMemcacheConnection();
$json = $memcache->get($memcacheKey);

if (!$json) {
	$connection = openConnection();

	$meetingBo = MeetingBo::newInstance($connection);
	$meetingRightBo = MeetingRightBo::newInstance($connection);
	$agendaBo = AgendaBo::newInstance($connection);

	$meeting = $meetingBo->getById($meetingId);

	if (!$meeting) {
		echo json_encode(array("ko" => "ko", "message" => "meeting_does_not_exist"));
	}

	// TODO Compute the key // Verify the key

	if (false) {
		echo json_encode(array("ko" => "ko", "message" => "meeting_not_accessible"));
	}

	$data = array();

	$agendas = $agendaBo->getByFilters(array("age_meeting_id" => $meeting[$meetingBo->ID_FIELD], "with_count_motions" => true));

	$end = new DateTime($meeting["mee_datetime"]);
	$duration = new DateInterval("PT" . ($meeting["mee_expected_duration"] ? $meeting["mee_expected_duration"] : 60) . "M");
	$meeting["mee_end_datetime"] = $end->add($duration);
	$meeting["mee_end_datetime"] = $meeting["mee_end_datetime"]->format("Y-m-d H:i:s");

	$meeting["mee_rights"] = array();
	
	$rights = $meetingRightBo->getByFilters(array("mri_meeting_id" => $meeting[$meetingBo->ID_FIELD]));
	foreach($rights as $right) {
		$meeting["mee_rights"][] = $right["mri_right"];
	}
	
	$data["meeting"] = $meeting;
	$data["agendas"] = $agendas;

	$data["ok"] = "ok";

	$json = json_encode($data, JSON_NUMERIC_CHECK);

	if (!$memcache->replace($memcacheKey, $json, MEMCACHE_COMPRESSED, 5)) {
		$memcache->set($memcacheKey, $json, MEMCACHE_COMPRESSED, 5);
	}
}
else {
	$data = json_decode($json, true);
	$data["cached"] = true;
}

echo json_encode($data, JSON_NUMERIC_CHECK);
?>
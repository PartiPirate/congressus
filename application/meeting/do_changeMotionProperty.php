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
require_once("engine/bo/MotionBo.php");

$memcache = openMemcacheConnection();

$connection = openConnection();

$motionBo = MotionBo::newInstance($connection);

$motion = $motionBo->getById($_REQUEST["motionId"]);

if (!$motion) {
	echo json_encode(array("ko" => "ko", "message" => "motion_not_accessible"));
	exit();
}

if ($_REQUEST["propositionId"] != "0") {
	$proposition = $motionBo->getByFilters(array($motionBo->ID_FIELD => $motion[$motionBo->ID_FIELD],
													$motionBo->ID_FIELD_PROPOSITION => $_REQUEST["propositionId"]));
	if (count($proposition)) {
		$proposition = array($motionBo->ID_FIELD_PROPOSITION => $proposition[0][$motionBo->ID_FIELD_PROPOSITION]);
		$proposition[$_REQUEST["property"]] = $_REQUEST["text"];

		$motionBo->saveProposition($proposition);
	}
	else {
		echo json_encode(array("ko" => "ko", "message" => "proposition_not_accessible"));
		exit();
	}
}
else {
	$motion = array($motionBo->ID_FIELD => $motion[$motionBo->ID_FIELD]);
	$motion[$_REQUEST["property"]] = $_REQUEST["text"];

	$motionBo->save($motion);
}

$data["ok"] = "ok";

$pointId = $motion["mot_agenda_id"];
$memcacheKey = "do_getAgendaPoint_$pointId";
$memcache->delete($memcacheKey);

echo json_encode($data, JSON_NUMERIC_CHECK);
?>
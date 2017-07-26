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
require_once("engine/bo/MotionBo.php");

require_once("engine/utils/LogUtils.php");
addLog($_SERVER, $_SESSION, null, $_POST);

$memcache = openMemcacheConnection();

$connection = openConnection();

$motionBo = MotionBo::newInstance($connection, $config);

$motion = $motionBo->getById($_REQUEST["motionId"]);

if (!$motion) {
	echo json_encode(array("ko" => "ko", "message" => "motion_not_accessible"));
	exit();
}

$pointId = $motion["mot_agenda_id"];

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

$memcacheKey = "do_getAgendaPoint_$pointId";
$memcache->delete($memcacheKey);

echo json_encode($data, JSON_NUMERIC_CHECK);
?>
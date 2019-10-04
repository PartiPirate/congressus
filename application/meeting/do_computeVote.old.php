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

//session_start();

$path = "../";
set_include_path(get_include_path() . PATH_SEPARATOR . $path);

include_once("config/database.php");
require_once("engine/utils/SessionUtils.php");
require_once("engine/bo/MotionBo.php");
require_once("engine/bo/VoteBo.php");

require_once("engine/utils/LogUtils.php");
addLog($_SERVER, $_SESSION, null, $_POST);

$connection = openConnection();

$motionBo = MotionBo::newInstance($connection, $config);
$data = array();

$propositions = $motionBo->getByFilters(array($motionBo->ID_FIELD => $_REQUEST["motionId"]));

$results = json_decode($_REQUEST["explanations"], true);

foreach($results as $key => $explanation) {
	$id = str_replace("proposition_", "", $key);

	foreach ($propositions as $proposition) {
		if ($proposition[$motionBo->ID_FIELD_PROPOSITION] == $id) {
			$updatedProposition = array($motionBo->ID_FIELD_PROPOSITION => $id);
			$updatedProposition["mpr_explanation"] = json_encode($explanation);
			$updatedProposition["mpr_winning"] = $explanation["winning"];

			$motionBo->updateProposition($updatedProposition);
		}
	}
}

echo json_encode($data, JSON_NUMERIC_CHECK);
?>
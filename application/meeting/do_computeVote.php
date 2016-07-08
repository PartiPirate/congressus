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
require_once("engine/utils/SessionUtils.php");
require_once("engine/bo/MotionBo.php");
require_once("engine/bo/VoteBo.php");

$connection = openConnection();

$motionBo = MotionBo::newInstance($connection);
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

$data["vote"] = $vote;

echo json_encode($data, JSON_NUMERIC_CHECK);
?>
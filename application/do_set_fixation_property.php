<?php /*
	Copyright 2015-2019 Cédric Levieux, Parti Pirate

	This file is part of Personae.

    Personae is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    Personae is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with Personae.  If not, see <http://www.gnu.org/licenses/>.
*/
include_once("config/database.php");
require_once("engine/utils/FormUtils.php");
require_once("engine/bo/FixationBo.php");
require_once("engine/bo/ThemeBo.php");
require_once("engine/bo/GaletteBo.php");
require_once("engine/bo/ServerAdminBo.php");

// We sanitize the request fields
xssCleanArray($_REQUEST);

session_start();

if (isset($_SESSION["memberId"])) {
	$sessionUserId = $_SESSION["memberId"];
}
else {
	echo json_encode(array("error" => "error_not_connected"));
}

$connection = openConnection();

// TODO add admin check

$fixationBo = FixationBo::newInstance($connection, $config);

$fixation = array();
$fixation[$_POST["property"]] = $_POST["value"];
$fixation["fix_id"] = $_POST["fix_id"];

$fixationBo->save($fixation);

// TODO Update theme admin event

echo json_encode(array("ok" => "ok", "fixation" => $fixation));
?>
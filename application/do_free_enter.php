<?php /*
	Copyright 2018 Cédric Levieux, Parti Pirate

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
/*
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
*/

include_once("config/database.php");
require_once("engine/utils/FormUtils.php");
require_once("engine/bo/FixationBo.php");
require_once("engine/bo/ThemeBo.php");
require_once("engine/bo/GaletteBo.php");

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

$fixationBo = FixationBo::newInstance($connection, $config);
$themeBo = ThemeBo::newInstance($connection, $config);
$galetteBo = GaletteBo::newInstance($connection, $config["galette"]["db"]);

$theme = $themeBo->getTheme($_REQUEST["the_id"]);

$fixation = array();
$fixation["fix_id"] = $theme["the_current_fixation_id"];

// $theme = array();
// $theme["the_id"] = $_REQUEST["tad_theme_id"];
// TODO
// if (!$themeBo->isMemberAdmin($theme, $sessionUserId)) {
// 	echo json_encode(array("error" => "theme_not_admin"));
// 	exit();
// }

$member = $galetteBo->getMemberById($sessionUserId);

if (!$member) {
	echo json_encode(array("error" => "fixation_no_member"));
	exit();
}

$fixationMember = array("fme_fixation_id" => $fixation["fix_id"],
						"fme_member_id" => $member["id_adh"],
						"fme_power" => intval($theme["the_voting_power"]));

if ($_REQUEST["action"] == "add_member") {
	$fixationBo->addFixationMember($fixationMember);
}
else {
	$fixationBo->removeFixationMember($fixationMember);
}

$fixationMember["fme_member_pseudo"] = GaletteBo::showIdentity($member);

// TODO Update theme admin event

echo json_encode(array("ok" => "ok", "fixationMember" => $fixationMember));
?>
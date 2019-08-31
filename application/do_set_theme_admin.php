<?php /*
	Copyright 2015 Cédric Levieux, Parti Pirate

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

$themeBo = ThemeBo::newInstance($connection, $config);
$galetteBo = GaletteBo::newInstance($connection, $config["galette"]["db"]);

$theme = array();
$theme["the_id"] = $_REQUEST["tad_theme_id"];

$serverAdminBo = ServerAdminBo::newInstance($connection, $config);
$isAdmin = count($serverAdminBo->getServerAdmins(array("sad_member_id" => $sessionUserId))) > 0;
$isAdmin = $isAdmin || $themeBo->isMemberAdmin($theme, $sessionUserId);

if (!$isAdmin) {
	echo json_encode(array("error" => "theme_not_admin"));
	exit();
}

if (isset($_REQUEST["tad_member_mail"]) && $_REQUEST["tad_member_mail"]) {
	$method = "bymail";
	$membersSource = explode(",", $_REQUEST["tad_member_mail"]);
}
else if ($_REQUEST["tad_member_id"]) {
	$method = "byid";
	$membersSource = explode(",", $_REQUEST["tad_member_id"]);
}

$manipulatedAdmins = array();

foreach($membersSource as $memberSource) {

	$memberSource = trim($memberSource);
	$member = null;
	if ($method == "bymail") {
		$member = $galetteBo->getMemberByMailOrPseudo($memberSource);
	}
	else if ($method == "byid") {
		$member = $galetteBo->getMemberById($memberSource);
	}

	if (!$member) {
		echo json_encode(array("error" => "theme_no_member"));
		exit();
	}

	if ($member["id_adh"] == $sessionUserId) {
		$admins = $themeBo->getMemberAdmins($theme);

		if (count($admins) == 1) {
			echo json_encode(array("error" => "theme_not_enough_admin"));
			exit();
		}
	}

	$admin = array("tad_theme_id" => $theme["the_id"], "tad_member_id" => $member["id_adh"]);

	if ($_REQUEST["action"] == "add_admin") {
		$themeBo->addMemberAdmin($admin);
	}
	else {
		$themeBo->removeMemberAdmin($admin);
	}

	$admin["tad_member_identity"] = GaletteBo::showIdentity($member);

	$manipulatedAdmins[] = $admin;
}

// TODO Update theme admin event

echo json_encode(array("ok" => "ok", "admins" => $manipulatedAdmins));
?>
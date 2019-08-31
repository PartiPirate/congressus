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
require_once("engine/bo/GroupBo.php");
require_once("engine/bo/ServerAdminBo.php");
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

$groupBo = GroupBo::newInstance($connection, $config["galette"]["db"]);
$galetteBo = GaletteBo::newInstance($connection, $config["galette"]["db"]);

$group = array();
$group["gro_id"] = $_REQUEST["gad_group_id"];

$serverAdminBo = ServerAdminBo::newInstance($connection, $config);
$isAdmin = count($serverAdminBo->getServerAdmins(array("sad_member_id" => $sessionUserId))) > 0;
$isAdmin = $isAdmin || $groupBo->isMemberAdmin($group, $sessionUserId);

if (!$isAdmin) {
	echo json_encode(array("error" => "group_not_admin"));
	exit();
}

if (isset($_REQUEST["gad_member_mail"]) && $_REQUEST["gad_member_mail"]) {
	$method = "bymail";
	$membersSource = explode(",", $_REQUEST["gad_member_mail"]);
}
else if ($_REQUEST["gad_member_id"]) {
	$method = "byid";
	$membersSource = explode(",", $_REQUEST["gad_member_id"]);
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
		echo json_encode(array("error" => "group_no_member"));
		exit();
	}

	if ($member["id_adh"] == $sessionUserId) {
		$admins = $groupBo->getMemberAdmins($group);

		if (count($admins) == 1) {
			echo json_encode(array("error" => "group_not_enough_admin"));
			exit();
		}
	}

	$admin = array("gad_group_id" => $group["gro_id"], "gad_member_id" => $member["id_adh"]);

	if ($_REQUEST["action"] == "add_admin") {
		$groupBo->addMemberAdmin($admin);
	}
	else {
		$groupBo->removeMemberAdmin($admin);
	}

	$admin["gad_member_identity"] = GaletteBo::showIdentity($member);

	$manipulatedAdmins[] = $admin;
}

// TODO Update theme admin event

echo json_encode(array("ok" => "ok", "admins" => $manipulatedAdmins));
?>
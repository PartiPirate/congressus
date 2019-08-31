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

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

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

$groupBo = GroupBo::newInstance($connection, $config);
$galetteBo = GaletteBo::newInstance($connection, $config["galette"]["db"]);

$group = array();
$group["gro_id"] = $_REQUEST["gau_group_id"];

$serverAdminBo = ServerAdminBo::newInstance($connection, $config);
$isAdmin = count($serverAdminBo->getServerAdmins(array("sad_member_id" => $sessionUserId))) > 0;
$isAdmin = $isAdmin || $groupBo->isMemberAdmin($group, $sessionUserId);

if (!$isAdmin) {
	echo json_encode(array("error" => "group_not_admin"));
	exit();
}

if ($_REQUEST["gau_authoritative_id"]) {
	$galetteGroup = $galetteBo->getGroupById($_REQUEST["gau_authoritative_id"]);
}
else {
	$galetteGroup = array("id_group" => 0, "group_name" => utf8_decode("Tous les membres"));
}

$manipulatedAuthoritatives = array();

if ($galetteGroup) {

	$authoritative = array("gau_group_id" => $group["gro_id"], "gau_authoritative_id" => $galetteGroup["id_group"]);

	if ($_REQUEST["action"] == "add_authority") {
		$groupBo->addAuthorityAdmin($authoritative);
	}
	else {
		$groupBo->removeAuthorityAdmin($authoritative);
	}

	$authoritative["gau_authoritative_name"] = utf8_encode($galetteGroup["group_name"]);

	$manipulatedAuthoritatives[] = $authoritative;
}
else {
	echo json_encode(array("error" => "group_no_galette_group"));
	exit();
}

// TODO Update theme authoritative event

echo json_encode(array("ok" => "ok", "authoritatives" => $manipulatedAuthoritatives));
?>
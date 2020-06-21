<?php /*
    Copyright 2015-2018 Cédric Levieux, Parti Pirate

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
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include_once("config/database.php");
require_once("engine/utils/FormUtils.php");
require_once("engine/bo/GroupBo.php");
require_once("engine/bo/ThemeBo.php");
require_once("engine/bo/ServerAdminBo.php");

// We sanitize the request fields
xssCleanArray($_REQUEST);

$connection = openConnection();

session_start();

if (isset($_SESSION["memberId"])) {
	$sessionUserId = $_SESSION["memberId"];
}
else {
	echo json_encode(array("error" => "error_not_connected"));
}

$themeBo = ThemeBo::newInstance($connection, $config);
$groupBo = GroupBo::newInstance($connection, $config);

$theme = array();
$theme["the_id"] = $_REQUEST["the_id"];

$serverAdminBo = ServerAdminBo::newInstance($connection, $config);
$isAdmin = count($serverAdminBo->getServerAdmins(array("sad_member_id" => $sessionUserId))) > 0;
$isAdmin = $isAdmin || $themeBo->isMemberAdmin($theme, $sessionUserId);

if (!$isAdmin) {
	echo json_encode(array("error" => "theme_not_admin"));

	exit();
}

$theme["the_label"] = $_REQUEST["the_label"];

// Fixation rules
$theme["the_min_members"] = $_REQUEST["the_min_members"];
$theme["the_max_members"] = $_REQUEST["the_max_members"];
$theme["the_dilution"] = $_REQUEST["the_dilution"];
$theme["the_max_delegations"] = $_REQUEST["the_max_delegations"];

$theme["the_type_date"] = $_REQUEST["the_type_date"];

$theme["the_next_fixation_date"] = $_REQUEST["the_next_fixation_date"];
$theme["the_next_fixed_until_date"] = $_REQUEST["the_next_fixed_until_date"];

$theme["the_periodicity"] = $_REQUEST["the_periodicity"];

$theme["the_voting_power"] = $_REQUEST["the_voting_power"];
$theme["the_voting_method"] = $_REQUEST["the_voting_method"];
$theme["the_secret_until_fixation"] = isset($_REQUEST["the_secret_until_fixation"]) ? 1 : 0;

// Eligibles persons source
$theme["the_eligible_group_type"] = $_REQUEST["the_eligible_group_type"];
$theme["the_eligible_group_id"] = $_REQUEST["the_eligible_group_id"];

// Voters persons source
$theme["the_voting_group_type"] = $_REQUEST["the_voting_group_type"];
$theme["the_voting_group_id"] = $_REQUEST["the_voting_group_id"];

$theme["the_discourse_group_labels"] = $_REQUEST["the_discourse_group_labels"];

$theme["the_tasker_type"] = $_REQUEST["the_tasker_type"];
$theme["the_tasker_project_id"] = $_REQUEST["the_tasker_project_id"];

$theme["the_entry_condition"] = $_REQUEST["the_entry_condition"];

// Discord exports
$theme["the_discord_export"] = isset($_REQUEST["the_discord_export"]) ? 1 : 0;

// Delegate only
$theme["the_delegate_only"] = isset($_REQUEST["the_delegate_only"]) ? 1 : 0;

// Delegation closed
$theme["the_delegation_closed"] = isset($_REQUEST["the_delegation_closed"]) ? 1 : 0;

// Permits the free theme entering
$theme["the_free_fixed"] = isset($_REQUEST["the_free_fixed"]) ? 1 : 0;

$isCreating = ($theme["the_id"] == 0);
$themeBo->save($theme);

if ($isCreating) {
	// Add the author into administration

	$admin = array("tad_theme_id" => $theme["the_id"], "tad_member_id" => $sessionUserId);
	$themeBo->addMemberAdmin($admin);

	// Add the theme into the given group

	if (isset($_REQUEST["the_group_id"]) && $_REQUEST["the_group_id"]) {
		$groupId = intval($_REQUEST["the_group_id"]);
		$group = $groupBo->getGroup($groupId);

		if ($group) {
			$groupBo->addTheme($group, $theme);
		}
	}
}

// TODO Create theme event

echo json_encode(array("ok" => "ok", "theme" => $theme));
?>
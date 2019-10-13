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
include_once("config/database.php");
require_once("engine/utils/FormUtils.php");
require_once("engine/bo/CandidateBo.php");
require_once("engine/bo/DelegationBo.php");
require_once("engine/bo/GroupBo.php");
require_once("engine/bo/ThemeBo.php");

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

$delegationBo = DelegationBo::newInstance($connection, $config);
$groupBo = GroupBo::newInstance($connection, $config);
$themeBo = ThemeBo::newInstance($connection, $config);

$delegation = array();
$delegation["del_theme_id"] = $_REQUEST["del_theme_id"];
$delegation["del_theme_type"] = $_REQUEST["del_theme_type"];
$delegation["del_member_from"] = $sessionUserId;
$delegation["del_member_to"] = $_REQUEST["del_member_to"];

if ($delegation["del_member_from"] == $delegation["del_member_to"]) {
	echo json_encode(array("error" => "error_voting_same_member_in_both_ends"));
	exit();
}

// TODO Test deligativity

if ($delegation["del_theme_type"] == "dlp_themes") {
	$instance = $themeBo->getTheme($delegation["del_theme_id"]);

	$eligiblesGroups = $groupBo->getMyGroups(array("the_id" => $delegation["del_theme_id"], "state" => "eligible"));
	foreach($eligiblesGroups as $eligiblesGroup) {
		foreach($eligiblesGroup["gro_themes"] as $eligiblesTheme) {
			$eligibles = $eligiblesTheme["members"];
		}
	}

	$votingsGroups = $groupBo->getMyGroups(array("the_id" => $delegation["del_theme_id"], "state" => "voting"));
	foreach($votingsGroups as $votingsGroup) {
		foreach($votingsGroup["gro_themes"] as $votingsTheme) {
			$votings = $votingsTheme["members"];
		}
	}
}

$instance["eligibles"] = $eligibles;
$instance["votings"] = $votings;

$powers = $delegationBo->computeFixation($instance);
$isCycling = DelegationBo::testCycle($powers, $delegation);

if ($isCycling) {
	echo json_encode(array("error" => "error_voting_cycling"));
	exit();
}

// Retrieve previous delegation
$exists = false;
$filter = $delegation;
$filter["no_condition"] = true;
$data = array("ok" => "ok");

$delegations = $delegationBo->getDelegations($filter);
if (count($delegations)) {
	$delegation = $delegations[0];
	$exists = true;
}

$delegation["del_power"] = $_REQUEST["del_power"];

//$instance["the_max_delegations"] = 1;

if ($delegation["del_power"] > 0) {	
	$actualDelegationNumbers = 0;

	foreach($powers as $power) {
		
		if ($delegation["del_member_to"] == $power["id_adh"]) {
			$actualDelegationNumbers = count($power["givers"]);
		}
	}

	if ($exists) {
		$actualDelegationNumbers--;
	}

//	echo "Number of delegations = " . $actualDelegationNumbers . "\n";

	if ($instance["the_max_delegations"] && $actualDelegationNumbers >= $instance["the_max_delegations"]) {
		echo json_encode(array("error" => "error_max_delegations", "max_delegation" => $instance["the_max_delegations"]));
		exit();
	}
}

if ($delegation["del_power"] > 0) {
	// Save it
	$delegationBo->save($delegation);
}
else {
	$delegationBo->deleteById($delegation);
}

// TODO Create delegation event

$data["delegation"] = $delegation;
$data["max_delegation"] = $instance["the_max_delegations"];
echo json_encode($data);
?>
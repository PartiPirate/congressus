<?php /*
    Copyright 2018 Cédric Levieux, Parti Pirate

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
require_once("engine/bo/CandidateBo.php");
require_once("engine/bo/DelegationBo.php");
require_once("engine/bo/DelegationConditionBo.php");
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

// THEME ids
$themeId = intval($_REQUEST["del_theme_id"]);
$themeType = $_REQUEST["del_theme_type"];

$delegationBo = DelegationBo::newInstance($connection);
$delegationConditionBo = DelegationConditionBo::newInstance($connection);
$groupBo = GroupBo::newInstance($connection, $config);
$themeBo = ThemeBo::newInstance($connection, $config);

// TODO Test deligativity

if ($themeType == "dlp_themes") {
	$instance = $themeBo->getTheme($themeId);

	$eligiblesGroups = $groupBo->getMyGroups(array("the_id" => $themeId, "state" => "eligible"));

	foreach($eligiblesGroups as $eligiblesGroup) {
		foreach($eligiblesGroup["gro_themes"] as $eligiblesTheme) {
			$eligibles = $eligiblesTheme["members"];
		}
	}

	$votingsGroups = $groupBo->getMyGroups(array("the_id" => $themeId, "state" => "voting"));

	foreach($votingsGroups as $votingsGroup) {
		foreach($votingsGroup["gro_themes"] as $votingsTheme) {
			$votings = $votingsTheme["members"];
		}
	}
}

$instance["eligibles"] = $eligibles;
$instance["votings"] = $votings;


$previousDelegations = $delegationBo->getDelegations(array("del_theme_id" => $themeId,
													"del_theme_type" => $themeType,
													"del_member_from" => $sessionUserId));

//print_r($previousDelegations);

foreach($previousDelegations as $previousDelegation) {
	$delegationBo->deleteById($previousDelegation);
}
foreach($previousDelegations as $previousCondition) {
	$delegationConditionBo->deleteByUniqueKey($previousCondition);
}

$delegations = json_decode($_REQUEST["delegation"], true);

// print_r($delegations);

$order = 0;

foreach($delegations["conditionals"] as $conditionalDelegation) {
	/*
	foreach($conditionalDelegation["conditions"] as $condition) {
		
	}
	*/
	$condition = array();
	$condition["dco_order"] = $order;
	$condition["dco_end_of_delegation"] = $conditionalDelegation["endOfDelegation"];
	$condition["dco_conditions"] = json_encode($conditionalDelegation["conditions"]);
	$condition["dco_member_from"] = $sessionUserId;
	$condition["dco_theme_id"] = $themeId;
	$condition["dco_theme_type"] = $themeType;

	$delegationConditionBo->save($condition);

//	print_r($condition);

	if (count($conditionalDelegation["delegations"])) {
		foreach($conditionalDelegation["delegations"] as $currentDelegation) {
			$delegation = array();
			$delegation["del_theme_id"] = $themeId;
			$delegation["del_theme_type"] = $themeType;
			$delegation["del_member_from"] = $sessionUserId;
	
			$delegation["del_member_to"] = $currentDelegation["id"];
			$delegation["del_power"] = $currentDelegation["power"];
	
			$delegation["del_delegation_condition_id"] = $condition["dco_id"];
	
	//		print_r($delegation);
	
			$delegationBo->save($delegation);
		}
	}
	else {
		$delegation = array();
		$delegation["del_theme_id"] = $themeId;
		$delegation["del_theme_type"] = $themeType;
		$delegation["del_member_from"] = $sessionUserId;

		$delegation["del_member_to"] = $sessionUserId;
		$delegation["del_power"] = 1;

		$delegation["del_delegation_condition_id"] = $condition["dco_id"];

//		print_r($delegation);

		$delegationBo->save($delegation);
	}

	$order++;
}

foreach($delegations["default"] as $currentDelegation) {
	$delegation = array();
	$delegation["del_theme_id"] = $themeId;
	$delegation["del_theme_type"] = $themeType;
	$delegation["del_member_from"] = $sessionUserId;

	$delegation["del_member_to"] = $currentDelegation["id"];
	$delegation["del_power"] = $currentDelegation["power"];

//	print_r($delegation);

	$delegationBo->save($delegation);
}

/*
$delegation["del_member_to"] = $_REQUEST["del_member_to"];

if ($delegation["del_member_from"] == $delegation["del_member_to"]) {
	echo json_encode(array("error" => "error_voting_same_member_in_both_ends"));
	exit();
}
*/

/* TODO ?
$powers = $delegationBo->computeFixation($instance);
$isCycling = DelegationBo::testCycle($powers, $delegation);

if ($isCycling) {
	echo json_encode(array("error" => "error_voting_cycling"));
	exit();
}
*/


// TODO Create delegation event

echo json_encode(array("ok" => "ok", "max_delegation" => $instance["the_max_delegations"]));
?>
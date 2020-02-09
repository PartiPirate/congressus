<?php /*
	Copyright 2015-2018 Cédric Levieux, Parti Pirate

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
    along with Personae.  If age, see <http://www.gnu.org/licenses/>.
*/

include_once("engine/conditional/ICondition.php");
include_once("engine/conditional/IOperator.php");
include_once("engine/conditional/ContainsOperator.php");
include_once("engine/conditional/EqualsOperator.php");
include_once("engine/conditional/DoNotContainOperator.php");
include_once("engine/conditional/DoVoteOperator.php");
include_once("engine/conditional/IsAfterOperator.php");
include_once("engine/conditional/IsBeforeOperator.php");
include_once("engine/conditional/MotionDateCondition.php");
include_once("engine/conditional/MotionDescriptionCondition.php");
include_once("engine/conditional/MotionTitleCondition.php");
include_once("engine/conditional/MotionTagsCondition.php");
include_once("engine/conditional/VoterMeCondition.php");
include_once("engine/conditional/ConditionalFactory.php");

require_once("engine/bo/DelegationBo.php");
require_once("engine/bo/FixationBo.php");
require_once("engine/bo/GroupBo.php");
require_once("engine/bo/ThemeBo.php");

if (!isset($api)) exit();

$connection = openConnection();

$data = array();

if (!testTokens()) {
	$data["error"] = "bad_tokens";
	echo json_encode($data, JSON_NUMERIC_CHECK);
	exit;
}

$groupBo = GroupBo::newInstance($connection, $config["galette"]["db"]);
$themeBo = ThemeBo::newInstance($connection, $config["galette"]["db"]);
$fixationBo = FixationBo::newInstance($connection, $config["galette"]["db"]);
$delegationBo = DelegationBo::newInstance($connection);

$motion = json_decode($_REQUEST["motion"], true);
$votes = json_decode($_REQUEST["votes"], true);
$themeId = $_REQUEST["themeId"];

$theme = $themeBo->getTheme($themeId);

$method = $theme["the_voting_method"];

$powers = array();

if ($method == "demliq") {

	$eligiblesGroups = $groupBo->getMyGroups(array("the_id" => $theme["the_id"], "state" => "eligible"));
	foreach($eligiblesGroups as $eligiblesGroup) {
		foreach($eligiblesGroup["gro_themes"] as $eligiblesTheme) {
			$eligibles = $eligiblesTheme["members"];
		}
	}

	$votingsGroups = $groupBo->getMyGroups(array("the_id" => $theme["the_id"], "state" => "voting"));
	foreach($votingsGroups as $votingsGroup) {
		foreach($votingsGroup["gro_themes"] as $votingsTheme) {
			$votings = $votingsTheme["members"];
		}
	}

	$fixation = array();

	$theme["eligibles"] = $eligibles;
	$theme["votings"] = $votings;

	$powers = $delegationBo->computeFixationWithContext($theme, $motion, $votes);

/*
	// Clean powerless members
	foreach($powers as $memberId => $member) {
		if ($member["power"] <= 0) unset($powers[$memberId]);
	}
*/

//    print_r($powers);

	$leftRooms = $theme["the_max_members"];

	foreach($powers as $memberId => $member) {
		if ($leftRooms == 0) break;

//		$powers[$member["id_adh"]] = array("power" => $member["power"], "explanation" => array());
		$powers[$member["id_adh"]] = $member;

		$leftRooms--;
	}
}
else {
	$filters = array();
	$filters["with_fixation_members"] = true;
	$filters["fix_id"] = $theme["the_current_fixation_id"];
	
	$fixedMembers = $fixationBo->getFixations($filters);

	foreach($fixedMembers as $fixedMember) {
		$member = array();
		
		$member["id_adh"] = $fixedMember["id_adh"];

		if ($fixedMember["pseudo_adh"]) {
			$member["nickname"] = $fixedMember["pseudo_adh"];
		}
		else {
			$member["nickname"] = $fixedMember["nom_adh"] . " " . $fixedMember["prenom_adh"];
		}

		$member["nickname"] = htmlentities($member["nickname"]);

		$member["power"] = $fixedMember["fme_power"];
		$member["max_power"] = $fixedMember["fme_power"];
		$member["delegation_level"] = 1;

		$powers[$member["id_adh"]] = $member;
	}
}

$data["motion"] = $motion;
$data["votes"] = $votes;
$data["theme"] = array("the_id" => $theme["the_id"], "the_label" => $theme["the_label"], "the_voting_power" => $theme["the_voting_power"]);
$data["powers"] = $powers;

echo json_encode($data, JSON_NUMERIC_CHECK);
?>
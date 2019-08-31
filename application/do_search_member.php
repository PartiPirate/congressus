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
include_once("language/language.php");
require_once("engine/utils/FormUtils.php");
require_once("engine/utils/SessionUtils.php");
require_once("engine/bo/ThemeBo.php");
require_once("engine/bo/GroupBo.php");
require_once("engine/bo/GaletteBo.php");

// We sanitize the request fields
xssCleanArray($_REQUEST);
// xssCleanArray($_POST);
// xssCleanArray($_GET);

$connection = openConnection();

session_start();

$sessionUserId = SessionUtils::getUserId($_SESSION);

// No session user, no result
if (!$sessionUserId) exit();

$galetteBo = GaletteBo::newInstance($connection, $config["galette"]["db"]);
$groupBo = GroupBo::newInstance($connection, $config["galette"]["db"]);

$filters = array();
if(isset($_POST["mem_lastname"]) && $_POST["mem_lastname"]) {
	$filters["nom_adh_like"] = utf8_decode($_POST["mem_lastname"]);
}
if(isset($_POST["mem_firstname"]) && $_POST["mem_firstname"]) {
	$filters["prenom_adh_like"] = utf8_decode($_POST["mem_firstname"]);
}
if(isset($_POST["mem_nickname"]) && $_POST["mem_nickname"]) {
	$filters["pseudo_adh_like"] = utf8_decode($_POST["mem_nickname"]);
}
if(isset($_POST["mem_mail"]) && $_POST["mem_mail"]) {
	$filters["email_adh"] = $_POST["mem_mail"];
}
if(isset($_POST["mem_zipcode"]) && $_POST["mem_zipcode"]) {
	$filters["cp_adh_like"] = $_POST["mem_zipcode"];
}
if(isset($_POST["mem_city"]) && $_POST["mem_city"]) {
	$filters["ville_adh_like"] = utf8_decode($_POST["mem_city"]);
}
if (isset($_POST["skill_ids"])) {
	$filters["skill_ids"] = $_POST["skill_ids"];
}

$filters["with_skills"] = true;

$filters["adh_only"] = true;

$members = $galetteBo->getMembers($filters);

$filtered = false;

if ($_POST["filterThemeId"]) {
	$eligibles = array();
	$eligiblesGroups = $groupBo->getMyGroups(array("the_id" => $_POST["filterThemeId"], "state" => "eligible"));
	foreach($eligiblesGroups as $eligiblesGroup) {
		foreach($eligiblesGroup["gro_themes"] as $eligiblesTheme) {
			$eligibles = $eligiblesTheme["members"];
		}
	}

	$votings = array();
	$votingsGroups = $groupBo->getMyGroups(array("the_id" => $_POST["filterThemeId"], "state" => "voting"));
	foreach($votingsGroups as $votingsGroup) {
		foreach($votingsGroup["gro_themes"] as $votingsTheme) {
			$votings = $votingsTheme["members"];
		}
	}

	foreach($eligibles as $eligible) {
		foreach($members as $index => $member) {
			if ($member["id_adh"] == $eligible["id_adh"]) {
				$members[$index]["mem_status"] = $eligible["can_status"];
			}
		}
	}

	foreach($votings as $voting) {
		foreach($members as $index => $member) {
			if ($member["id_adh"] == $voting["id_adh"] && !isset($members[$index]["mem_status"])) {
				$members[$index]["mem_status"] = "voting";
			}
		}
	}

	$filtered = true;
}

// print_r($members);

$rows = array();
foreach($members as $member) {
	$row = array();
	$row["id"] = $member["id_adh"];

	if ($member["pseudo_adh"]) {
		$row["lastname"] = "";
		$row["firstname"] = "";
		$row["nickname"] = utf8_encode($member["pseudo_adh"]);
		$row["mail"] = "";
	}
	else {
		$row["lastname"] = utf8_encode($member["nom_adh"]);
		$row["firstname"] = utf8_encode($member["prenom_adh"]);
		$row["nickname"] = "";
		$row["mail"] = $member["email_adh"];
	}

	$row["zipcode"] = $member["cp_adh"];
//	$row["city"] = utf8_encode($member["ville_adh"]);
	$row["city"] = "";
	$row["status"] = isset($member["mem_status"]) ? $member["mem_status"] : "";
	$row["action"] = "";
	
	$uSkills = array();
	
	if ($member["skills"]) {
		$skills = explode(",", $member["skills"]);
		foreach($skills as $skill) {
			$skill = explode("#", trim($skill));
			$uSkill = $skill[0];
			$uSkill .= " (";
			$uSkill .= lang("skill_level_" . $skill[1]);
			$uSkill .= " - ";
			$uSkill .= $skill[2];
			$uSkill .= ")";
			
			$uSkills[] = $uSkill;
		}
	}
	
	$row["skills"] = implode(", ", $uSkills);
	
	switch($row["status"]) {
		case "candidate":
			$row["status"] = "<span title='Candidat' class='text-success fa fa-thumbs-o-up'></span>";
			break;
		case "anti":
			$row["status"] = "<span title='Ne veut pas être élu' class='text-danger fa fa-thumbs-o-down'></span>";
			break;
		case "neutral":
		case "voting":
			$row["status"] = "<span title='Eligible ou votant' class='text-primary fa fa-hand-paper-o'></span>";
			break;
	}

//	print_r($row);

	if (!$member["id_type_cotis"]) continue;
	// If we filter, we need a status and the user can't be in the result (TODO)
	if ($filtered && !$row["status"]) continue;
	if ($filtered && $row["id"] == $sessionUserId && $_POST["filterWith"] != "true") continue;

	$rows[] = $row;
}

$numberOfRows = count($rows);

echo json_encode(array("ok" => "ok", "rows" => $rows, "numberOfRows" => $numberOfRows));
?>
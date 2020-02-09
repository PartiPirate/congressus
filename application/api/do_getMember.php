<?php /*
	Copyright 2015-2017 Cédric Levieux, Parti Pirate

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

if (!isset($api)) exit();

require_once("engine/bo/GroupBo.php");
require_once("engine/bo/GaletteBo.php");

$connection = openConnection();

$data = array();

if (!testTokens()) {
	$data["error"] = "bad_tokens";
	echo json_encode($data, JSON_NUMERIC_CHECK);
	exit;
}

if (!isset($_GET["nickname"])) {
	$data["error"] = "nickname_missing";
	echo json_encode($data, JSON_NUMERIC_CHECK);
	exit;
}

$data["nickname"] = $_GET["nickname"];

$galetteBo = GaletteBo::newInstance($connection, $config["galette"]["db"]);
$groupBo = GroupBo::newInstance($connection, $config["galette"]["db"]);

$member = $galetteBo->getMemberByDiscord(utf8_decode($data["nickname"]));

if (!$member) {
	$data["error"] = "unknown user";
}
else {
//	$data["member"] = $member;

	$groups = $groupBo->getGroups(array("for_user_only" => $member["id_adh"]));
	
	$inGroups = array();

	foreach($groups as $group) {
		$found = false;

		foreach($group["gro_themes"] as $theme) {
			foreach($theme["fixation"]["members"] as $themeMember) {
				if ($themeMember["id_adh"] == $member["id_adh"]) {
//					if (!$theme["the_label"] || !$group["gro_label"]) continue;
					
					switch($group["gro_label"]) {
						case "Assemblée générale":
							break;
						case "Equipes":
							if ($theme["the_discord_export"] == 1) {
								if (!array_key_exists($theme["the_label"], $inGroups)) $inGroups[$theme["the_label"]] = array("label" => $theme["the_label"], "type" => "theme");
							}
							break;
						default:
							if ($theme["the_discord_export"] == 1) {
								if (!array_key_exists($theme["the_label"], $inGroups)) $inGroups[$theme["the_label"]] = array("label" => $theme["the_label"], "type" => "theme");
							}
							if ($group["gro_label"]) {
								if (!array_key_exists($group["gro_label"], $inGroups)) $inGroups[$group["gro_label"]] = array("label" => $group["gro_label"], "type" => "group");
							}
							break;
					}

					$found = true;
					break;
				}
			}
			
//			if ($found) break;
		}
	}

	$galetteGroups = $galetteBo->getGroups();
	
	foreach($galetteGroups as $galetteGroup) {
//		print_r($galetteGroup);

		$groupMembers = $galetteBo->getMembers(array("adh_group_names" => array($galetteGroup["group_name"]), "adh_only" => true, "id_adh" => $member["id_adh"]));

//		print_r($groupMembers);
		
		if (count($groupMembers)) {
			$memberGroup = utf8_encode($galetteGroup["group_name"]);

//			if (!array_key_exists($memberGroup, $inGroups)) $inGroups[$memberGroup] = array("label" => $memberGroup, "type" => "section");
			if (!array_key_exists("Pirates", $inGroups)) $inGroups["Pirates"] = array("label" => "Pirates", "type" => "member");
		}
	}


	$data["groups"] = $inGroups;

//	print_r($data);
}

echo json_encode($data, JSON_NUMERIC_CHECK);
?>
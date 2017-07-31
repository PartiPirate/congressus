<?php /*
    Copyright 2015-2017 Cédric Levieux, Parti Pirate
    
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
    along with Congressus.  If not, see <http://www.gnu.org/licenses/>.
*/

class GaletteGroupSource {

    function getGroupKey() {
        return "galette_groups";
    }

    function getGroupKeyLabel() {
        return array("key" => "galette_groups", "label" => lang("notice_groupGalette"));
    }

    function getGroupOptions() {
        require_once("engine/bo/GaletteBo.php");
        global $config;
        global $connection;

        $galetteBo = GaletteBo::newInstance($connection, $config["galette"]["db"]);
        $galetteGroups = $galetteBo->getGroups();

		echo "		<!-- Galette groups -->\n";
		echo "			<option class=\"galette_groups\" value=\"0\" >Veuillez choisir un groupe</option>\n";

		foreach($galetteGroups as $listGroup) {
		    echo "		<option class=\"galette_groups\"";
			echo "			value=\"" . $listGroup["id_group"] . "\">";
			echo utf8_encode($listGroup["group_name"]);
			echo "		</option>\n";
		}
    }

    function getGroupLabel($groupId) {
        require_once("engine/bo/GaletteBo.php");
        global $config;
        global $connection;

        $galetteBo = GaletteBo::newInstance($connection, $config["galette"]["db"]);
        $galetteGroups = $galetteBo->getGroups(array("id_group" => $groupId));

        if (count($galetteGroups)) return utf8_encode($galetteGroups[0]["group_name"]);

        return null;
    }

    function updateNotice($meeting, &$notice, &$pings, &$usedPings) {
        require_once("engine/bo/GaletteBo.php");
        global $config;
        global $connection;
        global $now;

        $galetteBo = GaletteBo::newInstance($connection, $config["galette"]["db"]);

		$groups = $galetteBo->getGroups(array("id_group" => $notice["not_target_id"]));

		$group = array("group_name" => "");
		if (count($groups)) {
			$group = $groups[0];
		}
		$members = $galetteBo->getMembers(array("adh_group_ids" => array($notice["not_target_id"])));

		$notice["not_label"] = htmlspecialchars(utf8_encode($group["group_name"]), ENT_SUBSTITUTE);
		$notice["not_people"] = array();

		foreach($members as $member) {
			$people = array("mem_id" => $member["id_adh"]);
			$people["mem_nickname"] = htmlspecialchars(utf8_encode($member["pseudo_adh"] ? $member["pseudo_adh"] : $member["nom_adh"] . ' ' . $member["prenom_adh"]), ENT_SUBSTITUTE);
			$people["mem_power"] = 2;
			$people["mem_noticed"] = 1;
			$people["mem_voting"] = $notice["not_voting"];
			$people["mem_meeting_president"] = ($people["mem_id"] == $meeting["mee_president_member_id"]) ? 1 : 0;
			$people["mem_meeting_secretary"] = ($people["mem_id"] == $meeting["mee_secretary_member_id"]) ? 1 : 0;

            GroupSourceFactory::fixPing($pings, $usedPings, $people, $member, $now);

			$notice["not_people"][] = $people;
		}
    }
    
    function getNoticeMembers($notice) {
        require_once("engine/bo/GaletteBo.php");
        global $config;
        global $connection;

        $galetteBo = GaletteBo::newInstance($connection, $config["galette"]["db"]);
		$members = $galetteBo->getMembers(array("adh_group_ids" => array($notice["not_target_id"])));

		return $members;
    }
}

?>
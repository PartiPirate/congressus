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

class PersonaeGroupSource {

    function getGroupKey() {
        return "dlp_groups";
    }

    function getGroupKeyLabel() {
        return array("key" => "dlp_groups", "label" => lang("notice_group"));
    }

    function getGroupOptions() {
        require_once("engine/bo/GroupBo.php");
        global $config;
        global $connection;

        $groupBo = GroupBo::newInstance($connection, $config);
        $groups = $groupBo->getGroups();

		echo "		<!-- Groups -->\n";
		echo "			<option class=\"dlp_groups\" value=\"0\" >Veuillez choisir un groupe</option>\n";

		foreach($groups as $listGroup) {
		    echo "		<option class=\"dlp_groups\"";
			echo "          value=\"" . $listGroup["gro_id"] . "\">";
			echo            $listGroup["gro_label"];
			echo "		</option>\n";
		}
		
    }

    function getGroupLabel($groupId) {
        require_once("engine/bo/GroupBo.php");
        global $config;
        global $connection;

        $groupBo = GroupBo::newInstance($connection, $config);
        $group = $groupBo->getGroup($groupId);

        if ($group) return $group["gro_label"];

        return null;
    }

    function updateNotice($meeting, &$notice, &$pings, &$usedPings) {
        require_once("engine/bo/ThemeBo.php");
        global $config;
        global $connection;
        global $now;

        $groupBo = GroupBo::newInstance($connection, $config);

		$group = $groupBo->getGroup($notice["not_target_id"]);

		$notice["not_label"] = $group["gro_label"];
		$notice["not_people"] = array();
		$notice["not_children"] = array();

		foreach($group["gro_themes"] as $theme) {
			$child = array();
			$child["not_voting"] = $notice["not_voting"];
			$child["not_label"] = $theme["the_label"];
			$child["not_people"] = array();
			$child["not_power"] = $theme["gth_power"];

			foreach($theme["fixation"]["members"] as $fixationMember) {
				if (!$fixationMember["id_adh"]) continue;
				$people = array("mem_id" => $fixationMember["id_adh"]);
				$people["mem_nickname"] = htmlspecialchars(utf8_encode($fixationMember["pseudo_adh"] ? $fixationMember["pseudo_adh"] : $fixationMember["nom_adh"] . ' ' . $fixationMember["prenom_adh"]), ENT_SUBSTITUTE);
				$people["mem_power"] = $fixationMember["fme_power"];
				$people["mem_voting"] = $notice["not_voting"];
				$people["mem_noticed"] = 1;
				$people["mem_meeting_president"] = ($people["mem_id"] == $meeting["mee_president_member_id"]) ? 1 : 0;
				$people["mem_meeting_secretary"] = ($people["mem_id"] == $meeting["mee_secretary_member_id"]) ? 1 : 0;

                GroupSourceFactory::fixPing($pings, $usedPings, $people, $fixationMember, $now);

				$child["not_people"][] = $people;
			}

			$notice["not_children"][] = $child;
		}
    }

    function getNoticeMembers($notice) {
        require_once("engine/bo/ThemeBo.php");
        global $config;
        global $connection;

        $groupBo = GroupBo::newInstance($connection, $config);

		$group = $groupBo->getGroup($notice["not_target_id"]);
        $members = array();

		foreach($group["gro_themes"] as $theme) {
			foreach($theme["fixation"]["members"] as $member) {
				$members[] = $member;
			}
		}

		return $members;
    }
}

?>
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
    along with Congressus.  If not, see <http://www.gnu.org/licenses/>.
*/

class PersonaeGroupSource {

    function getGroupKey() {
        return "dlp_groups";
    }

    function getGroupKeyLabel() {
        return array("key" => "dlp_groups", "label" => lang("notice_group"), "selectable" => true);
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
        $group = $groupBo->getGroup($groupId, true);

        if ($group) return $group["gro_label"];

        return null;
    }

    function updateNotice($meeting, &$notice, &$pings, &$usedPings) {
        require_once("engine/bo/ThemeBo.php");
        require_once("engine/bo/GroupBo.php");
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
//				$people["mem_power"] = $fixationMember["fme_power"];
    			$people["mem_power"] = $theme["the_voting_power"];
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
        require_once("engine/bo/GroupBo.php");
        global $config;
        global $connection;

        $groupBo = GroupBo::newInstance($connection, $config);

		$group = $groupBo->getGroup($notice["not_target_id"], true);
        $members = array();

		foreach($group["gro_themes"] as $theme) {
			foreach($theme["fixation"]["members"] as $member) {
				$members[] = $member;
			}
		}

		return $members;
    }
    
    function addMotionNoticeVoters($queryBuilder, $filters) {
        global $config;
		//  personae group

        $personaeDatabase = "";

        if (isset($config["personae"]["db"]) && $config["personae"]["db"]) {
            $personaeDatabase = $config["personae"]["db"];
            $personaeDatabase .= ".";
        }

		$queryBuilder->join($personaeDatabase."dlp_groups",		        	"g.gro_id = not_target_id AND not_target_type = 'dlp_groups'",						"g", "left");
		$queryBuilder->join($personaeDatabase."dlp_group_themes",   		"ggt.gth_group_id = g.gro_id",														"ggt", "left");
		$queryBuilder->join($personaeDatabase."dlp_themes",		         	"gt.the_id = ggt.gth_theme_id",														"gt", "left");
		$queryBuilder->join($personaeDatabase."dlp_fixations",	        	"gtf.fix_id = gt.the_current_fixation_id AND gtf.fix_theme_type = 'dlp_themes'",	"gtf", "left");

		if (isset($filters["vot_member_id"])) {
			$queryBuilder->join($personaeDatabase."dlp_fixation_members",	"gtfm.fme_fixation_id = gtf.fix_id AND gtfm.fme_member_id = :vot_member_id",    	"gtfm", "left");
		}
		else {
			$queryBuilder->join($personaeDatabase."dlp_fixation_members",	"gtfm.fme_fixation_id = gtf.fix_id",									    		"gtfm", "left");
		}

		$queryBuilder->addSelect("gtfm.fme_power", "gta_vote_power");
		$queryBuilder->addSelect("gta.id_adh", "gta_id_adh");

		$userSource = UserSourceFactory::getInstance($config["modules"]["usersource"]);
		$userSource->upgradeQuery($queryBuilder, $config, "gtfm.fme_member_id", "gta");
    }

    function getMaxVotepower($motion) {
    	return $motion["gta_vote_power"];
    }

    function getVoterNotNull() {
    	return "(gta.id_adh IS NOT NULL)";
    }
}

?>
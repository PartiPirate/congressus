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
    along with Congressus.  If not, see <https://www.gnu.org/licenses/>.
*/

class CustomGroupSource {

    function getGroupKey() {
        return "cus_users";
    }

    function getGroupKeyLabel() {
        return array("key" => "cus_users", "label" => lang("notice_customUsers"));
    }

    function getGroupName() {
        return "Groupe Custom";
    }

    function getGroupOptions() {
        global $config;
        global $connection;

		echo "		<!-- Custom group -->\n";
		echo "			<option class=\"cus_users\" value=\"0\" >".lang("cgs_choose")."</option>\n";

//		foreach($galetteGroups as $listGroup) {
		    echo "		<option class=\"cus_users\"";
			echo "			value=\"" . -1 . "\">";
			echo $this->getGroupName();
			echo "		</option>\n";
//		}
    }

    function getGroupLabel($groupId) {
//        require_once("engine/bo/GaletteBo.php");
        global $config;
        global $connection;

        if ($groupId == -1) return $this->getGroupName();

        return null;
    }

    function updateNotice($meeting, &$notice, &$pings, &$usedPings) {
//        require_once("engine/bo/GaletteBo.php");
        global $config;
        global $connection;
        global $now;

        if ($notice["not_target_id"] != -1) return;

        // get members
        
        $customDatabase = $config["modules"]["custom"]["database"];

		$queryBuilder = QueryFactory::getInstance($config["database"]["dialect"]);
        $queryBuilder->select();

		$queryBuilder->addSelect($config["modules"]["custom"]["table"] . "." . $config["modules"]["custom"]["fields"]["id_adh"],      "id_adh");
		$queryBuilder->addSelect($config["modules"]["custom"]["table"] . "." . $config["modules"]["custom"]["fields"]["pseudo_adh"],  "pseudo_adh");
		$queryBuilder->addSelect($config["modules"]["custom"]["table"] . "." . $config["modules"]["custom"]["fields"]["login_adh"],   "login_adh");
		$queryBuilder->addSelect($config["modules"]["custom"]["table"] . "." . $config["modules"]["custom"]["fields"]["nom_adh"],     "nom_adh");
		$queryBuilder->addSelect($config["modules"]["custom"]["table"] . "." . $config["modules"]["custom"]["fields"]["prenom_adh"],  "prenom_adh");
		$queryBuilder->addSelect($config["modules"]["custom"]["table"] . "." . $config["modules"]["custom"]["fields"]["email_adh"],   "email_adh");

		$queryBuilder->from($customDatabase . "." . $config["modules"]["custom"]["table"]);

		$query = $queryBuilder->constructRequest();
		$statement = $connection->prepare($query);

		$members = array();

		$statement->execute($args);
		$members = $statement->fetchAll();

		foreach($members as $index => $line) {
			foreach($line as $field => $value) {
				if (is_numeric($field)) {
					unset($members[$index][$field]);
				}
			}
		}

		$notice["not_label"] = htmlspecialchars($this->getGroupName(), ENT_SUBSTITUTE);
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
//        require_once("engine/bo/GaletteBo.php");
        global $config;
        global $connection;

//        $galetteBo = GaletteBo::newInstance($connection, $config["galette"]["db"]);
//		$members = $galetteBo->getMembers(array("adh_group_ids" => array($notice["not_target_id"])));

        if ($notice["not_target_id"] != -1) return;

        // get members

        $customDatabase = $config["modules"]["custom"]["database"];

		$queryBuilder = QueryFactory::getInstance($config["database"]["dialect"]);
        $queryBuilder->select();

		$queryBuilder->addSelect($config["modules"]["custom"]["table"] . "." . $config["modules"]["custom"]["fields"]["id_adh"],      "id_adh");
		$queryBuilder->addSelect($config["modules"]["custom"]["table"] . "." . $config["modules"]["custom"]["fields"]["pseudo_adh"],  "pseudo_adh");
		$queryBuilder->addSelect($config["modules"]["custom"]["table"] . "." . $config["modules"]["custom"]["fields"]["login_adh"],   "login_adh");
		$queryBuilder->addSelect($config["modules"]["custom"]["table"] . "." . $config["modules"]["custom"]["fields"]["nom_adh"],     "nom_adh");
		$queryBuilder->addSelect($config["modules"]["custom"]["table"] . "." . $config["modules"]["custom"]["fields"]["prenom_adh"],  "prenom_adh");
		$queryBuilder->addSelect($config["modules"]["custom"]["table"] . "." . $config["modules"]["custom"]["fields"]["email_adh"],   "email_adh");

		$queryBuilder->from($customDatabase . "." . $config["modules"]["custom"]["table"]);

		$query = $queryBuilder->constructRequest();
		$statement = $connection->prepare($query);

		$members = array();

		$statement->execute($args);
		$members = $statement->fetchAll();

		foreach($members as $index => $line) {
			foreach($line as $field => $value) {
				if (is_numeric($field)) {
					unset($members[$index][$field]);
				}
			}
		}

		return $members;
    }

    function addMotionNoticeVoters($queryBuilder, $filters) {
        global $config;
		//  custom groups

        $customDatabase = $config["modules"]["custom"]["database"];

		if (isset($filters["vot_member_id"])) {
	    	$queryBuilder->join($customDatabase . "." . $config["modules"]["custom"]["table"], "-1 = not_target_id AND not_target_type = 'cus_users' AND cuga.".$config["modules"]["custom"]["fields"]["id_adh"]." = :vot_member_id",	"cuga", "left");
		}
		else {
    		$queryBuilder->join($customDatabase . "." . $config["modules"]["custom"]["table"], "-1 = not_target_id AND not_target_type = 'cus_users'",	"cuga", "left");
		}

		// TODO 2 <= externalize
		$queryBuilder->addSelect(2, "cuga_vote_power");
		$queryBuilder->addSelect("cuga." . $config["modules"]["custom"]["fields"]["id_adh"], "cuga_id_adh");
    }

    function getMaxVotepower($motion) {
    	return $motion["cuga_vote_power"];
    }

    function getVoterNotNull() {
        global $config;
    	return "(cuga." . $config["modules"]["custom"]["fields"]["id_adh"] . " IS NOT NULL)";
    }
}

?>
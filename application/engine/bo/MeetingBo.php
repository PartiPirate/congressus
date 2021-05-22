<?php /*
    Copyright 2015-2019 Cédric Levieux, Parti Pirate

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

class MeetingBo {
	var $pdo = null;
	var $config = null;
	var $personaeDatabase = "";

	var $TABLE = "meetings";
	var $ID_FIELD = "mee_id";

	const TYPE_MEETING		= "meeting";
	const TYPE_CONSTRUCTION = "construction";
	const TYPE_GATHERING	= "gathering";

	const STATUS_OPEN			= "open";
	const STATUS_CLOSED 		= "closed";
	const STATUS_TEMPLATE		= "template";
	const STATUS_WAITING		= "waiting";
	const STATUS_DELETED		= "deleted";
	const STATUS_CONSTRUCTION	= "construction";

	function __construct($pdo, $config) {
		$this->config = $config;
		$this->pdo = $pdo;
		$this->personaeDatabase = $this->config["personae"]["db"] ? $this->config["personae"]["db"] . "." : "";
	}

	static function newInstance($pdo, $config) {
		return new MeetingBo($pdo, $config);
	}

	function create(&$meeting) {
		return BoHelper::create($meeting, $this->TABLE, $this->ID_FIELD, $this->config, $this->pdo);
	}

	function update($meeting) {
		return BoHelper::update($meeting, $this->TABLE, $this->ID_FIELD, $this->config, $this->pdo);
	}

	function save(&$meeting) {
 		if (!isset($meeting[$this->ID_FIELD]) || !$meeting[$this->ID_FIELD]) {
			$this->create($meeting);
		}

		$this->update($meeting);
	}

	function getById($id, $withLocation = false) {
		$filters = array($this->ID_FIELD => intval($id));

		if ($withLocation) {
			$filters["with_principal_location"] = true;			
		}
		
		$results = $this->getByFilters($filters);

		if (count($results)) {
			return $results[0];
		}

		return null;
	}

	function getNumberOfVoters($meetingId) {
		$queryBuilder = QueryFactory::getInstance($this->config["database"]["dialect"]);

		$queryBuilder->select($this->TABLE);
		$queryBuilder->addSelect("COUNT(DISTINCT(vot_member_id)) as mee_number_of_voters");
		$queryBuilder->join("agendas", "age_meeting_id = mee_id");
		$queryBuilder->join("motions", "age_id = mot_agenda_id");
		$queryBuilder->join("motion_propositions", "mpr_motion_id = mot_id");
		$queryBuilder->join("votes", "vot_motion_proposition_id = mpr_id");
		
		$args = array();
		$args[$this->ID_FIELD] = $meetingId;
		$queryBuilder->where("$this->ID_FIELD = :$this->ID_FIELD");

		$query = $queryBuilder->constructRequest();
		$statement = $this->pdo->prepare($query);
//		echo showQuery($query, $args);
//		error_log(showQuery($query, $args));

		$results = array();

		try {
			$statement->execute($args);
			$results = $statement->fetchAll();

			foreach($results as $index => $line) {
				foreach($line as $field => $value) {
					if ($field == "mee_number_of_voters") return $value;
				}
			}
		}
		catch(Exception $e){
			echo 'Erreur de requète : ', $e->getMessage();
		}
		
		return 0;
	}

	function getByFilters($filters = null) {
		if (!$filters) $filters = array();
		$args = array();

		$queryBuilder = QueryFactory::getInstance($this->config["database"]["dialect"]);

		$queryBuilder->select($this->TABLE);
		$queryBuilder->addSelect("*");

		if ((isset($filters["with_principal_location"]) && $filters["with_principal_location"]) || isset($filters["loc_text_channel"])) {
			$queryBuilder->join("locations", "loc_meeting_id = $this->ID_FIELD AND loc_principal = 1", null, "left");
		}

		if (isset($filters["by_notice"])) {
			$queryBuilder->join("notices", "not_meeting_id = $this->ID_FIELD");
		}

		if (isset($filters["limit_to_viewable_groups"])) {
			$queryBuilder->join("notices", "not_meeting_id = $this->ID_FIELD");

			$groupWheres = array();
			foreach($filters["limit_to_viewable_groups"] as $group) {
				switch($group["type"]) {
					case "theme";
						$type = "dlp_themes";
						break;
					case "group";
						$type = "dlp_groups";
						break;
					default:
						$type = "";
				}
				$groupWhere = "(not_target_type = '" . $type . "' AND not_target_id = '" . $group["id"] . "')";
				$groupWheres[] = $groupWhere;
			}

			$queryBuilder->where("(" . implode(" OR ", $groupWheres) . ")");
		}

		if (isset($filters["by_personae_group"])) {
			$queryBuilder->join("notices", "not_meeting_id = $this->ID_FIELD AND not_target_type = 'dlp_groups'");
			//print_r($this->config);
			$queryBuilder->join($this->personaeDatabase."dlp_groups", "gro_id = not_target_id");
		}

		if (isset($filters["mee_from"])) {
			$args["mee_from"] = $filters["mee_from"];
			$queryBuilder->where("DATE_ADD(mee_datetime, INTERVAL mee_expected_duration MINUTE) >= :mee_from");
		}

		if (isset($filters["mee_to"])) {
			$args["mee_to"] = $filters["mee_to"];
			$queryBuilder->where("mee_datetime <= :mee_to");
		}

		if (isset($filters[$this->ID_FIELD])) {
			$args[$this->ID_FIELD] = $filters[$this->ID_FIELD];
			$queryBuilder->where("$this->ID_FIELD = :$this->ID_FIELD");
		}

		if (isset($filters["mee_secretary_member_id"])) {
			$args["mee_secretary_member_id"] = $filters["mee_secretary_member_id"];
			$queryBuilder->where("mee_secretary_member_id = :mee_secretary_member_id");
		}

		if (isset($filters["loc_text_channel"])) {
			$args["loc_text_channel"] = $filters["loc_text_channel"] . ",%";
			$queryBuilder->where("loc_channel LIKE :loc_text_channel");
		}

		if (isset($filters["with_status"])) {
			$status = "mee_status IN ('";
			$status .= implode("', '", $filters["with_status"]);
			$status .= " ')";
			$queryBuilder->where("$status");
		}

		if (isset($filters["by_personae_group"])) {
			$queryBuilder->orderBy("gro_label");
		}

		if (isset($filters["limit"])) {
			$queryBuilder->limit($filters["limit"]);
		}

		if (isset($filters["older_first"]) && $filters["older_first"]) {
			$queryBuilder->orderAscBy("mee_datetime");
		}
		else {
			$queryBuilder->orderDescBy("mee_datetime");
		}

		$query = $queryBuilder->constructRequest();
		$statement = $this->pdo->prepare($query);
//		echo "<!--";
//		echo showQuery($query, $args);
//		echo "-->";
//		error_log(showQuery($query, $args));

		$results = array();

		try {
			$statement->execute($args);
			$results = $statement->fetchAll();

			foreach($results as $index => $line) {
				foreach($line as $field => $value) {
					if (is_numeric($field)) {
						unset($results[$index][$field]);
					}
				}
			}
		}
		catch(Exception $e){
			echo 'Erreur de requète : ', $e->getMessage();
		}

		return $results;
	}
}
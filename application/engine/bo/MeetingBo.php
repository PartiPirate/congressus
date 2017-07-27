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
    along with Congressus.  If not, see <http://www.gnu.org/licenses/>.
*/

class MeetingBo {
	var $pdo = null;
	var $config = null;
	var $galetteDatabase = "";
	var $personaeDatabase = "";

	var $TABLE = "meetings";
	var $ID_FIELD = "mee_id";

	function __construct($pdo, $config) {
		$this->config = $config;

		$this->galetteDatabase = $config["galette"]["db"] . ".";
		$this->personaeDatabase = $config["personae"]["db"] . ".";

		$this->pdo = $pdo;
	}

	static function newInstance($pdo, $config) {
		return new MeetingBo($pdo, $config);
	}

	function create(&$meeting) {
		$query = "	INSERT INTO $this->TABLE () VALUES ()	";

		$statement = $this->pdo->prepare($query);
//				echo showQuery($query, $args);

		try {
			$statement->execute();
			$meeting[$this->ID_FIELD] = $this->pdo->lastInsertId();

			return true;
		}
		catch(Exception $e){
			echo 'Erreur de requète : ', $e->getMessage();
		}

		return false;
	}

	function update($meeting) {
		$query = "	UPDATE $this->TABLE SET ";

		$separator = "";
		foreach($meeting as $field => $value) {
			$query .= $separator;
			$query .= $field . " = :". $field;
			$separator = ", ";
		}

		$query .= "	WHERE $this->ID_FIELD = :$this->ID_FIELD ";

//		echo showQuery($query, $meeting);

		$statement = $this->pdo->prepare($query);
		$statement->execute($meeting);
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

	function getByFilters($filters = null) {
		if (!$filters) $filters = array();
		$args = array();

		$queryBuilder = QueryFactory::getInstance($this->config["database"]["dialect"]);

		$queryBuilder->select($this->TABLE);
		$queryBuilder->addSelect("*");

//		$query = "	SELECT *
//					FROM  $this->TABLE ";

		if (isset($filters["with_principal_location"]) && $filters["with_principal_location"]) {
//			$query .= " LEFT JOIN locations ON loc_meeting_id = $this->ID_FIELD AND loc_principal = 1 ";
			$queryBuilder->join("locations", "loc_meeting_id = $this->ID_FIELD AND loc_principal = 1", null, "left");
		}

		if (isset($filters["by_personae_group"])) {
			$queryBuilder->join("notices", "not_meeting_id = $this->ID_FIELD AND not_target_type = 'dlp_groups'");
			$queryBuilder->join($this->personaeDatabase."dlp_groups", "gro_id = not_target_id");
//			$query .= " JOIN notices ON not_meeting_id = $this->ID_FIELD AND not_target_type = 'dlp_groups' ";
//			$query .= "	JOIN ".$this->personaeDatabase."dlp_groups ON gro_id = not_target_id ";
		}

//		$query .= " WHERE
//						1 = 1 \n";

		if (isset($filters[$this->ID_FIELD])) {
			$args[$this->ID_FIELD] = $filters[$this->ID_FIELD];
//			$query .= " AND $this->ID_FIELD = :$this->ID_FIELD \n";
			$queryBuilder->where("$this->ID_FIELD = :$this->ID_FIELD");
		}

		if (isset($filters["mee_secretary_member_id"])) {
			$args["mee_secretary_member_id"] = $filters["mee_secretary_member_id"];
//			$query .= " AND mee_secretary_member_id = :mee_secretary_member_id \n";
			$queryBuilder->where("mee_secretary_member_id = :mee_secretary_member_id");
		}

		if (isset($filters["with_status"])) {
//			$query .= " AND mee_status IN ('";
//			$query .= implode("', '", $filters["with_status"]);
//			$query .= " ')";
			$status = "mee_status IN ('";
			$status .= implode("', '", $filters["with_status"]);
			$status .= " ')";
			$queryBuilder->where("$status");
		}

//		$query .= "	ORDER BY ";

		if (isset($filters["by_personae_group"])) {
//			$query .= "gro_label, ";
			$queryBuilder->orderBy("gro_label");
		}

//		$query .= "mee_datetime ";
		$queryBuilder->orderBy("mee_datetime");

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
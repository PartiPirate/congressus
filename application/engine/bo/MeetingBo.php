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

class MeetingBo {
	var $pdo = null;
	var $config = null;
	var $personaeDatabase = "";

	var $TABLE = "meetings";
	var $ID_FIELD = "mee_id";

	function __construct($pdo, $config) {
		$this->config = $config;

		$this->personaeDatabase = $config["personae"]["db"] . ".";

		$this->pdo = $pdo;
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

	function getByFilters($filters = null) {
		if (!$filters) $filters = array();
		$args = array();

		$queryBuilder = QueryFactory::getInstance($this->config["database"]["dialect"]);

		$queryBuilder->select($this->TABLE);
		$queryBuilder->addSelect("*");

		if (isset($filters["with_principal_location"]) && $filters["with_principal_location"]) {
			$queryBuilder->join("locations", "loc_meeting_id = $this->ID_FIELD AND loc_principal = 1", null, "left");
		}

		if (isset($filters["by_personae_group"])) {
			$queryBuilder->join("notices", "not_meeting_id = $this->ID_FIELD AND not_target_type = 'dlp_groups'");
			$queryBuilder->join($this->personaeDatabase."dlp_groups", "gro_id = not_target_id");
		}

		if (isset($filters[$this->ID_FIELD])) {
			$args[$this->ID_FIELD] = $filters[$this->ID_FIELD];
			$queryBuilder->where("$this->ID_FIELD = :$this->ID_FIELD");
		}

		if (isset($filters["mee_secretary_member_id"])) {
			$args["mee_secretary_member_id"] = $filters["mee_secretary_member_id"];
			$queryBuilder->where("mee_secretary_member_id = :mee_secretary_member_id");
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
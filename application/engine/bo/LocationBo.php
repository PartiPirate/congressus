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

class LocationBo {
	var $pdo = null;
	var $config = null;

	var $TABLE = "locations";
	var $ID_FIELD = "loc_id";

	function __construct($pdo, $config) {
		$this->config = $config;
		$this->pdo = $pdo;
	}

	static function newInstance($pdo, $config) {
		return new LocationBo($pdo, $config);
	}

	function create(&$location) {
		$query = "	INSERT INTO $this->TABLE () VALUES ()	";

		$statement = $this->pdo->prepare($query);
//				echo showQuery($query, $args);

		try {
			$statement->execute();
			$location[$this->ID_FIELD] = $this->pdo->lastInsertId();

			return true;
		}
		catch(Exception $e){
			echo 'Erreur de requète : ', $e->getMessage();
		}

		return false;
	}

	function update($location) {
		$query = "	UPDATE $this->TABLE SET ";

		$separator = "";
		foreach($location as $field => $value) {
			$query .= $separator;
			$query .= $field . " = :". $field;
			$separator = ", ";
		}

		$query .= "	WHERE $this->ID_FIELD = :$this->ID_FIELD ";

//		echo showQuery($query, $location);

		$statement = $this->pdo->prepare($query);
		$statement->execute($location);
	}

	function save(&$location) {
 		if (!isset($location[$this->ID_FIELD]) || !$location[$this->ID_FIELD]) {
			$this->create($location);
		}

		$this->update($location);
	}

	function getById($id) {
		$filters = array($this->ID_FIELD => intval($id));

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
//					FROM  $this->TABLE
//					WHERE
//						1 = 1 \n";

		if (isset($filters[$this->ID_FIELD])) {
			$args[$this->ID_FIELD] = $filters[$this->ID_FIELD];
//			$query .= " AND $this->ID_FIELD = :$this->ID_FIELD \n";
			$queryBuilder->where("$this->ID_FIELD = :$this->ID_FIELD");
		}

		if (isset($filters["loc_meeting_id"])) {
			$args["loc_meeting_id"] = $filters["loc_meeting_id"];
//			$query .= " AND loc_meeting_id = :loc_meeting_id \n";
			$queryBuilder->where("loc_meeting_id = :loc_meeting_id");
		}

//		$query .= "	ORDER BY gro_label, the_label ";

		$query = $queryBuilder->constructRequest();
		$statement = $this->pdo->prepare($query);
//		echo showQuery($query, $args);

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
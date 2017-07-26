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
    along with Congressus.  If age, see <http://www.gnu.org/licenses/>.
*/

class GuestBo {
	var $pdo = null;
	var $config = null;

	var $TABLE = "guests";
	var $ID_FIELD = "gue_id";

	function __construct($pdo, $config) {
		$this->config = $config;
		$this->pdo = $pdo;
	}

	static function newInstance($pdo, $config) {
		return new GuestBo($pdo, $config);
	}

	function create(&$guest) {
		$query = "	INSERT INTO $this->TABLE () VALUES ()	";

		$statement = $this->pdo->prepare($query);
//				echo showQuery($query, $args);

		try {
			$statement->execute();
			$guest[$this->ID_FIELD] = $this->pdo->lastInsertId();

			return true;
		}
		catch(Exception $e){
			echo 'Erreur de requète : ', $e->getMessage();
		}

		return false;
	}

	function update($guest) {
		$query = "	UPDATE $this->TABLE SET ";

		$separator = "";
		foreach($guest as $field => $value) {
			$query .= $separator;
			$query .= $field . " = :". $field;
			$separator = ", ";
		}

		$query .= "	WHERE $this->ID_FIELD = :$this->ID_FIELD ";

//		echo showQuery($query, $guest);

		$statement = $this->pdo->prepare($query);
		$statement->execute($guest);
	}

	function save(&$guest) {
 		if (!isset($guest[$this->ID_FIELD]) || !$guest[$this->ID_FIELD]) {
			$this->create($guest);
		}

		$this->update($guest);
	}

	function delete($guest) {
		$query = "	DELETE FROM $this->TABLE ";

		$query .= "	WHERE $this->ID_FIELD = :$this->ID_FIELD ";

		//		echo showQuery($query, $guest);

		$args = array($this->ID_FIELD => $guest[$this->ID_FIELD]);

		$statement = $this->pdo->prepare($query);
		$statement->execute($args);
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
		$queryBuilder->addSelect($this->TABLE . ".*");

//		$query = "	SELECT *
//					FROM  $this->TABLE
//					WHERE
//						1 = 1 \n";

		if (isset($filters[$this->ID_FIELD])) {
			$args[$this->ID_FIELD] = $filters[$this->ID_FIELD];
//			$query .= " AND $this->ID_FIELD = :$this->ID_FIELD \n";
			$queryBuilder->where("$this->ID_FIELD = :$this->ID_FIELD");
		}

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
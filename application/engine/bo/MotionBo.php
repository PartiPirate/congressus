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

class MotionBo {
	var $pdo = null;

	var $TABLE = "motions";
	var $ID_FIELD = "mot_id";

	var $TABLE_PROPOSITION = "motion_propositions";
	var $ID_FIELD_PROPOSITION = "mpr_id";

	function __construct($pdo) {
		$this->pdo = $pdo;
	}

	static function newInstance($pdo) {
		return new MotionBo($pdo);
	}

	function create(&$motion) {
		$query = "	INSERT INTO $this->TABLE () VALUES ()	";

		$statement = $this->pdo->prepare($query);
//				echo showQuery($query, $args);

		try {
			$statement->execute();
			$motion[$this->ID_FIELD] = $this->pdo->lastInsertId();

			return true;
		}
		catch(Exception $e){
			echo 'Erreur de requète : ', $e->getMessage();
		}

		return false;
	}

	function update($motion) {
		$query = "	UPDATE $this->TABLE SET ";

		$separator = "";
		foreach($motion as $field => $value) {
			$query .= $separator;
			$query .= $field . " = :". $field;
			$separator = ", ";
		}

		$query .= "	WHERE $this->ID_FIELD = :$this->ID_FIELD ";

//		echo showQuery($query, $motion);

		$statement = $this->pdo->prepare($query);
		$statement->execute($motion);
	}

	function save(&$motion) {
 		if (!isset($motion[$this->ID_FIELD]) || !$motion[$this->ID_FIELD]) {
			$this->create($motion);
		}

		$this->update($motion);
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

		$query = "	SELECT *
					FROM $this->TABLE
					LEFT JOIN motion_propositions ON mpr_motion_id = mot_id
					WHERE
						1 = 1 \n";

		if (isset($filters[$this->ID_FIELD])) {
			$args[$this->ID_FIELD] = $filters[$this->ID_FIELD];
			$query .= " AND $this->ID_FIELD = :$this->ID_FIELD \n";
		}

		if (isset($filters[$this->ID_FIELD_PROPOSITION])) {
			$args[$this->ID_FIELD_PROPOSITION] = $filters[$this->ID_FIELD_PROPOSITION];
			$query .= " AND $this->ID_FIELD_PROPOSITION = :$this->ID_FIELD_PROPOSITION \n";
		}

		if (isset($filters["mot_agenda_id"])) {
			$args["mot_agenda_id"] = $filters["mot_agenda_id"];
			$query .= " AND mot_agenda_id = :mot_agenda_id \n";
		}

		if (!isset($filters["with_deleted"])) {
			$query .= " AND mot_deleted = 0 \n";
		}

//		$query .= "	ORDER BY mot_parent_id ASC , mot_order ASC ";

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

	function createProposition(&$proposition) {
		$query = "	INSERT INTO $this->TABLE_PROPOSITION () VALUES ()	";

		$statement = $this->pdo->prepare($query);
		//				echo showQuery($query, $args);

		try {
			$statement->execute();
			$proposition[$this->ID_FIELD_PROPOSITION] = $this->pdo->lastInsertId();

			return true;
		}
		catch(Exception $e){
			echo 'Erreur de requète : ', $e->getMessage();
		}

		return false;
	}

	function updateProposition($proposition) {
		$query = "	UPDATE $this->TABLE_PROPOSITION SET ";

		$separator = "";
		foreach($proposition as $field => $value) {
			$query .= $separator;
			$query .= $field . " = :". $field;
			$separator = ", ";
		}

		$query .= "	WHERE $this->ID_FIELD_PROPOSITION = :$this->ID_FIELD_PROPOSITION ";

//		echo showQuery($query, $proposition);

		$statement = $this->pdo->prepare($query);
		$statement->execute($proposition);
	}

	function saveProposition(&$proposition) {
		if (!isset($proposition[$this->ID_FIELD_PROPOSITION]) || !$proposition[$this->ID_FIELD_PROPOSITION]) {
			$this->createProposition($proposition);
		}

		$this->updateProposition($proposition);
	}

	function deleteProposition($proposition) {
		$query = "	DELETE FROM $this->TABLE_PROPOSITION ";

		$query .= "	WHERE $this->ID_FIELD_PROPOSITION = :$this->ID_FIELD_PROPOSITION ";

		//		echo showQuery($query, $agenda);

		$args = array($this->ID_FIELD_PROPOSITION => $proposition[$this->ID_FIELD_PROPOSITION]);

		$statement = $this->pdo->prepare($query);
		$statement->execute($args);
	}

}
<?php /*
	Copyright 2015-2016 Cédric Levieux, Parti Pirate

	This file is part of Fabrilia.

    Fabrilia is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    Fabrilia is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with Fabrilia.  If age, see <http://www.gnu.org/licenses/>.
*/

class SkillUserBo {
	var $pdo = null;
	var $galetteDatabase = "";

	var $TABLE = "skill_users";
	var $ID_FIELD = "sus_id";

	function __construct($pdo, $config) {
		$this->galetteDatabase = $config["galette"]["db"] . ".";

		$this->pdo = $pdo;
	}

	static function newInstance($pdo, $config) {
		return new SkillUserBo($pdo, $config);
	}

	function create(&$skillUser) {
		$query = "	INSERT INTO ".$this->galetteDatabase."$this->TABLE () VALUES ()	";

		$statement = $this->pdo->prepare($query);
//				echo showQuery($query, $args);

		try {
			$statement->execute();
			$skillUser[$this->ID_FIELD] = $this->pdo->lastInsertId();

			return true;
		}
		catch(Exception $e){
			echo 'Erreur de requète : ', $e->getMessage();
		}

		return false;
	}

	function update($skillUser) {
		$query = "	UPDATE ".$this->galetteDatabase."$this->TABLE SET ";

		$separator = "";
		foreach($skillUser as $field => $value) {
			$query .= $separator;
			$query .= $field . " = :". $field;
			$separator = ", ";
		}

		$query .= "	WHERE $this->ID_FIELD = :$this->ID_FIELD ";

//		echo showQuery($query, $skillUser);

		$statement = $this->pdo->prepare($query);
		$statement->execute($skillUser);
	}

	function save(&$skillUser) {
 		if (!isset($skillUser[$this->ID_FIELD]) || !$skillUser[$this->ID_FIELD]) {
			$this->create($skillUser);
		}

		$this->update($skillUser);
	}

	function delete($skillUser) {
		$query = "	DELETE FROM ".$this->galetteDatabase."$this->TABLE ";
	
		$query .= "	WHERE $this->ID_FIELD = :$this->ID_FIELD ";
	
		//		echo showQuery($query, $skillUser);
	
		$args[$this->ID_FIELD] = $skillUser[$this->ID_FIELD];
	
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

		$query = "	SELECT * ";

			if (isset($filters["with_endorsments"]) && $filters["with_endorsments"]) {
			$query .= ", (SELECT COUNT(*) FROM ".$this->galetteDatabase."skill_endorsments WHERE sen_skill_user_id = sus_id) AS sus_total_endorsments \n";
		}
		
		if (isset($filters["is_endorser"]) && $filters["is_endorser"]) {
			$query .= ", (SELECT COUNT(*) FROM ".$this->galetteDatabase."skill_endorsments WHERE sen_skill_user_id = sus_id AND sen_user_id = :is_endorser) AS sus_is_endorser \n";
			$args["is_endorser"] = $filters["is_endorser"];
		}
		
		$query .= "	FROM ".$this->galetteDatabase."$this->TABLE ";

		if (isset($filters["with_label"]) && $filters["with_label"]) {
			$query .= " JOIN ".$this->galetteDatabase."skills ON sus_skill_id = ski_id \n";
		}

		$query .= "	WHERE
						1 = 1 \n";

		if (isset($filters[$this->ID_FIELD])) {
			$args[$this->ID_FIELD] = $filters[$this->ID_FIELD];
			$query .= " AND $this->ID_FIELD = :$this->ID_FIELD \n";
		}

		if (isset($filters["sus_user_id"])) {
			$args["sus_user_id"] = $filters["sus_user_id"];
			$query .= " AND sus_user_id = :sus_user_id \n";
		}

		
		//		$query .= "	ORDER BY ski_label ASC";

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
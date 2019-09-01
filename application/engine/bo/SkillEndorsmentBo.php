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

class SkillEndorsmentBo {
	var $pdo = null;
	var $galetteDatabase = "";

	var $TABLE = "skill_endorsments";
	var $ID_FIELD = "sen_id";

	function __construct($pdo, $config) {
		$this->galetteDatabase = $config["galette"]["db"] . ".";

		$this->pdo = $pdo;
	}

	static function newInstance($pdo, $config) {
		return new SkillEndorsmentBo($pdo, $config);
	}

	function create(&$skillEndorsment) {
		$query = "	INSERT INTO ".$this->galetteDatabase."$this->TABLE () VALUES ()	";

		$statement = $this->pdo->prepare($query);
//		echo showQuery($query, $skillEndorsment);

		try {
			$statement->execute();
			$skillEndorsment[$this->ID_FIELD] = $this->pdo->lastInsertId();

			return true;
		}
		catch(Exception $e){
			echo 'Erreur de requète : ', $e->getMessage();
		}

		return false;
	}

	function update($skillEndorsment) {
		$query = "	UPDATE ".$this->galetteDatabase."$this->TABLE SET ";

		$separator = "";
		foreach($skillEndorsment as $field => $value) {
			$query .= $separator;
			$query .= $field . " = :". $field;
			$separator = ", ";
		}

		$query .= "	WHERE $this->ID_FIELD = :$this->ID_FIELD ";
//		echo showQuery($query, $skillEndorsment);

		$statement = $this->pdo->prepare($query);
		$statement->execute($skillEndorsment);
	}

	function save(&$skillEndorsment) {
 		if (!isset($skillEndorsment[$this->ID_FIELD]) || !$skillEndorsment[$this->ID_FIELD]) {
			$this->create($skillEndorsment);
		}

		$this->update($skillEndorsment);
	}

	function delete($skillEndorsment) {
		$query = "	DELETE FROM ".$this->galetteDatabase."$this->TABLE ";
		
		$query .= "	WHERE $this->ID_FIELD = :$this->ID_FIELD ";
	
		//		echo showQuery($query, $skillEndorsment);
	
		$args[$this->ID_FIELD] = $skillEndorsment[$this->ID_FIELD];
		
		$statement = $this->pdo->prepare($query);
		$statement->execute($args);
	}

	function getRandomSkillUser($userId) {
		$query = "	SELECT *
					FROM ".$this->galetteDatabase."skill_users
					JOIN ".$this->galetteDatabase."skills ON ski_id = sus_skill_id
					JOIN ".$this->galetteDatabase."galette_adherents ON id_adh = sus_user_id
					LEFT JOIN ".$this->galetteDatabase."skill_endorsments ON sus_id = sen_skill_user_id AND sen_user_id = :id_adh
					WHERE 1
					AND sus_user_id != :id_adh
					AND sen_id IS NULL
					ORDER BY rand()	
					LIMIT 0, 1 ";

		$args["id_adh"] = $userId;
		
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

		if (count($results)) {
//			print_r($results[0]);
			
			return $results[0];
		}
		
		return null;
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
					FROM ".$this->galetteDatabase."$this->TABLE
					WHERE
						1 = 1 \n";

		if (isset($filters[$this->ID_FIELD])) {
			$args[$this->ID_FIELD] = $filters[$this->ID_FIELD];
			$query .= " AND $this->ID_FIELD = :$this->ID_FIELD \n";
		}

		$query .= "	ORDER BY ski_label ASC";

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
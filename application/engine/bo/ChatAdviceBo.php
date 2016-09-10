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

class ChatAdviceBo {
	var $pdo = null;

	var $TABLE = "chat_advices";
	var $ID_FIELD = "cad_id";

	function __construct($pdo) {
		$this->pdo = $pdo;
	}

	static function newInstance($pdo) {
		return new ChatAdviceBo($pdo);
	}

	function create(&$chatAdvice) {
		$query = "	INSERT INTO $this->TABLE () VALUES ()	";

		$statement = $this->pdo->prepare($query);
//				echo showQuery($query, $args);

		try {
			$statement->execute();
			$chatAdvice[$this->ID_FIELD] = $this->pdo->lastInsertId();

			return true;
		}
		catch(Exception $e){
			echo 'Erreur de requète : ', $e->getMessage();
		}

		return false;
	}

	function update($chatAdvice) {
		$query = "	UPDATE $this->TABLE SET ";

		$separator = "";
		foreach($chatAdvice as $field => $value) {
			$query .= $separator;
			$query .= $field . " = :". $field;
			$separator = ", ";
		}

		$query .= "	WHERE $this->ID_FIELD = :$this->ID_FIELD ";

//		echo showQuery($query, $chatAdvice);

		$statement = $this->pdo->prepare($query);
		$statement->execute($chatAdvice);
	}

	function save(&$chatAdvice) {
 		if (!isset($chatAdvice[$this->ID_FIELD]) || !$chatAdvice[$this->ID_FIELD]) {
			$this->create($chatAdvice);
		}

		$this->update($chatAdvice);
	}

	function delete($chatAdvice) {
		$query = "	DELETE FROM $this->TABLE ";

		$query .= "	WHERE $this->ID_FIELD = :$this->ID_FIELD ";

		//		echo showQuery($query, $chatAdvice);

		$args = array($this->ID_FIELD => $chatAdvice[$this->ID_FIELD]);

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

		if (isset($filters["with_count_motions"]) && $filters["with_count_motions"]) {
			$query .= "	, (SELECT COUNT(*) FROM motions WHERE mot_chatAdvice_id = age_id AND mot_deleted = 0) AS age_number_of_motions ";
		}

		$query .= "	FROM  $this->TABLE
					WHERE
						1 = 1 \n";

		if (isset($filters[$this->ID_FIELD])) {
			$args[$this->ID_FIELD] = $filters[$this->ID_FIELD];
			$query .= " AND $this->ID_FIELD = :$this->ID_FIELD \n";
		}

		if (isset($filters["cad_chat_id"])) {
			$args["cad_chat_id"] = $filters["cad_chat_id"];
			$query .= " AND cad_chat_id = :cad_chat_id \n";
		}

		if (isset($filters["cad_user_id"])) {
			$args["cad_user_id"] = $filters["cad_user_id"];
			$query .= " AND cad_user_id = :cad_user_id \n";
		}

		if (isset($filters["cad_advice"])) {
			$args["cad_advice"] = $filters["cad_advice"];
			$query .= " AND cad_advice = :cad_advice \n";
		}
		
		$query .= "	ORDER BY cad_advice ASC ";

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
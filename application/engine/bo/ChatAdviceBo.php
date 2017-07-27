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
    along with Congressus.  If age, see <http://www.gnu.org/licenses/>.
*/

class ChatAdviceBo {
	var $pdo = null;
	var $config = null;

	var $TABLE = "chat_advices";
	var $ID_FIELD = "cad_id";

	function __construct($pdo, $config) {
		$this->config = $config;
		$this->pdo = $pdo;
	}

	static function newInstance($pdo, $config) {
		return new ChatAdviceBo($pdo, $config);
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

		$queryBuilder = QueryFactory::getInstance($this->config["database"]["dialect"]);
		$queryBuilder->select($this->TABLE);
		$queryBuilder->addSelect("*");

		if (isset($filters["cad_agenda_id"]) && $filters["cad_agenda_id"]) {
			$queryBuilder->join("chats", "cha_id = cad_chat_id", null, "left");
		}

		if (isset($filters[$this->ID_FIELD])) {
			$args[$this->ID_FIELD] = $filters[$this->ID_FIELD];
			$queryBuilder->where("$this->ID_FIELD = :$this->ID_FIELD");
		}

		if (isset($filters["cad_agenda_id"]) && $filters["cad_agenda_id"]) {
			$args["cad_agenda_id"] = $filters["cad_agenda_id"];
			$queryBuilder->where("cha_agenda_id = :cad_agenda_id");
		}
		
		if (isset($filters["cad_chat_id"])) {
			$args["cad_chat_id"] = $filters["cad_chat_id"];
			$queryBuilder->where("cad_chat_id = :cad_chat_id");
		}

		if (isset($filters["cad_user_id"])) {
			$args["cad_user_id"] = $filters["cad_user_id"];
			$queryBuilder->where("cad_user_id = :cad_user_id");
		}

		if (isset($filters["cad_advice"])) {
			$args["cad_advice"] = $filters["cad_advice"];
			$queryBuilder->where("cad_advice = :cad_advice");
		}
		
		$queryBuilder->orderBy("cad_advice");

		$query = $queryBuilder->constructRequest();
		$statement = $this->pdo->prepare($query);

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
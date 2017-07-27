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

class ChatBo {
	var $pdo = null;
	var $config = null;
	var $galetteDatabase = "";

	var $TABLE = "chats";
	var $ID_FIELD = "cha_id";

	function __construct($pdo, $config) {
		$this->config = $config;
		$this->galetteDatabase = $config["galette"]["db"] . ".";

		$this->pdo = $pdo;
	}

	static function newInstance($pdo, $config) {
		return new ChatBo($pdo, $config);
	}

	function create(&$chat) {
		$query = "	INSERT INTO $this->TABLE () VALUES ()	";

		$statement = $this->pdo->prepare($query);

		try {
			$statement->execute();
			$chat[$this->ID_FIELD] = $this->pdo->lastInsertId();

			return true;
		}
		catch(Exception $e){
			echo 'Erreur de requète : ', $e->getMessage();
		}

		return false;
	}

	function update($chat) {
		$query = "	UPDATE $this->TABLE SET ";

		$separator = "";
		foreach($chat as $field => $value) {
			$query .= $separator;
			$query .= $field . " = :". $field;
			$separator = ", ";
		}

		$query .= "	WHERE $this->ID_FIELD = :$this->ID_FIELD ";

//		echo showQuery($query, $chat);

		$statement = $this->pdo->prepare($query);
		$statement->execute($chat);
	}

	function save(&$chat) {
 		if (!isset($chat[$this->ID_FIELD]) || !$chat[$this->ID_FIELD]) {
			$this->create($chat);
		}

		$this->update($chat);
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
		$queryBuilder->addSelect($this->TABLE . ".*")->addSelect("agendas.*")->addSelect("pings.*")->addSelect("galette_adherents.*");

		$queryBuilder->join($this->galetteDatabase."galette_adherents", "id_adh = cha_member_id", null, "left");
		$queryBuilder->join("agendas", "age_id = cha_agenda_id", null, "left");
		$queryBuilder->join("pings", "pin_guest_id = cha_guest_id", null, "left");

		if (isset($filters[$this->ID_FIELD])) {
			$args[$this->ID_FIELD] = $filters[$this->ID_FIELD];
//			$query .= " AND $this->ID_FIELD = :$this->ID_FIELD \n";
			$queryBuilder->where("$this->ID_FIELD = :$this->ID_FIELD");
		}

		if (isset($filters["cha_agenda_id"])) {
			$args["cha_agenda_id"] = $filters["cha_agenda_id"];
//			$query .= " AND cha_agenda_id = :cha_agenda_id \n";
			$queryBuilder->where("cha_agenda_id = :cha_agenda_id");
		}

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
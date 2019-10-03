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
    along with Congressus.  If not, see <https://www.gnu.org/licenses/>.
*/

class ChatBo {
	var $pdo = null;
	var $config = null;

	var $TABLE = "chats";
	var $ID_FIELD = "cha_id";

	function __construct($pdo, $config) {
		$this->config = $config;
		$this->pdo = $pdo;
	}

	static function newInstance($pdo, $config) {
		return new ChatBo($pdo, $config);
	}

	function create(&$chat) {
		return BoHelper::create($chat, $this->TABLE, $this->ID_FIELD, $this->config, $this->pdo);
	}

	function update($chat) {
		return BoHelper::update($chat, $this->TABLE, $this->ID_FIELD, $this->config, $this->pdo);
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
		$queryBuilder->addSelect($this->TABLE . ".*")->addSelect("agendas.*")->addSelect("pings.*");

		$userSource = UserSourceFactory::getInstance($this->config["modules"]["usersource"]);
		$userSource->upgradeQuery($queryBuilder, $this->config, "cha_member_id");

		$queryBuilder->join("agendas", "age_id = cha_agenda_id", null, "left");
		$queryBuilder->join("pings", "pin_guest_id = cha_guest_id", null, "left");

		if (isset($filters[$this->ID_FIELD])) {
			$args[$this->ID_FIELD] = $filters[$this->ID_FIELD];
			$queryBuilder->where("$this->ID_FIELD = :$this->ID_FIELD");
		}

		if (isset($filters["cha_agenda_id"])) {
			$args["cha_agenda_id"] = $filters["cha_agenda_id"];
			$queryBuilder->where("cha_agenda_id = :cha_agenda_id");
		}

		if (isset($filters["cha_motion_id"])) {
			$args["cha_motion_id"] = $filters["cha_motion_id"];
			$queryBuilder->where("cha_motion_id = :cha_motion_id");
		}

		if (isset($filters["cha_parent_id"])) {
			$args["cha_parent_id"] = $filters["cha_parent_id"];
			$queryBuilder->where("cha_parent_id = :cha_parent_id");
		}

		if (!isset($filters["with_deleted"])) {
			$args["cha_deleted"] = 0;
			$queryBuilder->where("cha_deleted = :cha_deleted");
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
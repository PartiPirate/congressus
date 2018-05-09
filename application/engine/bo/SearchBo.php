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

class SearchBo {
	var $pdo = null;
	var $config = null;

	var $TABLE = "searchs";
	var $ID_FIELD = "cha_id";

	function __construct($pdo, $config) {
		$this->config = $config;
		$this->pdo = $pdo;
	}

	static function newInstance($pdo, $config) {
		return new SearchBo($pdo, $config);
	}

	function search($filters) {
		$results = array();

		$results = array_merge($results, $this->meetingSearch($filters));
		$results = array_merge($results, $this->agendaSearch($filters));
		$results = array_merge($results, $this->chatSearch($filters));
		$results = array_merge($results, $this->taskSearch($filters));
		$results = array_merge($results, $this->conclusionSearch($filters));
		$results = array_merge($results, $this->motionSearch($filters));
		$results = array_merge($results, $this->propositionSearch($filters));

		foreach($results as $index => $line) {
			foreach($line as $field => $value) {
				if (is_numeric($field)) {
					unset($results[$index][$field]);
				}
			}
		}

		return $results;
	}

	function meetingSearch($filters) {
		$likeQuery = "%" . $filters["query"] . "%";
		$args = array("likeQuery" => $likeQuery);

		$queryBuilder = QueryFactory::getInstance($this->config["database"]["dialect"]);
		$queryBuilder->select("meetings");
		$queryBuilder->addSelect("meetings.*");
		$queryBuilder->addSelect("mee_label", "text");
		$queryBuilder->addSelect("CONCAT('[', '{\"type\":\"meeting\",\"id\":\"', mee_id, '\"}', ']')", "object");
		$queryBuilder->where("mee_label LIKE :likeQuery");

		$query = $queryBuilder->constructRequest();
//		echo showQuery($query, $args);

		$statement = $this->pdo->prepare($query);
		$statement->execute($args);
		$queryResults = $statement->fetchAll();

		return $queryResults;
	}

	function agendaSearch($filters) {
		$likeQuery = "%" . $filters["query"] . "%";

		$queryBuilder = QueryFactory::getInstance($this->config["database"]["dialect"]);
		$queryBuilder->select("meetings");
		$queryBuilder->join("agendas", "age_meeting_id = mee_id");
		$queryBuilder->addSelect("meetings.*");
		$queryBuilder->addSelect("agendas.*");

		$queryBuilder->addSelect("age_label", "text");
		$queryBuilder->addSelect("age_description", "text2");
		$queryBuilder->addSelect("CONCAT('[', '{\"type\":\"meeting\",\"id\":\"', mee_id, '\"}', ',', 
								'{\"type\":\"agenda\",\"id\":\"', age_id, '\"}', ']')", "object");
		$queryBuilder->where("(age_description LIKE :likeQuery OR age_label LIKE :likeQuery)");

		$query = $queryBuilder->constructRequest();

		//		echo showQuery($query, $args);

		$args = array("likeQuery" => $likeQuery);
		$statement = $this->pdo->prepare($query);
		$statement->execute($args);
		$queryResults = $statement->fetchAll();

		return $queryResults;
	}

	function conclusionSearch($filters) {
		$likeQuery = "%" . $filters["query"] . "%";

		$queryBuilder = QueryFactory::getInstance($this->config["database"]["dialect"]);
		$queryBuilder->select("meetings");
		$queryBuilder->join("agendas", "age_meeting_id = mee_id");
		$queryBuilder->join("conclusions", "con_agenda_id = age_id");
		$queryBuilder->join("notices", "not_meeting_id = mee_id AND not_voting = 1", null, "left");
		$queryBuilder->addSelect("meetings.*");
		$queryBuilder->addSelect("agendas.*");
		$queryBuilder->addSelect("conclusions.*");
		$queryBuilder->addSelect("notices.*");

		$queryBuilder->addSelect("con_text", "text");
		$queryBuilder->addSelect("CONCAT('[', '{\"type\":\"meeting\",\"id\":\"', mee_id, '\"}', ',' ,
								'{\"type\":\"agenda\",\"id\":\"', age_id, '\"}', ',' ,
								'{\"type\":\"conclusion\",\"id\":\"', con_id, '\"}',']')", "object");
		$queryBuilder->where("con_text LIKE :likeQuery");

		$query = $queryBuilder->constructRequest();

		//		echo showQuery($query, $args);

		$args = array("likeQuery" => $likeQuery);
		$statement = $this->pdo->prepare($query);
		$statement->execute($args);
		$queryResults = $statement->fetchAll();

		foreach($queryResults as $index => $line) {
			foreach($line as $field => $value) {
				if (is_numeric($field)) {
					unset($queryResults[$index][$field]);
				}
			}
		}

		return $queryResults;
	}

	function chatSearch($filters) {
		$likeQuery = "%" . $filters["query"] . "%";

		$queryBuilder = QueryFactory::getInstance($this->config["database"]["dialect"]);
		$queryBuilder->select("meetings");
		$queryBuilder->join("agendas", "age_meeting_id = mee_id");
		$queryBuilder->join("chats", "cha_agenda_id = age_id");
		$queryBuilder->addSelect("meetings.*");
		$queryBuilder->addSelect("agendas.*");
		$queryBuilder->addSelect("chats.*");

		$queryBuilder->addSelect("cha_text", "text");
		$queryBuilder->addSelect("CONCAT('[', '{\"type\":\"meeting\",\"id\":\"', mee_id, '\"}', ',' ,
								'{\"type\":\"agenda\",\"id\":\"', age_id, '\"}', ',' ,
								'{\"type\":\"chat\",\"id\":\"', cha_id, '\"}', ']')", "object");
		$queryBuilder->where("cha_text LIKE :likeQuery");

		$query = $queryBuilder->constructRequest();

		//		echo showQuery($query, $args);

		$args = array("likeQuery" => $likeQuery);
		$statement = $this->pdo->prepare($query);
		$statement->execute($args);
		$queryResults = $statement->fetchAll();

		return $queryResults;
	}

	function taskSearch($filters) {
		$likeQuery = "%" . $filters["query"] . "%";

		$queryBuilder = QueryFactory::getInstance($this->config["database"]["dialect"]);
		$queryBuilder->select("meetings");
		$queryBuilder->join("agendas", "age_meeting_id = mee_id");
		$queryBuilder->join("tasks", "tas_agenda_id = age_id");
		$queryBuilder->addSelect("meetings.*");
		$queryBuilder->addSelect("agendas.*");
		$queryBuilder->addSelect("tasks.*");

		$queryBuilder->addSelect("tas_label", "text");
		$queryBuilder->addSelect("CONCAT('[', '{\"type\":\"meeting\",\"id\":\"', mee_id, '\"}', ',' ,
								'{\"type\":\"agenda\",\"id\":\"', age_id, '\"}', ',' ,
								'{\"type\":\"task\",\"id\":\"', tas_id, '\"}', ']')", "object");
		$queryBuilder->where("tas_label LIKE :likeQuery");

		$query = $queryBuilder->constructRequest();

		$args = array("likeQuery" => $likeQuery);

//		echo showQuery($query, $args);

		$statement = $this->pdo->prepare($query);
		$statement->execute($args);
		$queryResults = $statement->fetchAll();
	
		return $queryResults;
	}
	
	function motionSearch($filters) {
		$likeQuery = "%" . $filters["query"] . "%";
		$args = array("likeQuery" => $likeQuery);

		$queryBuilder = QueryFactory::getInstance($this->config["database"]["dialect"]);
		$queryBuilder->select("meetings");
		$queryBuilder->join("agendas", "age_meeting_id = mee_id");
		$queryBuilder->join("motions", "mot_agenda_id = age_id AND mot_deleted = 0");
		$queryBuilder->addSelect("meetings.*");
		$queryBuilder->addSelect("agendas.*");
		$queryBuilder->addSelect("motions.*");

		$queryBuilder->addSelect("mot_title", "text");
		$queryBuilder->addSelect("mot_description", "text2");
		$queryBuilder->addSelect("CONCAT('[', '{\"type\":\"meeting\",\"id\":\"', mee_id, '\"}', ',' ,
								'{\"type\":\"agenda\",\"id\":\"', age_id, '\"}', ',' ,
								'{\"type\":\"motion\",\"id\":\"', mot_id, '\"}', ']')", "object");
		$queryBuilder->where("(mot_title LIKE :likeQuery OR mot_description LIKE :likeQuery)");

		if (isset($filters["mot_status"])) {
			$args["mot_status"] = $filters["mot_status"];
			$queryBuilder->where("mot_status = :mot_status");
		}

		if (isset($filters["mee_type"])) {
			$args["mee_type"] = $filters["mee_type"];
			$queryBuilder->where("mee_type = :mee_type");
		}

		$query = $queryBuilder->constructRequest();

//				echo showQuery($query, $args);

		$statement = $this->pdo->prepare($query);
		$statement->execute($args);
		$queryResults = $statement->fetchAll();

		return $queryResults;
	}

	function propositionSearch($filters) {
		$likeQuery = "%" . $filters["query"] . "%";
		$args = array("likeQuery" => $likeQuery);

		$queryBuilder = QueryFactory::getInstance($this->config["database"]["dialect"]);
		$queryBuilder->select("meetings");
		$queryBuilder->join("agendas", "age_meeting_id = mee_id");
		$queryBuilder->join("motions", "mot_agenda_id = age_id AND mot_deleted = 0");
		$queryBuilder->join("motion_propositions", "mpr_motion_id = mot_id");
		$queryBuilder->join("notices", "not_meeting_id = mee_id AND not_voting = 1", null, "left");
		$queryBuilder->addSelect("meetings.*");
		$queryBuilder->addSelect("agendas.*");
		$queryBuilder->addSelect("motions.*");
		$queryBuilder->addSelect("motion_propositions.*");
		$queryBuilder->addSelect("notices.*");

		$queryBuilder->addSelect("mpr_label", "text");
		$queryBuilder->addSelect("CONCAT('[', '{\"type\":\"meeting\",\"id\":\"', mee_id, '\"}', ',' ,
								'{\"type\":\"agenda\",\"id\":\"', age_id, '\"}', ',' ,
								'{\"type\":\"motion\",\"id\":\"', mot_id, '\"}', ',' ,
								'{\"type\":\"proposition\",\"id\":\"', mpr_id, '\"}', ']')", "object");
		$queryBuilder->where("mpr_label LIKE :likeQuery");

		if (isset($filters["mot_status"])) {
			$args["mot_status"] = $filters["mot_status"];
			$queryBuilder->where("mot_status = :mot_status");
		}

		if (isset($filters["mee_type"])) {
			$args["mee_type"] = $filters["mee_type"];
			$queryBuilder->where("mee_type = :mee_type");
		}

		$query = $queryBuilder->constructRequest();

//				echo showQuery($query, $args);
		$statement = $this->pdo->prepare($query);
		$statement->execute($args);
		$queryResults = $statement->fetchAll();

		foreach($queryResults as $index => $line) {
			foreach($line as $field => $value) {
				if (is_numeric($field)) {
					unset($queryResults[$index][$field]);
				}
			}
		}

		return $queryResults;
	}
}
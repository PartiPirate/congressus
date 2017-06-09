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

class SearchBo {
	var $pdo = null;
	var $galetteDatabase = "";
	var $personaeDatabase = "";

	var $TABLE = "searchs";
	var $ID_FIELD = "cha_id";

	function __construct($pdo, $config) {
		$this->galetteDatabase = $config["galette"]["db"] . ".";
		$this->personaeDatabase = $config["personae"]["db"] . ".";

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

//		print_r($results);

		return $results;
	}

	function meetingSearch($filters) {
		$likeQuery = "%" . $filters["query"] . "%";

		$query = "	SELECT
						CONCAT('[', '{\"type\":\"meeting\",\"id\":\"', mee_id, '\"}', ']') AS object,
						mee_label AS text,
						meetings . *
					FROM  meetings
					WHERE mee_label LIKE :likeQuery";

		//		echo showQuery($query, $args);

		$args = array("likeQuery" => $likeQuery);
		$statement = $this->pdo->prepare($query);
		$statement->execute($args);
		$queryResults = $statement->fetchAll();

		return $queryResults;
	}

	function agendaSearch($filters) {
		$likeQuery = "%" . $filters["query"] . "%";

		$query = "	SELECT
						CONCAT('[', '{\"type\":\"meeting\",\"id\":\"', mee_id, '\"}', ',' ,
								'{\"type\":\"agenda\",\"id\":\"', age_id, '\"}', ']') AS object,
						age_label AS text,
						age_description AS text2,
						meetings.*,
						agendas.*
					FROM  meetings
					JOIN agendas ON age_meeting_id = mee_id
					WHERE age_description LIKE :likeQuery OR age_label LIKE :likeQuery";

		//		echo showQuery($query, $args);

		$args = array("likeQuery" => $likeQuery);
		$statement = $this->pdo->prepare($query);
		$statement->execute($args);
		$queryResults = $statement->fetchAll();

		return $queryResults;
	}

	function conclusionSearch($filters) {
		$likeQuery = "%" . $filters["query"] . "%";

		$query = "	SELECT
						CONCAT('[', '{\"type\":\"meeting\",\"id\":\"', mee_id, '\"}', ',' ,
								'{\"type\":\"agenda\",\"id\":\"', age_id, '\"}', ',' ,
								'{\"type\":\"conclusion\",\"id\":\"', con_id, '\"}',']') AS object,
						con_text AS text,
						meetings.*,
						agendas.*,
						conclusions.*,
						notices.*
					FROM  meetings
					JOIN agendas ON age_meeting_id = mee_id
					JOIN conclusions ON con_agenda_id = age_id
					LEFT JOIN notices ON not_meeting_id = mee_id AND not_voting = 1
					WHERE con_text LIKE :likeQuery";

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

		$query = "	SELECT
						CONCAT('[', '{\"type\":\"meeting\",\"id\":\"', mee_id, '\"}', ',' ,
								'{\"type\":\"agenda\",\"id\":\"', age_id, '\"}', ',' ,
								'{\"type\":\"chat\",\"id\":\"', cha_id, '\"}', ']') AS object,
						cha_text AS text,
						meetings.*,
						agendas.*,
						chats.*
					FROM  meetings
					JOIN agendas ON age_meeting_id = mee_id
					JOIN chats ON cha_agenda_id = age_id
					WHERE cha_text LIKE :likeQuery";

		//		echo showQuery($query, $args);

		$args = array("likeQuery" => $likeQuery);
		$statement = $this->pdo->prepare($query);
		$statement->execute($args);
		$queryResults = $statement->fetchAll();

		return $queryResults;
	}

	function taskSearch($filters) {
		$likeQuery = "%" . $filters["query"] . "%";
	
		$query = "	SELECT
						CONCAT('[', '{\"type\":\"meeting\",\"id\":\"', mee_id, '\"}', ',' ,
								'{\"type\":\"agenda\",\"id\":\"', age_id, '\"}', ',' ,
								'{\"type\":\"task\",\"id\":\"', tas_id, '\"}', ']') AS object,
						tas_label AS text,
						meetings.*,
						agendas.*,
						tasks.*
					FROM  meetings
					JOIN agendas ON age_meeting_id = mee_id
					JOIN tasks ON tas_agenda_id = age_id
					WHERE tas_label LIKE :likeQuery";
	
		$args = array("likeQuery" => $likeQuery);

//		echo showQuery($query, $args);

		$statement = $this->pdo->prepare($query);
		$statement->execute($args);
		$queryResults = $statement->fetchAll();
	
		return $queryResults;
	}
	
	function motionSearch($filters) {
		$likeQuery = "%" . $filters["query"] . "%";

		$query = "	SELECT
						CONCAT('[', '{\"type\":\"meeting\",\"id\":\"', mee_id, '\"}', ',' ,
								'{\"type\":\"agenda\",\"id\":\"', age_id, '\"}', ',' ,
								'{\"type\":\"motion\",\"id\":\"', mot_id, '\"}', ']') AS object,
						mot_title AS text,
						mot_description AS text2,
						meetings.*,
						agendas.*,
						motions.*
					FROM  meetings
					JOIN agendas ON age_meeting_id = mee_id
					JOIN motions ON mot_agenda_id = age_id AND mot_deleted = 0
					WHERE mot_title LIKE :likeQuery OR mot_description LIKE :likeQuery";

		//		echo showQuery($query, $args);

		$args = array("likeQuery" => $likeQuery);
		$statement = $this->pdo->prepare($query);
		$statement->execute($args);
		$queryResults = $statement->fetchAll();

		return $queryResults;
	}

	function propositionSearch($filters) {
		$likeQuery = "%" . $filters["query"] . "%";

		$query = "	SELECT
						CONCAT('[', '{\"type\":\"meeting\",\"id\":\"', mee_id, '\"}', ',' ,
								'{\"type\":\"agenda\",\"id\":\"', age_id, '\"}', ',' ,
								'{\"type\":\"motion\",\"id\":\"', mot_id, '\"}', ',' ,
								'{\"type\":\"proposition\",\"id\":\"', mpr_id, '\"}', ']') AS object,
						mpr_label AS text,
						meetings.*,
						agendas.*,
						motions.*,
						motion_propositions.*,
						notices.*
					FROM  meetings
					JOIN agendas ON age_meeting_id = mee_id
					JOIN motions ON mot_agenda_id = age_id AND mot_deleted = 0
					JOIN motion_propositions ON mpr_motion_id = mot_id
					LEFT JOIN notices ON not_meeting_id = mee_id AND not_voting = 1
					WHERE mpr_label LIKE :likeQuery";

		$args = array("likeQuery" => $likeQuery);
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

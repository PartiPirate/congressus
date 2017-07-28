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

class TaskBo {
	var $pdo = null;
	var $config = null;

	var $TABLE = "tasks";
	var $ID_FIELD = "tas_id";

	function __construct($pdo, $config) {
		$this->config = $config;

		$this->pdo = $pdo;
	}

	static function newInstance($pdo, $config) {
		return new TaskBo($pdo, $config);
	}

	function create(&$task) {
		return BoHelper::create($task, $this->TABLE, $this->ID_FIELD, $this->config, $this->pdo);
	}

	function update($task) {
		return BoHelper::update($task, $this->TABLE, $this->ID_FIELD, $this->config, $this->pdo);
	}

	function save(&$task) {
 		if (!isset($task[$this->ID_FIELD]) || !$task[$this->ID_FIELD]) {
			$this->create($task);
		}

		$this->update($task);
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
		$queryBuilder->setDistinct();
		$queryBuilder->addSelect($this->TABLE . ".*");

		if ($filters && isset($filters["notices"])) {
			$queryBuilder->addSelect("age_meeting_id", "tas_meeting_id");
		}

		if ($filters && isset($filters["notices"])) {
			$queryBuilder->join("agendas", "age_id = tas_agenda_id");
			$queryBuilder->join("notices", "not_meeting_id = age_meeting_id");
						
		}

		if ($filters && isset($filters[$this->ID_FIELD])) {
			$args[$this->ID_FIELD] = $filters[$this->ID_FIELD];
			$queryBuilder->where("$this->ID_FIELD = :$this->ID_FIELD");
		}

		if ($filters && isset($filters["tas_agenda_id"])) {
			$args["tas_agenda_id"] = $filters["tas_agenda_id"];
			$queryBuilder->where("tas_agenda_id = :tas_agenda_id");
		}
		
		if ($filters && isset($filters["only_open"]) && $filters["only_open"]) {
			$queryBuilder->where("tas_finish_datetime IS NULL");
		}
		
		if ($filters && isset($filters["notices"])) {
			
			$noticeOrs = "not_id = -1";
			$noticeOr = " OR ";
			
			foreach($filters["notices"] as $notice) {
				$noticeOrs .= $noticeOr;
				$noticeOrs .= "(";
				
				$noticeOrs .= "not_target_type = '" . $notice["not_target_type"];
				$noticeOrs .= "' AND ";
				$noticeOrs .= "not_target_id = " . $notice["not_target_id"];
				
				
				$noticeOrs .= ")";
			}

			$queryBuilder->where("($noticeOrs)");
		}

		$queryBuilder->where("tas_deleted = 0");

		$queryBuilder->orderBy("tas_start_datetime");

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
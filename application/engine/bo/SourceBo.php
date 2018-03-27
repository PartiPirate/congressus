<?php /*
	Copyright 2018 Cédric Levieux, Parti Pirate

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

class SourceBo {
	var $pdo = null;
	var $config = null;

	var $TABLE = "sources";
	var $ID_FIELD = "sou_id";

	function __construct($pdo, $config) {
		$this->config = $config;
		$this->pdo = $pdo;
	}

	static function newInstance($pdo, $config = null) {
		return new SourceBo($pdo, $config);
	}

	function create(&$source) {
		return BoHelper::create($source, $this->TABLE, $this->ID_FIELD, $this->config, $this->pdo);
	}

	function update($source) {
		return BoHelper::update($source, $this->TABLE, $this->ID_FIELD, $this->config, $this->pdo);
	}

	function save(&$source) {
 		if (!isset($source[$this->ID_FIELD]) || !$source[$this->ID_FIELD]) {
			$this->create($source);
		}

		$this->update($source);
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
		$queryBuilder->addSelect($this->TABLE . ".*");

		if (isset($filters[$this->ID_FIELD])) {
			$args[$this->ID_FIELD] = $filters[$this->ID_FIELD];
			$queryBuilder->where("$this->ID_FIELD = :$this->ID_FIELD");
		}

		if (isset($filters["sou_motion_id"])) {
			$args["sou_motion_id"] = $filters["sou_motion_id"];
			$queryBuilder->where("sou_motion_id = :sou_motion_id");
		}

		if (!isset($filters["with_deleted"])) {
			$queryBuilder->where("sou_deleted = 0");
		}

		$query = $queryBuilder->constructRequest();
		$statement = $this->pdo->prepare($query);
//		echo showQuery($query, $args);
//		exit();
//		error_log(showQuery($query, $args));
//		echo showQuery($queryBuilder->constructRequest(), $args);

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
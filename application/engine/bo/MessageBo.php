<?php /*
    Copyright 2019 Cédric Levieux, Parti Pirate

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

class MessageBo {
	var $pdo = null;
	var $config = null;
	var $personaeDatabase = "";

	var $TABLE = "messages";
	var $ID_FIELD = "mes_id";

	function __construct($pdo, $config) {
		$this->config = $config;
		$this->pdo = $pdo;
	}

	static function newInstance($pdo, $config) {
		return new MessageBo($pdo, $config);
	}

	function create(&$meeting) {
		return BoHelper::create($meeting, $this->TABLE, $this->ID_FIELD, $this->config, $this->pdo);
	}

	function update($meeting) {
		return BoHelper::update($meeting, $this->TABLE, $this->ID_FIELD, $this->config, $this->pdo);
	}

	function save(&$meeting) {
 		if (!isset($meeting[$this->ID_FIELD]) || !$meeting[$this->ID_FIELD]) {
			$this->create($meeting);
		}

		$this->update($meeting);
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

		if (isset($filters[$this->ID_FIELD])) {
			$args[$this->ID_FIELD] = $filters[$this->ID_FIELD];
			$queryBuilder->where("$this->ID_FIELD = :$this->ID_FIELD");
		}

		if (!isset($filters["with_consumed"])) {
			$queryBuilder->where("mes_consumed = 0");
		}

		$queryBuilder->orderBy("mes_id");

		$query = $queryBuilder->constructRequest();
		$statement = $this->pdo->prepare($query);
//		echo showQuery($query, $args);
//		error_log(showQuery($query, $args));

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
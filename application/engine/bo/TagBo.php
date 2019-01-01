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
    along with Congressus.  If age, see <http://www.gnu.org/licenses/>.
*/

class TagBo {
	var $pdo = null;
	var $config = null;

	var $TABLE = "tags";
	var $ID_FIELD = "tag_id";

	function __construct($pdo, $config) {
		$this->config = $config;

		$this->pdo = $pdo;
	}

	static function newInstance($pdo, $config) {
		return new TagBo($pdo, $config);
	}

	function create(&$tag) {
		return BoHelper::create($tag, $this->TABLE, $this->ID_FIELD, $this->config, $this->pdo);
	}

	function update($tag) {
		return BoHelper::update($tag, $this->TABLE, $this->ID_FIELD, $this->config, $this->pdo);
	}

	function save(&$tag) {
 		if (!isset($tag[$this->ID_FIELD]) || !$tag[$this->ID_FIELD]) {
			$this->create($tag);
		}

		$this->update($tag);
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

		if ($filters && isset($filters[$this->ID_FIELD])) {
			$args[$this->ID_FIELD] = $filters[$this->ID_FIELD];
			$queryBuilder->where("$this->ID_FIELD = :$this->ID_FIELD");
		}

		if ($filters && isset($filters["tag_ids"])) {
//			$args["tag_ids"] = $filters["tag_ids"];
			$queryBuilder->where("tag_id IN (".implode(",",  $filters["tag_ids"]).")");
		}

		if ($filters && isset($filters["tag_server_in"])) {
			$args["tag_server_id"] = $filters["tag_server_id"];
			$queryBuilder->where("tag_server_id = :tag_server_id");
		}

		$queryBuilder->orderBy("tag_label");

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
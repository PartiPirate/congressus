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

class CoAuthorBo {
	var $pdo = null;
	var $config = null;

	var $TABLE = "co_authors";
	var $ID_FIELD = "cau_id";

	function __construct($pdo, $config) {
		$this->config = $config;

		$this->pdo = $pdo;
	}

	static function newInstance($pdo, $config) {
		return new CoAuthorBo($pdo, $config);
	}

	function create(&$coAuthor) {
		return BoHelper::create($coAuthor, $this->TABLE, $this->ID_FIELD, $this->config, $this->pdo);
	}

	function update($coAuthor) {
		return BoHelper::update($coAuthor, $this->TABLE, $this->ID_FIELD, $this->config, $this->pdo);
	}

	function save(&$coAuthor) {
 		if (!isset($coAuthor[$this->ID_FIELD]) || !$coAuthor[$this->ID_FIELD]) {
			$this->create($coAuthor);
		}

		$this->update($coAuthor);
	}

	function delete($coAuthor) {
		$query = "	DELETE FROM $this->TABLE ";

		$query .= "	WHERE $this->ID_FIELD = :$this->ID_FIELD ";

		//		echo showQuery($query, $coAuthor);

		$args = array($this->ID_FIELD => $coAuthor[$this->ID_FIELD]);

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
		$queryBuilder->addSelect($this->TABLE . ".*");

		$userSource = UserSourceFactory::getInstance($this->config["modules"]["usersource"]);
		$userSource->upgradeQuery($queryBuilder, $this->config, "cau_user_id");

		if (isset($filters[$this->ID_FIELD])) {
			$args[$this->ID_FIELD] = $filters[$this->ID_FIELD];
			$queryBuilder->where("$this->ID_FIELD = :$this->ID_FIELD");
		}

		if (isset($filters["cau_object_type"])) {
			$args["cau_object_type"] = $filters["cau_object_type"];
			$queryBuilder->where("cau_object_type = :cau_object_type");
		}

		if (isset($filters["cau_object_id"])) {
			$args["cau_object_id"] = $filters["cau_object_id"];
			$queryBuilder->where("cau_object_id = :cau_object_id");
		}

		if (isset($filters["cau_user_id"])) {
			$args["cau_user_id"] = $filters["cau_user_id"];
			$queryBuilder->where("cau_user_id = :cau_user_id");
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
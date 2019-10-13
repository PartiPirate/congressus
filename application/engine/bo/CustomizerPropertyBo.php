<?php /*
    Copyright 2018-2019 Cédric Levieux, Parti Pirate

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

class CustomizerPropertyBo {
	var $pdo = null;
	var $config = null;

	var $TABLE = "customizer_properties";
	var $ID_FIELD = "cpr_id";

	function __construct($pdo, $config) {
		$this->config = $config;

		$this->pdo = $pdo;
	}

	static function newInstance($pdo, $config) {
		return new CustomizerPropertyBo($pdo, $config);
	}

	function create(&$customizerProperty) {
		return BoHelper::create($customizerProperty, $this->TABLE, $this->ID_FIELD, $this->config, $this->pdo);
	}

	function update($customizerProperty) {
		return BoHelper::update($customizerProperty, $this->TABLE, $this->ID_FIELD, $this->config, $this->pdo);
	}

	function save(&$customizerProperty) {
 		if (!isset($customizerProperty[$this->ID_FIELD]) || !$customizerProperty[$this->ID_FIELD]) {
			$this->create($customizerProperty);
		}

		$this->update($customizerProperty);
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

		if ($filters && isset($filters["cpr_customizer_id"])) {
			$args["cpr_customizer_id"] = $filters["cpr_customizer_id"];
			$queryBuilder->where("cpr_customizer_id = :cpr_customizer_id");
		}

		if ($filters && isset($filters["cpr_key"])) {
			$args["cpr_key"] = $filters["cpr_key"];
			$queryBuilder->where("cpr_key = :cpr_key");
		}

//		$queryBuilder->where("cpr_deleted = 0");

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
	
	function getProperty($customizerProperties, $key) {
//		print_r($customizerProperties);
//		echo $key;

		foreach($customizerProperties as $customizerProperty) {

//			print_r($customizerProperty);

			if (isset($customizerProperty["cpr_key"]) && $customizerProperty["cpr_key"] == $key) {
				return $customizerProperty;
			}
		}
		
		return null;
	}
}
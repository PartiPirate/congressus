<?php /*
    Copyright 2020 Cédric Levieux, Parti Pirate

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

class TrustLinkBo {
	var $pdo = null;
	var $config = null;

	var $TABLE = "trust_links";
	var $ID_FIELD = "tli_id";

	const LINK = "link";
	const ASKING = "asking";
	const REFUSED = "refused";

	function __construct($pdo, $config) {
		$this->config = $config;

		$this->pdo = $pdo;
	}

	static function newInstance($pdo, $config) {
		return new TrustLinkBo($pdo, $config);
	}

	function delete(&$trustLink) {
		return BoHelper::delete($trustLink, $this->TABLE, $this->ID_FIELD, $this->config, $this->pdo);
	}

	function create(&$trustLink) {
		return BoHelper::create($trustLink, $this->TABLE, $this->ID_FIELD, $this->config, $this->pdo);
	}

	function update($trustLink) {
		return BoHelper::update($trustLink, $this->TABLE, $this->ID_FIELD, $this->config, $this->pdo);
	}

	function save(&$trustLink) {
 		if (!isset($trustLink[$this->ID_FIELD]) || !$trustLink[$this->ID_FIELD]) {
			$this->create($trustLink);
		}

		$this->update($trustLink);
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

		if ($filters && isset($filters[$this->ID_FIELD]) && $filters[$this->ID_FIELD]) {
			$args[$this->ID_FIELD] = $filters[$this->ID_FIELD];
			$queryBuilder->where("$this->ID_FIELD = :$this->ID_FIELD");
		}

		if ($filters && isset($filters["tli_from_member_id"])) {
			$args["tli_from_member_id"] = $filters["tli_from_member_id"];
			$queryBuilder->where("tli_from_member_id = :tli_from_member_id");

			$userSource = UserSourceFactory::getInstance($this->config["modules"]["usersource"]);
			$userSource->upgradeQuery($queryBuilder, $this->config, "tli_to_member_id");
		}

		if ($filters && isset($filters["tli_to_member_id"])) {
			$args["tli_to_member_id"] = $filters["tli_to_member_id"];
			$queryBuilder->where("tli_to_member_id = :tli_to_member_id");

			if (!isset($filters["tli_from_member_id"])) {
				$userSource = UserSourceFactory::getInstance($this->config["modules"]["usersource"]);
				$userSource->upgradeQuery($queryBuilder, $this->config, "tli_from_member_id");
			}
		}

		if ($filters && isset($filters["tli_status"])) {
			$args["tli_status"] = $filters["tli_status"];
			$queryBuilder->where("tli_status = :tli_status");
		}

		$queryBuilder->orderBy("tli_from_member_id");
		$queryBuilder->orderBy("tli_to_member_id");

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
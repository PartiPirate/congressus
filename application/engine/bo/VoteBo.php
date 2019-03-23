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

class VoteBo {
	var $pdo = null;
	var $config = null;

	var $TABLE = "votes";
	var $ID_FIELD = "vot_id";

	function __construct($pdo, $config) {
		$this->config = $config;

		$this->pdo = $pdo;
	}

	static function newInstance($pdo, $config) {
		return new VoteBo($pdo, $config);
	}

	function create(&$vote) {
		return BoHelper::create($vote, $this->TABLE, $this->ID_FIELD, $this->config, $this->pdo);
	}

	function update($vote) {
		return BoHelper::update($vote, $this->TABLE, $this->ID_FIELD, $this->config, $this->pdo);
	}

	function save(&$vote) {
 		if (!isset($vote[$this->ID_FIELD]) || !$vote[$this->ID_FIELD]) {
			$this->create($vote);
		}

		$this->update($vote);
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
		$queryBuilder->addSelect($this->TABLE . ".*")->addSelect("motion_propositions.*")->addSelect("motions.*");

		$userSource = UserSourceFactory::getInstance($this->config["modules"]["usersource"]);
		$userSource->upgradeQuery($queryBuilder, $this->config, "vot_member_id");

		$queryBuilder->join("motion_propositions", "mpr_id = vot_motion_proposition_id");
		$queryBuilder->join("motions", "mot_id = mpr_motion_id");

		if (isset($filters[$this->ID_FIELD])) {
			$args[$this->ID_FIELD] = $filters[$this->ID_FIELD];
			$queryBuilder->where("$this->ID_FIELD = :$this->ID_FIELD");
		}

		if (isset($filters["vot_member_id"])) {
			$args["vot_member_id"] = $filters["vot_member_id"];
			$queryBuilder->where("vot_member_id = :vot_member_id");
		}

		if (isset($filters["vot_motion_proposition_id"])) {
			$args["vot_motion_proposition_id"] = $filters["vot_motion_proposition_id"];
			$queryBuilder->where("vot_motion_proposition_id = :vot_motion_proposition_id");
		}

		if (isset($filters["mot_agenda_id"])) {
			$args["mot_agenda_id"] = $filters["mot_agenda_id"];
			$queryBuilder->where("mot_agenda_id = :mot_agenda_id");
		}

		if (isset($filters["mot_anonymous"])) {
			$args["mot_anonymous"] = $filters["mot_anonymous"];
			$queryBuilder->where("mot_anonymous = :mot_anonymous");
		}

		$queryBuilder->orderBy("mot_id");
		$queryBuilder->orderBy("mpr_id");

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
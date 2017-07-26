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
	var $galetteDatabase = "";
	var $personaeDatabase = "";

	var $TABLE = "votes";
	var $ID_FIELD = "vot_id";

	function __construct($pdo, $config) {
		$this->config = $config;
		$this->galetteDatabase = $config["galette"]["db"] . ".";
		$this->personaeDatabase = $config["personae"]["db"] . ".";

		$this->pdo = $pdo;
	}

	static function newInstance($pdo, $config) {
		return new VoteBo($pdo, $config);
	}

	function create(&$vote) {
		$query = "	INSERT INTO $this->TABLE () VALUES ()	";

		$statement = $this->pdo->prepare($query);
//				echo showQuery($query, $args);

		try {
			$statement->execute();
			$vote[$this->ID_FIELD] = $this->pdo->lastInsertId();

			return true;
		}
		catch(Exception $e){
			echo 'Erreur de requète : ', $e->getMessage();
		}

		return false;
	}

	function update($vote) {
		$query = "	UPDATE $this->TABLE SET ";

		$separator = "";
		foreach($vote as $field => $value) {
			$query .= $separator;
			$query .= $field . " = :". $field;
			$separator = ", ";
		}

		$query .= "	WHERE $this->ID_FIELD = :$this->ID_FIELD ";

//		echo showQuery($query, $vote);

		$statement = $this->pdo->prepare($query);
		$statement->execute($vote);
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
		$queryBuilder->addSelect($this->galetteDatabase . "galette_adherents.id_adh");
		$queryBuilder->addSelect($this->galetteDatabase . "galette_adherents.pseudo_adh");

		$queryBuilder->join("motion_propositions", "mpr_id = vot_motion_proposition_id");
		$queryBuilder->join("motions", "mot_id = mpr_motion_id");
		$queryBuilder->join($this->galetteDatabase."galette_adherents", "id_adh = vot_member_id", null, "left");

//		$query = "	SELECT
//						$this->TABLE.*,
//						motion_propositions.*,
//						motions.*,
//						".$this->galetteDatabase."galette_adherents.id_adh,
//						".$this->galetteDatabase."galette_adherents.pseudo_adh
//					FROM $this->TABLE
//					JOIN motion_propositions ON mpr_id = vot_motion_proposition_id
//					JOIN motions ON mot_id = mpr_motion_id
//					LEFT JOIN ".$this->galetteDatabase."galette_adherents ON id_adh = vot_member_id
//					WHERE
//						1 = 1 \n";

		if (isset($filters[$this->ID_FIELD])) {
			$args[$this->ID_FIELD] = $filters[$this->ID_FIELD];
//			$query .= " AND $this->ID_FIELD = :$this->ID_FIELD \n";
			$queryBuilder->where("$this->ID_FIELD = :$this->ID_FIELD");
		}

		if (isset($filters["vot_member_id"])) {
			$args["vot_member_id"] = $filters["vot_member_id"];
//			$query .= " AND vot_member_id = :vot_member_id \n";
			$queryBuilder->where("vot_member_id = :vot_member_id");
		}

		if (isset($filters["vot_motion_proposition_id"])) {
			$args["vot_motion_proposition_id"] = $filters["vot_motion_proposition_id"];
//			$query .= " AND vot_motion_proposition_id = :vot_motion_proposition_id \n";
			$queryBuilder->where("vot_motion_proposition_id = :vot_motion_proposition_id");
		}

		if (isset($filters["mot_agenda_id"])) {
			$args["mot_agenda_id"] = $filters["mot_agenda_id"];
//			$query .= " AND mot_agenda_id = :mot_agenda_id \n";
			$queryBuilder->where("mot_agenda_id = :mot_agenda_id");
		}

//		$query .= "	ORDER BY vot_parent_id ASC , vot_order ASC ";

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
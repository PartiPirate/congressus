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

class MotionBo {
	var $pdo = null;
	var $config = null;

	var $TABLE = "motions";
	var $ID_FIELD = "mot_id";

	var $TABLE_PROPOSITION = "motion_propositions";
	var $ID_FIELD_PROPOSITION = "mpr_id";

	const CONSTRUCTION = "construction";
	const VOTING = "voting";
	const RESOLVED = "resolved";

	function __construct($pdo, $config) {
		$this->config = $config;
		$this->pdo = $pdo;
	}

	static function newInstance($pdo, $config = null) {
		return new MotionBo($pdo, $config);
	}

	function create(&$motion) {
		return BoHelper::create($motion, $this->TABLE, $this->ID_FIELD, $this->config, $this->pdo);
	}

	function update($motion) {
		return BoHelper::update($motion, $this->TABLE, $this->ID_FIELD, $this->config, $this->pdo);
	}

	function save(&$motion) {
 		if (!isset($motion[$this->ID_FIELD]) || !$motion[$this->ID_FIELD]) {
			$this->create($motion);
		}

		$this->update($motion);
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
		$queryBuilder->join("motion_propositions", "mpr_motion_id = mot_id", null, "left");
		$queryBuilder->addSelect($this->TABLE . ".*")->addSelect("motion_propositions.*");

		if (isset($filters["vot_member_id"]) && $filters["vot_member_id"]) {
			$queryBuilder->addSelect("votes.*");
		}

		if (isset($filters["vot_member_id"])) {
			$args["vot_member_id"] = $filters["vot_member_id"];
			$queryBuilder->join("votes", "vot_motion_proposition_id = mpr_id AND vot_member_id = :vot_member_id", null, "left");
		}

		if (isset($filters["with_meeting"]) && $filters["with_meeting"]) {
			$queryBuilder->addSelect("agendas.*")->addSelect("meetings.*");
			$queryBuilder->join("agendas", "age_id = mot_agenda_id");
			$queryBuilder->join("meetings", "mee_id = age_meeting_id AND mee_deleted = 0 AND mee_status != 'deleted'");
		}

		if (isset($filters["with_notice"]) && $filters["with_notice"]) {
			$queryBuilder->addSelect("not_target_type");
			$queryBuilder->join("notices", "not_meeting_id = mee_id AND not_voting = 1");

			$or = "(";
			$orSeparator = "";

			foreach($this->config["modules"]["groupsources"] as $groupSourceKey) {
				$groupSource = GroupSourceFactory::getInstance($groupSourceKey);

		    	$groupSource->addMotionNoticeVoters($queryBuilder, $filters);

				if (isset($filters["vot_member_id"]) && $filters["vot_member_id"]) {
			    	$or .= $orSeparator;
			    	$or .= $groupSource->getVoterNotNull();
			    	$orSeparator = " OR ";
				}
			}
			
			$or .= ")";

			if (isset($filters["vot_member_id"]) && $filters["vot_member_id"] && $or != "()") {
				$queryBuilder->where($or);
			}
		}

		if (isset($filters[$this->ID_FIELD])) {
			$args[$this->ID_FIELD] = $filters[$this->ID_FIELD];
			$queryBuilder->where("$this->ID_FIELD = :$this->ID_FIELD");
		}

		if (isset($filters[$this->ID_FIELD_PROPOSITION])) {
			$args[$this->ID_FIELD_PROPOSITION] = $filters[$this->ID_FIELD_PROPOSITION];
			$queryBuilder->where("$this->ID_FIELD_PROPOSITION = :$this->ID_FIELD_PROPOSITION");
		}

		if (isset($filters["mot_id"])) {
			$args["mot_id"] = $filters["mot_id"];
			$queryBuilder->where("mot_id = :mot_id");
		}

		if (isset($filters["mot_agenda_id"])) {
			$args["mot_agenda_id"] = $filters["mot_agenda_id"];
			$queryBuilder->where("mot_agenda_id = :mot_agenda_id");
		}

		if (isset($filters["with_meeting"]) && $filters["with_meeting"] && isset($filters["age_id"])) {
			$args["age_id"] = $filters["age_id"];
			$queryBuilder->where("age_id = :age_id");
		}

		if (isset($filters["with_meeting"]) && $filters["with_meeting"] && isset($filters["mee_id"])) {
			$args["mee_id"] = $filters["mee_id"];
			$queryBuilder->where("mee_id = :mee_id");
		}

		if (isset($filters["mot_status"])) {
			$args["mot_status"] = $filters["mot_status"];
			$queryBuilder->where("mot_status = :mot_status");
		}

		if (!isset($filters["with_deleted"])) {
			$queryBuilder->where("mot_deleted = 0");
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

	function createProposition(&$proposition) {
		return BoHelper::create($proposition, $this->TABLE_PROPOSITION, $this->ID_FIELD_PROPOSITION, $this->config, $this->pdo);
	}

	function updateProposition($proposition) {
		return BoHelper::update($proposition, $this->TABLE_PROPOSITION, $this->ID_FIELD_PROPOSITION, $this->config, $this->pdo);
	}

	function saveProposition(&$proposition) {
		if (!isset($proposition[$this->ID_FIELD_PROPOSITION]) || !$proposition[$this->ID_FIELD_PROPOSITION]) {
			$this->createProposition($proposition);
		}

		$this->updateProposition($proposition);
	}

	function deleteProposition($proposition) {
		$query = "	DELETE FROM $this->TABLE_PROPOSITION ";

		$query .= "	WHERE $this->ID_FIELD_PROPOSITION = :$this->ID_FIELD_PROPOSITION ";

		//		echo showQuery($query, $agenda);

		$args = array($this->ID_FIELD_PROPOSITION => $proposition[$this->ID_FIELD_PROPOSITION]);

		$statement = $this->pdo->prepare($query);
		$statement->execute($args);
	}

	static function orderPinFirst($motionA, $motionB) {
		$diff = $motionA["mot_pinned"] - $motionB["mot_pinned"];
		
		if ($diff) return -$diff;
		
		return $motionA["mot_id"] - $motionB["mot_id"];
	}
}
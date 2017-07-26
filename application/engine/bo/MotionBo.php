<?php /*
	Copyright 2015 Cédric Levieux, Parti Pirate

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
	var $galetteDatabase = "";
	var $personaeDatabase = "";

	var $TABLE = "motions";
	var $ID_FIELD = "mot_id";

	var $TABLE_PROPOSITION = "motion_propositions";
	var $ID_FIELD_PROPOSITION = "mpr_id";

	const CONSTRUCTION = "construction";
	const VOTING = "voting";
	const RESOLVED = "resolved";

	function __construct($pdo, $config) {
		$this->config = $config;
//		if ($config) {
		$this->galetteDatabase = $config["galette"]["db"] . ".";
		$this->personaeDatabase = $config["personae"]["db"] . ".";
//		}

		$this->pdo = $pdo;
	}

	static function newInstance($pdo, $config = null) {
		return new MotionBo($pdo, $config);
	}

	function create(&$motion) {
		$query = "	INSERT INTO $this->TABLE () VALUES ()	";

		$statement = $this->pdo->prepare($query);
//				echo showQuery($query, $args);

		try {
			$statement->execute();
			$motion[$this->ID_FIELD] = $this->pdo->lastInsertId();

			return true;
		}
		catch(Exception $e){
			echo 'Erreur de requète : ', $e->getMessage();
		}

		return false;
	}

	function update($motion) {
		$query = "	UPDATE $this->TABLE SET ";

		$separator = "";
		foreach($motion as $field => $value) {
			$query .= $separator;
			$query .= $field . " = :". $field;
			$separator = ", ";
		}

		$query .= "	WHERE $this->ID_FIELD = :$this->ID_FIELD ";

//		echo showQuery($query, $motion);

		$statement = $this->pdo->prepare($query);
		$statement->execute($motion);
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

		$query = "	SELECT $this->TABLE.*, motion_propositions.* ";

		if (isset($filters["vot_member_id"]) && $filters["vot_member_id"]) {
			$query .= ", votes.* ";
			$queryBuilder->addSelect("votes.*");
		}

		if (isset($filters["with_meeting"]) && $filters["with_meeting"]) {
			$query .= ", agendas.*, meetings.* ";
			$queryBuilder->addSelect("agendas.*")->addSelect("meetings.*");
		}

		if (isset($filters["with_notice"]) && $filters["with_notice"]) {
			$queryBuilder->addSelect("gga.id_adh", "gga_id_adh")->addSelect("ta.id_adh", "ta_id_adh")->addSelect("gta.id_adh", "gta_id_adh");
			// TODO 2 <= externalize
			$queryBuilder->addSelect(2, "gga_vote_power")->addSelect("tfm.fme_power", "ta_vote_power")->addSelect("gtfm.fme_power", "gta_vote_power");

			$query .= ", gga.id_adh as gga_id_adh, ta.id_adh as ta_id_adh, gta.id_adh as gta_id_adh ";
			// TODO 2 <= externalize
			$query .= ", 2 as gga_vote_power, tfm.fme_power as ta_vote_power, gtfm.fme_power as gta_vote_power ";
		}

		$query .= "	FROM $this->TABLE
					LEFT JOIN motion_propositions ON mpr_motion_id = mot_id  \n";

		if (isset($filters["vot_member_id"])) {
			$args["vot_member_id"] = $filters["vot_member_id"];
			$query .= "	LEFT JOIN votes ON vot_motion_proposition_id = mpr_id AND vot_member_id = :vot_member_id  \n";
			$queryBuilder->join("votes", "vot_motion_proposition_id = mpr_id AND vot_member_id = :vot_member_id", null, "left");
		}

		if (isset($filters["with_meeting"]) && $filters["with_meeting"]) {
			$query .= "	JOIN agendas ON age_id = mot_agenda_id  \n";
			$query .= "	JOIN meetings ON mee_id = age_meeting_id AND mee_deleted = 0  \n";
			$queryBuilder->join("agendas", "age_id = mot_agenda_id");
			$queryBuilder->join("meetings", "mee_id = age_meeting_id AND mee_deleted = 0");
		}

		if (isset($filters["with_notice"]) && $filters["with_notice"]) {
			$query .= "	JOIN notices ON not_meeting_id = mee_id AND not_voting = 1  \n";
			$queryBuilder->join("notices", "not_meeting_id = mee_id AND not_voting = 1");
			
			//  galette groups

			$query .= "	LEFT JOIN ".$this->galetteDatabase."galette_groups gg ON gg.id_group = not_target_id AND not_target_type = 'galette_groups' \n";
			$query .= "	LEFT JOIN ".$this->galetteDatabase."galette_groups_members ggm ON gg.id_group = ggm.id_group  \n";
			$queryBuilder->join($this->galetteDatabase."galette_groups",			"gg.id_group = not_target_id AND not_target_type = 'galette_groups'",	"gg", "left");

			if (isset($filters["vot_member_id"])) {
				$queryBuilder->join($this->galetteDatabase."galette_groups_members",	"gg.id_group = ggm.id_group	AND ggm.id_adh = :vot_member_id",		"ggm", "left");
				$query .= "	AND ggm.id_adh = :vot_member_id  \n";
			}
			else {
				$queryBuilder->join($this->galetteDatabase."galette_groups_members",	"gg.id_group = ggm.id_group",										"ggm", "left");
			}

			$query .= "	LEFT JOIN ".$this->galetteDatabase."galette_adherents gga ON gga.id_adh = ggm.id_adh \n";
			$queryBuilder->join($this->galetteDatabase."galette_adherents", 			"gga.id_adh = ggm.id_adh",											"gga", "left");

			//  personae theme

			$query .= "	LEFT JOIN ".$this->personaeDatabase."dlp_themes t ON t.the_id = not_target_id AND not_target_type = 'dlp_themes' \n";
			$query .= "	LEFT JOIN ".$this->personaeDatabase."dlp_fixations tf ON tf.fix_id = t.the_current_fixation_id AND tf.fix_theme_type = 'dlp_themes' \n";
			$query .= "	LEFT JOIN ".$this->personaeDatabase."dlp_fixation_members tfm ON tfm.fme_fixation_id = tf.fix_id \n";
			$queryBuilder->join($this->personaeDatabase."dlp_themes",			"t.the_id = not_target_id AND not_target_type = 'dlp_themes'",					"t", "left");
			$queryBuilder->join($this->personaeDatabase."dlp_fixations",		"tf.fix_id = t.the_current_fixation_id AND tf.fix_theme_type = 'dlp_themes'",	"tf", "left");

			if (isset($filters["vot_member_id"])) {
				$query .= "	AND tfm.fme_member_id = :vot_member_id  \n";
				$queryBuilder->join($this->personaeDatabase."dlp_fixation_members", "tfm.fme_fixation_id = tf.fix_id AND tfm.fme_member_id = :vot_member_id",	"tfm", "left");
			}
			else {
				$queryBuilder->join($this->personaeDatabase."dlp_fixation_members", "tfm.fme_fixation_id = tf.fix_id",											"tfm", "left");
			}

			$query .= "	LEFT JOIN ".$this->galetteDatabase."galette_adherents ta ON ta.id_adh = tfm.fme_member_id \n";
			$queryBuilder->join($this->galetteDatabase."galette_adherents", 		"ta.id_adh = tfm.fme_member_id",											"ta", "left");

			// OK

			//  personae groupe

			$query .= "	LEFT JOIN ".$this->personaeDatabase."dlp_groups g ON g.gro_id = not_target_id AND not_target_type = 'dlp_groups' \n";
			$query .= "	LEFT JOIN ".$this->personaeDatabase."dlp_group_themes ggt ON ggt.gth_group_id = g.gro_id \n";
			$query .= "	LEFT JOIN ".$this->personaeDatabase."dlp_themes gt ON gt.the_id = ggt.gth_theme_id \n";
			$query .= "	LEFT JOIN ".$this->personaeDatabase."dlp_fixations gtf ON gtf.fix_id = gt.the_current_fixation_id AND gtf.fix_theme_type = 'dlp_themes' \n";
			$query .= "	LEFT JOIN ".$this->personaeDatabase."dlp_fixation_members gtfm ON gtfm.fme_fixation_id = gtf.fix_id \n";
			$queryBuilder->join($this->personaeDatabase."dlp_groups",			"g.gro_id = not_target_id AND not_target_type = 'dlp_groups'",						"g", "left");
			$queryBuilder->join($this->personaeDatabase."dlp_group_themes",		"ggt.gth_group_id = g.gro_id",														"ggt", "left");
			$queryBuilder->join($this->personaeDatabase."dlp_themes",			"gt.the_id = ggt.gth_theme_id",														"gt", "left");
			$queryBuilder->join($this->personaeDatabase."dlp_fixations",		"gtf.fix_id = gt.the_current_fixation_id AND gtf.fix_theme_type = 'dlp_themes'",	"gtf", "left");

			if (isset($filters["vot_member_id"])) {
				$query .= "	AND gtfm.fme_member_id = :vot_member_id \n";
				$queryBuilder->join($this->personaeDatabase."dlp_fixation_members",	"gtfm.fme_fixation_id = gtf.fix_id AND gtfm.fme_member_id = :vot_member_id",	"gtfm", "left");
			}
			else {
				$queryBuilder->join($this->personaeDatabase."dlp_fixation_members",	"gtfm.fme_fixation_id = gtf.fix_id",											"gtfm", "left");
			}

			$query .= "	LEFT JOIN ".$this->galetteDatabase."galette_adherents gta ON gta.id_adh = gtfm.fme_member_id \n";
			$queryBuilder->join($this->galetteDatabase."galette_adherents", 		"gta.id_adh = gtfm.fme_member_id",												"gta", "left");
		}

		$query .= "	WHERE
						1 = 1 \n";

		if (isset($filters[$this->ID_FIELD])) {
			$args[$this->ID_FIELD] = $filters[$this->ID_FIELD];
			$query .= " AND $this->ID_FIELD = :$this->ID_FIELD \n";
			$queryBuilder->where("$this->ID_FIELD = :$this->ID_FIELD");
		}

		if (isset($filters[$this->ID_FIELD_PROPOSITION])) {
			$args[$this->ID_FIELD_PROPOSITION] = $filters[$this->ID_FIELD_PROPOSITION];
			$query .= " AND $this->ID_FIELD_PROPOSITION = :$this->ID_FIELD_PROPOSITION \n";
			$queryBuilder->where("$this->ID_FIELD_PROPOSITION = :$this->ID_FIELD_PROPOSITION");
		}

		if (isset($filters["mot_id"])) {
			$args["mot_id"] = $filters["mot_id"];
			$query .= " AND mot_id = :mot_id \n";
			$queryBuilder->where("mot_id = :mot_id");
		}

		if (isset($filters["mot_agenda_id"])) {
			$args["mot_agenda_id"] = $filters["mot_agenda_id"];
			$query .= " AND mot_agenda_id = :mot_agenda_id \n";
			$queryBuilder->where("mot_agenda_id = :mot_agenda_id");
		}

		if (isset($filters["with_meeting"]) && $filters["with_meeting"] && isset($filters["age_id"])) {
			$args["age_id"] = $filters["age_id"];
			$query .= " AND age_id = :age_id \n";
			$queryBuilder->where("age_id = :age_id");
		}

		if (isset($filters["with_meeting"]) && $filters["with_meeting"] && isset($filters["mee_id"])) {
			$args["mee_id"] = $filters["mee_id"];
			$query .= " AND mee_id = :mee_id \n";
			$queryBuilder->where("mee_id = :mee_id");
		}

		if (isset($filters["mot_status"])) {
			$args["mot_status"] = $filters["mot_status"];
			$query .= " AND mot_status = :mot_status \n";
			$queryBuilder->where("mot_status = :mot_status");
		}

		if (!isset($filters["with_deleted"])) {
			$query .= " AND mot_deleted = 0 \n";
			$queryBuilder->where("mot_deleted = 0");
		}

		if (isset($filters["vot_member_id"]) && $filters["vot_member_id"] && isset($filters["with_notice"]) && $filters["with_notice"]) {
			$query .= " AND ((gga.id_adh IS NOT NULL) OR (ta.id_adh IS NOT NULL) OR (gta.id_adh IS NOT NULL)) ";
			$queryBuilder->where("((gga.id_adh IS NOT NULL) OR (ta.id_adh IS NOT NULL) OR (gta.id_adh IS NOT NULL))");
		}

//		$query .= "	ORDER BY mot_parent_id ASC , mot_order ASC ";

		$query = $queryBuilder->constructRequest();
		$statement = $this->pdo->prepare($query);
//		echo showQuery($query, $args);
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
		$query = "	INSERT INTO $this->TABLE_PROPOSITION () VALUES ()	";

		$statement = $this->pdo->prepare($query);
		//				echo showQuery($query, $args);

		try {
			$statement->execute();
			$proposition[$this->ID_FIELD_PROPOSITION] = $this->pdo->lastInsertId();

			return true;
		}
		catch(Exception $e){
			echo 'Erreur de requète : ', $e->getMessage();
		}

		return false;
	}

	function updateProposition($proposition) {
		$query = "	UPDATE $this->TABLE_PROPOSITION SET ";

		$separator = "";
		foreach($proposition as $field => $value) {
			$query .= $separator;
			$query .= $field . " = :". $field;
			$separator = ", ";
		}

		$query .= "	WHERE $this->ID_FIELD_PROPOSITION = :$this->ID_FIELD_PROPOSITION ";

//		echo showQuery($query, $proposition);

		$statement = $this->pdo->prepare($query);
		$statement->execute($proposition);
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

}
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
    along with Congressus.  If not, see <https://www.gnu.org/licenses/>.
*/

class FixationBo {
	var $pdo = null;
	var $galetteDatabase = "";
	var $personaeDatabase = "";

	function __construct($pdo, $galetteDatabase) {
			if (is_string($galetteDatabase)) {
			$this->galetteDatabase = $galetteDatabase . ".";
		}
		else if (is_array($galetteDatabase)) {
			$this->galetteDatabase = $galetteDatabase["galette"]["db"] . ".";
			$this->personaeDatabase = $galetteDatabase["personae"]["db"] . ".";
		}
		$this->pdo = $pdo;
	}

	static function newInstance($pdo, $galetteDatabase) {
		return new FixationBo($pdo, $galetteDatabase);
	}

	function create(&$fixation) {
		$query = "	INSERT INTO ".$this->personaeDatabase."dlp_fixations () VALUES ()	";

		$statement = $this->pdo->prepare($query);
		//		echo showQuery($query, $args);

		try {
			$statement->execute();
			$fixation["fix_id"] = $this->pdo->lastInsertId();

			return true;
		}
		catch(Exception $e){
			echo 'Erreur de requète : ', $e->getMessage();
		}

		return false;
	}

	function update($fixation) {
		$query = "	UPDATE ".$this->personaeDatabase."dlp_fixations SET ";

		$separator = "";
		foreach($fixation as $field => $value) {
			$query .= $separator;
			$query .= $field . " = :". $field;
			$separator = ", ";
		}

		$query .= "	WHERE fix_id = :fix_id ";

//		echo showQuery($query, $fixation);

		$statement = $this->pdo->prepare($query);
		$statement->execute($fixation);
	}

	function save(&$fixation) {
 		if (!isset($fixation["fix_id"]) || !$fixation["fix_id"]) {
			$this->create($fixation);
 		}

		$this->update($fixation);
	}

	function getFixation($id) {
		$id = intval($id);

		$filters = array("fix_id" => $id);
		$fixations = $this->getFixations($filters);

		if (count($fixations)) {
			return $fixations[0];
		}

		return null;
	}

	function getFixations($filters = null) {
		$args = array();

		$query = "	SELECT *, IF(fix_id = the_current_fixation_id, 1, 0) as fix_is_current
					FROM  ".$this->personaeDatabase."dlp_fixations \n ";

		$query .= "	LEFT JOIN ".$this->personaeDatabase."dlp_themes ON fix_theme_id = the_id AND fix_theme_type = 'dlp_themes' AND the_deleted = 0 \n ";

//		if ($filters && isset($filters["with_fixation_information"]) && $filters["with_fixation_information"]) {
//			$query .= "	LEFT JOIN ".$this->personaeDatabase."dlp_fixations ON fix_id = fix_current_fixation_id";
//		}

		if ($filters && isset($filters["with_fixation_members"]) && $filters["with_fixation_members"]) {
			$query .= "	LEFT JOIN ".$this->personaeDatabase."dlp_fixation_members ON fix_id = fme_fixation_id \n";
			$query .= "	LEFT JOIN ".$this->galetteDatabase."galette_adherents ON fme_member_id = id_adh \n";
		}

		$query .= "	WHERE
						1 = 1 \n";

		if ($filters && isset($filters["fix_id"])) {
			$args["fix_id"] = $filters["fix_id"];
			$query .= " AND fix_id = :fix_id \n";
		}

		if ($filters && isset($filters["fix_next_fixation_date"])) {
			$args["fix_next_fixation_date"] = $filters["fix_next_fixation_date"];
			$query .= " AND fix_next_fixation_date = :fix_next_fixation_date \n";
		}

		if ($filters && isset($filters["fme_member_id"])) {
			$args["fme_member_id"] = $filters["fme_member_id"];
			$query .= " AND fme_member_id = :fme_member_id \n";
		}

		$query .= "	ORDER BY fix_until_date DESC, fix_id DESC ";

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

	function addFixationMember($fixationMember) {
		$query = "	INSERT INTO ".$this->personaeDatabase."dlp_fixation_members
						(fme_fixation_id, fme_member_id, fme_power)
					VALUES
						(:fme_fixation_id, :fme_member_id, :fme_power)	";

		$statement = $this->pdo->prepare($query);
		//		echo showQuery($query, $fixationMember);

		$statement->execute($fixationMember);
		return true;
	}

	function removeFixationMember($fixationMember) {
		if (isset($fixationMember["fme_power"])) {
			unset($fixationMember["fme_power"]);
		}

		$query = "	DELETE FROM  ".$this->personaeDatabase."dlp_fixation_members
					WHERE
						fme_fixation_id = :fme_fixation_id
					AND fme_member_id = :fme_member_id \n";

		//		$query .= "	ORDER BY gro_label, the_label ";

		$statement = $this->pdo->prepare($query);
		//		echo showQuery($query, $args);

		$statement->execute($fixationMember);
	}
}
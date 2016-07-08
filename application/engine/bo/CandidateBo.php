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
    along with Congressus.  If not, see <http://www.gnu.org/licenses/>.
*/

class CandidateBo {
	var $pdo = null;

	function __construct($pdo) {
		$this->pdo = $pdo;
	}

	static function newInstance($pdo) {
		return new CandidateBo($pdo);
	}

	function create(&$candidate) {
		$query = "	INSERT INTO dlp_candidates () VALUES ()	";

		$statement = $this->pdo->prepare($query);
//				echo showQuery($query, $args);

		try {
			$statement->execute();
			$candidate["can_id"] = $this->pdo->lastInsertId();

			return true;
		}
		catch(Exception $e){
			echo 'Erreur de requète : ', $e->getMessage();
		}

		return false;
	}

	function update($candidate) {
		$query = "	UPDATE dlp_candidates SET ";

		$separator = "";
		foreach($candidate as $field => $value) {
			$query .= $separator;
			$query .= $field . " = :". $field;
			$separator = ", ";
		}

		$query .= "	WHERE can_id = :can_id ";

//		echo showQuery($query, $candidate);

		$statement = $this->pdo->prepare($query);
		$statement->execute($candidate);
	}

	function save(&$candidate) {
 		if (!isset($candidate["can_id"]) || !$candidate["can_id"]) {
			$this->create($candidate);
		}

		$this->update($candidate);
	}

	function getCandidates($filters = null) {
		$args = array();

		$query = "	SELECT *
					FROM  dlp_candidates
					WHERE
						1 = 1 \n";

		if (isset($filters["can_theme_id"])) {
			$args["can_theme_id"] = $filters["can_theme_id"];
			$query .= " AND can_theme_id = :can_theme_id \n";
		}

		if (isset($filters["can_member_id"])) {
			$args["can_member_id"] = $filters["can_member_id"];
			$query .= " AND can_member_id = :can_member_id \n";
		}

//		$query .= "	ORDER BY gro_label, the_label ";

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
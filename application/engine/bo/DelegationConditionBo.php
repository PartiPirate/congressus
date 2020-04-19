<?php /*
    Copyright 2018 Cédric Levieux, Parti Pirate

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

class DelegationConditionBo {
	var $pdo = null;
	var $personaeDatabase = "";

	function __construct($pdo, $config) {
		$this->pdo = $pdo;

		if ($config && isset($config["personae"]["db"])) {
			$this->personaeDatabase = $config["personae"]["db"] . ".";
		}
	}

	static function newInstance($pdo, $config = null) {
		return new DelegationConditionBo($pdo, $config);
	}

	function create(&$delegationCondition) {
		$query = "	INSERT INTO ".$this->personaeDatabase."dlp_delegation_conditions () VALUES ()	";

		$statement = $this->pdo->prepare($query);
//				echo showQuery($query, $args);

		try {
			$statement->execute();
			$delegationCondition["dco_id"] = $this->pdo->lastInsertId();

			return true;
		}
		catch(Exception $e){
			echo 'Erreur de requète : ', $e->getMessage();
		}

		return false;
	}

	function update($delegationCondition) {
		$query = "	UPDATE ".$this->personaeDatabase."dlp_delegation_conditions SET ";

		$separator = "";
		foreach($delegationCondition as $field => $value) {
			$query .= $separator;
			$query .= $field . " = :". $field;
			$separator = ", ";
		}

		$query .= "	WHERE dco_id = :dco_id ";

//		echo showQuery($query, $delegationCondition);

		$statement = $this->pdo->prepare($query);
		$statement->execute($delegationCondition);
	}

	function save(&$delegationCondition) {
 		if (!isset($delegationCondition["dco_id"]) || !$delegationCondition["dco_id"]) {
			$this->create($delegationCondition);
		}

		$this->update($delegationCondition);
	}

	function deleteByUniqueKey($delegationCondition) {
		$args = array();

		if (is_array($delegationCondition)) {
			$args["dco_id"] = $delegationCondition["dco_id"];
		}
		else {
			$args["dco_id"] = $delegationCondition;
		}

		$query = "	DELETE FROM ".$this->personaeDatabase."dlp_delegation_conditions
					WHERE
						dco_id = :dco_id ";

//		echo showQuery($query, $args);
//		error_log("SQL : " . showQuery($query, $args));

		$statement = $this->pdo->prepare($query);
		$statement->execute($args);
	}
	
	function getConditions($filters) {
		$args = array();

		/* LEFT */

		$query = "	SELECT *
					FROM  ".$this->personaeDatabase."dlp_delegation_conditions ";
		$query .= "	WHERE
						1 = 1 \n";

		if (isset($filters["dco_id"])) {
			$args["dco_id"] = $filters["dco_id"];
			$query .= " AND dco_id = :dco_id \n";
		}

		if (isset($filters["dco_member_from"])) {
			$args["dco_member_from"] = $filters["dco_member_from"];
			$query .= " AND dco_member_from = :dco_member_from \n";
		}

		if (isset($filters["dco_theme_id"])) {
			$args["dco_theme_id"] = $filters["dco_theme_id"];
			$query .= " AND dco_theme_id = :dco_theme_id \n";
		}

		if (isset($filters["dco_theme_type"])) {
			$args["dco_theme_type"] = $filters["dco_theme_type"];
			$query .= " AND dco_theme_type = :dco_theme_type \n";
		}

		if (isset($filters["dco_order"])) {
			$args["dco_order"] = $filters["dco_order"];
			$query .= " AND dco_order = :dco_order \n";
		}

		$statement = $this->pdo->prepare($query);
//		echo showQuery($query, $args);
//		error_log("SQL : " . showQuery($query, $args));

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
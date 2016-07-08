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

class DelegationBo {
	var $pdo = null;

	function __construct($pdo) {
		$this->pdo = $pdo;
	}

	static function newInstance($pdo) {
		return new DelegationBo($pdo);
	}

	function create(&$delegation) {
		$query = "	INSERT INTO dlp_delegations () VALUES ()	";

		$statement = $this->pdo->prepare($query);
//				echo showQuery($query, $args);

		try {
			$statement->execute();
			$delegation["del_id"] = $this->pdo->lastInsertId();

			return true;
		}
		catch(Exception $e){
			echo 'Erreur de requète : ', $e->getMessage();
		}

		return false;
	}

	function update($delegation) {
		$query = "	UPDATE dlp_delegations SET ";

		$separator = "";
		foreach($delegation as $field => $value) {
			$query .= $separator;
			$query .= $field . " = :". $field;
			$separator = ", ";
		}

		$query .= "	WHERE del_id = :del_id ";

//		echo showQuery($query, $delegation);

		$statement = $this->pdo->prepare($query);
		$statement->execute($delegation);
	}

	function save(&$delegation) {
 		if (!isset($delegation["del_id"]) || !$delegation["del_id"]) {
			$this->create($delegation);
		}

		$this->update($delegation);
	}

	function deleteByUniqueKey($delegation) {
			if (isset($delegation["del_id"])) {
			unset($delegation["del_id"]);
		}
		if (isset($delegation["del_power"])) {
			unset($delegation["del_power"]);
		}

		$query = "	DELETE FROM dlp_delegations
					WHERE
						del_theme_id = :del_theme_id AND
						del_theme_type = :del_theme_type AND
						del_member_from = :del_member_from AND
						del_member_to = :del_member_to ";

		$statement = $this->pdo->prepare($query);
		$statement->execute($delegation);
	}

	static function testGivers($givers, $memberFrom) {
		if (!$givers) return false;
// 		echo "#";
// 		print_r($givers);
// 		echo "#";

		foreach($givers as $giver) {
			if ($giver["id_adh"] == $memberFrom) return true;
			if (isset($giver["givers"])) {
				$test = DelegationBo::testGivers($giver["givers"], $memberFrom);

				if ($test) return true;
			}
		}

		return false;
	}

	static function testCycle($powers, $delegation) {
		foreach($powers as $power) {
			if ($power["id_adh"] != $delegation["del_member_from"]) continue;

			return DelegationBo::testGivers($power["givers"], $delegation["del_member_to"]);
		}

		return false;
	}

	static function memberPowerComparator($memberA, $memberB) {
		if ($memberA["power"] == $memberB["power"]) return 0;

		return $memberA["power"] > $memberB["power"] ? -1 : 1;
	}

	function computeFixation(&$instance) {
		$filters = array();
		$members = array();

		foreach($instance["eligibles"] as $member) {
			$members[$member["id_adh"]] = $member;
		}

		foreach($instance["votings"] as $member) {
			$members[$member["id_adh"]] = $member;
		}

		$instancePower = 0;

		if (isset($instance["the_id"])) {
			$filters["del_theme_id"] = $instance["the_id"];
			$filters["del_theme_type"] = "dlp_themes";

			$instancePower = $instance["the_voting_power"];
		}

		foreach($members as $index => $member) {
			$members[$index]["power"] = $instancePower;
			$members[$index]["max_power"] = $instancePower;
		}

		$delegations = $this->getDelegations($filters);

// 		echo "<br/>----- Start members -------<br/>\n";
// 		print_r($members);
// 		echo "<br/>----- Start delegations -------<br/>\n";
// 		print_r($delegations);
// 		echo "<br/>------------<br/>\n";

		error_log("Before - Number of delegations : " . count($delegations));

		// We need to clear all useless delegation
		foreach($delegations as $index => $delegation) {
			$fromFound = false;
			$toFound = false;

			foreach ($members as $member) {
				if ($delegation["del_member_from"] == $member["id_adh"]) {
					$fromFound = true;
				}
				if ($delegation["del_member_to"] == $member["id_adh"]) {
					$toFound = true;
				}

				if ($fromFound && $toFound) break;
			}

			if (!$fromFound || !$toFound) {
				unset($delegations[$index]);

				// TODO delete delegation
			}
		}

		error_log("After - Number of delegations : " . count($delegations));

		// While there is delegation we need to compute the powers
		while(count($delegations) > 0) {
			$givers = array();
			$takers = array();

			error_log("Number of delegations : " . count($delegations));

			foreach($delegations as $delegation) {
				$givers[$delegation["del_member_from"]] = $delegation["del_member_from"];
				$takers[$delegation["del_member_to"]] = $delegation["del_member_to"];
			}

// 			echo "<br/>---- Givers --------<br/>\n";
// 			print_r($givers);
// 			echo "<br/>---- Takers --------<br/>\n";
// 			print_r($takers);
// 			echo "<br/>------------<br/>\n";

			foreach($takers as $taker) {
				foreach($givers as $index => $giver) {
					if ($taker == $giver) {
						unset($givers[$index]);
					}
				}
			}

// 			echo "<br/>+++++ Givers +++++++<br/>\n";
// 			print_r($givers);
// 			echo "<br/>++++++++++++<br/>\n";

			foreach($givers as $giver) {
				$givenPower = 0;
				foreach($members as $giverIndex => $giverMember) {
					if ($giverMember["id_adh"] != $giver) continue;

					foreach($delegations as $delegationIndex => $delegation) {
						if ($delegation["del_member_from"] != $giver) continue;
						if (!$delegation["del_power"]) continue;

						foreach($members as $takerIndex => $takerMember) {
							if ($takerMember["id_adh"] != $delegation["del_member_to"]) continue;

							$toGive = $giverMember["power"] * $delegation["del_power"] / $instancePower;
							$givenPower += $toGive;

							$members[$takerIndex]["power"] += $toGive;
							$members[$takerIndex]["max_power"] = max($members[$takerIndex]["power"], $members[$takerIndex]["max_power"]);
							$giverMember["given_power"] = $toGive;
							$members[$takerIndex]["givers"][$giverMember["id_adh"]] = $giverMember;
						}

						unset($delegations[$delegationIndex]);
					}

					$members[$giverIndex]["power"] -= $givenPower;
				}
			}

// 			echo "<br/>----- Members -------<br/>\n";
// 			print_r($members);
// 			echo "<br/>----- Delegations -------<br/>\n";
// 			print_r($delegations);
// 			echo "<br/>------------<br/>\n";
		}

		uasort($members, array('self', 'memberPowerComparator'));

		return $members;
	}

	function getDelegations($filters = null) {
		$args = array();

		$query = "	SELECT *
					FROM  dlp_delegations
					WHERE
						1 = 1
					AND del_power > 0 \n";

		if (isset($filters["del_theme_id"])) {
			$args["del_theme_id"] = $filters["del_theme_id"];
			$query .= " AND del_theme_id = :del_theme_id \n";
		}

		if (isset($filters["del_theme_type"])) {
			$args["del_theme_type"] = $filters["del_theme_type"];
			$query .= " AND del_theme_type = :del_theme_type \n";
		}

		if (isset($filters["del_member_from"])) {
			$args["del_member_from"] = $filters["del_member_from"];
			$query .= " AND del_member_from = :del_member_from \n";
		}

		if (isset($filters["del_member_to"])) {
			$args["del_member_to"] = $filters["del_member_to"];
			$query .= " AND del_member_to = :del_member_to \n";
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
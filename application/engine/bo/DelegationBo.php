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

class DelegationBo {
	var $pdo = null;
	var $personaeDatabase = "";

	function __construct($pdo, $config) {
		$this->pdo = $pdo;

		if ($config && isset($config["personae"]["db"])) {
			$this->personaeDatabase = $config["personae"]["db"] . ".";
		}
		
//		print_r($this);
	}

	static function newInstance($pdo, $config = null) {
		return new DelegationBo($pdo, $config);
	}

	function create(&$delegation) {
		$query = "	INSERT INTO ".$this->personaeDatabase."dlp_delegations () VALUES ()	";

		$statement = $this->pdo->prepare($query);
//		echo showQuery($query, array());

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
		$query = "	UPDATE ".$this->personaeDatabase."dlp_delegations SET ";

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

		$query = "	DELETE FROM ".$this->personaeDatabase."dlp_delegations
					WHERE
						del_theme_id = :del_theme_id AND
						del_theme_type = :del_theme_type AND
						del_member_from = :del_member_from AND
						del_member_to = :del_member_to ";

//		echo showQuery($query, $args);
//		error_log("SQL : " . showQuery($query, $args));

		$statement = $this->pdo->prepare($query);
		$statement->execute($delegation);
	}

	function deleteById($delegation) {
		$args = array();

		if (is_array($delegation)) {
			$args["del_id"] = $delegation["del_id"];
		}
		else {
			$args["del_id"] = $delegation;
		}

		$query = "	DELETE FROM ".$this->personaeDatabase."dlp_delegations
					WHERE
						del_id = :del_id ";

		$statement = $this->pdo->prepare($query);
		$statement->execute($args);
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

		$instancePower = 0;

		if (isset($instance["the_id"])) {
			$filters["del_theme_id"] = $instance["the_id"];
			$filters["del_theme_type"] = "dlp_themes";

			$instancePower = $instance["the_voting_power"];
		}

		foreach($instance["eligibles"] as $member) {

//			print_r($member);
//			exit();

			if (!$member["id_type_cotis"]) continue;
			$members[$member["id_adh"]] = $member;
			$members[$member["id_adh"]]["power"] = 0;
			$members[$member["id_adh"]]["max_power"] = 0;
		}

		foreach($instance["votings"] as $member) {
			if (!$member["id_type_cotis"]) continue;
			$members[$member["id_adh"]] = $member;
			$members[$member["id_adh"]]["power"] = $instancePower;
			$members[$member["id_adh"]]["max_power"] = $instancePower;
		}
/*
		foreach($members as $index => $member) {
			$members[$index]["power"] = $instancePower;
			$members[$index]["max_power"] = $instancePower;
		}
*/
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
			else if ($delegation["dco_id"]) { // if there is a condition, in this case it's not taken in account
				unset($delegations[$index]);
			}
		}

		error_log("After - Number of delegations : " . count($delegations));

		$dilution = (((isset($instance["the_dilution"])  && $instance["the_dilution"]) ? $instance["the_dilution"] : 100) / 100);

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

							$toGive = $giverMember["power"] * $delegation["del_power"] / $instancePower * $dilution;
							$givenPower += $toGive / $dilution;

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

	function computeFixationWithContext(&$instance, $motion, $votes) {
		$filters = array();
		$members = array();

		$instancePower = 0;

		if (isset($instance["the_id"])) {
			$filters["del_theme_id"] = $instance["the_id"];
			$filters["del_theme_type"] = "dlp_themes";

			$instancePower = $instance["the_voting_power"];
		}

		foreach($instance["eligibles"] as $member) {

//			print_r($member);
//			exit();

			if (!$member["id_type_cotis"]) continue;
			$members[$member["id_adh"]] = $member;
			$members[$member["id_adh"]]["power"] = 0;
			$members[$member["id_adh"]]["max_power"] = 0;
		}

		foreach($instance["votings"] as $member) {
			if (!$member["id_type_cotis"]) continue;
			$members[$member["id_adh"]] = $member;
			$members[$member["id_adh"]]["power"] = $instancePower;
			$members[$member["id_adh"]]["max_power"] = $instancePower;
		}
/*
		foreach($members as $index => $member) {
			$members[$index]["power"] = $instancePower;
			$members[$index]["max_power"] = $instancePower;
		}
*/

		foreach($members as $index => $member) {
//			$member["mem_nickname"] = GaletteBo::showIdentity($member);

//			$members[$index]["nickname"] = "Toto";
//			$members[$index]["keys"] = array();

			if ($member["pseudo_adh"]) {
				$members[$index]["nickname"] = $member["pseudo_adh"];
			}
			else {
				$members[$index]["nickname"] = $member["nom_adh"] . " " . $member["prenom_adh"];
			}

			$members[$index]["nickname"] = htmlentities($members[$index]["nickname"]);

			foreach($member as $key => $value) {
//				$members[$index]["keys"][] = $key;
				if ($key == "email_adh" || $key == "pseudo_adh" || $key == "nom_adh" || $key == "prenom_adh"
					|| $key == "can_status" || $key == "can_text"
					|| $key == "id_type_cotis") {
					unset($members[$index][$key]);
				}
			}

//			$members[$index]["nickname"] = GaletteBo::showIdentity($member);
//			$members[$index]["nickname"] = "Cédric";
//			$members[$index]["power"] = $instancePower;
//			$members[$index]["max_power"] = $instancePower;
			$members[$index]["delegation_level"] = 1;
		}

		$filters["with_conditions"] = true;

		$delegations = $this->getDelegations($filters);

// 		echo "<br/>----- Start members -------<br/>\n";
// 		print_r($members);
// 		echo "<br/>----- Start delegations -------<br/>\n";
// 		print_r($delegations);
// 		echo "<br/>------------<br/>\n";

		error_log("Before - Number of delegations : " . count($delegations));

		$context = array("motion" => $motion, "votes" => $votes, "me" => null);

		// We need to clear all useless delegation
		foreach($delegations as $index => $delegation) {
			$fromFound = false;
			$toFound = false;

			$fromMember = null;
			$fromMember2 = null;
			$toMember = null;

//			print_r($delegation);

			foreach ($members as $member) {
//				$context["me"] = $member;

				if ($delegation["del_member_from"] == $member["id_adh"]) {

//					echo "Member found\n";
					
					$fromFound = true;
					$fromMember = $member;
//					$context["me"] = $member;
				}

				if (!$delegation["del_member_from"] && ($delegation["dco_member_from"] == $member["id_adh"])) {

//					echo "Owner found\n";

					$fromFound = true;
					$toFound = true;
					$fromMember2 = $member;

//					$context["me"] = $member;
				}

				if ($delegation["del_member_to"] == $member["id_adh"]) {
					$toFound = true;
					$toMember = $member;
				}

				if ($fromFound && $toFound) break;
			}

//			if ($fromMember2) $fromMember = $fromMember2;
			$context["me"] = $fromMember;

//			print_r($fromMember2);

			$conditioned = true;

			if (isset($delegation["dco_conditions"])) {
				$conditions = json_decode($delegation["dco_conditions"], true);

				$result = ConditionalFactory::testConditions($conditions, $context);

				if (!$result) $conditioned = false;
			}

			if (!$fromFound || !$toFound || !$conditioned) {
				unset($delegations[$index]);
			}
		}

		function sortDelegations($delegationA, $delegationB) {
			if (!$delegationA["del_member_from"]) $delegationA["del_member_from"] = $delegationA["dco_member_from"];
			if (!$delegationB["del_member_from"]) $delegationB["del_member_from"] = $delegationB["dco_member_from"];
			
			if ($delegationA["del_member_from"] != $delegationB["del_member_from"]) return ($delegationA["del_member_from"] > $delegationB["del_member_from"] ? 1 : -1);
			
			if (!$delegationA["del_delegation_condition_id"]) return 1;
			if (!$delegationB["del_delegation_condition_id"]) return -1;

			if (!$delegationA["del_delegation_condition_id"] && !$delegationB["del_delegation_condition_id"]) return 0;

			return ($delegationA["dco_order"] > $delegationB["dco_order"] ? 1 : -1);
		}

		usort($delegations, "sortDelegations");

// 		print_r($delegations);
// 		echo "\n";

//		$toChangePowers = array();
		$newDelegations = array();
 		
		$count = count($delegations);
		for($index = 0; $index < $count; $index++) {
			$delegation = $delegations[$index];

//			error_log("Delegation " . print_r($delegation, true));

			if (!$delegation["del_member_from"]) $delegation["del_member_from"] = $delegation["dco_member_from"];
			$memberFrom = $delegation["del_member_from"];

//			echo "Index : $index \n";
//			error_log("Member : " . $memberFrom);
//			echo "\n";

			$memberDelegations = array();

			for($jndex = $index; $jndex < $count; $jndex++) {
				$delegation = $delegations[$jndex];

				if ($memberFrom == $delegation["del_member_from"]) {
					$delegation["del_index"] = $jndex;
					$memberDelegations[] = $delegation;
				}
				else {
					break;
				}
			}

//			echo "Number of delegation : " . count($memberDelegations);
//			echo "\n";

			$availablePower = $instancePower;

			$stopDelegation = null;

			for($jndex = 0; $jndex < count($memberDelegations); $jndex++) {
				$memberDelegation = $memberDelegations[$jndex];

//				print_r($memberDelegation);

//				echo "Power : ";
//				echo ($delegations[$memberDelegation["del_index"]]["del_power"]);
//				echo "\n";

//				echo "Available power : ";
//				echo "$availablePower";
//				echo "\n";

				if (!$stopDelegation || $stopDelegation == $memberDelegation["dco_id"]) {


					if ($availablePower >= $memberDelegation["del_power"]) {
//						echo "Power available\n";
//						print_r($memberDelegation);
//						echo "\n";

						if ($memberDelegation["del_power"] && $memberDelegation["del_member_from"] != $memberDelegation["del_member_to"]) {
							$newDelegations[] = $memberDelegation;
							$availablePower -= $memberDelegation["del_power"];
						}
					}
					else {
						$memberDelegation["del_power"] = $availablePower;
						if ($memberDelegation["del_power"]) $newDelegations[] = $memberDelegation;

//						$toChangePowers[] = array("del_index" => $memberDelegation["del_index"], "del_id" => $memberDelegation["del_id"], "del_power" => $availablePower);
						$availablePower = 0;
//				echo ($delegations[$memberDelegation["del_index"]]["del_power"]);
/*
						$delegations[$memberDelegation["del_index"]]["del_power"] = 0;
*/						
					}

					if ($memberDelegation["dco_end_of_delegation"]) {
						$stopDelegation = $memberDelegation["dco_id"];
//						echo "Stop delegation\n";
					}

				}
				else {
					$memberDelegation["del_power"] = $availablePower;
//					if ($memberDelegation["del_power"]) $newDelegations[] = $memberDelegation;
					
//					echo "Delegation stopped\n";
//					$toChangePowers[] = array("del_index" => $memberDelegation["del_index"], "del_id" => $memberDelegation["del_id"], "del_power" => 0);

//					echo ($delegations[$memberDelegation["del_index"]]["del_power"]);
				
//					$delegations[$memberDelegation["del_index"]]["del_power"] = 0;
/*
					$delegations[$memberDelegation["del_index"]]["del_power"] = 0;
*/					
				}

//				echo "--\n";
			}

			$index += (count($memberDelegations) - 1);
		}

/*
		foreach($delegations as &$delegation) {
			foreach($toChangePowers as $index => $toChangePower) {
				if ($delegation["del_id"] == $toChangePower["del_id"]) {
					echo "del_id : " . $delegation["del_id"];
					echo " - del_member_from : " . $delegation["del_member_from"];
					echo " - del_power : " . $delegation["del_power"];
					echo " => " . $toChangePower["del_power"];
					echo "\n";

//					$delegation["del_power"] = $toChangePower["del_power"];
					unset($toChangePowers[$index]);
					break;
				}
			}
		}
*/

/*
		foreach($toChangePowers as $toChangePower) {
			$delegations[$toChangePower["del_index"]]["del_power"] = $toChangePower["del_power"];
		}
*/

// 		echo "\n";
// 		print_r($delegations);
// 		echo "\n";

		$delegations = $newDelegations;

		error_log("After - Number of delegations : " . count($delegations));

		$dilution = (((isset($instance["the_dilution"])  && $instance["the_dilution"]) ? $instance["the_dilution"] : 100) / 100);

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

							$toGive = ($giverMember["power"] * $delegation["del_power"] / $instancePower) * $dilution;
							$givenPower += $toGive / $dilution;

							$members[$takerIndex]["power"] += $toGive;
							$members[$takerIndex]["max_power"] = max($members[$takerIndex]["power"], $members[$takerIndex]["max_power"]);
							$giverMember["given_power"] = $toGive;

/*							
							foreach($giverMember as $key => $value) {
								if ($key == "email_adh" || $key == "pseudo_adh" || $key == "nom_adh" || $key == "prenom_adh"
									|| $key == "can_status" || $key == "can_text"
									|| $key == "id_type_cotis") {
									unset($giverMember[$key]);
								}
							}
*/

							$members[$takerIndex]["givers"][$giverMember["id_adh"]] = $giverMember;
							
							if ($members[$takerIndex]["delegation_level"] < ($giverMember["delegation_level"] + 1)) {
								$members[$takerIndex]["delegation_level"] = $giverMember["delegation_level"] + 1;
							}
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

		/* LEFT */

		$query = "	SELECT *
					FROM  ".$this->personaeDatabase."dlp_delegations
					LEFT JOIN ".$this->personaeDatabase."dlp_delegation_conditions ON del_delegation_condition_id = dco_id ";
		$query .= "	WHERE
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

		if (isset($filters["no_condition"]) && $filters["no_condition"]) {
			$query .= " AND dco_id IS NULL";
		}

		/* UNION */
		
		$query .= " \nUNION\n ";

		/* RIGHT */
		
		$query .= "	SELECT *
			FROM  ".$this->personaeDatabase."dlp_delegations
			RIGHT JOIN ".$this->personaeDatabase."dlp_delegation_conditions ON del_delegation_condition_id = dco_id
			WHERE
				1 = 1
			\n";

		if (isset($filters["del_theme_id"])) {
			$args["del_theme_id"] = $filters["del_theme_id"];
			$query .= " AND dco_theme_id = :del_theme_id \n";
		}

		if (isset($filters["del_theme_type"])) {
			$args["del_theme_type"] = $filters["del_theme_type"];
			$query .= " AND dco_theme_type = :del_theme_type \n";
		}

		if (isset($filters["del_member_from"])) {
			$args["del_member_from"] = $filters["del_member_from"];
			$query .= " AND dco_member_from = :del_member_from \n";
		}

		if (isset($filters["no_condition"]) && $filters["no_condition"]) {
			$query .= " AND dco_id IS NULL";
		}

		$query .= "	ORDER BY dco_order ASC ";

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
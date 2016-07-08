<?php /*
	Copyright 2015 Cédric Levieux, Parti Pirate

	This file is part of PPMoney.

    PPMoney is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    PPMoney is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with PPMoney.  If not, see <http://www.gnu.org/licenses/>.
*/

class GaletteBo {
	var $pdo = null;
	var $database = "";

	function __construct($pdo, $database) {
		if ($database) {
			$this->database = $database . ".";
		}
		$this->pdo = $pdo;
	}

	static function newInstance($pdo, $database) {
		return new GaletteBo($pdo, $database);
	}

	function getMembers($filters = null) {

		$args = array();

		$query = "	SELECT *
					FROM ".$this->database."galette_adherents ga \n";

		if ($filters && isset($filters["adh_group_names"])) {
			foreach($filters["adh_group_names"] as $index => $groupName) {
				$query .= "	JOIN ".$this->database."galette_groups_members ggm$index ON ggm$index.id_adh = ga.id_adh \n";
				$query .= "	JOIN ".$this->database."galette_groups gg$index ON gg$index.id_group = ggm$index.id_group \n";
			}
		}

		if ($filters && isset($filters["adh_group_ids"])) {
			foreach($filters["adh_group_ids"] as $index => $id) {
				$query .= "	JOIN ".$this->database."galette_groups_members ggm$index ON ggm$index.id_adh = ga.id_adh \n";
				$query .= "	JOIN ".$this->database."galette_groups gg$index ON gg$index.id_group = ggm$index.id_group \n";
			}
		}

		if ($filters && isset($filters["adh_only"])) {
			$query .= "	JOIN ".$this->database."galette_cotisations gc ON ga.id_adh = gc.id_adh AND info_cotis = '' AND date_fin_cotis > NOW() \n";
			$query .= "	JOIN ".$this->database."galette_types_cotisation gtc ON gc.id_type_cotis = gtc.id_type_cotis AND cotis_extension = 1 \n";
		}

		$query .= "	WHERE 1 = 1 \n";

		if ($filters && isset($filters["email_pseudo_adh"])) {
			$query .= "	AND (ga.email_adh = :email_pseudo_adh OR ga.pseudo_adh = :email_pseudo_adh) \n";
			$args["email_pseudo_adh"] = $filters["email_pseudo_adh"];
		}

		if ($filters) {
			foreach($filters as $key => $value) {
				if (in_array($key, array("id_adh", "nom_adh", "prenom_adh", "pseudo_adh", "email_adh", "cp_adh", "ville_adh"))) {
					$query .= "	AND ga.$key = :$key \n";
					$args[$key] = $value;
				}
			}
		}

		if ($filters && isset($filters["adh_group_names"])) {
			foreach($filters["adh_group_names"] as $index => $groupName) {
				$query .= "	AND gg$index.group_name =  :group_name_$index \n";
				$args["group_name_$index"] = $groupName;
			}
		}

		if ($filters && isset($filters["adh_group_ids"])) {
			foreach($filters["adh_group_ids"] as $index => $groupName) {
				$query .= "	AND gg$index.id_group =  :group_id_$index \n";
				$args["group_id_$index"] = $groupName;
			}
		}

		$statement = $this->pdo->prepare($query);

//		echo showQuery($query, $args);

		try {
			$statement->execute($args);
			$results = $statement->fetchAll();

			foreach($results as $key => $member) {
				foreach($member as $field => $value) {
					if (is_numeric($field)) {
						unset($results[$key][$field]);
					}
				}
			}

			return $results;
		}
		catch(Exception $e){
			echo 'Erreur de requète : ', $e->getMessage();
		}

		return array();
	}

	function getMemberByMail($email) {
		$results = $this->getMembers(array("email_adh" => $email));

		if (count($results)) {
			$member = $results[0];

			return $member;
		}

		return null;
	}

	function getMemberByMailOrPseudo($emailOrPseudo) {
		$results = $this->getMembers(array("email_pseudo_adh" => $emailOrPseudo));

		if (count($results)) {
			$member = $results[0];

			return $member;
		}

		return null;
	}

	function getMemberById($memberId) {
		$results = $this->getMembers(array("id_adh" => $memberId));

		if (count($results)) {
			$member = $results[0];

			return $member;
		}

		return null;
	}

	function getSectionByName($sectionName) {
		return $this->getGroupByName($sectionName);
	}

	function getGroups($filters = null) {
		$query = "	SELECT *
					FROM ".$this->database."galette_groups
					WHERE 1 = 1";

		$args = array();

		if ($filters && isset($filters["group_name"])) {
			$query .= "	AND group_name = :group_name";
			$args["group_name"] = $filters["group_name"];
		}

		if ($filters && isset($filters["id_group"])) {
			$query .= "	AND id_group = :id_group";
			$args["id_group"] = $filters["id_group"];
		}

		//		echo showQuery($query, $args);
		$statement = $this->pdo->prepare($query);

		$groups = array();

		try {
			$statement->execute($args);
			$results = $statement->fetchAll();

			foreach($results as $group) {

				foreach($group as $field => $value) {
					if (is_numeric($field)) {
						unset($group[$field]);
					}
				}

				$groups[] = $group;
			}
		}
		catch(Exception $e){
			echo 'Erreur de requète : ', $e->getMessage();
		}

		return $groups;
	}

	function getGroupByName($groupName) {
		$filters = array("group_name" => $groupName);

		$groups = $this->getGroups($filters);

		if (count($groups)) return $groups[0];

		return null;
	}

	function getStatusByLabel($statusLabel) {
		$query = "	SELECT *
					FROM ".$this->database."galette_statuts
					WHERE 1 = 1
					AND libelle_statut = :libelle_statut";
		$args = array("libelle_statut" => $statusLabel);

		$statement = $this->pdo->prepare($query);

//		echo showQuery($query, $args);

		try {
			$statement->execute($args);
			$results = $statement->fetchAll();

//			print_r($results);

			if (count($results)) {
				$status = $results[0];

				foreach($status as $field => $value) {
					if (is_numeric($field)) {
						unset($status[$field]);
					}
				}

				return $status;
			}
		}
		catch(Exception $e){
			echo 'Erreur de requète : ', $e->getMessage();
		}

		return null;
	}

	function createMember(&$member) {
		$query = "	INSERT INTO ".$this->database."galette_adherents (id_statut, login_adh) VALUES (:id_statut, :login_adh)	";
		$args = array("id_statut" => $member["id_statut"]);
		$args["login_adh"] = strtolower(substr(sha1(rand()), 0, 20));

		$statement = $this->pdo->prepare($query);
//		echo showQuery($query, $args);

		try {
			$statement->execute($args);
			$member["id_adh"] = $this->pdo->lastInsertId();

			return true;
		}
		catch(Exception $e) {
			echo 'Erreur de requète : ', $e->getMessage();
		}

		return false;
	}

	function updateMember(&$member) {
		$query = "	UPDATE ".$this->database."galette_adherents SET ";

		$separator = "";
		foreach($member as $field => $value) {
			$query .= $separator;
			$query .= $field . " = :". $field;
			$separator = ", ";
		}

		$query .= "	WHERE id_adh = :id_adh ";

//		echo showQuery($query, $member);

		$statement = $this->pdo->prepare($query);
		$statement->execute($member);
	}

	function saveMember(&$member) {
		if (!isset($member["id_adh"]) || !$member["id_adh"]) {
			$this->createMember($member);
		}

		$this->updateMember($member);
	}

	function insertMemberInGroup($memberInGroup) {
		$query = "	INSERT INTO ".$this->database."galette_groups_members (id_group, id_adh) VALUES (:id_group, :id_adh)	";

		$statement = $this->pdo->prepare($query);
//		echo showQuery($query, $memberInGroup);

		try {
			$statement->execute($memberInGroup);

			return true;
		}
		catch(Exception $e) {
			echo 'Erreur de requète : ', $e->getMessage();
		}

		return false;
	}

	function insertTransaction(&$transaction) {
		$query = "	INSERT INTO ".$this->database."galette_transactions
						(trans_date, trans_amount, trans_desc, id_adh)
					VALUES
						(:trans_date, :trans_amount, :trans_desc, :id_adh)	";

		$statement = $this->pdo->prepare($query);
		//		echo showQuery($query, $args);

		try {
			$statement->execute($transaction);
			$transaction["trans_id"] = $this->pdo->lastInsertId();

			return true;
		}
		catch(Exception $e) {
			echo 'Erreur de requète : ', $e->getMessage();
		}

		return false;
	}

	function insertAdditional($additional) {
		$query = "	INSERT INTO galette_dynamic_fields
						(item_id, field_id, field_form, val_index,
						field_val)
					VALUES
						(:item_id, :field_id, :field_form, :val_index,
						:field_val)	";

		$statement = $this->pdo->prepare($query);
		//		echo showQuery($query, $cotisation);

		try {
			$statement->execute($additional);

			return true;
		}
		catch(Exception $e){
			echo 'Erreur de requète : ', $e->getMessage();
		}

		return false;
	}

	function insertAdditionals($additionals) {
		foreach($additionals as $additional) {
			$this->insertAdditional($additional);
		}
	}

	function insertCotisation(&$cotisation) {
		$query = "	INSERT INTO galette_cotisations
						(id_adh, id_type_cotis, montant_cotis, type_paiement_cotis,
						info_cotis, date_enreg, date_debut_cotis,
						date_fin_cotis, trans_id)
					VALUES
						(:id_adh, :id_type_cotis, :montant_cotis, :type_paiement_cotis,
						:info_cotis, :date_enreg, :date_debut_cotis,
						:date_fin_cotis, :trans_id)	";

		$statement = $this->pdo->prepare($query);
//		echo showQuery($query, $cotisation);

		try {
			$statement->execute($cotisation);
			$cotisation["id_cotis"] = $this->pdo->lastInsertId();

			return true;
		}
		catch(Exception $e){
			echo 'Erreur de requète : ', $e->getMessage();
		}

		return false;
	}

	function getTypeCotisationByLabel($label) {
		$query = "	SELECT *
					FROM ".$this->database."galette_types_cotisation
					WHERE 1 = 1
					AND libelle_type_cotis = :libelle_type_cotis";
		$args = array("libelle_type_cotis" => $label);

		$statement = $this->pdo->prepare($query);

//		echo showQuery($query, $args);

		try {
			$statement->execute($args);
			$results = $statement->fetchAll();

//			print_r($results);

			if (count($results)) {
				$typeCotisation = $results[0];

				foreach($typeCotisation as $field => $value) {
					if (is_numeric($field)) {
						unset($typeCotisation[$field]);
					}
				}

				return $typeCotisation;
			}
		}
		catch(Exception $e){
			echo 'Erreur de requète : ', $e->getMessage();
		}

		return null;
	}

	static function showPseudo($member) {
		$identity = "";

		$identity = $member["pseudo_adh"];

		if (!mb_detect_encoding($identity, 'UTF-8', true)) {
			$identity = utf8_encode($identity);
		}

		$identity = htmlentities($identity);

		return $identity;
	}

	static function showFullname($member) {
		$identity = "";

		$identity = $member["nom_adh"] . " " . $member["prenom_adh"];

		if (!mb_detect_encoding($identity, 'UTF-8', true)) {
			$identity = utf8_encode($identity);
		}

		$identity = htmlentities($identity);

		return $identity;
	}

	static function showIdentity($member) {
		$identity = "";

		if ($member["pseudo_adh"]) {
			$identity = $member["pseudo_adh"];
		}
		else {
			$identity = $member["nom_adh"] . " " . $member["prenom_adh"];
		}

		if (!mb_detect_encoding($identity, 'UTF-8', true)) {
			$identity = utf8_encode($identity);
		}

		$identity = htmlentities($identity);

		return $identity;
	}
}
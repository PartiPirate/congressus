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
    along with Congressus.  If not, see <https://www.gnu.org/licenses/>.
*/

class UserBo {
	var $pdo = null;
	var $config = "";

	function __construct($pdo, $config) {
		$this->config = $config;
		$this->pdo = $pdo;
	}

	static function newInstance($pdo, $config) {
		return new UserBo($pdo, $config);
	}
	
	public function getById($id) {
		$filters = array("id_adh" => intval($id));

		$results = $this->getByFilters($filters);

		if (count($results)) {
			return $results[0];
		}

		return null;
	}

	public function getByMail($email) {
		$filters = array("email_adh" => $email);

		$results = $this->getByFilters($filters);

		if (count($results)) {
			return $results[0];
		}

		return null;
	}

	public function getByPseudo($pseudo) {
		$filters = array("pseudo_adh" => $pseudo);

		$results = $this->getByFilters($filters);

		if (count($results)) {
			return $results[0];
		}

		return null;
	}

	public function getByFilters($filters = null) {
		if (!$filters) $filters = array();
		$args = array();

		$queryBuilder = QueryFactory::getInstance($this->config["database"]["dialect"]);

		$userSource = UserSourceFactory::getInstance($this->config["modules"]["usersource"]);
		$userSource->selectQuery($queryBuilder, $this->config);

        if (isset($filters["not_id_adh"])) {
    		$userSource->whereNotId($queryBuilder, $this->config, ":not_id_adh");
    		$args["not_id_adh"] = $filters["not_id_adh"];
        }

        if (isset($filters["id_adh"])) {
    		$userSource->whereId($queryBuilder, $this->config, ":id_adh");
    		$args["id_adh"] = $filters["id_adh"];
        }

        if (isset($filters["email_adh"])) {
    		$userSource->whereEmail($queryBuilder, $this->config, ":email_adh");
    		$args["email_adh"] = $filters["email_adh"];
        }

        if (isset($filters["pseudo_adh"])) {
    		$userSource->wherePseudo($queryBuilder, $this->config, ":pseudo_adh");
    		$args["pseudo_adh"] = $filters["pseudo_adh"];
        }


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

	function hasDataExist($field, $value, $exceptUserId = null) {
		$filters = array($field => $value);

		if ($exceptUserId) {
			$filters["not_id_adh"] = $exceptUserId;
		}

		$users = $this->getByFilters($filters);

		return (count($users) > 0);
	}

	static function hashKey($key) {
		global $config;

		$computed = hash("sha256", $config["salt"] . $key . $config["salt"], false);
//		error_log("Computed password : " . $computed);

		return $computed;
	}
	

	static function computePassword($password) {
		global $config;

		require_once("engine/libs/password.php");
		return password_hash($password, PASSWORD_BCRYPT);
	}

	function register($login, $mail, $hashedPassword, $activationKey, $language) {
		$args = array(	"use_activated" => 0,
						"email_adh" => $mail,
						"use_activation_key" => $activationKey,
						"pref_lang" => $language,
						"login_adh" => $login,
						"mdp_adh" => $hashedPassword);

		$query = "	INSERT INTO galette_adherents
						(login_adh, pseudo_adh, mdp_adh, email_adh, pref_lang, use_activated, use_activation_key)
					VALUES
						(:login_adh, :login_adh, :mdp_adh, :email_adh, :pref_lang, :use_activated, :use_activation_key) ";

		$statement = $this->pdo->prepare($query);

		//		echo showQuery($query, $args);

		try {
			$statement->execute($args);
			return true;
		}
		catch(Exception $e){
			echo 'Erreur de requète : ', $e->getMessage();
		}

		return false;
	}

	function activate($mail, $code) {
		$args = array("use_activated" => 0, "email_adh" => $mail, "use_activation_key" => $code);
		$query = "	UPDATE galette_adherents
					SET use_activated = 1, use_activation_key = ''
					WHERE
						use_activated = :use_activated
					AND	use_activation_key = :use_activation_key
					AND	email_adh = :email_adh ";

		$statement = $this->pdo->prepare($query);

		//		echo showQuery($query, $args);

		try {
			$statement->execute($args);
			$rowCount = $statement->rowCount();

			if ($rowCount) {
				return true;
			}
		}
		catch(Exception $e){
			echo 'Erreur de requète : ', $e->getMessage();
		}

		return false;
	}
}
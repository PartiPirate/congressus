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

class CustomAuthenticator {
	var $pdo = null;
	var $database = "";

	function __construct($pdo, $config) {
		$this->config = $config;
		$this->pdo = $pdo;
	}

	static function newInstance($pdo, $config) {
		return new CustomAuthenticator($pdo, $config);
	}

	function computePassword($password) {
		require_once("engine/libs/password.php");
		return password_hash($password, PASSWORD_BCRYPT);
	}

	function forgotten($email, $password) {
		$internalPassword = $this->computePassword($password);
        $customDatabase = $this->config["modules"]["custom"]["database"];

		$args = array("mdp_adh" => $internalPassword, "email_adh" => $email);

		$queryBuilder = QueryFactory::getInstance($this->config["database"]["dialect"]);
		$queryBuilder->update($customDatabase . "." . $this->config["modules"]["custom"]["table"]);

		$queryBuilder->set($this->config["modules"]["custom"]["fields"]["mdp_adh"], ":mdp_adh");
		$queryBuilder->where($this->config["modules"]["custom"]["fields"]["email_adh"] . " = :email_adh");

		$query = $queryBuilder->constructRequest();

//		echo showQuery($query, $args);

		$statement = $this->pdo->prepare($query);
		$statement->execute($args);
	}

	function get($userId) {
		$queryBuilder = QueryFactory::getInstance($this->config["database"]["dialect"]);

		$userSource = UserSourceFactory::getInstance($this->config["modules"]["usersource"]);
		$userSource->selectQuery($queryBuilder, $this->config);

		$userSource->whereId($queryBuilder, $this->config, ":id_adh");
		$args["id_adh"] = $filters["id_adh"];

		$query = $queryBuilder->constructRequest();
		$statement = $this->pdo->prepare($query);
//		echo showQuery($query, $args);

		$statement->execute($args);
		$results = $statement->fetchAll();
	
		foreach($results as $key => $member) {
			foreach($member as $field => $value) {
				if (is_numeric($field)) {
					unset($results[$key][$field]);
				}
			}
	
			return $results[$key];
		}
	
		return false;
	}

	function authenticate($login, $password) {

		$queryBuilder = QueryFactory::getInstance($this->config["database"]["dialect"]);

		$userSource = UserSourceFactory::getInstance($this->config["modules"]["usersource"]);
		$userSource->selectQuery($queryBuilder, $this->config);

		$queryBuilder->where("(" . $this->config["modules"]["custom"]["fields"]["login_adh"] . " = :login OR ". $this->config["modules"]["custom"]["fields"]["email_adh"] . " = :login)");
		$args["login"] = $login;

		$query = $queryBuilder->constructRequest();
		$statement = $this->pdo->prepare($query);
//		echo showQuery($query, $args);

		$statement->execute($args);
		$results = $statement->fetchAll();
	
		foreach($results as $key => $member) {
			foreach($member as $field => $value) {
				if (is_numeric($field)) {
					unset($results[$key][$field]);
				}
			}

//			print_r($results);

			if (password_verify($password, $results[$key]["mdp_adh"])) return $results[$key];
		}

		return false;
	}

	function changePassword($memberId, $password) {
		$internalPassword = $this->computePassword($password);
	}
}
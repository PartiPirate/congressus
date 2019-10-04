<?php /*
    Copyright 2019 Cédric Levieux, Parti Pirate

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

class ServerAdminBo {
	var $pdo = null;
	var $database = "";
	var $galetteDatabase = "";
	var $personaeDatabase = "";

	function __construct($pdo, $config) {
/*
		if ($database) {
			$this->database = $database . ".";
		}
*/
		$this->galetteDatabase = $config["galette"]["db"] . ".";
		$this->personaeDatabase = $config["personae"]["db"] . ".";
		
		$this->pdo = $pdo;
	}

	static function newInstance($pdo, $database) {
		return new ServerAdminBo($pdo, $database);
	}

	function create(&$admin) {
		$query = "	INSERT INTO ".$this->personaeDatabase."dlp_server_admins (sad_server_id, sad_member_id) VALUES (:sad_server_id, :sad_member_id)	";

		$args = array("sad_server_id" => $admin["sad_server_id"], "sad_member_id" => $admin["sad_member_id"]);

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

	function delete($admin) {
		$query = "	DELETE FROM ".$this->personaeDatabase."dlp_server_admins WHERE sad_server_id = :sad_server_id AND sad_member_id := sad_member_id";

		$args = array("sad_server_id" => $admin["sad_server_id"], "sad_member_id" => $admin["sad_member_id"]);

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

	function getServerAdmins($filters = null) {
		$args = array();

		$query = "	SELECT *
					FROM  ".$this->personaeDatabase."dlp_server_admins
					JOIN " . $this->galetteDatabase . "galette_adherents ON sad_member_id = id_adh";

		$query .= "	WHERE
						1 = 1 \n";

		if ($filters && isset($filters["sad_member_id"])) {
			$args["sad_member_id"] = $filters["sad_member_id"];
			$query .= " AND sad_member_id = :sad_member_id \n";
		}

		if ($filters && isset($filters["sad_server_id"])) {
			$args["sad_server_id"] = $filters["sad_server_id"];
			$query .= " AND sad_server_id = :sad_server_id \n";
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
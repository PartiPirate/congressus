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

abstract class ABo {
	var $pdo = null;
	var $database = "";
	var $table = "";
	var $idField = "";

	function __construct($pdo, $database = "") {
		if ($database) {
			$this->database = $database . ".";
		}
		$this->pdo = $pdo;
	}

	static function newInstance($pdo) {
		return new ThemeBo($pdo);
	}

	function create(&$theme) {
		$query = "	INSERT INTO themes () VALUES ()	";

		$statement = $this->pdo->prepare($query);
		//		echo showQuery($query, $args);

		try {
			$statement->execute();
			$theme["the_id"] = $this->pdo->lastInsertId();

			return true;
		}
		catch(Exception $e){
			echo 'Erreur de requète : ', $e->getMessage();
		}

		return false;
	}

	function update($theme) {
		$query = "	UPDATE themes SET ";

		$separator = "";
		foreach($theme as $field => $value) {
			$query .= $separator;
			$query .= $field . " = :". $field;
			$separator = ", ";
		}

		$query .= "	WHERE the_id = :the_id ";

//		echo showQuery($query, $theme);

		$statement = $this->pdo->prepare($query);
		$statement->execute($theme);
	}

	function save(&$theme) {
// 		if (!isset($theme["sig_id"]) || !$theme["sig_id"]) {
			$this->create($theme);

			// create reference
// 			$theme["tra_reference"] = "" . $theme["sig_id"];
// 			while(strlen($theme["tra_reference"]) < 8) {
// 				$theme["tra_reference"] = "0" . $theme["tra_reference"];
// 			}
// 			$theme["tra_reference"] = "PP" . $theme["tra_reference"];
// 		}

		$this->update($theme);
	}
}
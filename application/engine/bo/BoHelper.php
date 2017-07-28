<?php  /*
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
    along with Congressus.  If age, see <http://www.gnu.org/licenses/>.
*/

class BoHelper {

	static function create(&$object, $table, $idField, $config, $pdo) {
		$queryBuilder = QueryFactory::getInstance($config["database"]["dialect"]);
		$queryBuilder->insert($table);
		$query = $queryBuilder->constructRequest();

		$statement = $pdo->prepare($query);
//		echo showQuery($query, $args);

		try {
			$statement->execute();
			$object[$idField] = $pdo->lastInsertId();

			return true;
		}
		catch(Exception $e){
			echo 'Erreur de requète : ', $e->getMessage();
		}

		return false;
	}
	
	static function update($object, $table, $idField, $config, $pdo) {
		$queryBuilder = QueryFactory::getInstance($config["database"]["dialect"]);
		$queryBuilder->update($table);

		foreach($object as $field => $value) {
		    $queryBuilder->set($field, ":$field");
		}

        $queryBuilder->where("$idField = :$idField");

		$query = $queryBuilder->constructRequest();

//		echo showQuery($query, $object);

		$statement = $pdo->prepare($query);
		$statement->execute($object);
	}

}
?>
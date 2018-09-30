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
    along with Congressus.  If age, see <http://www.gnu.org/licenses/>.
*/

function tableExists($pdo, $table) {
	$query = "SHOW TABLES LIKE '$table'";

	$statement = $pdo->prepare($query);
	$statement->execute();

	$results = $statement->fetchAll();
	
	return count($results) > 0;
}

function createTable($pdo, $table, $structure) {
	$query = "CREATE TABLE $table (";

	$separator = "\n\t";
	foreach($structure["fields"] as $fieldName => $fieldStructure) {
		$query .= $separator;

		$query .= "`$fieldName` "; 
		$query .= $fieldStructure["type"]; 

		if (isset($fieldStructure["size"])) {
			$query .= " (" . $fieldStructure["size"] . ")";
		}

		if ($fieldStructure["null"]) {
			$query .= " NULL";
		}
		else {
			$query .= " NOT NULL";
		}

		if (isset($fieldStructure["default"])) {
			$query .= " DEFAULT '" . $fieldStructure["default"] . "'";
		}

		if (isset($fieldStructure["comment"])) {
			$query .= " COMMENT '" . $fieldStructure["comment"] . "'";
		}

		$separator = ",\n\t";
	}


	$query .= "\n) ENGINE=InnoDB DEFAULT CHARSET=latin1;";

	$statement = $pdo->prepare($query);
	$statement->execute();
	
	return $query;
}

function createIndex($pdo, $table, $type, $fields, $indexName = null) {
/*	
	ALTER TABLE `agendas`
  ADD PRIMARY KEY (`age_id`),
  ADD KEY `age_meeting_id` (`age_meeting_id`),
  ADD KEY `age_parent_id` (`age_parent_id`),
  ADD KEY `age_order` (`age_order`);
*/
	$query = "ALTER TABLE `$table`\n\tADD ";

	if ($type == "PRIMARY") {
		$query .= " PRIMARY ";
		$query .= " KEY (";
	}
	else {
		$query .= " KEY ";
		$query .= " `$indexName` ";
		$query .= " (";
	}

	$separator = "";
	foreach($fields as $field) {
		$query .= $separator;
		$query .= $field;
		$separator = ", ";
	}

	$query .= ");";
	
	$statement = $pdo->prepare($query);
	$statement->execute();
	
	return $query;
}

function createAutoIncrement($pdo, $table, $field, $fieldStructure) {
	
	$query = "ALTER TABLE `$table`\n\tMODIFY ";
	
	$query .= "`$field` "; 
	$query .= $fieldStructure["type"]; 

	if (isset($fieldStructure["size"])) {
		$query .= " (" . $fieldStructure["size"] . ")";
	}

	if ($fieldStructure["null"]) {
		$query .= " NULL";
	}
	else {
		$query .= " NOT NULL";
	}

	if (isset($fieldStructure["default"])) {
		$query .= " DEFAULT '" . $fieldStructure["default"] . "'";
	}

	$query .= " AUTO_INCREMENT";

	if (isset($fieldStructure["comment"])) {
		$query .= " COMMENT '" . $fieldStructure["comment"] . "'";
	}

	$query .= ";";

	$statement = $pdo->prepare($query);
	$statement->execute();
	
	return $query;
}

if (!isset($api)) exit();

$data = array();

$host = $arguments["database_host_input"];
$port = $arguments["database_port_input"];
$login = $arguments["database_login_input"];
$password = $arguments["database_password_input"];
$database = $arguments["database_database_input"];

$dns = 'mysql:host='.$host.';dbname=' . $database;
if (isset($config["database"]["port"])) {
	$dns .= ";port=" . $config["database"]["port"];
}

try {
	$pdo = new PDO($dns, $login, $password);

	require_once("install/Schema.php");

	$data["schema"] = $schema; // TEST

	// TODO schema version

	foreach($schema["tables"] as $tableName => $table) {
		// test if the table exists
		
		if (!tableExists($pdo, $tableName)) {
			// create the table
//			$data["tables"][$tableName]["sql"] = createTable($pdo, $tableName, $table);
			createTable($pdo, $tableName, $table);

			$data["tables"][$tableName]["statuts"] = "created";
		}
		else {
			$data["tables"][$tableName]["statuts"] = "exists";
		}

		// add primary key
		foreach($table["fields"] as $fieldName => $fieldStructure) {
			$primary = array();
			
			if (isset($fieldStructure["primary"]) && $fieldStructure["primary"]) {
				$primary[] = $fieldName;
			}
			
			if (count($primary)) {
				createIndex($pdo, $tableName, "PRIMARY", $primary);
			}
		}
		
		// add indexes
		foreach($table["indexes"] as $indexName => $fields) {
			createIndex($pdo, $tableName, "INDEX", $fields, $indexName);
		}

		foreach($table["fields"] as $fieldName => $fieldStructure) {
			// add auto increment
			if (isset($fieldStructure["autoincrement"]) && $fieldStructure["autoincrement"]) {
				createAutoIncrement($pdo, $tableName, $fieldName, $fieldStructure);
			}
		}
	}

	$data["ok"] = "ok";
}
catch(Exception $e){
	$data["ko"] = "ko";
	
	if (strpos($e->getMessage(), "[1045]")) {
		$data["error"] =  "bad_credentials";
	}
	else if (strpos($e->getMessage(), "[1049]")) {
		$data["error"] =  "no_database";
	}
	else if (strpos($e->getMessage(), "[2002]")) {
		$data["error"] =  "no_host";
	}
	else {
		$data["error"] =  $e->getMessage();
	}
}

echo json_encode($data, JSON_NUMERIC_CHECK);
?>
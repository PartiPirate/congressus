<?php /*
    Copyright 2018-2020 Cédric Levieux, Parti Pirate

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

function tableExists($pdo, $table) {
	$query = "SHOW TABLES LIKE '$table'";

	$statement = $pdo->prepare($query);
	$statement->execute();

	$results = $statement->fetchAll();
	
	return count($results) > 0;
}

function getTableConfiguration($pdo, $database, $table) {
//SELECT * FROM information_schema.`COLUMNS` where TABLE_SCHEMA = 'congressus' and table_name = 'meetings'	

	$query = "SELECT * FROM information_schema.columns  where TABLE_SCHEMA = '$database' and table_name = '$table' order by ordinal_position";
	
//	echo $query;

	$statement = $pdo->prepare($query);
	$statement->execute();

	$results = $statement->fetchAll();
	$columns = array();

	foreach($results as $line) {
		$column = array();
		$column["type"] = $line["DATA_TYPE"];
		$column["null"] = ($line["IS_NULLABLE"] == "YES");
		$column["size"] = $line["COLUMN_TYPE"];
		if ($line["COLUMN_KEY"] == "PRI") $column["primary"] = true;
		if (strpos($line["EXTRA"], "auto_increment") !== false) $column["autoincrement"] = 1;
		if ($line["COLUMN_DEFAULT"] !== null)  $column["default"] = $line["COLUMN_DEFAULT"];
		if ($line["COLUMN_COMMENT"])  $column["comment"] = $line["COLUMN_COMMENT"];

		$column["size"] = str_replace($column["type"], "", $column["size"]);

		if (!$column["size"]) {
			unset($column["size"]);
		}
		else {
			$column["size"] = substr($column["size"], 1, strlen($column["size"]) - 2);
		}

		if (isset($column["default"]) && $column["default"] && $column["default"][0] == "'") {
			$column["default"] = substr($column["default"], 1, strlen($column["default"]) - 2);
			$column["default"] = str_replace("''", "'", $column["default"]);
		}

		$columns[$line["COLUMN_NAME"]] = $column;
	}
	
	return $columns;
}

function compareFieldStructure($plannedStructure, $actualStructure) {
	$results = array("equal" => array(), "add" => array(), "modify" => array(), "delete" => array());

	if (isset($plannedStructure["fields"])) $plannedStructure = $plannedStructure["fields"];

	foreach($plannedStructure as $fieldName => $fieldStructure) {
		// not present in the actual database, so add it
		if (!isset($actualStructure[$fieldName])) {
			$results["add"][$fieldName] = $fieldStructure;
			continue;
		}
		
		// present in the actual database, equal or modify ?
		foreach($fieldStructure as $key => $value) {
			if (!isset($actualStructure[$fieldName][$key])) {
				if (!isset($results["modify"][$fieldName])) $results["modify"][$fieldName] = $fieldStructure;
				$results["modify"][$fieldName]["attributes"][] = $key;
				continue;
			}

			if ($actualStructure[$fieldName][$key] != $value) {
				if (!isset($results["modify"][$fieldName])) $results["modify"][$fieldName] = $fieldStructure;
				$results["modify"][$fieldName]["attributes"][] = $key . " " . $actualStructure[$fieldName][$key] . " vs " . $value;
				continue;
			}
		}

		if (!isset($results["modify"][$fieldName])) {
				$results["equal"][$fieldName] = $fieldStructure;
		}
	}

	foreach($actualStructure as $fieldName => $fieldStructure) {
		// not present in the planned database, so delete it ?
		if (!isset($plannedStructure[$fieldName])) {
			$results["delete"][$fieldName] = $fieldStructure;
		}
	}

	return $results;
}

function createTable($pdo, $table, $structure) {
	$query = "CREATE TABLE $table (";

	$separator = "\n\t";
	foreach($structure["fields"] as $fieldName => $fieldStructure) {
		$query .= $separator;

		$query .= "`$fieldName` ";
		$query .= $fieldStructure["type"];

		if (isset($fieldStructure["size"])) {
			$query .= "(" . $fieldStructure["size"] . ")";
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

function alterColumn($pdo, $table, $field, $fieldStructure, $action) {

	$action = strtoupper($action);

	$query = "ALTER TABLE `$table`\n\t" . $action . " ";
	
	$query .= "`$field` ";
	
	if ($action != "ADD") {
		$query .= "`$field` "; 
	}
	$query .= $fieldStructure["type"]; 

	if (isset($fieldStructure["size"])) {
		$query .= "(" . $fieldStructure["size"] . ")";
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

	if (isset($fieldStructure["autoincrement"]) && $fieldStructure["autoincrement"]) {
		$query .= " AUTO_INCREMENT";
	}

	if (isset($fieldStructure["comment"])) {
		$query .= " COMMENT '" . $fieldStructure["comment"] . "'";
	}

	$query .= ";";

	$statement = $pdo->prepare($query);
	$statement->execute();

//	echo $query . "\n";

	return $query;
}

if (!isset($api)) exit();

$data = array();

$host = $arguments["database_host_input"];
$port = $arguments["database_port_input"];
$login = $arguments["database_login_input"];
$password = $arguments["database_password_input"];
$database = $arguments["database_database_input"];

$dry = isset($_REQUEST["database_dry"]);

//$dry = true;
//echo "Dry : $dry\n";

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
			if (!$dry) createTable($pdo, $tableName, $table);

			$data["tables"][$tableName]["status"] = "created";
			$data["tables"][$tableName]["errorCode"] = $pdo->errorCode();

			foreach($table["fields"] as $fieldName => $fieldStructure) {
				// add auto increment
				if (isset($fieldStructure["autoincrement"]) && $fieldStructure["autoincrement"]) {
					if (!$dry) alterColumn($pdo, $tableName, $fieldName, $fieldStructure, "change");
				}
			}
		}
		else {
			$data["tables"][$tableName]["status"] = "exists";
			
			$actualConfiguration = getTableConfiguration($pdo, $database, $tableName);
			$data["tables"][$tableName]["actualConfiguration"] = $actualConfiguration;
			$comparison = compareFieldStructure($table, $actualConfiguration);
			$data["tables"][$tableName]["compare"] = $comparison;

			foreach($comparison["add"]  as $fieldName => $fieldStructure) {
				if (!$dry) alterColumn($pdo, $tableName, $fieldName, $fieldStructure, "add");
			}

			foreach($comparison["modify"]  as $fieldName => $fieldStructure) {
				if (!$dry) alterColumn($pdo, $tableName, $fieldName, $fieldStructure, "change");
			}
		}

		// add primary key
		foreach($table["fields"] as $fieldName => $fieldStructure) {
			$primary = array();

			if (isset($fieldStructure["primary"]) && $fieldStructure["primary"]) {
				$primary[] = $fieldName;
			}

			if (count($primary)) {
				if (!$dry) createIndex($pdo, $tableName, "PRIMARY", $primary);
			}
		}
		
		// add indexes
		foreach($table["indexes"] as $indexName => $fields) {
			if (!$dry) createIndex($pdo, $tableName, "INDEX", $fields, $indexName);
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
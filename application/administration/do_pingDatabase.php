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
    along with Congressus.  If age, see <http://www.gnu.org/licenses/>.
*/

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
<?php /*
	Copyright 2015-2018 Cédric Levieux, Parti Pirate

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

@include_once("config/mail.php");

try {
    $config["smtp"]["host"] = $arguments["smtp_host_input"];
    $config["smtp"]["port"] = $arguments["smtp_port_input"];

    ////Set the encryption system to use - ssl (deprecated) or tls
    $config["smtp"]["secure"] = $arguments["smtp_secure_input"];

    $config["smtp"]["username"] = $arguments["smtp_username_input"];
    $config["smtp"]["password"] = $arguments["smtp_password_input"];

	$config["smtp"]["from.address"] = $arguments["smtp_from_address_input"];
	$config["smtp"]["from.name"] = $arguments["smtp_from_name_input"];

	$message = getMailInstance();

	$message->Subject = "TEST";

	$message->isHTML(true); 

	$message->addBCC($arguments["smtp_test_address_input"], "TEST");

	$message->Body = "TEST";
	$message->AltBody = "TEST";
	$message->setFrom($config["smtp"]["from.address"], $config["smtp"]["from.name"]);

	if ($message->send()) {
		$data["ok"] = "ok";
	}
	else {
		$data["ko"] = "ko";
		$data["error"] =  "bad_credentials";
	}
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
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
    along with Congressus.  If not, see <http://www.gnu.org/licenses/>.
*/
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

$method = $_GET["method"];

error_log("Administration API Method : $method");

// Security

if (strpos($method, "..") !== false) {
	echo json_encode(array("error" => "not_a_service"));
	exit();
}

if (!file_exists("administration/$method.php")) {
	echo json_encode(array("error" => "not_a_service"));
	exit();
}

if (!$_SESSION["administrator"]) {
	echo json_encode(array("error" => "not_enough_rights"));
	exit();
}


$arguments = $_POST;
$api = true;

//error_log(print_r($_POST, true));

include("administration/$method.php");

?>
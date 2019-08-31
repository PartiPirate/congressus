<?php /*
	Copyright 2015-2017 Cédric Levieux, Parti Pirate

	This file is part of Personae.

    Personae is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    Personae is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with Personae.  If not, see <http://www.gnu.org/licenses/>.
*/
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once("config/database.php");
include_once("language/language.php");
require_once("engine/utils/FormUtils.php");

session_start();

xssCleanArray($_REQUEST);
xssCleanArray($_GET);
xssCleanArray($_POST);

if (!isset($_GET["method"])) {
	echo json_encode(array("error" => "method_not_given"));
	exit();
}

$method = $_GET["method"];

error_log("Meeting API Method : $method");
// Security

if (strpos($method, "..") !== false) {
	echo json_encode(array("error" => "forbidden_service", "method" => $method));
	exit();
}

if (!file_exists("api/$method.php")) {
	echo json_encode(array("error" => "not_a_service", "method" => $method));
	exit();
}

//$arguments = $_POST;
$api = true;

$gamifierClient = null;
if (isset($config["gamifier"]["url"])) {
    $gamifierClient = GamifierClient::newInstance($config["gamifier"]["url"]);
}

function createGameEvent($userId, $eventId) {
    global $config;

    $event = array("user_uuid" => computeGameUserId($userId), "event_uuid" => $eventId, "service_uuid" => $config["gamifier"]["service_uuid"], "service_secret" => $config["gamifier"]["service_secret"]);
    
    return $event;
}

function computeGameUserId($userId) {
    global $config;

    return sha1($config["gamifier"]["user_secret"] . $userId);
}

function testTokens() {
    $token = $_REQUEST["token"];
    $secret = $_REQUEST["secret"]; 

    global $config;

    if (!isset($config["applications"])) {
        return false;
    }

    foreach($config["applications"] as $application) {
        if ($token == $application["token"] && $secret == $application["secret"]) {
            return true;
        }
    }

    return false;
}

include("api/$method.php");

?>
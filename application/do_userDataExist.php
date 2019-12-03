<?php /*/*
    Copyright 2018-2019 Cédric Levieux, Parti Pirate

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

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
include_once("config/config.php");
include_once("config/database.php");
require_once("engine/bo/UserBo.php");
require_once("engine/utils/SessionUtils.php");

// TODO verify referer
if (!isset($_SERVER["HTTP_REFERER"]) || !$_SERVER["HTTP_REFERER"]) {
	exit();
}

$userId = SessionUtils::getUserId($_SESSION);

$connection = openConnection();
$userBo = UserBo::newInstance($connection, $config);

$field = $_REQUEST["field"];
$value = $_REQUEST["value"];

$data = array();

switch ($field) {
	case "mail":
		$field = "email_adh";
		break;
	case "login":
		$field = "pseudo_adh";
		break;
	default:
		$data["ko"] = "ko";
		$data["message"] = "error_not_permitted";
}

if (!isset($data["ko"])) {
	$dataExists = $userBo->hasDataExist($field, $value, $userId);

	$data["exist"] = $dataExists;
	$data["ok"] = "ok";
}

echo json_encode($data);
?>

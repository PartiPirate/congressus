<?php /*
    Copyright 2020 Cédric Levieux, Parti Pirate

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

//exit();

session_start();
include_once("config/database.php");
require_once("engine/bo/MessageBo.php");
require_once("language/language.php");

$connection = openConnection();

$messageBo = MessageBo::newInstance($connection, $config);

$filters = array();
$data = array();

$messageIds = $_REQUEST["messageIds"];
$messages = array();

foreach($messageIds as $messageId) {
	$message = array("mes_id" => $messageId, "mes_consumed" => 1);
	$messageBo->update($message);

	$messages[] = $message;
}

$data["messages"] = $messages;

echo json_encode($data, JSON_NUMERIC_CHECK);
?>
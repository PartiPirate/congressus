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
    along with Congressus.  If not, see <http://www.gnu.org/licenses/>.
*/

require_once("config/database.php");
require_once("engine/utils/SessionUtils.php");

session_start();
$connection = openConnection();

if (isset($_REQUEST["userId"])) {
    $userId = intval($_REQUEST["userId"]);
}
else {
    $userId = SessionUtils::getUserId($_SESSION);
}

$galetteDatabase = "";

if (isset($config["galette"]["db"]) && $config["galette"]["db"]) {
    $galetteDatabase = $config["galette"]["db"];
    $galetteDatabase .= ".";
}

$queryBuilder = QueryFactory::getInstance($config["database"]["dialect"]);
$userSource = UserSourceFactory::getInstance($config["modules"]["usersource"]);
$userSource->selectQuery($queryBuilder, $config);


$queryBuilder->addSelect("gp" . ".picture");
$queryBuilder->addSelect("gp" . ".format");
$queryBuilder->join($galetteDatabase . "galette_pictures", "gp.id_adh = galette_adherents.id_adh", "gp", "left");


$queryBuilder->where("galette_adherents.id_adh = " . ":id_adh");
//$userSource->whereId($queryBuilder, $config, ":id_adh");
$args["id_adh"] = $userId;

$query = $queryBuilder->constructRequest();
$statement = $connection->prepare($query);
//echo showQuery($query, $args);

$statement->execute($args);
$results = $statement->fetchAll();

//print_r($results);

//echo count($results);

if (!count($results) || !$results[0]["format"]) {
    header('Content-type: image/png');
    echo file_get_contents("assets/images/avatar-default.png");
    exit();
}

$user = $results[0];

//print_r($user);

header('Content-type: image/' . $user["format"]);
echo $user["picture"];
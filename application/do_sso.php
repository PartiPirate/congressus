<?php /*
	Copyright 2016 Cédric Levieux, Parti Pirate

	This file is part of Congressus.

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

include_once("config/database.php");
require_once("engine/utils/FormUtils.php");
require_once("engine/bo/GaletteBo.php");
require_once("engine/authenticators/GaletteAuthenticator.php");

session_start();

// We sanitize the request fields
xssCleanArray($_REQUEST);

$connection = openConnection();

$query = "	SELECT *
			FROM sso.tokens
			WHERE
				tok_user_id = :tok_user_id
			AND tok_validity_date > NOW()
			AND tok_used_date IS NULL
			AND tok_secret = :tok_secret \n";


$userId = $_REQUEST["userId"];

$args = array(	"tok_user_id" => $userId,
				"tok_secret" => $_REQUEST["secret"]);

$statement = $connection->prepare($query);
$statement->execute($args);
$results = $statement->fetchAll();

// print_r($args);
// print_r($results);

if (!count($results)) exit();
if ($results[0]["tok_application"] != "congressus") exit();

$updateSsoQuery = "	UPDATE sso.tokens SET tok_used_date = NOW() WHERE tok_id = :tok_id";
$updateSsoArgs = array("tok_id" => $results[0]["tok_id"]);

//echo showQuery($updateSsoQuery, $updateSsoArgs);

$statement = $connection->prepare($updateSsoQuery);
$statement->execute($updateSsoArgs);

$galetteAuthenticator = GaletteAuthenticator::newInstance($connection, $config["galette"]["db"]);

$data = array();

$member = $galetteAuthenticator->get($userId);
if ($member) {
	$data["ok"] = "ok";
	$connectedMember = array();
	$connectedMember["pseudo_adh"] = GaletteBo::showIdentity($member);
	$connectedMember["id_adh"] = $member["id_adh"];

	$_SESSION["member"] = json_encode($connectedMember);
	$_SESSION["memberId"] = $member["id_adh"];
}
else {
	$data["ko"] = "ko";
	$data["message"] = "error_login_bad";
}

session_write_close();

header('Location: index.php');
?>
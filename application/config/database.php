<?php /*
    Copyright 2015-2017 Cédric Levieux, Parti Pirate

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
@include_once("config/config.php");
@include_once("config/modules.config.php");
@include_once("config/salt.php");
@include_once("engine/bo/BoHelper.php");
@include_once("engine/requests/sql/QueryFactory.php");
@include_once("engine/requests/sql/MySQLQuery.php");

@include_once("engine/modules/usersource/UserSourceFactory.php");
@include_once("engine/modules/usersource/InternalUserSource.php");
@include_once("engine/modules/usersource/GaletteUserSource.php");
@include_once("engine/modules/usersource/CustomUserSource.php");

@include_once("engine/modules/groupsource/GroupSourceFactory.php");
@include_once("engine/modules/groupsource/GaletteGroupSource.php");
@include_once("engine/modules/groupsource/GaletteAllMembersGroupSource.php");
@include_once("engine/modules/groupsource/PersonaeGroupSource.php");
@include_once("engine/modules/groupsource/PersonaeThemeSource.php");
@include_once("engine/modules/groupsource/CustomGroupSource.php");

@require_once("engine/authenticators/AuthenticatorFactory.php");
@require_once("engine/authenticators/InternalAuthenticator.php");
@require_once("engine/authenticators/GaletteAuthenticator.php");
@require_once("engine/authenticators/CustomAuthenticator.php");

function openConnection($dbname = null) {
	global $config;
	if (!$dbname) {
		$dbname = $config["database"]["database"];
	}

	$dns = $config["database"]["dialect"].':host='.$config["database"]["host"].';dbname=' . $dbname;

	if (isset($config["database"]["port"])) {
		$dns .= ";port=" . $config["database"]["port"];
	}

	$user = $config["database"]["login"];
	$password = $config["database"]["password"];

	$pdo = null;
	try {
		$pdo = new PDO($dns, $user, $password );
	}
	catch(Exception $e){
//		echo 'Erreur de requète : ', $e->getMessage();
		return null;
	}

	return $pdo;
}

function showQuery($query, $args) {
	foreach($args as $key => $value) {
		$query = str_replace(":$key", "'$value'", $query);
	}

	return "<pre>" . $query . "</pre>\n";
}

?>
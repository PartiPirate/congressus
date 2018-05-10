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
session_start();
include_once("config/database.php");
require_once("engine/utils/SessionUtils.php");
require_once("engine/bo/GaletteBo.php");
require_once("engine/bo/UserBo.php");
require_once("engine/bo/UserPropertyBo.php");

//require_once("engine/authenticators/GaletteAuthenticator.php");

$user = SessionUtils::getUser($_SESSION);
$password = $_REQUEST["password"];
$confirmation = $_REQUEST["confirmation"];
$old = $_REQUEST["old"];

$connection = openConnection();
//$galetteBo = GaletteBo::newInstance($connection, $config["galette"]["db"]);
//$galetteAuthenticator = GaletteAuthenticator::newInstance($connection, $config["galette"]["db"]);
$userBo = UserBo::newInstance($connection, $config);
$userPropertyBo = UserPropertyBo::newInstance($connection, $config);

function getUserProperty($property) {
	global $userProperties;
	
	foreach($userProperties as $userProperty) {
		if ($userProperty["upr_property"] == $property) {
			return $userProperty;
		}
	}
	
	return array("upr_id" => 0, "upr_user_id" => 0, "upr_property" => $property);
}

$authenticator = AuthenticatorFactory::getInstance($connection, $config, $config["modules"]["authenticator"]);

$sessionUserId = SessionUtils::getUserId($_SESSION);
$user = $userBo->getById($sessionUserId);

if (!$user) {
	echo json_encode(array("ko" => "ko", "message" => "error_cant_change_password"));
	exit();
}

$userProperties = $userPropertyBo->getByFilters(array("upr_user_id" => $sessionUserId));

$data = array();

if ($old) {
    if ($password != $confirmation) {
    	echo json_encode(array("ko" => "ko", "message" => "error_cant_change_password"));
    	exit();
    }
    
    if (!password_verify($old, $user["mdp_adh"])) {
    	echo json_encode(array("ko" => "ko", "message" => "error_cant_change_password"));
    	exit();
    }
    
    if ($password) {
    	$authenticator->forgotten($user["email_adh"], $password);
    }
}

$data["theme"] = "unchanged";
if (isset($_REQUEST["theme"])) {
    $themeUserProperty = getUserProperty("theme");
    
    if ($themeUserProperty["upr_value"] != $_REQUEST["theme"]) {
        $themeUserProperty["upr_value"] = $_REQUEST["theme"];
        $themeUserProperty["upr_user_id"] = $sessionUserId;
        
        $userPropertyBo->save($themeUserProperty);
        
        $data["theme"] = "changed";
    }
}

$data["ok"] = "ok";

echo json_encode($data, JSON_NUMERIC_CHECK);
?>
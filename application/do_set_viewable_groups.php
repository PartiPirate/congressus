<?php /*
    Copyright 2021 Cédric Levieux, Parti Pirate

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
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

include_once("config/database.php");
require_once("engine/utils/FormUtils.php");
require_once("engine/utils/SessionUtils.php");
require_once("engine/bo/UserBo.php");
require_once("engine/bo/UserPropertyBo.php");

function getUserProperty($property) {
	global $userProperties;
	
	foreach($userProperties as $userProperty) {
		if ($userProperty["upr_property"] == $property) {
			return $userProperty;
		}
	}
	
	return array("upr_id" => 0, "upr_user_id" => 0, "upr_property" => $property);
}

$connection = openConnection();

$groups = $_POST["viewable_groups"];

$data = array("viewable_groups" => $groups);

//print_r($_SESSION);

$sessionUserId = SessionUtils::getUserId($_SESSION);

if ($sessionUserId) {
    $userPropertyBo = UserPropertyBo::newInstance($connection, $config);

    $userProperties = $userPropertyBo->getByFilters(array("upr_user_id" => $sessionUserId));

    $property = getUserProperty("viewable_groups");
    $propertyValue = json_encode($groups);

    if ($property["upr_value"] != $propertyValue) {
        $property["upr_value"] = $propertyValue;
        $property["upr_user_id"] = $sessionUserId;
        
        $userPropertyBo->save($property);
    }
    
    $data["property"] = $property;
}

$_SESSION["viewable_groups"] = $groups;

echo json_encode($data);

?>
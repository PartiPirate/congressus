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

if (!isset($api)) exit();

include_once("config/database.php");
require_once("engine/bo/TrustLinkBo.php");

$connection = openConnection();

$trustLinkBo = TrustLinkBo::newInstance($connection, $config);

$data = array();

$link = $trustLinkBo->getById($arguments["tli_id"]);

if (!$link) {
	echo json_encode(array("ko" => "ko", "message" => "no_link"));
	exit();
}

$userId = SessionUtils::getUserId($_SESSION);

$action = $arguments["action"];

switch($action) {
    case "cancel": 
        if ($link["tli_from_member_id"] == $userId || $link["tli_to_member_id"] == $userId) {
            $trustLinkBo->delete($link);
        }
        else {
        	echo json_encode(array("ko" => "ko", "message" => "not_enough_rights"));
        	exit();
        }
        break;
    case "accept": 
    case "reject": 
        if ($link["tli_from_member_id"] == $userId) {
            $link["tli_status"] = ($action == "accept" ? TrustLinkBo::LINK : TrustLinkBo::REFUSED);

            $trustLinkBo->save($link);
        }
        else {
        	echo json_encode(array("ko" => "ko", "message" => "not_enough_rights"));
        	exit();
        }
        break;
}

$data["ok"] = "ok";

echo json_encode($data, JSON_NUMERIC_CHECK);
?>
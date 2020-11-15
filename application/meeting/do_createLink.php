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
require_once("engine/bo/UserBo.php");
require_once("engine/bo/TrustLinkBo.php");

$connection = openConnection();

$userBo = UserBo::newInstance($connection, $config);
$trustLinkBo = TrustLinkBo::newInstance($connection, $config);

$data = array();

$userId = SessionUtils::getUserId($_SESSION);

$link = array();
$link["tli_id"] = 0;
$link["tli_rights"] = array();

$memberId = 2;

$pseudo = $arguments["pseudo"];

$user = $userBo->getByPseudo($pseudo);
if (!$user && strpos($pseudo, "@") !== false) {
    $user = $userBo->getByMail($pseudo);
}

if (!$user) {
	echo json_encode(array("ko" => "ko", "message" => "no_user"));
	exit();
}

$memberId = $user["id_adh"];

if ($arguments["type"] == "from") {
    $link["tli_from_member_id"] = $userId;
    $link["tli_to_member_id"] = $memberId;
    $link["tli_status"] = TrustLinkBo::LINK;
}
else if ($arguments["type"] == "to") {
    $link["tli_to_member_id"] = $userId;
    $link["tli_from_member_id"] = $memberId;
    $link["tli_status"] = TrustLinkBo::ASKING;
}
else {
	echo json_encode(array("ko" => "ko", "message" => "no_action"));
	exit();
}

if (isset($arguments["rights"])) {
    foreach($arguments["rights"] as $right) {
        $link["tli_rights"][$right] = true;
    }
}

$link["tli_rights"] = json_encode($link["tli_rights"]);

$existingLinks = $trustLinkBo->getByFilters($link);

if (count($existingLinks)) {
	echo json_encode(array("ko" => "ko", "message" => "existing_link"));
	exit();
}

$trustLinkBo->save($link);

$data["ok"] = "ok";
$data["link"] = $link;

echo json_encode($data, JSON_NUMERIC_CHECK);
?>
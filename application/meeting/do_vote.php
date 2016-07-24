<?php /*
	Copyright 2015 Cédric Levieux, Parti Pirate

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
    along with Congressus.  If age, see <http://www.gnu.org/licenses/>.
*/

session_start();

$path = "../";
set_include_path(get_include_path() . PATH_SEPARATOR . $path);

include_once("config/database.php");
include_once("config/memcache.php");
require_once("engine/utils/SessionUtils.php");
require_once("engine/bo/MotionBo.php");
require_once("engine/bo/VoteBo.php");

require_once("engine/utils/LogUtils.php");
addLog($_SERVER, $_SESSION, null, $_POST);

$memcache = openMemcacheConnection();

$connection = openConnection();

$motionBo = MotionBo::newInstance($connection);
$voteBo = VoteBo::newInstance($connection, $config);

$motion = $motionBo->getById($_REQUEST["motionId"]);
$proposition = array("mpr_id" => $_REQUEST["propositionId"]);

$data = array();

$userId = SessionUtils::getUserId($_SESSION);

$vote = array();
$vote["vot_member_id"] = $userId;
$vote["vot_motion_proposition_id"] = $proposition["mpr_id"];

$votes = $voteBo->getByFilters($vote);
if (count($votes)) {
	$vote[$voteBo->ID_FIELD] = $votes[0][$voteBo->ID_FIELD];
}

$vote["vot_power"] = $_REQUEST["power"];

$voteBo->save($vote);
$vote = $voteBo->getById($vote[$voteBo->ID_FIELD]);

$vote["mem_id"] = $vote["id_adh"] ? $vote["id_adh"] : "G" . $vote["chat_guest_id"];
$vote["mem_nickname"] = $vote["pseudo_adh"] ? $vote["pseudo_adh"] : $vote["pin_nickname"];

$data["ok"] = "ok";
$data["vote"] = $vote;

$pointId = $motion["mot_agenda_id"];
$memcacheKey = "do_getAgendaPoint_$pointId";
$memcache->delete($memcacheKey);

echo json_encode($data, JSON_NUMERIC_CHECK);
?>
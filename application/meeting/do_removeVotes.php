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

if (!isset($api)) exit();

include_once("config/database.php");
include_once("config/memcache.php");
require_once("engine/utils/SessionUtils.php");
require_once("engine/bo/MotionBo.php");
require_once("engine/bo/VoteBo.php");

require_once("engine/utils/LogUtils.php");
addLog($_SERVER, $_SESSION, null, $_POST);

$memcache = openMemcacheConnection();

$connection = openConnection();

$motionBo = MotionBo::newInstance($connection, $config);
$voteBo = VoteBo::newInstance($connection, $config);

$motion = $motionBo->getById($_REQUEST["motionId"]);
$motionId = $motion[$motionBo->ID_FIELD];

$data = array();

$userId = SessionUtils::getUserId($_SESSION);

if (!$userId) {
	echo json_encode(array("ko" => "ko", "message" => "vote_not_accessible"));
	exit();
}

$votes = $voteBo->getByFilters(array("mot_id" => $motionId, "vot_member_id" => $userId));

foreach($votes as $vote) {
    $voteBo->delete($vote);
}

$pointId = $motion["mot_agenda_id"];
$memcacheKey = "do_getAgendaPoint_$pointId";
$memcache->delete($memcacheKey);
$memcacheKey = "do_getComputeVote_$motionId";
$memcache->delete($memcacheKey);

$data["ok"] = "ok";

echo json_encode($data, JSON_NUMERIC_CHECK);
?>
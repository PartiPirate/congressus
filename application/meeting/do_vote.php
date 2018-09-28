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

require_once("config/database.php");
require_once("config/memcache.php");
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
$vote["mem_nickname"] = htmlspecialchars(utf8_encode($vote["pseudo_adh"] ? $vote["pseudo_adh"] : $vote["pin_nickname"]));

$data["ok"] = "ok";

foreach ($vote as $key => $value) {
    if (strpos($key, "_adh")) unset($vote[$key]);
    if ($key == "id_statut") unset($vote[$key]);
    if ($key == "bool_display_info") unset($vote[$key]);
    if ($key == "date_echeance") unset($vote[$key]);
    if ($key == "pref_lang") unset($vote[$key]);
    if ($key == "lieu_naissance") unset($vote[$key]);
    if ($key == "gpgid") unset($vote[$key]);
    if ($key == "fingerprint") unset($vote[$key]);
    if ($key == "parent_id") unset($vote[$key]);
}
$data["vote"] = $vote;

if ($gamifierClient) {
    $events = array();
    $events[] = createGameEvent($userId, GameEvents::HAS_VOTED);
    
    $addEventsResult = $gamifierClient->addEvents($events);

    $data["gamifiedUser"] = $addEventsResult;
}

$pointId = $motion["mot_agenda_id"];
$memcacheKey = "do_getAgendaPoint_$pointId";
$memcache->delete($memcacheKey);
$memcacheKey = "do_getComputeVote_$motionId";
$memcache->delete($memcacheKey);

echo json_encode($data, JSON_NUMERIC_CHECK);
?>
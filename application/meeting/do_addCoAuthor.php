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
    along with Congressus.  If not, see <https://www.gnu.org/licenses/>.
*/

if (!isset($api)) exit();

include_once("config/database.php");
include_once("config/memcache.php");
require_once("engine/utils/SessionUtils.php");
require_once("engine/bo/AgendaBo.php");
require_once("engine/bo/CoAuthorBo.php");
require_once("engine/bo/MeetingBo.php");
require_once("engine/bo/MotionBo.php");
require_once("engine/bo/UserBo.php");
require_once("engine/bo/VoteBo.php");
require_once("engine/utils/EventStackUtils.php");

if (!SessionUtils::getUserId($_SESSION)) {
	echo json_encode(array("ko" => "ko", "message" => "must_be_connected"));
	exit();
}

require_once("engine/utils/LogUtils.php");
addLog($_SERVER, $_SESSION, null, $_POST);

$memcache = openMemcacheConnection();

$connection = openConnection();

$agendaBo   = AgendaBo::newInstance($connection, $config);
$meetingBo  = MeetingBo::newInstance($connection, $config);
$motionBo   = MotionBo::newInstance($connection, $config);
$userBo     = UserBo::newInstance($connection, $config);
$coAuthorBo = CoAuthorBo::newInstance($connection, $config);

$meetingId = $_REQUEST["meetingId"];

$meeting = $meetingBo->getById($meetingId);

if (!$meeting) {
	echo json_encode(array("ko" => "ko", "message" => "meeting_does_not_exist"));
	exit();
}

// TODO Compute the key // Verify the key

if (false) {
	echo json_encode(array("ko" => "ko", "message" => "meeting_not_accessible"));
	exit();
}

$userId = SessionUtils::getUserId($_SESSION);

$pointId = $_REQUEST["pointId"];
$agenda = $agendaBo->getById($pointId);

if (!$agenda || $agenda["age_meeting_id"] != $meeting[$meetingBo->ID_FIELD]) {
	echo json_encode(array("ko" => "ko", "message" => "agenda_point_not_accessible"));
	exit();
}

$agenda["age_objects"] = json_decode($agenda["age_objects"]);

// Get motion
$motion = $motionBo->getById($_REQUEST["motionId"]);
if (!$motion) {
	echo json_encode(array("ko" => "ko", "message" => "motion_does_not_exist"));
	exit();
}

$userData = $_REQUEST["userData"];

// TODO Search by pseudo, email and id

//$memberId = intval($userData);

$member = $userBo->getByPseudo($userData);
if (!$member) {
    $member = $userBo->getByMail($userData);
}
if (!$member) {
    $member = $userBo->getById($userData);
}

if (!$member) {
    $data["ko"] = "ko";
    $data["message"] = "motion_cant_add_coauthor";
}
else {
    $coAuthor = array();
    $coAuthor["cau_user_id"] = $member["id_adh"];
    $coAuthor["cau_object_type"] = "motion";
    $coAuthor["cau_object_id"] = $motion[$motionBo->ID_FIELD];
    
    $coAuthorBo->save($coAuthor);
    
    if ($coAuthor["cau_id"]) {
    //    $coAuthorBo->getById($coAuthor["cau_id"]);
        $coAuthor["pseudo_adh"] = htmlspecialchars(utf8_encode($member["pseudo_adh"] ? $member["pseudo_adh"] : $member["nom_adh"] . ' ' . $member["prenom_adh"]), ENT_SUBSTITUTE);
    
        $data["ok"] = "ok";
        $data["coAuthor"] = $coAuthor;
    }
    else {
        $data["ko"] = "ko";
        $data["message"] = "motion_cant_add_coauthor";
    }
}

echo json_encode($data, JSON_NUMERIC_CHECK);
?>
<?php /*
	Copyright 2018 Cédric Levieux, Nino Treyssat-Vincent, Parti Pirate

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

if (!isset($api)) exit();

include_once("config/discourse.config.php");
include_once("config/discourse.structure.php");
require_once("engine/discourse/DiscourseAPI.php");

require_once("engine/bo/GaletteBo.php");
require_once("engine/bo/AgendaBo.php");
require_once("engine/bo/MotionBo.php");
require_once("engine/bo/SourceBo.php");
require_once("engine/bo/UserBo.php");

$discourseApi = new richp10\discourseAPI\DiscourseAPI($config["discourse"]["url"], $config["discourse"]["api_key"], $config["discourse"]["protocol"]);

require_once("engine/utils/LogUtils.php");
addLog($_SERVER, $_SESSION, null, $_POST);

$connection = openConnection();

$agendaBo   = AgendaBo::newInstance($connection, $config);
$meetingBo  = MeetingBo::newInstance($connection, $config);
$motionBo   = MotionBo::newInstance($connection, $config);
$sourceBo   = SourceBo::newInstance($connection, $config);
$userBo     = UserBo::newInstance($connection, $config);

$meeting = $meetingBo->getById($_REQUEST["meetingId"]);

if (!$meeting) {
	echo json_encode(array("ko" => "ko", "message" => "meeting_does_not_exist"));
	exit();
}

$pointId = intval($_REQUEST["pointId"]);
$agenda = $agendaBo->getById($pointId);

if (!$agenda || $agenda["age_meeting_id"] != $meeting[$meetingBo->ID_FIELD]) {
	echo json_encode(array("ko" => "ko", "message" => "agenda_point_not_accessible"));
	exit();
}

$agenda["age_objects"] = json_decode($agenda["age_objects"]);

$motionId = intval($_REQUEST["motionId"]);
$motion = $motionBo->getByFilters(array("with_meeting" => true, "mot_id" => $motionId));

if (count($motion)) {
	$motion = $motion[0];
	$meeting = $motion;
	
//	print_r($motion);
}
else {
	echo json_encode(array("ko" => "ko", "message" => "motion_does_not_exist"));
	exit();
}

$userId = SessionUtils::getUserId($_SESSION);

if (!isset($userId)) {
	echo json_encode(array("ko" => "ko", "message" => "no_user"));
	exit();
}
else if (($userId !== $meeting["mee_president_member_id"]) AND ($userId !== $meeting["mee_secretary_member_id"])) {
	echo json_encode(array("ko" => "ko", "message" => "not_enough_right"));
	exit();
}

$author = null;
if ($motion["mot_author_id"]) {
	$author = $userBo->getById($motion["mot_author_id"]);
}

$now = getNow();
$month = $now->format("Y-m");

$discourse_category = 64; // TODO Upgrade it
$discourse_title = "Débats $month : " . $motion["mot_title"]; // TODO Upgrade this
$discourse_content = "Lien vers Congressus : ".$config["server"]["base"]."construction_motion.php?motionId=" . $motion["mot_id"] . "

Rapporteur : @" . GaletteBo::showIdentity($author);  // TODO Upgrade that

$new_topic = $discourseApi->createTopic($discourse_title, $discourse_content , $discourse_category, $config["discourse"]["user"], 0);

//print_r($new_topic->apiresult);

$topicId = isset($new_topic->apiresult->topic_id) ? $new_topic->apiresult->topic_id : 0;

//$http_code_topic = $discourseApi->getTopic($topicId)->http_code;

if ($topicId) {
	$data = array();
	
	$data["ok"] = "ok";
	$data["discourse"] = array(); 
	$data["discourse"]["url"] = $config["discourse"]["base"] . "/t/" . $topicId . "?u=congressus";
	$data["discourse"]["title"] = $discourse_title;

	// add source
	$source = array();
	$source["sou_title"] = $discourse_title;
	$source["sou_is_default_source"] = 0;
	$source["sou_url"] = $data["discourse"]["url"];
    $source["sou_articles"] = "[]";
	$source["sou_content"] = $discourse_content;
	$source["sou_type"] = "forum";
	$source["sou_motion_id"] = $motion["mot_id"];
	
	$sourceBo->save($source);

	// Add it to the agenda
	$agenda["age_objects"][] = array("sourceId" => $source[$sourceBo->ID_FIELD]);
	$agenda["age_objects"] = json_encode($agenda["age_objects"]);
	$agendaBo->save($agenda);

	echo json_encode($data);
	exit();
}
else {
	echo json_encode(array("ko" => "ko", "message" => $new_topic->apiresult->error_type));
	exit();
}
?>

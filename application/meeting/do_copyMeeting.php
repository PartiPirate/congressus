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

$path = "../";
set_include_path(get_include_path() . PATH_SEPARATOR . $path);

include_once("config/database.php");
require_once("engine/utils/SessionUtils.php");
require_once("engine/bo/LocationBo.php");
require_once("engine/bo/MeetingBo.php");
require_once("engine/bo/AgendaBo.php");
require_once("engine/bo/NoticeBo.php");
require_once("engine/bo/MotionBo.php");
require_once("engine/bo/ChatBo.php");
require_once("engine/bo/TaskBo.php");
require_once("engine/bo/ConclusionBo.php");

require_once("engine/utils/GamifierClient.php");

require_once("engine/utils/LogUtils.php");
addLog($_SERVER, $_SESSION, null, $_POST);

$userId = SessionUtils::getUserId($_SESSION);
if (!$userId) {
	exit();
}

$connection = openConnection();

$locationBo   = LocationBo::newInstance($connection, $config);
$meetingBo    = MeetingBo::newInstance($connection, $config);
$agendaBo     = AgendaBo::newInstance($connection, $config);
$noticeBo     = NoticeBo::newInstance($connection, $config);
$motionBo     = MotionBo::newInstance($connection, $config);
$chatBo       = ChatBo::newInstance($connection, $config);
$taskBo       = TaskBo::newInstance($connection, $config);
$conclusionBo = ConclusionBo::newInstance($connection, $config);

$sourceMeeting = $meetingBo->getById($_REQUEST["mee_id"], true);

$meeting = array();
$meeting["mee_label"]               = $_REQUEST["mee_label"];
$meeting["mee_secretary_member_id"] = $userId;
$meeting["mee_type"]                = $_REQUEST["mee_type"];
$meeting["mee_datetime"]            = $_REQUEST["mee_date"] . ' ' . $_REQUEST["mee_time"];
$meeting["mee_expected_duration"]   = $_REQUEST["mee_expected_duration"];
$meeting["mee_status"]              = "construction";

// From source
$meeting["mee_class"]               = $sourceMeeting["mee_class"];
$meeting["mee_meeting_type_id"]     = $sourceMeeting["mee_meeting_type_id"];
$meeting["mee_synchro_vote"]        = $sourceMeeting["mee_synchro_vote"];
if ($sourceMeeting["mee_quorum"]) {
    $meeting["mee_quorum"]          = $sourceMeeting["mee_quorum"];
}

$meetingBo->save($meeting);

$location = array();
$location["loc_meeting_id"] = $meeting[$meetingBo->ID_FIELD];
// From source
$location["loc_type"] = $sourceMeeting["loc_type"];
$location["loc_channel"] = $sourceMeeting["loc_channel"];
$location["loc_extra"] = $sourceMeeting["loc_extra"];
$location["loc_principal"] = 1;

$locationBo->save($location);

// notices
$sourceNotices = $noticeBo->getByFilters(array("not_meeting_id" => $sourceMeeting[$meetingBo->ID_FIELD]));
foreach($sourceNotices as $sourceNotice) {
    $notice = array();
    $notice["not_meeting_id"]     = $meeting[$meetingBo->ID_FIELD];
    $notice["not_noticed"]        = 0;
    $notice["not_target_type"]    = $sourceNotice["not_target_type"];
    $notice["not_target_id"]      = $sourceNotice["not_target_id"];
    $notice["not_external_mails"] = $sourceNotice["not_external_mails"];
    $notice["not_voting"]         = $sourceNotice["not_voting"];

    $noticeBo->save($notice);
}

// agendas

$agendaTranslationsIds = array();
$motionTranslationsIds = array();

$sourceAgendas = $agendaBo->getByFilters(array("age_meeting_id" => $sourceMeeting[$meetingBo->ID_FIELD]));

$agendaIds = array();
foreach($sourceAgendas as $sourceAgenda) {
    $agendaIds[$sourceAgenda["age_id"]] = $sourceAgenda["age_id"];
}
foreach($sourceAgendas as $id => $sourceAgenda) {
    if (!$sourceAgenda["age_parent_id"]) continue;
    
    if (!isset($agendaIds[$sourceAgenda["age_parent_id"]])) {
        unset($sourceAgendas[$id]);
    }
}

foreach($sourceAgendas as $sourceAgenda) {
    $agenda = array();

//	age_id
//	age_parent_id

    $agenda["age_meeting_id"]  = $meeting[$meetingBo->ID_FIELD];
    $agenda["age_order"]       = $sourceAgenda["age_order"];
	$agenda["age_active"]      = 0;
	$agenda["age_expected_duration"] = $sourceAgenda["age_expected_duration"];
	$agenda["age_duration"]    = $sourceAgenda["age_duration"];
	$agenda["age_label"]       = $sourceAgenda["age_label"];
	$agenda["age_objects"]     = "[]";
	$agenda["age_description"] = $sourceAgenda["age_description"];

    $agendaBo->save($agenda);
    
    $agendaTranslationsIds["" . $sourceAgenda["age_id"]] = $agenda["age_id"];

    if ($meeting["mee_type"] == "meeting") {
        $objects = json_decode($sourceAgenda["age_objects"], true);
        $repackObjects = array();
        foreach($objects as &$object) {
            if (!isset($object["taskId"]) && ((isset($object["motionId"]) && $object["motionId"]) || (isset($object["chatId"]) && $object["chatId"]) || (isset($object["conclusionId"]) && $object["conclusionId"]))) {
                $repackObjects[] = $object;
            }
        }
        $objects = $repackObjects;

        // TODO

        // motions
    	$sourceMotions     = $motionBo->getByFilters(array("mot_agenda_id" => $sourceAgenda[$agendaBo->ID_FIELD]));
        foreach($sourceMotions as $sourceMotion) {

            if (!isset($motionTranslationsIds["" . $sourceMotion["mot_id"]])) {
                $motion = array();
    
            	$motion["mot_author_id"]   = $sourceMotion["mot_author_id"];
            	$motion["mot_agenda_id"]   = $agendaTranslationsIds["" . $sourceMotion["mot_agenda_id"]];
            	$motion["mot_deleted"]     = 0;
            	$motion["mot_status"]      = "construction";
            	$motion["mot_tag_ids"]     = $sourceMotion["mot_tag_ids"];
            	$motion["mot_pinned"]      = $sourceMotion["mot_pinned"];
            	$motion["mot_anonymous"]   = $sourceMotion["mot_anonymous"];
            	$motion["mot_type"]        = $sourceMotion["mot_type"];
            	$motion["mot_win_limit"]   = $sourceMotion["mot_win_limit"];
            	$motion["mot_title"]       = $sourceMotion["mot_title"];
            	$motion["mot_description"] = $sourceMotion["mot_description"];
            	$motion["mot_explanation"] = $sourceMotion["mot_explanation"];
    
                $motionBo->save($motion);

                $motionTranslationsIds["" . $sourceMotion["mot_id"]] = $motion["mot_id"];

                foreach($objects as &$object) {
                    if (isset($object["motionId"]) && $object["motionId"] == $sourceMotion["mot_id"]) {
                        $object["motionId"] = $motion["mot_id"];
                    }
                }
            }

            if ($sourceMotion["mpr_id"] && $sourceMotion["mpr_neutral"] == 0) { // if there is a proposal and it's not neutral (neutral will be created when the motion will be put in vote)
                $proposition = array();
    
            	$proposition["mpr_motion_id"]   = $motionTranslationsIds["" . $sourceMotion["mpr_motion_id"]];
            	$proposition["mpr_label"]       = $sourceMotion["mpr_label"];
            	$proposition["mpr_winning"]     = 0;
            	$proposition["mpr_neutral"]     = $sourceMotion["mpr_neutral"];
//            	$proposition["mpr_explanation"] = $sourceMotion["mpr_id"];
    
                $motionBo->saveProposition($proposition);
            }
        }

        // chat
    	$sourceChats       = $chatBo->getByFilters(array("cha_agenda_id" => $sourceAgenda[$agendaBo->ID_FIELD]));
        foreach($sourceChats as $sourceChat) {
            $chat = array();

            $chat["cha_agenda_id"] = $agendaTranslationsIds["" . $sourceChat["cha_agenda_id"]];
//            $chat["cha_motion_id"] = $sourceChat["cha_motion_id"];
//            $chat["cha_parent_id"] = $sourceChat["cha_parent_id"];
            $chat["cha_member_id"] = $sourceChat["cha_member_id"];
            $chat["cha_guest_id"]  = $sourceChat["cha_guest_id"];
            $chat["cha_deleted"]   = 0;
            $chat["cha_type"]      = $sourceChat["cha_type"];
            $chat["cha_text"]      = $sourceChat["cha_text"];
            $chat["cha_datetime"]  = $sourceChat["cha_datetime"];

            $chatBo->save($chat);

            foreach($objects as &$object) {
                if (isset($object["chatId"]) && $object["chatId"] == $sourceChat["cha_id"]) {
                    $object["chatId"] = $chat["cha_id"];
                }
            }
        }

        /* // task DONT!
        
    	$sourceTasks       = $taskBo->getByFilters(array("tas_agenda_id" => $sourceAgenda[$agendaBo->ID_FIELD]));
        foreach($sourceTasks as $sourceTask) {
            $task = array()

            $task["tas_agenda_id"]       = $sourceTask["tas_agenda_id"];
            $task["tas_deleted"]         = 0;
            $task["tas_label"]           = $sourceTask["tas_label"];
            $task["tas_target_type"]     = $sourceTask["tas_target_type"];
            $task["tas_target_id"]       = $sourceTask["tas_target_id"];
            $task["tas_start_datetime"]  = $sourceTask["tas_start_datetime"];
            $task["tas_finish_datetime"] = $sourceTask["tas_finish_datetime"];

            $taskBo->save($task);
        }
        */

        // conclusion
    	$sourceConclusions = $conclusionBo->getByFilters(array("con_agenda_id" => $sourceAgenda[$agendaBo->ID_FIELD]));
        foreach($sourceConclusions as $sourceConclusion) {
            $conclusion = array();
            
            $conclusion["con_agenda_id"] = $agendaTranslationsIds["" . $sourceConclusion["con_agenda_id"]];
            $conclusion["con_deleted"]   = 0;
            $conclusion["con_text"]      = $sourceConclusion["con_text"];

            $conclusionBo->save($conclusion);

            foreach($objects as &$object) {
                if (isset($object["conclusionId"]) && $object["conclusionId"] == $sourceConclusion["con_id"]) {
                    $object["conclusionId"] = $conclusion["con_id"];
                }
            }
        }

        // Just set the age_objects after copy
        $agenda = array();

    	$agenda["age_id"]          = $agendaTranslationsIds["" . $sourceAgenda["age_id"]];
    	$agenda["age_objects"] = json_encode($objects);

        $agendaBo->save($agenda);

    } // End of copy of a meeting in meeting mode
} // End of agenda part

//print_r($agendaTranslationsIds);
//print_r($motionTranslationsIds);

// translate parent ids
foreach($sourceAgendas as $sourceAgenda) {
    // If no parent
    if (!$sourceAgenda["age_parent_id"]) continue;

    $agenda["age_id"] = $agendaTranslationsIds[$sourceAgenda["age_id"]];
    $agenda["age_parent_id"] = $agendaTranslationsIds[$sourceAgenda["age_parent_id"]];

    $agendaBo->save($agenda);
}

$data["ok"] = "ok";
$data["meeting"] = $meeting;

if (isset($config["gamifier"]["url"])) {
    $gamifierClient = GamifierClient::newInstance($config["gamifier"]["url"]);

    $events = array();
    $events[] = array("user_uuid" => sha1($config["gamifier"]["user_secret"] . $userId),"event_uuid" => "0a730dcc-3a64-11e7-bc38-0242ac110005","service_uuid" => $config["gamifier"]["service_uuid"], "service_secret" => $config["gamifier"]["service_secret"]);

    $addEventsResult = $gamifierClient->addEvents($events);
}

if (isset($_REQUEST["ajax"])) {
    $data["url"] = "meeting.php?id=" . $meeting[$meetingBo->ID_FIELD];
	echo json_encode($data, JSON_NUMERIC_CHECK);
}
else {
	header("Location: meeting.php?id=" . $meeting[$meetingBo->ID_FIELD]);
}
?>

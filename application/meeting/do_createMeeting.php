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
require_once("engine/utils/SessionUtils.php");
require_once("engine/bo/LocationBo.php");
require_once("engine/bo/AgendaBo.php");
require_once("engine/bo/MeetingBo.php");
require_once("engine/bo/NoticeBo.php");

require_once("engine/utils/GamifierClient.php");

require_once("engine/utils/LogUtils.php");
addLog($_SERVER, $_SESSION, null, $_POST);

$userId = SessionUtils::getUserId($_SESSION);
if (!$userId) {
	exit();
}

$connection = openConnection();

$locationBo = LocationBo::newInstance($connection, $config);
$meetingBo = MeetingBo::newInstance($connection, $config);
$agendaBo = AgendaBo::newInstance($connection, $config);

$meeting = array();
$meeting["mee_label"] = $_REQUEST["mee_label"];
$meeting["mee_class"] = $_REQUEST["mee_class"];
$meeting["mee_secretary_member_id"] = $userId;
$meeting["mee_meeting_type_id"] = $_REQUEST["mee_meeting_type_id"];
$meeting["mee_type"] = $_REQUEST["mee_type"];
$meeting["mee_datetime"] = $_REQUEST["mee_date"] . ' ' . $_REQUEST["mee_time"];
$meeting["mee_expected_duration"] = $_REQUEST["mee_expected_duration"];

$meetingBo->save($meeting);

print_r($meeting);

if (isset($_REQUEST["not_target_type"]) && $_REQUEST["not_target_type"] && ((isset($_REQUEST["not_target_id"]) && $_REQUEST["not_target_id"]) || $_REQUEST["not_target_type"] == "galette_adherents")) {
    $noticeBo = NoticeBo::newInstance($connection, $config);

    $notice = array();

    $notice["not_meeting_id"] = $meeting[$meetingBo->ID_FIELD];
	$notice["not_target_id"] = $_REQUEST["not_target_id"];
    $notice["not_target_type"] = isset($_REQUEST["not_target_type"]) ? $_REQUEST["not_target_type"] : 0;
	$notice["not_voting"] = isset($_REQUEST["not_voting"]) ? 1 : 0;

    $noticeBo->save($notice);
    $data["notice"] = $notice;
}

$data["ok"] = "ok";
$data["meeting"] = $meeting;

$location = array();
$location["loc_meeting_id"] = $meeting[$meetingBo->ID_FIELD];
$location["loc_type"] = $_REQUEST["loc_type"];

if ($location["loc_type"] == "discord") {
    $location["loc_channel"] = $_REQUEST["loc_discord_text_channel"] . "," . $_REQUEST["loc_discord_vocal_channel"];
}
else {
    $location["loc_channel"] = $_REQUEST["loc_channel"];
}

$location["loc_extra"] = $_REQUEST["loc_extra"];
$location["loc_principal"] = 1;

$locationBo->save($location);

if (isset($_REQUEST["age_lines"])) {
    $lines = explode("\n", $_REQUEST["age_lines"]);

    $entries = array();

    foreach($lines as $line) {

        $levelCounter = 0;
        foreach(explode(" ", $line) as $lineWords) {
            if (strlen($lineWords)) break;
            
            $levelCounter++;
        }

        if (!trim($line)) continue;

        $entry = array("label" => trim($line), "level" => $levelCounter, "root" => true, "children" => array(), "position" => count($entries));

        for($index = count($entries) - 1; $index >= 0; $index--) {
            $existingEntry = &$entries[$index];
            if ($existingEntry["level"] < $entry["level"]) {
                $entry["root"] = false;
                $existingEntry["children"][] = $entry;
                break;
            }
        }
        
        $entries[] = $entry;
    }

    $offset = 0;

    function createChild($parent, $entry) {
        global $entries;
        global $meeting;
        global $meetingBo;
        global $agendaBo;
        global $offset;
        
        $offset++;

        foreach($entries as $existingEntry) {
            if ($existingEntry["position"] == $entry["position"] && $existingEntry["position"] == $entry["position"]) {
                $entry = $existingEntry;
                break;
            }
        }
  
        $agenda = array("age_meeting_id" => $meeting[$meetingBo->ID_FIELD]);
        $agenda["age_order"] = time() + $offset * 10;
        $agenda["age_active"] = 0;
        $agenda["age_expected_duration"] = 0;
        $agenda["age_label"] = $entry["label"];
        $agenda["age_objects"] = "[]";
        $agenda["age_description"] = ($_REQUEST["mee_type"] == "meeting" ? "Pas de description" : "");

        if ($parent) {
        	$agenda["age_parent_id"] = $parent["age_id"];
        }

        $agendaBo->save($agenda);

        $entry["age_id"] = $agenda["age_id"];

        foreach($entry["children"] as $child) {
            createChild($entry, $child);
        }
    }

    foreach($entries as $entry) {
        if (!$entry["root"]) continue;

        createChild($child, $entry);
    }
}

if (isset($config["gamifier"]["url"])) {
    $gamifierClient = GamifierClient::newInstance($config["gamifier"]["url"]);

    $events = array();
    $events[] = array("user_uuid" => sha1($config["gamifier"]["user_secret"] . $userId),"event_uuid" => "0a730dcc-3a64-11e7-bc38-0242ac110005","service_uuid" => $config["gamifier"]["service_uuid"], "service_secret" => $config["gamifier"]["service_secret"]);

    $addEventsResult = $gamifierClient->addEvents($events);
}

if (isset($_REQUEST["ajax"])) {
	echo json_encode($data, JSON_NUMERIC_CHECK);
}
else {
	header("Location: ../meeting.php?id=" . $meeting[$meetingBo->ID_FIELD]);
}
?>

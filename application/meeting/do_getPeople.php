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

define("CONNECTED_TIME", 60);
define("DISCONNECTED_TIME", 65);

require_once("config/database.php");
require_once("config/memcache.php");
require_once("engine/utils/SessionUtils.php");
require_once("engine/utils/EventStackUtils.php");
require_once("engine/utils/DateTimeUtils.php");

require_once("engine/bo/MeetingBo.php");
require_once("engine/bo/NoticeBo.php");
require_once("engine/bo/PingBo.php");

$meetingId = $_REQUEST["id"];
$memcacheKey = "do_getPeople_$meetingId";

$memcache = openMemcacheConnection();
$json = $memcache->get($memcacheKey);

if (true || !$json) {
	$connection = openConnection();

	$meetingBo = MeetingBo::newInstance($connection, $config);
	$noticeBo = NoticeBo::newInstance($connection, $config);
	$pingBo = PingBo::newInstance($connection, $config);

	$meeting = $meetingBo->getById($_REQUEST["id"]);

	if (!$meeting) {
		echo json_encode(array("ko" => "ko", "message" => "meeting_does_not_exist"));
	}

	// TODO Compute the key // Verify the key

	if (false) {
		echo json_encode(array("ko" => "ko", "message" => "meeting_not_accessible"));
	}

	function pingSpeakingRequestCompare($pingA, $pingB) {
		if ($pingA == $pingB) {
			return 0;
		}
		return ($pingA["pin_speaking_request"] < $pingB["pin_speaking_request"]) ? -1 : 1;
	}

	$pings = $pingBo->getByFilters(array("pin_meeting_id" => $meeting[$meetingBo->ID_FIELD]));
	usort($pings, "pingSpeakingRequestCompare");
	
	$order = 1;

	//print_r($pings);
	$now = getNow();

	$numberOfConnected = 0;
	$numberOfPresents = 0;

	$numberOfVoters = 0;
	$numberOfVoters = $meetingBo->getNumberOfVoters($meeting[$meetingBo->ID_FIELD]);

	foreach($pings as $index => $ping) {
		$lastPing = new DateTime($ping["pin_datetime"]);

		$diff = $now->getTimestamp() -  $lastPing->getTimestamp();

		if ($diff >= CONNECTED_TIME) {
			if ($diff <= DISCONNECTED_TIME) {
				addEvent($meetingId, EVENT_LEFT, "", array("userId" => $ping["pin_member_id"] ? $ping["pin_member_id"] : "G" . $ping["pin_guest_id"]));
			}
			
			if ($ping["pin_guest_id"])
			{
				continue;
			}
		}
		else {
			$numberOfConnected++;
		}
		
		if ($ping["pin_first_presence_datetime"] && $ping["pin_noticed"] == 1) $numberOfPresents++;

		if (!$ping["pin_speaking_request"]) continue;
		
		$pings[$index]["pin_speaking_request"] = $order;
		$order++;
	}

	//print_r($pings);

	$notices = $noticeBo->getByFilters(array("not_meeting_id" => $meeting[$meetingBo->ID_FIELD]));

	$data = array();
	$data["numberOfConnected"] = $numberOfConnected;
	$data["numberOfPresents"] = $numberOfPresents;
	$data["numberOfVoters"] = $numberOfVoters;
	$data["notices"] = array();

	$usedPings = array();

	$numberOfNoticed = 0;
	$numberOfPowers = 0;

	foreach($notices as $notice) {

		foreach($config["modules"]["groupsources"] as $groupSourceKey) {
			$groupSource = GroupSourceFactory::getInstance($groupSourceKey);
        	$groupKeyLabel = $groupSource->getGroupKeyLabel();

        	if ($groupKeyLabel["key"] != $notice["not_target_type"]) continue;

        	$groupSource->updateNotice($meeting, $notice, $pings, $usedPings);
		}

		$data["notices"][] = $notice;
		
		if (isset($notice["not_people"])) $numberOfNoticed += count($notice["not_people"]);
		foreach($notice["not_people"] as $childPeople) {
			$numberOfPowers += $childPeople["mem_power"];
		}

		if (isset($notice["no_children"])) {
			foreach($notice["no_children"] as $childNotice) {
				if (isset($childNotice["not_people"])) $numberOfNoticed += count($childNotice["not_people"]);
				$numberOfPowers += $childNotice["not_power"];
			}
		}
	}

	$data["numberOfNoticed"] = $numberOfNoticed;
	$data["numberOfPowers"] = $numberOfPowers;
	$data["mee_quorum"] = $meeting["mee_quorum"];
	$data["mee_computed_quorum"] = ceil(eval("return " . $meeting["mee_quorum"] . ";"));

	$nowString = $now->format("Y-m-d H:i:s");

	if (
			$meeting["mee_start_time"] && // we have a start date
			$meeting["mee_start_time"] != "0000-00-00 00:00:00" && // and it's not an empty date
			$meeting["mee_start_time"] < $nowString && // and we are now behind it (obviously the case)
			$meeting["mee_status"] != "closed") { // and the meeting is still not closed
		foreach($usedPings as $ping) {
			// If the noticed information is not set, set it, the used pings are noticed people
			if (!$ping["pin_noticed"]) {
				$noticedPing = array($pingBo->ID_FIELD => $ping[$pingBo->ID_FIELD]);
				$noticedPing["pin_noticed"] = 1;
				$pingBo->save($noticedPing);
			}

			if (!$ping["pin_first_presence_datetime"] || $ping["pin_first_presence_datetime"] == "0000-00-00 00:00:00") {
				$lastPing = new DateTime($ping["pin_datetime"]);

				$diff = $now->getTimestamp() -  $lastPing->getTimestamp();

				if ($diff < CONNECTED_TIME) {
					$presencePing = array($pingBo->ID_FIELD => $ping[$pingBo->ID_FIELD]);
					$presencePing["pin_first_presence_datetime"] = $nowString;
					$pingBo->save($presencePing);
				}
			}
		}

		foreach($pings as $ping) {
			if (!$ping["pin_first_presence_datetime"] || $ping["pin_first_presence_datetime"] == "0000-00-00 00:00:00") {
				$lastPing = new DateTime($ping["pin_datetime"]);

				$diff = $now->getTimestamp() -  $lastPing->getTimestamp();

				if ($diff < CONNECTED_TIME) {
					$presencePing = array($pingBo->ID_FIELD => $ping[$pingBo->ID_FIELD]);
					$presencePing["pin_first_presence_datetime"] = $nowString;
					$pingBo->save($presencePing);
				}
			}
		}
	}

	$data["visitors"] = array();

	foreach($pings as $ping) {
		$people = array("mem_id" => $ping["id_adh"] ? $ping["id_adh"] : "G" . $ping["pin_guest_id"]);
		$people["mem_nickname"] = htmlspecialchars(utf8_encode($ping["id_adh"] ? $ping["pseudo_adh"] : $ping["pin_nickname"]));
		$people["mem_meeting_president"] = ($people["mem_id"] == $meeting["mee_president_member_id"]) ? 1 : 0;
		$people["mem_meeting_secretary"] = ($people["mem_id"] == $meeting["mee_secretary_member_id"]) ? 1 : 0;

		$lastPing = new DateTime($ping["pin_datetime"]);

		$diff = $now->getTimestamp() -  $lastPing->getTimestamp();

		if ($diff < CONNECTED_TIME) {
			$people["mem_connected"] = true;
		}
		else if (!$ping["id_adh"]) {
			continue;
		}

		$people["mem_speaking"] = $ping["pin_speaking"];
		$people["mem_speaking_time"] = $ping["pin_speaking_time"];
		$people["mem_speaking_request"] = $ping["pin_speaking_request"];

		$data["visitors"][] = $people;
	}

	$data["ok"] = "ok";

	$json = json_encode($data, JSON_NUMERIC_CHECK);

	if (!$memcache->replace($memcacheKey, $json, MEMCACHE_COMPRESSED, 5)) {
		$memcache->set($memcacheKey, $json, MEMCACHE_COMPRESSED, 5);
	}
}
else {
	$data = json_decode($json, true);
	$data["cached"] = true;
}

if (!SessionUtils::getUserId($_SESSION)) {
	$data["notices"] = array();
}

echo json_encode($data, JSON_NUMERIC_CHECK);
?>
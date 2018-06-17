<?php /*
	Copyright 2015-2081 Cédric Levieux, Parti Pirate

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

define("EVENT_JOIN", "join");
define("EVENT_LEFT", "left");
define("EVENT_MOTION_ADD", "motion_add");
define("EVENT_MOTION_TO_VOTE", "motion_to_vote");
define("EVENT_MOTION_CLOSE_VOTE", "motion_close_vote");
define("EVENT_MOTION_REMOVE", "motion_remove");
define("EVENT_SECRETARY_READS_ANOTHER_POINT", "secretary_reads_another_point");
define("EVENT_SPEAK_REQUEST", "speak_request");
define("EVENT_SPEAK_RENOUNCE", "speak_renounce");
define("EVENT_SPEAK_SET", "speak_set");
define("EVENT_EXTERNAL_CHAT", "external_chat");
define("EVENT_USER_ON_AGENDA_POINT", "user_on_agenda_point");

function addEvent($meetingId, $type, $text, $options = null) {
	$events = getEvents($meetingId);

	if (!$options) $options = array();
	
	$now = time();
	
	$event = array();
	$event["timestamp"] = $now;
	$event["type"] = $type;
	$event["text"] = $text;
	$event["options"] = $options;
	
	foreach($events as $previousEvent) {
		if (($previousEvent["timestamp"] - $event["timestamp"]) < 0 && ($previousEvent["timestamp"] - $event["timestamp"]) > -5) {
			if ($event["type"] == $previousEvent["type"]) {
				// If same text, don't add it
				if ($event["text"] != "" && $event["text"] == $previousEvent["text"]) return;
				// If same options, don't add it
				if ($event["text"] == "" && json_encode($event["options"]) == json_encode($previousEvent["options"])) return;
			}
		}
	}
	
	$events[] = $event;
	
	$newEvents = array();
	
	// clean a bit the events
	foreach($events as $eventId => $event) {
		if (($now - $event["timestamp"]) < (2 * 60)) {
			//unset($events[$eventId]);
			$newEvents[] = $event;
		}
	}
	
	$json = json_encode($newEvents);
	
	$memcached = openMemcacheConnection();
	if (!$memcached->replace("events_$meetingId", $json, MEMCACHE_COMPRESSED, 90)) {
		$memcached->set("events_$meetingId", $json, MEMCACHE_COMPRESSED, 90);
	}
	
//	$memcache->delete()
}

function getEvents($meetingId) {
	$memcached = openMemcacheConnection();
	
	$json = $memcached->get("events_$meetingId");
	if ($json) {
		$events = json_decode($json, true);
	}
	else {
		$events = array();
	}
	
	return $events;
}

?>
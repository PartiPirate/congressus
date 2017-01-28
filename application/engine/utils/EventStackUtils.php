<?php

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

function addEvent($meetingId, $type, $text, $options = null) {
	$events = getEvents($meetingId);

	if (!$options) $options = array();
	
	$event = array();
	$event["timestamp"] = time();
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
	
	$json = json_encode($events);
	
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
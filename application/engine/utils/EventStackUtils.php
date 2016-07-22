<?php

define("EVENT_JOIN", "join");
define("EVENT_LEFT", "left");
define("EVENT_MOTION_ADD", "motion_add");
define("EVENT_MOTION_TO_VOTE", "motion_to_vote");
define("EVENT_MOTION_REMOVE", "motion_remove");

function addEvent($meetingId, $type, $text) {
	$events = getEvents($meetingId);

	$event = array();
	$event["timestamp"] = time();
	$event["type"] = $type;
	$event["text"] = $text;
	
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
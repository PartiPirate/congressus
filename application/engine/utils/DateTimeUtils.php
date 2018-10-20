<?php 

function getNow() {
	return getDateTime();
}

function getDateTime($sqlFormat = null) {
	global $config;
	
	$timezone = null;
	if ($config["server"]["timezone"]) {
		$timezone = new DateTimeZone($config["server"]["timezone"]);
	}

	$now = new DateTime($sqlFormat, $timezone);

	return $now;
}

function getDurationString($seconds) {
	$minutes = ($seconds - $seconds % 60) / 60;
	$seconds = $seconds % 60;
	
	return (($minutes < 10) ? "0" : "") . $minutes . ":" . (($seconds < 10) ? "0" : "") . $seconds;
}

?>
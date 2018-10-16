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

?>
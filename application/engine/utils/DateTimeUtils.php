<?php 

function getNow() {
	global $config;
	
	$timezone = null;
	if ($config["server"]["timezone"]) {
		$timezone = new DateTimeZone($config["server"]["timezone"]);
	}

	$now = new DateTime(null, $timezone);

	return $now;
}

?>
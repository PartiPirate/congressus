<?php
if(!isset($config)) {
	$config = array();
}

$config["smtp"] = array();

$config["smtp"]["host"] = "server.smtp.host";
$config["smtp"]["port"] = "server.smtp.port";
$config["smtp"]["username"] = "smtp.username";
$config["smtp"]["password"] = "smtp.password";
$config["smtp"]["secure"] = "smtp.secure";

$config["smtp"]["from.address"] = "congressus@server.smtp";
$config["smtp"]["from.name"] = "Congressus Sample";

?>
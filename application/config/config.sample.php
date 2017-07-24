<?php
if(!isset($config)) {
	$config = array();
}

$config["administrator"] = array();
$config["administrator"]["login"] = "r00t";
$config["administrator"]["password"] = "r00t";

$config["database"] = array();
$config["database"]["host"] = "127.0.0.1";
$config["database"]["port"] = 3306;
$config["database"]["login"] = "root";
$config["database"]["password"] = "r00t";
$config["database"]["database"] = "congressus";
$config["database"]["prefix"] = "";

$config["memcached"]["host"] = "127.0.0.1";
$config["memcached"]["port"] = 11211;

$config["galette"]["db"] = "congressus";
$config["personae"]["db"] = "congressus";

$config["server"] = array();
$config["server"]["base"] = "https://congressus.host/";
// The server line, ex : dev, beta - Leave it empty for production
$config["server"]["line"] = "";
$config["server"]["timezone"] = "Europe/Paris";

$config["congressus"]["ballot_majorities"] = array(0, 50, 66, 80, -1);
$config["congressus"]["ballot_majority_judgment"] = array(1, 2, 3, 4, 5, 6);
/*
$config["gamifier"]["url"] = "http://gamifier.url/gamifier_api.php";
$config["gamifier"]["user_secret"] = "asecret";
$config["gamifier"]["service_uuid"] = "service-uuid";
$config["gamifier"]["service_secret"] = "service-secret";
*/

?>

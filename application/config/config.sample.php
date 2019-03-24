<?php
if(!isset($config)) {
	$config = array();
}

$config["administrator"] = array();
$config["administrator"]["login"] = "r00t";
$config["administrator"]["password"] = "r00t";

$config["database"] = array();
$config["database"]["dialect"] = "mysql";
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

$config["server"]["pad"]["ws_url"] = "wss://congressus.local:33333/"; // external ws url
$config["server"]["pad"]["local_port"] = 33333; // local ws port
$config["server"]["pad"]["local_cert"] = "/etc/ssl/congressus.local/fullchain.pem"; // local certificate
$config["server"]["pad"]["local_pk"] = "/etc/ssl/congressus.local/privkey.pem"; // local private key
$config["server"]["pad"]["allow_self_signed"] = false;
$config["server"]["pad"]["verify_peer"] = false;

$config["congressus"]["ballot_majorities"] = array(0, 50, 66, 80, -1, -2);
$config["congressus"]["ballot_majority_judgment"] = array(1, 2, 3, 4, 5, 6);
$config["congressus"]["ballot_majority_judgment_force"] = true; // If ALL vote are mandatory in a single judgment motion
/*
$config["gamifier"]["url"] = "http://gamifier.url/gamifier_api.php";
$config["gamifier"]["user_secret"] = "asecret";
$config["gamifier"]["service_uuid"] = "service-uuid";
$config["gamifier"]["service_secret"] = "service-secret";
*/

$config["modules"]["usersource"] = "Custom";
$config["modules"]["groupsources"] = array("CustomGroups");

/* OR
$config["modules"]["usersource"] = "Galette";
$config["modules"]["groupsources"] = array("PersonaeGroups", "PersonaeThemes", "GaletteGroups");
*/

$config["modules"]["custom"]["database"] = "galette";
$config["modules"]["custom"]["table"] = "galette_adherents";
$config["modules"]["custom"]["fields"] = array();
$config["modules"]["custom"]["fields"]["id_adh"]        = "id_adh";
$config["modules"]["custom"]["fields"]["pseudo_adh"]    = "pseudo_adh";
$config["modules"]["custom"]["fields"]["login_adh"]     = "login_adh";
$config["modules"]["custom"]["fields"]["nom_adh"]       = "nom_adh";
$config["modules"]["custom"]["fields"]["prenom_adh"]    = "prenom_adh";
$config["modules"]["custom"]["fields"]["email_adh"]     = "email_adh";

$config["parameters"] = array();
$config["parameters"]["show_support"] = false;

?>

<?php
if(!isset($config)) {
	$config = array();
}

$config["modules"]["authenticator"] = "Custom";
$config["modules"]["usersource"] = "Custom";
$config["modules"]["groupsources"] = array("CustomGroups");

/* OR
$config["modules"]["authenticator"] = "Galette";
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

?>
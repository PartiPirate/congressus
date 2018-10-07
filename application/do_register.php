<?php /*
	Copyright 2015-2018 Cédric Levieux, Parti Pirate

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
    along with Congressus.  If not, see <http://www.gnu.org/licenses/>.
*/
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
include_once("config/config.php");
include_once("config/database.php");
include_once("config/mail.php");
include_once("language/language.php");
require_once("engine/bo/UserBo.php");
require_once("engine/utils/SessionUtils.php");

$connection = openConnection();
$userBo = UserBo::newInstance($connection, $config);

$data = array();

// The email field is not empty
if (isset($_REQUEST["mail"]) && $_REQUEST["mail"]) {
	$data["ok"] = "ok";
	$data["message"] = "ok";
	echo json_encode($data);
	exit();
}

// The cgv field contains bad data
if (isset($_REQUEST["cgv"]) && $_REQUEST["cgv"] != "okgirls") {
	$data["ok"] = "ok";
	$data["message"] = "ok";
	echo json_encode($data);
	exit();
}

// The authenticator is not set to internal
if (!isset($config["modules"]["authenticator"]) && $config["modules"]["authenticator"] != "Internal") {
	$data["ok"] = "ok";
	$data["message"] = "ok";
	echo json_encode($data);
	exit();
}

$login = $_REQUEST["login"];
$email = $_REQUEST["xxx"];
$password = $_REQUEST["password"];
$confirmation = $_REQUEST["confirmation"];
$language = $_REQUEST["language"];

SessionUtils::setLanguage($language, $_SESSION);

if ($password != $confirmation) {
	$data["ko"] = "ko";
	$data["message"] = "error_passwords_not_equal";
	echo json_encode($data);
	exit();
}

$hashedPassword = UserBo::computePassword($password);
$activationKey = UserBo::hashKey($config["salt"] . time());
$url = $config["server"]["base"] . "activate.php?code=$activationKey&mail=" . urlencode($email);

$mail = getMailInstance();

$mail->setFrom($config["smtp"]["from.address"], $config["smtp"]["from.name"]);
$mail->addReplyTo($config["smtp"]["from.address"], $config["smtp"]["from.name"]);
$mail->addAddress($email);

$mailMessage = lang("register_mail_content", false);
$mailMessage = str_replace("{activationUrl}", $url, $mailMessage);
$mailMessage = str_replace("{login}", $login, $mailMessage);
$mailSubject = lang("register_mail_subject", false);

$mail->Subject = utf8_decode($mailSubject);
$mail->msgHTML(str_replace("\n", "<br>\n", utf8_decode($mailMessage)));
$mail->AltBody = utf8_decode($mailMessage);

if (!$mail->send()) {
	$data["ko"] = "ko";
	$data["message"] = "error_cant_send_mail";
	$data["mail"] = $mail->ErrorInfo;
	echo json_encode($data);
	exit();
}

// TODO
if ($userBo->register($login, $email, $hashedPassword, $activationKey, $language)) {
	$data["ok"] = "ok";
}
else {
	$data["ko"] = "ko";
	$data["message"] = "error_cant_register";
}

echo json_encode($data);
?>
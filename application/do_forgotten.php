<?php /*
	Copyright 2014 Cédric Levieux, Jérémy Collot, ArmagNet

	This file is part of OpenTweetBar.

    OpenTweetBar is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    OpenTweetBar is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with OpenTweetBar.  If not, see <http://www.gnu.org/licenses/>.
*/
session_start();
include_once("config/database.php");
include_once("config/mail.php");
include_once("language/language.php");
//require_once("engine/bo/UserBo.php");
require_once("engine/utils/SessionUtils.php");
require_once("engine/bo/GaletteBo.php");
require_once("engine/authenticators/GaletteAuthenticator.php");

$connection = openConnection();
$galetteBo = GaletteBo::newInstance($connection, $config["galette"]["db"]);
$galetteAuthenticator = GaletteAuthenticator::newInstance($connection, $config["galette"]["db"]);

$data = array();

$email = $_REQUEST["mail"];

$member = $galetteBo->getMemberByMail($email);
if (!$member) {
	$data["ko"] = "ko";
}
else {
	$chars = array();
	for($index = 0; $index < 26; $index++) {
		if ($index < 10) {
			$chars[] = $index;
		}
		$chars[] = chr(65 + $index);
		$chars[] = chr(97 + $index);
	}

	$nbChars = count($chars);

	$password = "";
	for($index = 0; $index < 32; $index++) {
		$password .= $chars[rand(0, $nbChars - 1)];
	}

	//$hashedPassword = UserBo::computePassword($password);

	$mail = getMailInstance();

	$mail->setFrom($config["smtp"]["from.address"], $config["smtp"]["from.name"]);
	$mail->addReplyTo($config["smtp"]["from.address"], $config["smtp"]["from.name"]);
	$mail->addAddress($email);

	$mailMessage = lang("forgotten_mail_content", false);
	$mailMessage = str_replace("{password}", $password, $mailMessage);
	//$mailMessage = str_replace("{login}", $login, $mailMessage);
	$mailSubject = lang("forgotten_mail_subject", false);

	$mail->Subject = utf8_decode($mailSubject);
	$mail->msgHTML(str_replace("\n", "<br>\n", $mailMessage));
	$mail->AltBody = utf8_decode($mailMessage);

	if ($mail->send()) {
		$data["ok"] = "ok";
		$galetteAuthenticator->forgotten($email, $password);
	}
	else {
		$data["ko"] = "ko";
	}
}

echo json_encode($data, JSON_NUMERIC_CHECK);
?>
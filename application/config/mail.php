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
@include_once("config/mail.config.php");
require_once("engine/utils/PHPMailerAutoload.php");

function getMailInstance() {
    global $config;

    $mail = new PHPMailer();
    $mail->isSMTP();
//    $mail->CharSet = 'UTF-8';
    $mail->CharSet = 'ISO-8859-1';

    //Enable SMTP debugging
    // 0 = off (for production use)
    // 1 = client messages
    // 2 = client and server messages
    $mail->SMTPDebug = 0;

    //Ask for HTML-friendly debug output
    $mail->Debugoutput = "html";

    $mail->Host = $config["smtp"]["host"];
    $mail->Port = $config["smtp"]["port"];

    ////Set the encryption system to use - ssl (deprecated) or tls
    $mail->SMTPSecure = "tls";

    $mail->SMTPAuth = true;
    $mail->Username = $config["smtp"]["username"];
    $mail->Password = $config["smtp"]["password"];

    return $mail;
}

?>
<?php /*
	Copyright 2018 Cédric Levieux, Parti Pirate

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

$lang["breadcrumb_register"] = "Anmelden";
$lang["breadcrumb_activation"] = "Aktivieren";

$lang["register_guide"] = "Willkommen zur Registrierungseite von Congressus";
$lang["register_form_legend"] = "Konfiguration deines Zugangs";
$lang["register_form_loginInput"] = "Login";
$lang["register_form_mailInput"] = "Mail-Adresse";
$lang["register_form_passwordInput"] = "Passwort";
$lang["register_form_passwordHelp"] = "Dein Passwort muss nicht zwangsweise komplexe Zeichen beinhalten, aber es sollte zumindest lang und gut merkbar sein";
$lang["register_form_confirmationInput"] = "Passwort bestätigen";
$lang["register_form_languageInput"] = "Sprache";
$lang["register_form_iamabot"] = "Ich bin ein Bot und weiß nicht, wie man ein Kontrollkästchen abwählt";
$lang["register_success_title"] = "Login erfolgreich";
$lang["register_success_information"] = "Registrierung erfolgreich.
<br>Du erhältst in Kürze eine Mail mit einem Link um deinen Account zu aktivieren.";
$lang["register_mail_subject"] = "[Congressus] Registrierungsmail";
$lang["register_mail_content"] = "Hallo {login},
Es sieht so aus als hättest du dich auf Congressus registriert. Um deine Registrierung zu bestätigen, klicke bitte auf folgenden Link :
<a href='{activationUrl}'>{activationUrl}</a>

Das @Congressus-Team";
$lang["register_save"] = "Login";
$lang["register_validation_user_empty"] = "Das Benutzerfeld darf nicht leer sein";
$lang["register_validation_user_already_taken"] = "Dieser Username existiert bereits";
$lang["register_validation_mail_empty"] = "Die Mailadresse darf nicht leer sein";
$lang["register_validation_mail_not_valid"] = "Dies ist keine gültige Mailadresse";
$lang["register_validation_mail_already_taken"] = "Diese Mailadresse wird bereits verwendet";
$lang["register_validation_password_empty"] = "Das Passwortfeld darf nicht leer sein";

$lang["activation_guide"] = "Willkommen zum Aktivierungsbildschirm deines Useraccounts";
$lang["activation_title"] = "Status der Aktivierung";
$lang["activation_information_success"] = "Die Aktivierung deines Useraccounts war erfolgreich. Du kannst dich nun <a href=\"connect.php?referer=index.php\" href=\"#\">einloggen</a>.";
$lang["activation_information_danger"] = "Die Aktivierung deines Useraccounts ist fehlgeschlagen";
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

$lang["breadcrumb_register"] = "Enregistrement";
$lang["breadcrumb_activation"] = "Activation";

$lang["register_guide"] = "Bienvenue sur la page d'enregistrement de Congresus";
$lang["register_form_legend"] = "Configuration de votre accès";
$lang["register_form_loginInput"] = "Identifiant";
//$lang["register_form_loginHelp"] = "Utilisez de préférence votre identifiant Twitter si vous voulez recevoir des notifications sur Twitter";
$lang["register_form_mailInput"] = "Adresse mail";
$lang["register_form_passwordInput"] = "Mot de passe";
$lang["register_form_passwordHelp"] = "Votre mot de passe ne doit pas forcement contenir de caractères bizarres, mais doit de préférence être long et mémorisable";
$lang["register_form_confirmationInput"] = "Confirmation du mot de passe";
$lang["register_form_languageInput"] = "Langage";
$lang["register_form_iamabot"] = "Je suis un robot et je ne sais pas décocher une case";
/*
$lang["register_form_notificationInput"] = "Notification pour validation";
$lang["register_form_notification_none"] = "Aucune";
$lang["register_form_notification_mail"] = "Par mail";
$lang["register_form_notification_simpledm"] = "Par simple DM";
$lang["register_form_notification_dm"] = "DM multiple";
*/
$lang["register_success_title"] = "Enregistrement réussi";
$lang["register_success_information"] = "Votre enregistrement a réussi.
<br>Vous allez recevoir un mail avec un lien à cliquer permettant l'activation de votre compte.";
$lang["register_mail_subject"] = "[Congressus] Mail d'enregistrement";
$lang["register_mail_content"] = "Bonjour {login},

Il semblerait que vous vous soyez enregistré sur Congressus. Pour confirmer votre enregistrement, veuillez cliquer sur le lien ci-dessous :
<a href='{activationUrl}'>{activationUrl}</a>

L'équipe @Congressus";
$lang["register_save"] = "S'enregistrer";
$lang["register_validation_user_empty"] = "Le champ utilisateur ne peut être vide";
$lang["register_validation_user_already_taken"] = "Cet utilisateur est déjà pris";
$lang["register_validation_mail_empty"] = "Le champ mail ne peut être vide";
$lang["register_validation_mail_not_valid"] = "Cette adresse mail n'est pas une adresse valide";
$lang["register_validation_mail_already_taken"] = "Cette adresse mail est déjà prise";
$lang["register_validation_password_empty"] = "Le champ mot de passe ne peut être vide";

$lang["activation_guide"] = "Bienvenue sur l'écran d'activation de votre compte";
$lang["activation_title"] = "Statut de votre activation";
$lang["activation_information_success"] = "L'activation de votre compte utilisateur a réussi. Vous pouvez maintenant vous <a href=\"connect.php?referer=index.php\" href=\"#\">identifier</a>.";
$lang["activation_information_danger"] = "L'activation de votre compte utilisateur a échoué.";

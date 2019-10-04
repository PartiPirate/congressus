<?php /*
    Copyright 2018-2019 Cédric Levieux, Parti Pirate

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
    along with Congressus.  If not, see <https://www.gnu.org/licenses/>.
*/

$lang["breadcrumb_register"] = "Sign in";
$lang["breadcrumb_activation"] = "Activation";

$lang["register_guide"] = "Welcome to the register page of Congressus";
$lang["register_form_legend"] = "Configuration of your access";
$lang["register_form_loginInput"] = "Login";
$lang["register_form_mailInput"] = "Mail address";
$lang["register_form_passwordInput"] = "Password";
$lang["register_form_passwordHelp"] = "Your password doesn't have to inevitably contain strange characters, but it should preferably be long and memorizable";
$lang["register_form_confirmationInput"] = "Password confirmation";
$lang["register_form_languageInput"] = "Language";
$lang["register_form_iamabot"] = "I'm a bot and i don't know how to uncheck a checkbox";
$lang["register_success_title"] = "Successful sign in";
$lang["register_success_information"] = "Your registration is done.
<br>You will soon receive a mail with a link to click letting you activate your account.";
$lang["register_mail_subject"] = "[Congressus] Registration mail";
$lang["register_mail_content"] = "Hello {login},
It seems that you registered yourself on Congressus. To confirm your registration, please click the link below :
<a href='{activationUrl}'>{activationUrl}</a>

L'équipe @Congressus";
$lang["register_save"] = "Sign in";
$lang["register_validation_user_empty"] = "The user field can't be empty";
$lang["register_validation_user_already_taken"] = "This username is already taken";
$lang["register_validation_mail_empty"] = "The mail field can't be empty";
$lang["register_validation_mail_not_valid"] = "This mail is not a valid mail";
$lang["register_validation_mail_already_taken"] = "This mail is already taken";
$lang["register_validation_password_empty"] = "The password field can't be empty";

$lang["activation_guide"] = "Welcome on the activation screen of your user account";
$lang["activation_title"] = "Activation status";
$lang["activation_information_success"] = "The activation of your user account succeeded. You can now <a href=\"connect.php?referer=index.php\" href=\"#\">login</a>.";
$lang["activation_information_danger"] = "The activation of your user account failed.";
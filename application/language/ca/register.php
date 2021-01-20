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

$lang["breadcrumb_register"] = "Crear un compte";
$lang["breadcrumb_activation"] = "Activació";

$lang["register_guide"] = "Benvingut a la pàgina de registre de Congressus";
$lang["register_form_legend"] = "Configuració del teu accés";
$lang["register_form_loginInput"] = "Iniciar sessió";
$lang["register_form_mailInput"] = "Direcció de correu electrònic";
$lang["register_form_passwordInput"] = "Contrasenya";
$lang["register_form_passwordHelp"] = "No és necessari que la contrasenya contingui caràcters estranys, però preferiblement ha de ser llarga i segura";
$lang["register_form_confirmationInput"] = "Confirmació de la contrasenya";
$lang["register_form_languageInput"] = "Idioma";
$lang["register_form_iamabot"] = "Sóc un robot i no sé com desmarcar una casella de selecció";
$lang["register_success_title"] = "Registre correcte";
$lang["register_success_information"] = "El vostre registre s'ha acabat correctament.<br />
Dintre de poc rebràs un correu amb un enllaç que hauràs de clicar per activar el teu usuari.";
$lang["register_mail_subject"] = "[Congressus] Correu de registre";
$lang["register_mail_content"] = "Hola {login},
He rebut una petició de registre a Congressus. Per confirmar el registra clica al següent enllaç:
<a href='{activationUrl}'>{activationUrl}</a>

L'equip de @Congressus";
$lang["register_save"] = "Registre";
$lang["register_validation_user_empty"] = "El camp usuari no pot estar buit";
$lang["register_validation_user_already_taken"] = "Aquest nom d'usuari ja està registrat";
$lang["register_validation_mail_empty"] = "El correu electrònic no pot estar buit";
$lang["register_validation_mail_not_valid"] = "Correu electrònic invàlid";
$lang["register_validation_mail_already_taken"] = "Aquest correu ja està registrat";
$lang["register_validation_password_empty"] = "La contrasenya no pot estar buida";

$lang["activation_guide"] = "Benvingut a la pantalla d'activació del vostre compte d'usuari";
$lang["activation_title"] = "Estat d'activació";
$lang["activation_information_success"] = "L'activació del vostre compte d'usuari ha acabat amb èxit. Ara ja pots <a href=\"connect.php?referer=index.php\" href=\"#\">iniciar sessió</a>.";
 $lang["activation_information_danger"] = "L'activació del vostre compte d'usuari ha fallat.";
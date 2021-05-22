<?php /*
    Copyright 2015-2020 Cédric Levieux, Parti Pirate

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

$lang["administration_guide"] = "Configuració de l'aplicació";

$lang["administration_server"]                      = "Informació del servidor";
$lang["administration_server_base"]                 = "URL base";
$lang["administration_server_line"]                 = "Server line";
$lang["administration_server_line_dev"]             = "Desenvolupament";
$lang["administration_server_line_beta"]            = "Beta";
$lang["administration_server_line_prod"]            = "Producció";
$lang["administration_server_timezone"]             = "Zona horària";
$lang["administration_server_timezone_none"]        = "Cap";
$lang["administration_server_timezone_europeparis"] = "Europe/Paris";
$lang["administration_server_salt"]                 = "Sal";

$lang["administration_database"]            = "Configuració de la base de dades";
$lang["administration_database_host"]       = "Servidor de la base de dades";
$lang["administration_database_port"]       = "Port de la base de dades";
$lang["administration_database_database"]   = "Base de dades";
$lang["administration_database_login"]      = "Usuari de la base de dades";
$lang["administration_database_password"]   = "Contrasenya de la base de dades";
$lang["administration_database_galette"]    = "Base de dades de Galette";
$lang["administration_database_personae"]   = "Base de dades de Personae";
$lang["administration_database_dialect"]    = "Dialecte";
$lang["administration_ping_database"]       = "Ping";
$lang["administration_create_database"]     = "Crear";
$lang["administration_test_database"]       = "Prova";
$lang["administration_deploy_database"]     = "Desplega";
$lang["administration_test_database_title"] = "Operacions necessàries a la base de dades";

$lang["administration_memcached"]       = "Configuració de Memcached";
$lang["administration_memcached_host"]  = "Servidor de Memcached";
$lang["administration_memcached_port"]  = "Por de Memcached";
$lang["administration_ping_memcached"]  = "Prova";

$lang["administration_mail"]                    = "Configuració del correu electrònic";
$lang["administration_mail_host"]               = "Servidor SMTP";
$lang["administration_mail_port"]               = "Port SMTP";
$lang["administration_mail_secure"]             = "Seguretat del servidor";
$lang["administration_mail_secure_none"]        = "Cap";
$lang["administration_mail_secure_none_alert"]  = "Text plà, els correus poden ser interceptats";
$lang["administration_mail_secure_ssl_alert"]   = "SSL, els correus estan encriptats, però poden ser interceptats";
$lang["administration_mail_secure_tls_alert"]   = "TLS, els correus estan encriptats";
$lang["administration_mail_username"]           = "Usuari SMTP";
$lang["administration_mail_password"]           = "Contrasenya SMTP";
$lang["administration_mail_from_address"]       = "Adreça del remitent";
$lang["administration_mail_from_name"]          = "Nom del remitent";

$lang["administration_account"]             = "Credencials d'administració";
$lang["administration_account_login"]       = "Usuari d'administració";
$lang["administration_account_password"]    = "Contrasenya d'administració";

$lang["administration_mail_test_adress"]    = "Envia un correu electrònic de prova a la següent adreça electrònica:";
$lang["administration_mail_test"]           = "Enviar";

$lang["administration_alert_ok"]                        = "Configuració actualitzada correctament";
$lang["administration_alert_memcached_ok"]              = "La configuració de Memcached és correcte";
$lang["administration_alert_memcached_no_host"]         = "Servidor Memcached no trobat";
$lang["administration_alert_ping_ok"]                   = "La configuració de la base de dades és correcte";
$lang["administration_alert_ping_no_host"]              = "Servidor no trobat";
$lang["administration_alert_ping_bad_credentials"]      = "Credencials invàlides";
$lang["administration_alert_ping_no_database"]          = "Base de dades no trabada";
$lang["administration_alert_create_ok"]                 = "La configuració de la base de dades és correcte";
$lang["administration_alert_deploy_ok"]                 = "La configuració del desplegament és correcte";
$lang["administration_alert_mail_ok"]                   = "La configuració del servidor de correu és correcte";
$lang["administration_alert_mail_bad_credentials"]      = "Les credencials del servidor de correu són invàlides";
$lang["administration_alert_mail_no_host"]              = "Servidor de correu no trobat";
$lang["administration_congressus_ballot_majorities"]    = "Mètodes de votació";
$lang["administration_congressus_ballot_majority_judgment"] = "Judici majoritari";

$lang["administration_discourse"]           = "Discourse";
$lang["administration_discourse_api_key"]   = "Clau API";
$lang["administration_discourse_url"]       = "Servidor";
$lang["administration_discourse_protocol"]  = "Protocol";
$lang["administration_discourse_user"]      = "Usuari";
$lang["administration_discourse_base"]      = "URL base";
$lang["administration_discourse_allowed_categories"] = "Categories permeses";

$lang["administration_mediawiki"]               = "MediaWiki";
$lang["administration_mediawiki_url"]           = "Servidor";
$lang["administration_mediawiki_login"]         = "Usuari";
$lang["administration_mediawiki_password"]      = "Contrasenya";
$lang["administration_mediawiki_base"]          = "URL base";
$lang["administration_mediawiki_categories"]    = "Categories permeses";

$lang["administration_modules"]                         = "Mòduls d'usuari";
$lang["administration_modules_authenticator"]           = "Credencials";
$lang["administration_modules_authenticator_internal"]  = "Intern";
$lang["administration_modules_authenticator_galette"]   = "Galette";
$lang["administration_modules_authenticator_custom"]    = "Personalitzat";
$lang["administration_modules_usersource"]              = "User source";
$lang["administration_modules_usersource_internal"]     = "Intern";
$lang["administration_modules_usersource_galette"]      = "Galette";
$lang["administration_modules_usersource_custom"]       = "Personalitzat";
$lang["administration_modules_groups"]                  = "Groupe sources";
$lang["administration_modules_groups_personaegroups"]   = "Grups de Personae";
$lang["administration_modules_groups_personaethemes"]   = "Temes de Personae";
$lang["administration_modules_groups_galettegroups"]    = "Grups de Galette";
$lang["administration_modules_groups_galetteallmembers"] = "Tots els membres de Galette";
$lang["administration_modules_groups_customgroups"]     = "Grup personalitzat";

?>

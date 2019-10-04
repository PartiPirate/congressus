<?php /*
    Copyright 2015-2017 Cédric Levieux, Parti Pirate

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

$lang["administration_guide"] = "Application Settings";

$lang["administration_server"]                      = "Server information";
$lang["administration_server_base"]                 = "Base url";
$lang["administration_server_line"]                 = "Server line";
$lang["administration_server_line_dev"]             = "Development";
$lang["administration_server_line_beta"]            = "Beta";
$lang["administration_server_line_prod"]            = "Production";
$lang["administration_server_timezone"]             = "Timezone";
$lang["administration_server_timezone_none"]        = "None";
$lang["administration_server_timezone_europeparis"] = "Europe/Paris";

$lang["administration_database"]            = "Database settings";
$lang["administration_database_host"]       = "Database host";
$lang["administration_database_port"]       = "Database port";
$lang["administration_database_database"]   = "Database";
$lang["administration_database_login"]      = "Login Database";
$lang["administration_database_password"]   = "Password Database";
$lang["administration_database_galette"]    = "Galette Database";
$lang["administration_database_personae"]   = "Personae Database";

$lang["administration_memcached"]       = "Memcached settings";
$lang["administration_memcached_host"]  = "Memcached host";
$lang["administration_memcached_port"]  = "Memcached port";

$lang["administration_mail"]                    = "Mail settings";
$lang["administration_mail_host"]               = "SMTP Host";
$lang["administration_mail_port"]               = "SMTP port";
$lang["administration_mail_secure"]             = "Mail securing";
$lang["administration_mail_secure_none"]        = "None";
$lang["administration_mail_secure_none_alert"]  = "Mails are sent in plain and could be intercepted";
$lang["administration_mail_secure_ssl_alert"]   = "Mails are sent encrypted but may be intercepted";
$lang["administration_mail_secure_tls_alert"]   = "Mails are sent encrypted";
$lang["administration_mail_username"]           = "Username SMTP";
$lang["administration_mail_password"]           = "Password SMTP";
$lang["administration_mail_from_address"]       = "Address From";
$lang["administration_mail_from_name"]          = "Name From";

$lang["administration_account"]             = "Administrator's login";
$lang["administration_account_login"]       = "Login Administrator";
$lang["administration_account_password"]    = "Password Administrator";

$lang["administration_ping_database"]       = "Test";

$lang["administration_alert_ok"]                        = "The configuration has been updated";
$lang["administration_alert_ping_ok"]                   = "The database's configuration is good";
$lang["administration_alert_ping_no_host"]              = "Unknown host";
$lang["administration_alert_ping_bad_credentials"]      = "Bad credentials";
$lang["administration_alert_ping_no_database"]          = "Unknown database";
$lang["administration_congressus_ballot_majorities"]    = "Modalité de vote";

$lang["administration_discourse"]           = "Discourse";
$lang["administration_discourse_api_key"]   = "API Key";
$lang["administration_discourse_url"]       = "Host";
$lang["administration_discourse_protocol"]  = "Protocol";
$lang["administration_discourse_user"]      = "Impersonification";
$lang["administration_discourse_base"]      = "Discourse base URL";
$lang["administration_discourse_allowed_categories"] = "Allowed categoties";

$lang["administration_mediawiki"]               = "MediaWiki";
$lang["administration_mediawiki_url"]           = "Host";
$lang["administration_mediawiki_login"]         = "Login";
$lang["administration_mediawiki_password"]      = "Password";
$lang["administration_mediawiki_base"]          = "MediaWiki base URL";
$lang["administration_mediawiki_categories"]    = "Catégories connues";

$lang["administration_modules"] = "User modules";
$lang["administration_modules_authenticator"] = "Authentication";
$lang["administration_modules_authenticator_internal"] = "Internal";
$lang["administration_modules_authenticator_galette"] = "Galette";
$lang["administration_modules_authenticator_custom"] = "Custom";
$lang["administration_modules_groups"] = "Groupe sources";
$lang["administration_modules_groups_personaegroups"] = "Personae Groups";
$lang["administration_modules_groups_personaethemes"] = "Personae Themes";
$lang["administration_modules_groups_galettegroups"]  = "Galette Groups";
$lang["administration_modules_groups_galetteallmembers"] = "All Galette members";
$lang["administration_modules_groups_customgroups"] = "Custom Group";

?>

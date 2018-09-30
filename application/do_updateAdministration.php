<?php /*
	Copyright 2015 Cédric Levieux, Parti Pirate

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

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
$data = array();

if (!$_SESSION["administrator"]) {
	$data["ko"] = "not_enough_rights";
	echo json_encode($data, JSON_NUMERIC_CHECK);
	exit();
}

$data["ok"] = "ok";

// config.php

$ballot_majorities = explode(",", @$_REQUEST["congressus_ballot_majorities_input"]);
foreach($ballot_majorities as $id => $majority) {
	$ballot_majorities[$id] = trim($majority);
}

$ballot_majorities = "array(" . implode(", ", $ballot_majorities) . ")";

$configDotPhp = "<?php
if(!isset(\$config)) {
	\$config = array();
}

\$config[\"administrator\"] = array();
\$config[\"administrator\"][\"login\"] = \"" . @$_REQUEST["administrator_login_input"] . "\";
\$config[\"administrator\"][\"password\"] = \"" . @$_REQUEST["administrator_password_input"] . "\";

\$config[\"database\"] = array();
\$config[\"database\"][\"dialect\"] = \"mysql\";
\$config[\"database\"][\"host\"] = \"" . @$_REQUEST["database_host_input"] . "\";
\$config[\"database\"][\"port\"] = \"" . @$_REQUEST["database_port_input"] . "\";
\$config[\"database\"][\"login\"] = \"" . @$_REQUEST["database_login_input"] . "\";
\$config[\"database\"][\"password\"] = \"" . @$_REQUEST["database_password_input"] . "\";
\$config[\"database\"][\"database\"] = \"" . @$_REQUEST["database_database_input"] . "\";
\$config[\"database\"][\"prefix\"] = \"\";
\$config[\"galette\"][\"db\"] = \"" . @$_REQUEST["galette_db_input"] . "\";
\$config[\"personae\"][\"db\"] = \"" . @$_REQUEST["personae_db_input"] . "\";

\$config[\"memcached\"] = array();
\$config[\"memcached\"][\"host\"] = \"" . @$_REQUEST["memcached_host_input"] . "\";
\$config[\"memcached\"][\"port\"] = \"" . @$_REQUEST["memcached_port_input"] . "\";

\$config[\"server\"] = array();
\$config[\"server\"][\"base\"] = \"" . @$_REQUEST["server_base_input"] . "\";
// The server line, ex : dev, beta - Leave it empty for production
\$config[\"server\"][\"line\"] = \"" . @$_REQUEST["server_line_input"] . "\";
\$config[\"server\"][\"timezone\"] = \"" . @$_REQUEST["server_timezone_input"] . "\";
\$config[\"congressus\"][\"ballot_majorities\"] = " . $ballot_majorities . ";
\$config[\"congressus\"][\"ballot_majority_judgment\"] = array(1, 2, 3, 4, 5, 6);

?>";

// mail.config.php
$mailConfigDotPhp = "<?php
if(!isset(\$config)) {
	\$config = array();
}

\$config[\"smtp\"] = array();
\$config[\"smtp\"][\"host\"] = \"" . @$_REQUEST["smtp_host_input"] . "\";
\$config[\"smtp\"][\"port\"] = \"" . @$_REQUEST["smtp_port_input"] . "\";
\$config[\"smtp\"][\"username\"] = \"" . @$_REQUEST["smtp_username_input"] . "\";
\$config[\"smtp\"][\"password\"] = \"" . @$_REQUEST["smtp_password_input"] . "\";
\$config[\"smtp\"][\"secure\"] = \"" . @$_REQUEST["smtp_secure_input"] . "\";
\$config[\"smtp\"][\"from.address\"] = \"" . @$_REQUEST["smtp_from_address_input"] . "\";
\$config[\"smtp\"][\"from.name\"] = \"" . @$_REQUEST["smtp_from_name_input"] . "\";

?>";

// discourse.config.php
$discourseConfigDotPhp = "<?php
if(!isset(\$config)) {
	\$config = array();
}

\$config[\"discourse\"] = array();
\$config[\"discourse\"][\"exportable\"] = " . (@$_REQUEST["discourse_exportable_input"] ? $_REQUEST["discourse_exportable_input"] : "false") . ";
\$config[\"discourse\"][\"api_key\"] = \"" . @$_REQUEST["discourse_api_key_input"] . "\";
\$config[\"discourse\"][\"url\"] = \"" . @$_REQUEST["discourse_url_input"] . "\";
\$config[\"discourse\"][\"protocol\"] = \"" . @$_REQUEST["discourse_protocol_input"] . "\";
\$config[\"discourse\"][\"user\"] = \"" . @$_REQUEST["discourse_user_input"] . "\";
\$config[\"discourse\"][\"base\"] = \"" . @$_REQUEST["discourse_base_input"] . "\";
\$config[\"discourse\"][\"allowed_categories\"] = array(";
$separator = "";

if (!@$_REQUEST["allowed_categories_input"]) {
	$_REQUEST["allowed_categories_input"] = array();
}

foreach (@$_REQUEST["allowed_categories_input"] as $allowed_category) {
	$discourseConfigDotPhp .= $separator . $allowed_category;

	$separator = ",\n\t";
}

$discourseConfigDotPhp .= ");

?>";

// mediawiki.config.php
$mediawikiConfigDotPhp = "<?php
if(!isset(\$config)) {
	\$config = array();
}

\$config[\"mediawiki\"] = array();
\$config[\"mediawiki\"][\"exportable\"] = " . (@$_REQUEST["mediawiki_exportable_input"] ? $_REQUEST["mediawiki_exportable_input"] : "false") . ";
\$config[\"mediawiki\"][\"url\"] = \"" . @$_REQUEST["mediawiki_url_input"] . "\";
\$config[\"mediawiki\"][\"login\"] = \"" . @$_REQUEST["mediawiki_user_login"] . "\";
\$config[\"mediawiki\"][\"password\"] = \"" . @$_REQUEST["mediawiki_user_password"] . "\";
\$config[\"mediawiki\"][\"base\"] = \"" . @$_REQUEST["discourse_base_input"] . "\";
\$config[\"mediawiki\"][\"categories\"] = array(";
$separator = "";
foreach (explode("\n", @$_REQUEST["mediawiki_categories_input"]) as $category) {
	$category = str_replace("\"", "\\\"", trim($category));
	if (!$category) continue;

	$mediawikiConfigDotPhp .= $separator;
	$mediawikiConfigDotPhp .= "\"";
	$mediawikiConfigDotPhp .= $category;
	$mediawikiConfigDotPhp .= "\"";

	$separator = ",\n\t";
}

$mediawikiConfigDotPhp .= ");

?>";

if (file_exists("config/config.php")) {
	if (file_exists("config/config.php~")) {
		unlink("config/config.php~");
	}
	rename("config/config.php", "config/config.php~");
}
file_put_contents("config/config.php", $configDotPhp);

if (file_exists("config/mail.config.php")) {
	if (file_exists("config/mail.config.php~")) {
		unlink("config/mail.config.php~");
	}
	rename("config/mail.config.php", "config/mail.config.php~");
}
file_put_contents("config/mail.config.php", $mailConfigDotPhp);

if (file_exists("config/discourse.config.php")) {
	if (file_exists("config/discourse.config.php~")) {
		unlink("config/discourse.config.php~");
	}
	rename("config/discourse.config.php", "config/discourse.config.php~");
}
file_put_contents("config/discourse.config.php", $discourseConfigDotPhp);

if (file_exists("config/mediawiki.config.php")) {
	if (file_exists("config/mediawiki.config.php~")) {
		unlink("config/mediawiki.config.php~");
	}
	rename("config/mediawiki.config.php", "config/mediawiki.config.php~");
}
file_put_contents("config/mediawiki.config.php", $mediawikiConfigDotPhp);

if (!isset($_REQUEST["api"]) || $_REQUEST["api"]) {
	echo json_encode($data, JSON_NUMERIC_CHECK);
}
?>

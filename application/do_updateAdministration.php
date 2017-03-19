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
session_start();

$data = array();

if (!$_SESSION["administrator"]) {
	$data["ko"] = "not_enough_rights";
	echo json_encode($data, JSON_NUMERIC_CHECK);
	exit();
}

$data["ok"] = "ok";

// config.php

$ballot_majorities = explode(",", $_REQUEST["congressus_ballot_majorities_input"]);
foreach($ballot_majorities as $id => $majority) {
	$ballot_majorities[$id] = trim($majority);
}

$ballot_majorities = "array(" . implode(", ", $ballot_majorities) . ")";

$configDotPhp = "<?php
if(!isset(\$config)) {
	\$config = array();
}

\$config[\"administrator\"] = array();
\$config[\"administrator\"][\"login\"] = \"" . $_REQUEST["administrator_login_input"] . "\";
\$config[\"administrator\"][\"password\"] = \"" . $_REQUEST["administrator_password_input"] . "\";

\$config[\"database\"] = array();
\$config[\"database\"][\"host\"] = \"" . $_REQUEST["database_host_input"] . "\";
\$config[\"database\"][\"port\"] = " . $_REQUEST["database_port_input"] . ";
\$config[\"database\"][\"login\"] = \"" . $_REQUEST["database_login_input"] . "\";
\$config[\"database\"][\"password\"] = \"" . $_REQUEST["database_password_input"] . "\";
\$config[\"database\"][\"database\"] = \"" . $_REQUEST["database_database_input"] . "\";
\$config[\"database\"][\"prefix\"] = \"\";
\$config[\"galette\"][\"db\"] = \"" . $_REQUEST["galette_db_input"] . "\";
\$config[\"personae\"][\"db\"] = \"" . $_REQUEST["personae_db_input"] . "\";
		
\$config[\"memcached\"] = array();
\$config[\"memcached\"][\"host\"] = \"" . $_REQUEST["memcached_host_input"] . "\";
\$config[\"memcached\"][\"port\"] = " . $_REQUEST["memcached_port_input"] . ";

\$config[\"server\"] = array();
\$config[\"server\"][\"base\"] = \"" . $_REQUEST["server_base_input"] . "\";
// The server line, ex : dev, beta - Leave it empty for production
\$config[\"server\"][\"line\"] = \"" . $_REQUEST["server_line_input"] . "\";
\$config[\"server\"][\"timezone\"] = \"" . $_REQUEST["server_timezone_input"] . "\";
\$config[\"congressus\"][\"ballot_majorities\"] = \"" . $ballot_majorities . "\";

?>";

// mail.config.php
$mailConfigDotPhp = "<?php
if(!isset(\$config)) {
	\$config = array();
}

\$config[\"smtp\"] = array();
\$config[\"smtp\"][\"host\"] = \"" . $_REQUEST["smtp_host_input"] . "\";
\$config[\"smtp\"][\"port\"] = \"" . $_REQUEST["smtp_port_input"] . "\";
\$config[\"smtp\"][\"username\"] = \"" . $_REQUEST["smtp_username_input"] . "\";
\$config[\"smtp\"][\"password\"] = \"" . $_REQUEST["smtp_password_input"] . "\";
\$config[\"smtp\"][\"secure\"] = \"" . $_REQUEST["smtp_secure_input"] . "\";
\$config[\"smtp\"][\"from.address\"] = \"" . $_REQUEST["smtp_from_address_input"] . "\";
\$config[\"smtp\"][\"from.name\"] = \"" . $_REQUEST["smtp_from_name_input"] . "\";

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

echo json_encode($data, JSON_NUMERIC_CHECK);
?>
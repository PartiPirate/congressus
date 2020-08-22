<?php /*
    Copyright 2020 Cédric Levieux, Parti Pirate

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

if(!isset($configurators)) {
	$configurators = array();
}

$configurator = array("file" => "mail.config.php", "panels" => array());

// Panel Mail

$panel = array("label" => "administration_mail", "id" => "mail", "fields" => array(), "buttons" => array());

$field = array("label" => "administration_mail_host", "type" => "text", "id" => "smtp_host_input", "path" => "smtp/host", "position" => "");
$panel["fields"][] = $field;
$field = array("label" => "administration_mail_port", "type" => "number", "id" => "smtp_port_input", "path" => "smtp/port", "position" => "");
$panel["fields"][] = $field;

$panel["fields"][] = array("type" => "separator");

$field = array("label" => "administration_mail_secure", "type" => "select", "id" => "smtp_secure_input", "path" => "smtp/secure", "position" => "");
$field["values"][] = array("value" => "",       "label" => "administration_mail_secure_none");
$field["values"][] = array("value" => "ssl",    "label" => "SSL");
$field["values"][] = array("value" => "tls",    "label" => "TLS");
$panel["fields"][] = $field;

$field = array("type" => "content", "content" => 

<<<'EOD'
	<p class="bg-danger form-alert simply-hidden secure-message secure-value-" style="display: block;">Les mails partent en clair et sont interceptables</p>
	<p class="bg-warning form-alert simply-hidden secure-message secure-value-ssl" style="display: none;">Les mails partent chiffrés mais semblent interceptables</p>
	<p class="bg-success form-alert simply-hidden secure-message secure-value-tls" style="display: none;">Les mails partent chiffrés, c'est la bonne pratique actuelle</p>
EOD

);

$panel["fields"][] = $field;

$panel["fields"][] = array("type" => "separator");

$field = array("label" => "administration_mail_username", "type" => "text", "id" => "smtp_username_input", "path" => "smtp/username", "position" => "");
$panel["fields"][] = $field;
$field = array("label" => "administration_mail_password", "type" => "text", "id" => "smtp_password_input", "path" => "smtp/password", "position" => "");
$panel["fields"][] = $field;

$panel["fields"][] = array("type" => "separator");

$field = array("label" => "administration_mail_from_address", "type" => "text", "id" => "smtp_from_address_input", "path" => "smtp/from.address", "position" => "");
$panel["fields"][] = $field;
$field = array("label" => "administration_mail_from_name", "type" => "text", "id" => "smtp_from_name_input", "path" => "smtp/from.name", "position" => "");
$panel["fields"][] = $field;

$button = array("id" => "btn-mail-test", "class" => "", "label" => "administration_mail_test", "input" => "smtp_test_address_input", "input-label" => "administration_mail_test_adress");
$panel["buttons"][] = $button;

$configurator["panels"][] = $panel;

$configurators[] = $configurator;

?>

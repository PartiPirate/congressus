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

$configurator = array("file" => "mediawiki.config.php", "panels" => array());

$panel = array("label" => "administration_mediawiki", "id" => "mediawiki", "fields" => array(), "buttons" => array(), "toggle" => array("id" => "mediawiki_exportable_input", "path" => "mediawiki/exportable"));

$field = array("label" => "administration_mediawiki_url", "type" => "text", "id" => "mediawiki_url_input", "path" => "mediawiki/url", "position" => "");
$panel["fields"][] = $field;

$field = array("label" => "administration_mediawiki_base", "type" => "text", "id" => "mediawiki_base_input", "path" => "mediawiki/base", "position" => "");
$panel["fields"][] = $field;

$panel["fields"][] = array("type" => "separator");

$field = array("label" => "administration_mediawiki_login", "type" => "text", "id" => "mediawiki_login_input", "path" => "mediawiki/login", "position" => "");
$panel["fields"][] = $field;

$field = array("label" => "administration_mediawiki_password", "type" => "text", "id" => "mediawiki_password_input", "path" => "mediawiki/password", "position" => "");
$panel["fields"][] = $field;

$panel["fields"][] = array("type" => "separator");

$field = array("label" => "administration_mediawiki_categories", "width" => 10, "type" => "concat", "id" => "mediawiki_categories_input[]", "path" => "mediawiki/categories", "position" => "");
$panel["fields"][] = $field;

$configurator["panels"][] = $panel;

$configurators[] = $configurator;

?>
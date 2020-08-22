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

$configurator = array("file" => "modules.config.php", "panels" => array());

$panel = array("label" => "administration_modules", "id" => "modules", "fields" => array(), "buttons" => array());

$field = array("label" => "administration_modules_authenticator", "type" => "select", "id" => "modules_authenticator_input", "path" => "modules/authenticator", "position" => "", "values" => array());
$field["values"][] = array("value" => "Internal",    "label" => "administration_modules_authenticator_internal");
$field["values"][] = array("value" => "Galette",    "label" => "administration_modules_authenticator_galette");
$field["values"][] = array("value" => "Custom",    "label" => "administration_modules_authenticator_custom");
$panel["fields"][] = $field;

$field = array("label" => "administration_modules_usersource", "type" => "select", "id" => "modules_usersource_input", "path" => "modules/usersource", "position" => "", "values" => array());
$field["values"][] = array("value" => "Internal",    "label" => "administration_modules_usersource_internal");
$field["values"][] = array("value" => "Galette",    "label" => "administration_modules_usersource_galette");
$field["values"][] = array("value" => "Custom",    "label" => "administration_modules_usersource_custom");
$panel["fields"][] = $field;

$panel["fields"][] = array("type" => "separator");

$field = array("label" => "administration_modules_groups", "width" => 10, "type" => "checkboxes", "id" => "module_groups_boxes[]", "path" => "modules/groupsources", "position" => "", "values" => array());
$field["values"][] = array("value" => "PersonaeGroups",    "label" => "administration_modules_groups_personaegroups");
$field["values"][] = array("value" => "PersonaeThemes",    "label" => "administration_modules_groups_personaethemes");
$field["values"][] = array("value" => "GaletteGroups",     "label" => "administration_modules_groups_galettegroups");
$field["values"][] = array("value" => "GaletteAllMembersGroups",    "label" => "administration_modules_groups_galetteallmembers");
$field["values"][] = array("value" => "CustomGroups",      "label" => "administration_modules_groups_customgroups");
$panel["fields"][] = $field;

/*
$field = array("label" => "administration_mediawiki_login", "type" => "text", "id" => "mediawiki_login_input", "path" => "mediawiki/user", "position" => "");
$panel["fields"][] = $field;

$field = array("label" => "administration_mediawiki_password", "type" => "text", "id" => "mediawiki_password_input", "path" => "mediawiki/base", "password" => "");
$panel["fields"][] = $field;

$field = array("label" => "administration_mediawiki_base", "type" => "text", "id" => "mediawiki_base_input", "path" => "mediawiki/base", "password" => "");
$panel["fields"][] = $field;
*/

$configurator["panels"][] = $panel;

$configurators[] = $configurator;

?>
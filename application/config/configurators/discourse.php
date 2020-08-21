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

$configurator = array("file" => "discourse.config.php", "panels" => array());

$panel = array("label" => "administration_discourse", "id" => "discourse", "fields" => array(), "buttons" => array(), "toggle" => array("id" => "discourse_exportable_input", "path" => "discourse/exportable"));

$field = array("label" => "administration_discourse_api_key", "width" => 10, "type" => "text", "id" => "discourse_api_key_input", "path" => "discourse/api_key", "position" => "");
$panel["fields"][] = $field;
$panel["fields"][] = array("type" => "separator");


$field = array("label" => "administration_discourse_url", "type" => "text", "id" => "discourse_url_input", "path" => "discourse/url", "position" => "");
$panel["fields"][] = $field;

$field = array("label" => "administration_discourse_protocol", "type" => "text", "id" => "discourse_protocol_input", "path" => "discourse/protocol", "position" => "");
$panel["fields"][] = $field;

$panel["fields"][] = array("type" => "separator");

$field = array("label" => "administration_discourse_base", "type" => "text", "id" => "discourse_base_input", "path" => "discourse/base", "position" => "");
$panel["fields"][] = $field;

$field = array("label" => "administration_discourse_user", "type" => "text", "id" => "discourse_user_input", "path" => "discourse/user", "position" => "");
$panel["fields"][] = $field;

$panel["fields"][] = array("type" => "separator");

$field = array("label" => "administration_discourse_allowed_categories", "width" => 10, "type" => "checkboxes", "id" => "allowed_categories_input[]", "path" => "discourse/allowed_categories", "position" => "", "values" => array());

require_once("config/discourse.structure.php");

foreach ($categories_all as $category) {
    $value = array("value" => $category['id'], "label" => $category['name']);

//    print_r(array("value" => $category['id'], "label" => $category['name']));

	if (isset($category['subcategory'])) {
	    $value["values"] = array();

        foreach ($category['subcategory'] as $subcategoy) {
            $value["values"][] = array("value" => $subcategoy['id'], "label" => $subcategoy['name']);
        }
	}

    $field["values"][] = $value;
}

//print_r($field["values"]);

$panel["fields"][] = $field;

$configurator["panels"][] = $panel;

$configurators[] = $configurator;

?>
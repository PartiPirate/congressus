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

$configurator = array("file" => "style.config.php", "panels" => array());

$panel = array("label" => "administration_style", "id" => "style", "fields" => array(), "buttons" => array());













$field = array("label" => "administration_style_logo", "type" => "file", "upload" => "assets/images/", "id" => "logo_input", "path" => "style/logo_path", "position" => "", "width" => 10);
$panel["fields"][] = $field;

$panel["fields"][] = array("type" => "separator");

$field = array("label" => "administration_style_extra_css", "type" => "file", "upload" => "assets/css/", "id" => "css_input", "path" => "style/extra_css", "position" => "", "width" => 10);
$panel["fields"][] = $field;

$panel["fields"][] = array("type" => "separator");

$field = array("label" => "administration_style_extra_style", "type" => "text_wide", "id" => "style_input", "path" => "style/extra_style", "position" => "", "width" => 10, "minHeight" => "100px");
$panel["fields"][] = $field;

$panel["fields"][] = array("type" => "separator");

$field = array("label" => "administration_style_extra_js", "type" => "file", "upload" => "assets/js/", "id" => "js_input", "path" => "style/extra_js", "position" => "", "width" => 10);
$panel["fields"][] = $field;

$panel["fields"][] = array("type" => "separator");

$field = array("label" => "administration_style_extra_javascript", "type" => "text_wide", "id" => "javascript_input", "path" => "style/extra_javascript", "position" => "", "width" => 10, "minHeight" => "100px");
$panel["fields"][] = $field;

$panel["fields"][] = array("type" => "separator");



$configurator["panels"][] = $panel;

$configurators[] = $configurator;

?>
<?php /*
	Copyright 2018 Cédric Levieux, Parti Pirate

	This file is part of Installer.

    Installer is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    Installer is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with Installer.  If not, see <http://www.gnu.org/licenses/>.
*/

// This file contains the schema of the bdd, used for deploy or update of the database

$schema = array("version" => filemtime("install/Schema.php"), "tables" => array());

$schema["tables"]["agendas"] = array("fields" => array());
$schema["tables"]["agendas"]["fields"]["age_id"] = array("type" => "bigint", "size" => 20, "null" => false, "primary" => true, "autoincrement" => 1);
$schema["tables"]["agendas"]["fields"]["age_meeting_id"] = array("type" => "bigint", "size" => 20, "null" => true);
$schema["tables"]["agendas"]["fields"]["age_parent_id"] = array("type" => "bigint", "size" => 20, "null" => true);
$schema["tables"]["agendas"]["fields"]["age_order"] = array("type" => "int", "size" => 11, "null" => false, "default" => "1");
$schema["tables"]["agendas"]["fields"]["age_active"] = array("type" => "tinyint", "size" => 4, "null" => false, "default" => "0");
$schema["tables"]["agendas"]["fields"]["age_expected_duration"] = array("type" => "int", "size" => 11, "null" => false, "comment" => "value in minutes");
$schema["tables"]["agendas"]["fields"]["age_duration"] = array("type" => "int", "size" => 11, "null" => false, "comment" => "value in minutes");
$schema["tables"]["agendas"]["fields"]["age_label"] = array("type" => "varchar", "size" => 255, "null" => false);
$schema["tables"]["agendas"]["fields"]["age_objects"] = array("type" => "text", "null" => false);
$schema["tables"]["agendas"]["fields"]["age_description"] = array("type" => "text", "null" => false);

$schema["tables"]["agendas"]["indexes"]["age_meeting_id"] = array("age_meeting_id");
$schema["tables"]["agendas"]["indexes"]["age_parent_id"] = array("age_parent_id");
$schema["tables"]["agendas"]["indexes"]["age_order"] = array("age_order");

?>
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
    along with Congressus.  If age, see <http://www.gnu.org/licenses/>.
*/

if (!isset($api)) exit();

require_once("engine/bo/TagBo.php");

$connection = openConnection();
$memcache = openMemcacheConnection();

$tagBo     = TagBo::newInstance($connection, $config);

$data = array();

$tags = $tagBo->getTagsForCombobox();

/*
$tags = $tagBo->getByFilters(array());

foreach($tags as &$tag) {
    $tag["id"] = $tag["tag_id"];
    $tag["label"] = $tag["tag_label"];

    unset($tag["tag_id"]);
    unset($tag["tag_label"]);
    unset($tag["tag_server_id"]);
    unset($tag["tag_deleted"]);
}
*/

echo json_encode($tags, JSON_NUMERIC_CHECK);
?>

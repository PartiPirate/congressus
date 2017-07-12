<?php /*
	Copyright 2017 Nino Treyssat-Vincent, Parti Pirate

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
// TODO: Add a configuration pannel for the administrator to edit theses values.
$allowed_categories = array( // Add here the categories allowed for export.
  // "Ektek",
  // "CR - CN",
  "Sandbox"
);

require_once("engine/discourse/DiscourseAPI.php");

$discourseApi = new richp10\discourseAPI\DiscourseAPI("discourse.partipirate.org", $config["discourse"]["api_key"], "https");

$categories = $discourseApi->getSite()->apiresult->categories;

foreach ($categories as $category) {
  if (isset($category->parent_category_id)){
    $categories_all[$category->parent_category_id]['subcategory'][$category->id]['id'] = $category->id;
    $categories_all[$category->parent_category_id]['subcategory'][$category->id]['slug'] = $category->slug;
    $categories_all[$category->parent_category_id]['subcategory'][$category->id]['name'] = $category->name;
  } else {
    $categories_all[$category->id]['id'] = $category->id;
    $categories_all[$category->id]['slug'] = $category->slug;
    $categories_all[$category->id]['name'] = $category->name;
  }
}

unset($categories);
foreach ($categories_all as $categoy) {
  if (in_array($categoy['name'], $allowed_categories)){
    $categories[$categoy['id']]['id'] = $categoy['id'];
    $categories[$categoy['id']]['slug'] = $categoy['slug'];
    $categories[$categoy['id']]['name'] = $categoy['name'];
  }
  if (isset($categoy['subcategory'])) {
    foreach ($categoy['subcategory'] as $subcategoy) {
      if (in_array($subcategoy['name'], $allowed_categories)){
        $categories[$subcategoy['id']]['id'] = $subcategoy['id'];
        $categories[$subcategoy['id']]['slug'] = $subcategoy['slug'];
        $categories[$subcategoy['id']]['name'] = $categoy['name'] . " : " . $subcategoy['name'];
      }
    }
  }
}
?>

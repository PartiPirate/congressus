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
	along with Congressus.  If not, see <https://www.gnu.org/licenses/>.
*/
require_once("discourse.config.php");
require_once("engine/discourse/DiscourseAPI.php");

function discourseApi($url, $api_key, $protocol) {
    if ($url=="") {
        throw new Exception('Discourse url missing.');
    }
    if ($api_key=="") {
        throw new Exception('Discourse API key missing.');
    }
    if ($protocol=="") {
        throw new Exception('Discourse protocol missing.');
    }
    $discourseApi = new richp10\discourseAPI\DiscourseAPI($url, $api_key, $protocol);
    return $discourseApi;
}

$categories_all = array();

try {
    if (isset($config["discourse"]["exportable"]) && $config["discourse"]["exportable"]) {
        $discourseApi = discourseApi($config["discourse"]["url"], $config["discourse"]["api_key"], $config["discourse"]["protocol"]);

/*    
        print_r($discourseApi);
        print_r($discourseApi->getSite());
*/

        $categories = $discourseApi->getSite()->apiresult->categories;
    
        foreach ($categories as $category) {
//            echo $category->id . " " . $category->name . "<br>";
            
            if (isset($category->parent_category_id)){
                $categories_all[$category->parent_category_id]['subcategory'][$category->id]['id'] = $category->id;
                $categories_all[$category->parent_category_id]['subcategory'][$category->id]['slug'] = $category->slug;
                $categories_all[$category->parent_category_id]['subcategory'][$category->id]['name'] = $category->name;
            }
            else {
                $categories_all[$category->id]['id'] = $category->id;
                $categories_all[$category->id]['slug'] = $category->slug;
                $categories_all[$category->id]['name'] = $category->name;
            }
        }
    
        unset($categories);
        foreach ($categories_all as $category) {
            if (!isset($config["discourse"]["allowed_categories"]) || count($config["discourse"]["allowed_categories"]) == 0 || in_array($category['id'], $config["discourse"]["allowed_categories"])) {
                $categories[$category['id']]['id'] = $category['id'];
                $categories[$category['id']]['slug'] = $category['slug'];
                $categories[$category['id']]['name'] = $category['name'];
            }
            if (isset($category['subcategory'])) {
                foreach ($category['subcategory'] as $subcategory) {
                    if (!isset($config["discourse"]["allowed_categories"]) || count($config["discourse"]["allowed_categories"]) == 0 || in_array($subcategory['id'], $config["discourse"]["allowed_categories"])) {
                        $categories[$subcategory['id']]['id'] = $subcategory['id'];
                        $categories[$subcategory['id']]['slug'] = $subcategory['slug'];
                        $categories[$subcategory['id']]['name'] = $category['name'] . " : " . $subcategory['name'];
                    }
                }
            }
        }
        
//        print_r($categories_all);
    }
}
catch (Exception $e) {
    $error_message = ('Exception : ' .  $e->getMessage() . "\n");
    echo "<div class='alert alert-danger'>" . $error_message . "</div>";
    $categories[1]['id'] = $error_message;
    $categories[1]['slug'] = $error_message;
    $categories[1]['name'] = $error_message;
}

?>

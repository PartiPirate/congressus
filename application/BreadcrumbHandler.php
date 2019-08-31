<?php /*
	Copyright 2018-2019 Cédric Levieux, Parti Pirate

	This file is part of Personae.

    Personae is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    Personae is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with Personae.  If not, see <http://www.gnu.org/licenses/>.
*/

function getBreadcrumb() {
    $crumbs = array();
    $currentPage = basename($_SERVER["SCRIPT_FILENAME"]);
    
    $indexCrumb = array("isActive" => ($currentPage == "index.php"), "links" => array("index.php"), "labels" => array(lang("breadcrumb_index")));
    $crumbs[] = $indexCrumb;

    if ($currentPage == "index.php") {
    }
    else if ($currentPage == "about.php") {
        $crumb = array("isActive" => true, "links" => array("about.php"), "labels" => array(lang("breadcrumb_about")));
        $crumbs[] = $crumb;
    }
    else if ($currentPage == "connect.php") {
        $crumb = array("isActive" => true, "links" => array("connect.php"), "labels" => array(lang("breadcrumb_connect")));
        $crumbs[] = $crumb;
    }
    else if ($currentPage == "forgotten.php") {
        $crumb = array("isActive" => true, "links" => array("forgotten.php"), "labels" => array(lang("breadcrumb_forgotten")));
        $crumbs[] = $crumb;
    }
    else if ($currentPage == "mypreferences.php") {
        global $member;
        $crumb = array("isActive" => true, "links" => array("mypreferences.php"), "labels" => array(lang("breadcrumb_mypreferences")));
        $crumbs[] = $crumb;
    }
    else if ($currentPage == "member.php") {
        global $member;
        $crumb = array("isActive" => true, "links" => array("member.php?id=" . $member["id_adh"]), "labels" => array(GaletteBo::showPseudo($member)));
        $crumbs[] = $crumb;
    }
    else if ($currentPage == "groups.php" || $currentPage == "groups2.php") {
        global $limit, $isConnected;
        
        $crumb = array("isActive" => ($limit != "mine" || !$isConnected), "links" => array("groups.php"), "labels" => array(lang("breadcrumb_groups")));
        $crumbs[] = $crumb;

        if ($isConnected) {
            $crumb = array("isActive" => $limit == "mine", "links" => array("groups.php?limit=mine"), "labels" => array(lang("breadcrumb_my_groups")));
            $crumbs[] = $crumb;
        }
    }
    else if ($currentPage == "group.php") {
        global $showAdmin, $isAdmin, $group;

        $crumb = array("isActive" => false, "links" => array("groups.php"), "labels" => array(lang("breadcrumb_groups")));
        $crumbs[] = $crumb;

        if (false) {
            $crumb = array("isActive" => false, "links" => array("groups.php?limit=mine"), "labels" => array(lang("breadcrumb_my_groups")));
            $crumbs[] = $crumb;
        }

        $crumb = array("isActive" => (!$showAdmin || !$isAdmin), "links" => array("group.php?id=" . $group["gro_id"]), "classes" => array("group-link"), "labels" => array($group["gro_label"]));
        $crumbs[] = $crumb;
        
        if ($isAdmin) {
            $crumb = array("isActive" => $showAdmin, "links" => array("group.php?admin=&id=" . $group["gro_id"]), "classes" => array("group-admin-link"), "labels" => array(lang("breadcrumb_group_administration")));
            $crumbs[] = $crumb;
        }
    }
    else if ($currentPage == "theme.php") {
        global $showAdmin, $isAdmin, $theme, $themeGroups;

        $crumb = array("isActive" => false, "links" => array("groups.php"), "labels" => array(lang("breadcrumb_groups")));
        $crumbs[] = $crumb;

        if (false) {
            $crumb = array("isActive" => false, "links" => array("groups.php?limit=mine"), "labels" => array(lang("breadcrumb_my_groups")));
            $crumbs[] = $crumb;
        }

        if (count($themeGroups) > 1 || (count($themeGroups) == 1 && $themeGroups[0]["gro_label"])) {
            // there is parent groups (at least one)
            $crumb = array("isActive" => false, "links" => array(), "classes" => array(), "labels" => array());

            foreach($themeGroups as $themeGroup) {
                $crumb["links"][] = "group.php?id=" . $themeGroup["gro_id"];
                $crumb["labels"][] = $themeGroup["gro_label"];
                $crumb["classes"][] = "group-link";
            }

            $crumbs[] = $crumb;
        }

        $crumb = array("isActive" => (!$showAdmin || !$isAdmin), "links" => array("theme.php?id=" . $theme["the_id"]), "classes" => array("theme-link"), "labels" => array($theme["the_label"]));
        $crumbs[] = $crumb;
        
        if ($isAdmin) {
            $crumb = array("isActive" => $showAdmin, "links" => array("theme.php?admin=&id=" . $theme["the_id"]), "classes" => array("theme-admin-link"), "labels" => array(lang("breadcrumb_theme_administration")));
            $crumbs[] = $crumb;
        }
    }

    return dumpBreadcrumb($crumbs);;
}

function dumpBreadcrumb($crumbs) {
    $breadcrumb = "<ol class=\"breadcrumb\">";
    $breadSeparator = "\n";

    foreach($crumbs as $crumb) {
        $breadcrumb .= $breadSeparator;
//        $breadSeparator = "\n";
        
        $breadcrumb .= "\t<li";
        if (@$crumb["isActive"]) {
            $breadcrumb .= " class=\"active\"";
        }
        $breadcrumb .= ">";
        
        $linkSeparator = "";

        foreach($crumb["links"] as $linkIndex => $link) {
            $breadcrumb .= $linkSeparator;
            $label = $crumb["labels"][$linkIndex];
            $class = (isset($crumb["classes"][$linkIndex])) ? $crumb["classes"][$linkIndex] : "";

            if (@$crumb["isActive"]) {
                $breadcrumb .= "$label";
            }        
            else {
                $breadcrumb .= "<a href=\"$link\" class=\"$class\">";
                $breadcrumb .= "$label";
                $breadcrumb .= "</a>";
            }
            
            $linkSeparator = " ou ";
        }
        
        $breadcrumb .= "</li>";
    }

    $breadcrumb .= $breadSeparator;
    $breadcrumb .= "</ol>\n";
    
    return $breadcrumb;
}

?>
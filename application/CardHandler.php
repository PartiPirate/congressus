<?php /*
    Copyright 2018 Cédric Levieux, Parti Pirate

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

$current_url = $config["server"]["base"] . $_SERVER["REQUEST_URI"];
$page_title = lang("personae_title");
$page_description = lang("index_description");

$currentPage = basename($_SERVER["SCRIPT_FILENAME"]);

if ($currentPage == "theme.php") {
    if (!isset($theme)) {
    	require_once("engine/bo/ThemeBo.php");
//    	$themeBo = ThemeBo::newInstance($connection, $config["galette"]["db"]);
    	$themeBo = ThemeBo::newInstance($connection, $config);
    	$theme = $themeBo->getTheme($_REQUEST["id"]);
    }

	$page_title .= " - " . @$theme["the_label"];
	$page_description = "Thème";
}
else if ($currentPage == "group.php") {
    if (!isset($group)) {
    	require_once("engine/bo/GroupBo.php");
//    	$groupBo = GroupBo::newInstance($connection, $config["galette"]["db"]);
    	$groupBo = GroupBo::newInstance($connection, $config);
    	$group = $groupBo->getGroup($_GET["id"]);
    }

	$page_title .= " - " . @$group["gro_label"];
	$page_description = "Groupe de thèmes";
}

ob_start();
?>
<!-- CARD HANDLER -->
<!-- Facebook -->
<meta property="og:type" content="website" />
<meta property="og:url" content="<?php echo $current_url; ?>" />
<meta property="og:title" content="Parti Pirate <?php echo $page_title;?>" />
<meta property="og:description" content="<?php echo $page_description;?>" />
<meta property="og:image" content="<?php echo $config["server"]["base"]; ?>assets/images/logo_voile_fond.png" />
<meta property="og:locale" content="fr_FR" />
<meta property="og:locale:alternate" content="en_US" />
<meta property="fb:page_id" content="partipiratefr" />
<!-- Google +1 -->
<meta itemprop="name" content="Parti Pirate <?php echo $page_title;?>" />
<meta itemprop="description" content="<?php echo $page_description;?>" />
<meta itemprop="image" content="<?php echo $config["server"]["base"]; ?>assets/images/logo_voile_fond.png" />
<meta itemprop="author" content="farlistener" />
<!-- Twitter -->
<meta name="twitter:site" content="@partipirate" />
<meta name="twitter:creator" content="@farlistener" />
<meta name="twitter:card" content="summary" />
<meta name="twitter:url" content="<?php echo $current_url; ?>" />
<meta name="twitter:title" content="Parti Pirate <?php echo $page_title;?>" />
<meta name="twitter:description" content="<?php echo $page_description;?>" />
<meta name="twitter:image" content="<?php echo $config["server"]["base"]; ?>assets/images/logo_voile_fond.png" />
<meta name="twitter:image:alt" content="Logo de Personae" />
<?php

// PUT into $CARD the card properties

$CARD = ob_get_contents();
ob_end_clean();

?>
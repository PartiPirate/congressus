<?php /*
	Copyright 2015 Cédric Levieux, Parti Pirate

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

$limit = "all";

if (isset($_REQUEST["limit"])) {
	$limit = $_REQUEST["limit"];
}

include_once("header.php");

require_once("engine/bo/GroupBo.php");
require_once("engine/bo/GaletteBo.php");

$groupFilters = array();

$groupBo = GroupBo::newInstance($connection, $config);
$groups = $groupBo->getGroups(array("for_user_only" => (isset($_GET["limit"]) ? $sessionUserId : 0)));

if ($sessionUserId) {
	foreach($groups as $index => $group) {
		$groups[$index]["gro_is_admin"] = $groupBo->isMemberAdmin($group, $sessionUserId);
	}
}

$myVotingGroups = array();
$myEligibleGroups = array();
if ($sessionUserId) {
	$myVotingGroups = $groupBo->getMyGroups(array("userId" => $sessionUserId, "state" => "voting"));
	$myEligibleGroups = $groupBo->getMyGroups(array("userId" => $sessionUserId, "state" => "eligible"));

//	print_r($myVotingGroups);
//	print_r($myEligibleGroups);
	
}

function isInMyGroup($theme, $mygroups) {
	foreach($mygroups as $group) {
		foreach($group["gro_themes"] as $myTheme) {
			if ($myTheme["the_id"] == $theme["the_id"]) return true;
		}
	}

	return false;
}

foreach($groups as $groupId => $group) {
	
//	echo "#" . @$group["gro_label"] . "#" . "<br>";
	
	$groups[$groupId]["memberInGroup"] = false;
	foreach($groups[$groupId]["gro_themes"] as $themeId => $theme) {
		$groups[$groupId]["gro_themes"][$themeId]["memberInTheme"] = false;
		foreach($groups[$groupId]["gro_themes"][$themeId]["fixation"]["members"] as $memberId => $member) {
			if ($memberId == $sessionUserId) {
				$groups[$groupId]["memberInGroup"] = true;
				$groups[$groupId]["gro_themes"][$themeId]["memberInTheme"] = true;
			}
		}
//		unset($groups[$groupId]["gro_themes"][$themeId]["fixation"]);
	}
}

?>

<div class="theme-showcase" style="margin-left: 32px; margin-right: 32px; " role="main">
<!--
	<?php echo getBreadcrumb(); ?>
-->

	<div class="col-md-3 group-container-div invisible-scrollbar">
		<ul class="group-list group-container-list">
<?php
	$class = "";

	foreach($groups as $groupId => $group) { 
		if (!$group["memberInGroup"]) continue; ?>

			<li class="group-li-<?=$groupId?>">
				<?php //print_r($group);?>
				
				<a href="group.php?id=<?=$groupId?>" class="group-link"><?=$group["gro_label"]?></a>
				
<?php 	if ($group["gro_id"] && ($isAdmin || (isset($group["gro_is_admin"]) && $group["gro_is_admin"]))) {?>
				<button data-group-id="<?php echo $groupId; ?>" class="btn btn-default btn-xs handleGroupButton" title="G&eacute;rer" style="position: relative; top: -3px;"><i class="fa fa-cog" aria-hidden="true"></i></button>
				<button data-group-id="<?php echo $groupId; ?>" class="btn btn-default btn-xs addThemeButton" title="Ajouter un th&egrave;me" style="position: relative; top: -3px;"><i class="fa fa-plus" aria-hidden="true"></i></button>
<?php 	}?>

				<ul>
<?php
		foreach($group["gro_themes"] as $themeId => $theme) { ?>
					<li class='<?=$class?> theme-li-<?=$themeId?>'><a href="theme.php?id=<?=$themeId?>" class="theme-link"><?=$theme["the_label"]?></a>

<!--
<?php				if (isInMyGroup($theme, $myVotingGroups)) { ?>
VOT
<?php				}	?>

<?php				if (isInMyGroup($theme, $myEligibleGroups)) { ?>
ELI
<?php				}	?>
-->

					</li>
<?php
		}
?>
				</ul>			
			</li>
<?php
	}
?>

			<li class="separator"><hr></li>

<?php
	foreach($groups as $groupId => $group) { 
		if ($group["memberInGroup"]) continue; ?>

			<li class="group-li-<?=$groupId?>">
				<a href="group.php?id=<?=$groupId?>" class="group-link"><?=$group["gro_label"]?></a>

<?php 	if ($group["gro_id"] && ($isAdmin || (isset($group["gro_is_admin"]) && $group["gro_is_admin"]))) {?>
				<button data-group-id="<?php echo $groupId; ?>" class="btn btn-default btn-xs handleGroupButton" title="G&eacute;rer" style="position: relative; top: -3px;"><i class="fa fa-cog" aria-hidden="true"></i></button>
				<button data-group-id="<?php echo $groupId; ?>" class="btn btn-default btn-xs addThemeButton" title="Ajouter un th&egrave;me" style="position: relative; top: -3px;"><i class="fa fa-plus" aria-hidden="true"></i></button>
<?php 	}?>

				<ul>
<?php
		foreach($group["gro_themes"] as $themeId => $theme) { ?>
					<li class='<?=$class?> theme-li-<?=$themeId?>'><a href="theme.php?id=<?=$themeId?>" class="theme-link"><?=$theme["the_label"]?></a>

<!--
<?php				if (isInMyGroup($theme, $myVotingGroups)) { ?>
VOT
<?php				}	?>

<?php				if (isInMyGroup($theme, $myEligibleGroups)) { ?>
ELI
<?php				}	?>
-->

					</li>
<?php
		}
?>
				</ul>			
			</li>
<?php
	}
?>
		</ul>
	</div>
	<div class="col-md-9" id="frame">
		<div class="well well-sm">
			<p><?php echo lang("groups_guide"); ?></p>
		</div>

		<?php	if ($isAdmin) { ?>
		<div style="text-align: center;">
			<button class="btn btn-primary add-group-button">Ajouter un groupe <span class="glyphicon glyphicon-plus"></span></button>
		</div>
		<?php	} ?>

		<?php include("connect_button.php"); ?>
	</div>

	<div class="clearfix"></div>

</div>

<div class="lastDiv"></div>

<?php include("footer.php");?>
<script>

function checkGroupVisibility() {
	$(".group").each(function() {
		var numberOfVisibleThemes = $(this).find(".theme:visible").length;

		if (numberOfVisibleThemes == 0) {
			$(this).hide();
		}

		// TODO if there is an admin button, show the group in all case
	});
}

$(function() {
//	checkGroupVisibility();
});

</script>

</body>
</html>

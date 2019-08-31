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
include_once("header.php");

require_once("engine/bo/GaletteBo.php");
require_once("engine/bo/FixationBo.php");
require_once("engine/bo/SkillUserBo.php");

$galetteBo = GaletteBo::newInstance($connection, $config["galette"]["db"]);
$fixationBo = FixationBo::newInstance($connection, $config["galette"]["db"]);
$skillUserBo = SkillUserBo::newInstance($connection, $config);

if (isset($_REQUEST["id"])) {
	$member = $galetteBo->getMemberById(intval($_REQUEST["id"]));
}
else if (isset($_REQUEST["pseudo"])) {
	$member = $galetteBo->getMemberByMailOrPseudo($_REQUEST["pseudo"]);
}

$fixations = array();

if ($member) {
	$filters = array();
	$filters["with_fixation_members"] = true;
	$filters["fme_member_id"] = $member["id_adh"];

	$fixations = $fixationBo->getFixations($filters);
	
	$userSkills = $skillUserBo->getByFilters(array("sus_user_id" => $member["id_adh"], "with_label" => true, "with_endorsments" => true, "is_endorser" => $sessionUserId));
}

?>

<div class="container theme-showcase" role="main">
	<?php echo getBreadcrumb(); ?>

	<div class="panel panel-default">
		<div class="panel-heading">
			Identité&nbsp;
		</div>
		<div class="panel-body">

Pseudo : <?php echo GaletteBo::showPseudo($member); ?><br />

<?php if ($isConnected && false) {?>
Identité : <?php echo GaletteBo::showFullname($member); ?><br />
Mail : <?php echo $member["email_adh"]; ?><br />
<?php }?>

<?php if (count($fixations)) {?>

		</div>
	</div>

	<div class="panel panel-default">
		<div class="panel-heading">
			Pouvoir&nbsp;
		</div>
		<div class="panel-body">

<?php
foreach($fixations as $fixation) {
	if ($fixation["fix_is_current"] != 1) continue;

	if (!isset($encours)) {
		echo "<h3>En cours</h3>";
		$encours = true;
	}

	echo $fixation["the_label"];
	if ($fixation["fix_until_date"]) {
		echo " Jusqu'au ";
		echo $fixation["fix_until_date"];
	}
	echo " avec une confiance de ";
	echo $fixation["fme_power"];
	echo "<br/>";
}
?>

<?php
foreach($fixations as $fixation) {
	if ($fixation["fix_is_current"] != 0) continue;

	if (!isset($passes)) {
		echo "<h3>Passés</h3>";
		$passes = true;
	}

	echo $fixation["the_label"];
	if ($fixation["fix_until_date"]) {
		echo " Jusqu'au ";
		echo $fixation["fix_until_date"];
	}
	echo " avec une confiance de ";
	echo $fixation["fme_power"];
	echo "<br/>";
}
?>

<?php }?>

<?php if ($isConnected) {?>

		</div>
	</div>


	<div class="panel panel-default" id="panel-endorsments">
		<div class="panel-heading">
			Compétences&nbsp;
		</div>
		<div class="panel-body row-striped row-hover">

	<?php 	if (!count($userSkills)) {
				echo "Pas de compétence connue";
			}
			else {
				foreach($userSkills as $userSkill) {
	?>
	<div class="row data" 
		data-id="<?php echo $userSkill["sus_id"]?>" 
		data-skill-label="<?php echo $userSkill["ski_label"]?>" 
		data-identity="<?php echo GaletteBo::showIdentity($member); ?>">
		<div class="col-md-8"><?php echo $userSkill["ski_label"]; ?> <?php
			if ($userSkill["sus_total_endorsments"]) {
				echo "(";
				echo $userSkill["sus_total_endorsments"]; 
				echo ")";
			}
		?></div>
		<div class="col-md-2"><?php echo lang("skill_level_" . $userSkill["sus_level"]); ?></div>
		<div class="col-md-2">
			<?php if ($member["id_adh"] != $sessionUserId && $userSkill["sus_is_endorser"] == 0) {?>
			<button class="btn btn-default btn-xs btn-endorse-skill"><span class="glyphicon glyphicon-thumbs-up"></span> Approuver</button>
			<?php }
				  elseif ($userSkill["sus_is_endorser"] == 1) {?>
				  	Vous avez approuvé
			<?php }?>
		</div>
	</div>
	<?php 				
				}
			}?>

<?php }?>


		</div>
	</div>


</div>

	<div id="dialog-endorse-skill" class="modal fade" tabindex="-1" role="dialog">
		<div class="modal-dialog" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
					<h4 class="modal-title">Approuver la compétence de <span class="skill-user-identity"></span></h4>
				</div>
				<div class="modal-body">
					Approuver la compétence de <span class="skill-user-identity"></span> : <span class="skill-label"></span>
					<form>
						<input type="hidden" name="sus_id">
					</form>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-default" data-dismiss="modal">Fermer</button>
					<button type="button" class="btn btn-ok btn-success">Approuver</button>
				</div>
			</div>
		</div>
	</div>


<div class="lastDiv"></div>

<?php include("footer.php");?>

</body>
</html>
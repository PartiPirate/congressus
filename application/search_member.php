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

$isModal = isset($_GET["isModal"]);

if (!$isModal) {
	include_once("header.php");
}
else {
	include_once("config/database.php");
	
	$connection = openConnection();
}

require_once("engine/bo/SkillBo.php");

$skillBo = SkillBo::newInstance($connection, $config);

$skills = $skillBo->getByFilters(array());

?>

<div class="container theme-showcase" role="main">

<?php if (!$isModal) { ?>
	<ol class="breadcrumb">
		<li><a href="index.php"><?php echo lang("breadcrumb_index"); ?></a></li>
<!--
		TODO add members page
		<li><a href="groups.php"><?php echo lang("breadcrumb_groups"); ?></a></li>
 -->
		<li class="active">Membres</li>
	</ol>

	<div class="panel panel-default">
		<div class="panel-heading">
			Recherche&nbsp;
		</div>
		<div class="panel-body">
<?php } else { ?>
	<div style="position: relative;">
<?php } ?>
			<form class="form-horizontal search-member-form">

				<input type="hidden" id="successFunction" value="<?php echo isset($_POST["successFunction"]) ? $_POST["successFunction"] : ""; ?>" />
				<input type="hidden" id="filterThemeId" name="filterThemeId" value="<?php echo isset($_POST["filterThemeId"]) ? $_POST["filterThemeId"] : ""; ?>" />
				<input type="hidden" id="filterWith" name="filterWith" value="<?php echo isset($_POST["filterWith"]) ? $_POST["filterWith"] : "false"; ?>" />
				<input type="hidden" id="selectionType" value="<?php echo isset($_POST["selectionType"]) ? $_POST["selectionType"] : "multi"; ?>" />

				<fieldset>

					<div class="form-group">
						<label class="col-md-2 control-label" for="mem_lastname">Nom : </label>
						<div class="col-md-4">
							<input type="text" name="mem_lastname" id="mem_lastname"
								placeholder="nom" class="form-control input-md"/>
						</div>
						<label class="col-md-2 control-label" for="mem_firstname">Prenom : </label>
						<div class="col-md-4">
							<input type="text" name="mem_firstname" id="mem_firstname"
								placeholder="prénom" class="form-control input-md"/>
						</div>
					</div>

					<div class="form-group">
						<label class="col-md-2 control-label" for="mem_nickname">Pseudo : </label>
						<div class="col-md-4">
							<input type="text" name="mem_nickname" id="mem_nickname"
								placeholder="pseudo" class="form-control input-md"/>
						</div>
						<label class="col-md-2 control-label" for="mem_mail">Mail : </label>
						<div class="col-md-4">
							<input type="text" name="mem_mail" id="mem_mail"
								placeholder="mail" class="form-control input-md"/>
						</div>
					</div>

					<div class="form-group">
						<label class="col-md-2 control-label" for="mem_zipcode">Code postal : </label>
						<div class="col-md-2">
							<input type="text" name="mem_zipcode" id="mem_zipcode"
								placeholder="code postal" class="form-control input-md"/>
						</div>
						<label class="col-md-2 control-label" for="mem_city">Ville : </label>
						<div class="col-md-6">
							<input type="text" name="mem_city" id="mem_city"
								placeholder="nom de la ville" class="form-control input-md"/>
						</div>
					</div>
				</fieldset>
				<fieldset>

					<legend>Filtrer sur les compétences <button type="button" class="pull-right btn btn-xs btn-primary btn-add-skill-filter"><span class="fa fa-plus"></span></button></legend>

					<div class="form-group text-center">
						<?php 
							foreach($skills as $skill) {
						?>
							<input type="checkbox" 
								class="simply-hidden"
								name="skill_ids[]" value="<?php echo $skill["ski_id"]; ?>" id="skill_<?php echo $skill["ski_id"]; ?>">
							<button type="button"
								style="display: none; " 
								class="btn btn-default btn-skill-filter" data-for="skill_<?php echo $skill["ski_id"]; ?>"><?php echo $skill["ski_label"]; ?></button>
						<?php 								
							}
						?>
					</div>


					<div class="form-group text-center">
						<button class="btn btn-primary btn-search-member">Chercher <span class="fa fa-search"></span></button>
					</div>

				</fieldset>
			</form>

			<table class="table search-member-table" style="display: none;">
				<thead>
					<tr>
						<th>Identité</th>
						<th>Mail</th>
						<th>Code postal</th>
						<th>Ville</th>
						<th>Statut</th>
						<th>Compétences (Niveau - Approbations)</th>
<!-- 						
						<th>Actions</th>
 -->						
					</tr>
				</thead>
				<tbody>
				</tbody>

			</table>
<?php if (!$isModal) { ?>
		</div>
	</div>
</div>
<?php } else { ?>
			<div class="clearfix"></div>
	</div>
<?php } ?>

	<templates>
		<table>
			<tr data-template-id="template-tweet"
				data-row=""
				class="template">
				<td><a href="member.php?id=${id}" target="_blank">${lastname} ${firstname} ${nickname}</a></td>
				<td>${mail}</td>
				<td>${zipcode}</td>
				<td>${city}</td>
				<td class="text-center">${status}</td>
				<td>${skills}</td>
<!-- 				
				<td>${action}</td>
 -->				
			</tr>
		</table>

	</templates>

	<div class="lastDiv"></div>

<?php if (!$isModal) { ?>
<!--
<div class="lastDiv"></div>
 -->

<?php include("footer.php");?>
<?php } ?>

<?php if (!$isModal) { ?>
</body>
</html>
<?php } ?>

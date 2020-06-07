<?php /*
    Copyright 2015-2018 Cédric Levieux, Parti Pirate

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
?>

<!-- Administration part -->

<?php if (!$showAdmin || !$isAdmin) return; ?>

<form id="saveThemeForm" action="do_save_theme.php" method="post" class="form-horizontal">
	<div class="saved" style="display: none;">Sauv&eacute;</div>
	<input type="hidden" name="the_id" id="the_id" value="<?php echo $theme["the_id"]; ?>" />

<div class="panel panel-default admin">
	<div class="panel-heading">
		R&egrave;gle de fixation&nbsp;
	</div>
	<div class="panel-body">

		<fieldset>

			<?php if (isset($_REQUEST["groupId"])) {?>
			<input name="the_group_id" type="hidden" value="<?php echo intval($_REQUEST["groupId"]); ?>" />
			<?php }?>

			<div class="form-group">
				<label class="col-md-4 control-label" for="the_label">Nom du thème :</label>
				<div class="col-md-8">
					<input type="text" name="the_label" id="the_label"
						placeholder="" class="form-control input-md"
						value="<?php echo $theme["the_label"]; ?>"/>
				</div>
			</div>
			<div class="form-group">
				<label class="col-md-4 control-label" for="the_min_members">Nombre minimum de personnes :</label>
				<div class="col-md-2">
					<input type="text" name="the_min_members" id="the_min_members"
						placeholder="" class="form-control input-md"
						value="<?php echo $theme["the_min_members"]; ?>"/>
				</div>
				<label class="col-md-4 control-label method demliq" for="the_max_members">Nombre maximum de personnes : </label>
				<div class="col-md-2 method demliq">
					<input type="text" name="the_max_members" id="the_max_members"
						placeholder="" class="form-control input-md"
						value="<?php echo $theme["the_max_members"]; ?>"/>
				</div>
			</div>
			<div class="form-group">
				<label class="col-md-4 control-label method demliq" for="the_max_delegations">Nombre maximum de délégations :</label>
				<div class="col-md-2 method demliq">
					<input type="text" name="the_max_delegations" id="the_max_delegations"
						placeholder="0 pour illimité" class="form-control input-md"
						value="<?php echo $theme["the_max_delegations"]; ?>"/>
				</div>
				<label class="col-md-4 control-label method demliq" for="the_dilution">Dilution : </label>
				<div class="col-md-2 method demliq">
					<div class="input-group">
						<input type="number" name="the_dilution" id="the_dilution"
							min="10" max="100" step="5"
							placeholder="entre 10 et 100%" class="form-control input-md"
							aria-describedby="dilution-addon"
							value="<?php echo $theme["the_dilution"]; ?>" />
						<span class="input-group-addon" id="dilution-addon">%</span>
					</div>
					
					
				</div>
			</div>
			
			<div class="form-group">
				<div class="col-md-12 text-center">
					<input type="hidden" 
						name="the_type_date" id="the_type_date" 
						value="<?php echo $theme["the_type_date"]; ?>">
					<div class="btn-group btn-group-periodicity" role="group">
						<button type="button" class="btn btn-default btn-periodicity <?php echo ($theme["the_type_date"] == "date" ? "active" : ""); ?>" data-value="date">Date fixe</button>
						<button type="button" class="btn btn-default btn-periodicity <?php echo ($theme["the_type_date"] == "periodicity" ? "active" : ""); ?>" data-value="periodicity">Périodique</button>
					</div>
				</div>
			</div>

			<div class="form-group type-date date">
				<label class="col-md-4 control-label" for="the_next_fixation_date">Date de la prochaine fixation : </label>
				<div class="col-md-2">
					<input type="date" name="the_next_fixation_date" id="the_next_fixation_date"
						placeholder="" class="form-control input-md"
						value="<?php echo $theme["the_next_fixation_date"]; ?>"/>
				</div>
				<label class="col-md-4 control-label" for="the_next_fixed_until_date">Date prévue de fin de mandat pour la prochaine fixation : </label>
				<div class="col-md-2">
					<input type="date" name="the_next_fixed_until_date" id="the_next_fixed_until_date"
						placeholder="" class="form-control input-md"
						value="<?php echo $theme["the_next_fixed_until_date"]; ?>"/>
				</div>
			</div>


			<div class="form-group type-date periodicity">
				<label class="col-md-4 control-label" for="the_periodicity">Périodicité : </label>
				<div class="col-md-2">
					<select name="the_periodicity" id="the_periodicity" class="form-control input-md">
						<option value="hour" <?php echo $theme["the_periodicity"] == "hour" ? 'selected="selected"' : ''; ?>>Toutes les heures</option>
						<option value="day" <?php echo $theme["the_periodicity"] == "day" ? 'selected="selected"' : ''; ?>>Tous les jours</option>
						<option value="week" <?php echo $theme["the_periodicity"] == "week" ? 'selected="selected"' : ''; ?>>Toutes les semaines</option>
						<option value="month" <?php echo $theme["the_periodicity"] == "month" ? 'selected="selected"' : ''; ?>>Tous les mois</option>
					</select>

				</div>
			</div>

			<div class="form-group">
				<label class="col-md-4 control-label" for="the_voting_method">Méthode de fixation : </label>
				<div class="col-md-2">
					<select name="the_voting_method" id="the_voting_method" class="form-control input-md">
						<option value="demliq" <?php echo $theme["the_voting_method"] == "demliq" ? 'selected="selected"' : ''; ?>>Démocratie liquide</option>
						<option value="sort" <?php echo $theme["the_voting_method"] == "sort" ? 'selected="selected"' : ''; ?>>Tirage au sort</option>
						<option value="external_results" <?php echo $theme["the_voting_method"] == "external_results" ? 'selected="selected"' : ''; ?>>Résultats externes</option>
					</select>

				</div>
			</div>
			<div class="form-group method demliq">
				<label class="col-md-4 control-label" for="the_voting_power">Pouvoir de vote par votants : </label>
				<div class="col-md-2">
					<input type="text" name="the_voting_power" id="the_voting_power"
						placeholder="" class="form-control input-md"
						value="<?php echo $theme["the_voting_power"]; ?>"/>
				</div>
			</div>
			<div class="form-group method demliq">
				<div class="col-md-4 text-right">
					<input type="checkbox" name="the_secret_until_fixation" id="the_secret_until_fixation"
						placeholder="" class=""
						<?php echo $theme["the_secret_until_fixation"] ? " checked " : ""; ?>
						value="1"/>
				</div>
				<div class="col-md-6">
					<label class="form-control labelForCheckbox" for="the_secret_until_fixation">D&eacute;l&eacute;gation secr&egrave;te jusqu'&agrave; la fixation</label>
				</div>
			</div>
			<div class="form-group">
				<label class="col-md-4 control-label" for="the_discourse_group_labels">Groupes discourse : </label>
				<div class="col-md-8">
					<input type="text" name="the_discourse_group_labels" id="the_discourse_group_labels"
						placeholder="" class="form-control input-md"
						value="<?php echo htmlspecialchars($theme["the_discourse_group_labels"], ENT_QUOTES); ?>"/>
				</div>
			</div>
			<div class="form-group">
				<label class="col-md-4 control-label" for="the_discord_export">Export discord : </label>
				<div class="col-md-2 text-right">
					<input type="checkbox" name="the_discord_export" id="the_discord_export"
						placeholder="" class=""
						<?php echo $theme["the_discord_export"] ? " checked " : ""; ?>
						value="1"/>
				</div>
			</div>
			<div class="form-group method demliq">
				<label class="col-md-4 control-label" for="the_delegate_only">Delegation seule : </label>
				<div class="col-md-2 text-right">
					<input type="checkbox" name="the_delegate_only" id="the_delegate_only"
						placeholder="" class=""
						<?php echo $theme["the_delegate_only"] ? " checked " : ""; ?>
						value="1"/>
				</div>
			</div>
			<div class="form-group method demliq">
				<label class="col-md-4 control-label" for="the_delegation_closed">Delegations fermées : </label>
				<div class="col-md-2 text-right">
					<input type="checkbox" name="the_delegation_closed" id="the_delegation_closed"
						placeholder="" class=""
						<?php echo $theme["the_delegation_closed"] ? " checked " : ""; ?>
						value="1"/>
				</div>
			</div>
			<div class="form-group method external_results">
				<label class="col-md-4 control-label" for="the_free_fixed">Entrée libre : </label>
				<div class="col-md-2 text-right">
					<input type="checkbox" name="the_free_fixed" id="the_free_fixed"
						placeholder="" class=""
						<?php echo $theme["the_free_fixed"] ? " checked " : ""; ?>
						value="1"/>
				</div>
			</div>
		</fieldset>
	</div>
</div>

<div id="source" class="panel panel-default voting">
	<div class="panel-heading">
		Source des candidats&nbsp;
	</div>
	<div class="panel-body">
		<fieldset>
			<div class="form-group">
				<label class="col-md-3 control-label" for="the_eligible_group_type">Source primaire : </label>
				<div class="col-md-3">
					<select id="the_eligible_group_type" name="the_eligible_group_type" class="form-control input-md">
						<option value="dlp_groups" <?php echo ($theme["the_eligible_group_type"] == "dlp_groups") ? " selected " : ""; ?>>Groupe</option>
						<option value="dlp_themes" <?php echo ($theme["the_eligible_group_type"] == "dlp_themes") ? " selected " : ""; ?>>Theme</option>
						<option value="galette_groups" <?php echo ($theme["the_eligible_group_type"] == "galette_groups") ? " selected " : ""; ?>>Groupe Galette</option>
						<option value="galette_adherents" <?php echo ($theme["the_eligible_group_type"] == "galette_adherents") ? " selected " : ""; ?>>Adh&eacute;rents Galette</option>
					</select>
				</div>
				<label class="col-md-3 control-label" for="the_eligible_group_id">Source secondaire : </label>
				<div class="col-md-3">
					<select id="the_eligible_group_id" name="the_eligible_group_id" class="form-control input-md">
						<option class="dlp_groups" value="0" >Veuillez choisir un groupe</option>
				<?php 	foreach($groups as $listGroup) {?>
						<option class="dlp_groups"
							value="<?php echo $listGroup["gro_id"]; ?>"
						<?php  if ($theme["the_eligible_group_type"] == "dlp_groups" && $theme["the_eligible_group_id"] == $listGroup["gro_id"]) {?>
							selected="selected"
						<?php 	}?>

						><?php echo $listGroup["gro_label"]; ?></option>
				<?php 	}?>

						<option class="dlp_themes" value="0" >Veuillez choisir un theme</option>
				<?php 	foreach($themes as $listTheme) {?>
						<option class="dlp_themes"
							value="<?php echo $listTheme["the_id"]; ?>"

						<?php  if ($theme["the_eligible_group_type"] == "dlp_themes" && $theme["the_eligible_group_id"] == $listTheme["the_id"]) {?>
							selected="selected"
						<?php 	}?>

						><?php echo $listTheme["the_label"]; ?></option>
				<?php 	}?>

						<option class="galette_groups" value="0" >Veuillez choisir un groupe</option>
				<?php 	foreach($galetteGroups as $listGroup) {?>
						<option class="galette_groups"
							value="<?php echo $listGroup["id_group"]; ?>"

						<?php  if ($theme["the_eligible_group_type"] == "galette_groups" && $theme["the_eligible_group_id"] == $listGroup["id_group"]) {?>
							selected="selected"
						<?php 	}?>

						><?php echo utf8_encode($listGroup["group_name"]); ?></option>
				<?php 	}?>

						<option class="galette_adherents" value="0" >Tous les adh&eacute;rents</option>
					</select>
				</div>
			</div>
		</fieldset>
	</div>
</div>

<div id="voting" class="method demliq panel panel-default voting">
	<div class="panel-heading">
		Source des &eacute;lecteurs&nbsp;
	</div>
	<div class="panel-body">
		<fieldset>
			<div class="form-group">
				<label class="col-md-3 control-label" for="the_voting_group_type">Source primaire : </label>
				<div class="col-md-3">
					<select id="the_voting_group_type" name="the_voting_group_type" class="form-control input-md">
						<option value="dlp_groups" <?php echo ($theme["the_voting_group_type"] == "dlp_groups") ? " selected " : ""; ?>>Groupe</option>
						<option value="dlp_themes" <?php echo ($theme["the_voting_group_type"] == "dlp_themes") ? " selected " : ""; ?>>Theme</option>
						<option value="galette_groups" <?php echo ($theme["the_voting_group_type"] == "galette_groups") ? " selected " : ""; ?>>Groupe Galette</option>
						<option value="galette_adherents" <?php echo ($theme["the_voting_group_type"] == "galette_adherents") ? " selected " : ""; ?>>Adh&eacute;rents Galette</option>
					</select>
				</div>


				<label class="col-md-3 control-label" for="the_voting_group_id">Source secondaire : </label>
				<div class="col-md-3">
					<select id="the_voting_group_id" name="the_voting_group_id" class="form-control input-md">
						<option class="dlp_groups" value="0" >Veuillez choisir un groupe</option>
				<?php 	foreach($groups as $listGroup) {?>
						<option class="dlp_groups"
							value="<?php echo $listGroup["gro_id"]; ?>"

						<?php  if ($theme["the_voting_group_type"] == "dlp_groups" && $theme["the_voting_group_id"] == $listGroup["gro_id"]) {?>
							selected="selected"
						<?php 	}?>

						><?php echo $listGroup["gro_label"]; ?></option>
				<?php 	}?>

						<option class="dlp_themes" value="0" >Veuillez choisir un theme</option>
				<?php 	foreach($themes as $listTheme) {?>
						<option class="dlp_themes"
							value="<?php echo $listTheme["the_id"]; ?>"

						<?php  if ($theme["the_voting_group_type"] == "dlp_themes" && $theme["the_voting_group_id"] == $listTheme["the_id"]) {?>
							selected="selected"
						<?php 	}?>

						><?php echo $listTheme["the_label"]; ?></option>
				<?php 	}?>

						<option class="galette_groups" value="0" >Veuillez choisir un groupe</option>
				<?php 	foreach($galetteGroups as $listGroup) {?>
						<option class="galette_groups"
							value="<?php echo $listGroup["id_group"]; ?>"

						<?php  if ($theme["the_voting_group_type"] == "galette_groups" && $theme["the_voting_group_id"] == $listGroup["id_group"]) {?>
							selected="selected"
						<?php 	}?>

						><?php echo utf8_encode($listGroup["group_name"]); ?></option>
				<?php 	}?>

						<option class="galette_adherents" value="0" >Tous les adh&eacute;rents</option>
					</select>
				</div>
			</div>
		</fieldset>
	</div>
</div>

<div id="tasks" <?php if (!$theme["the_id"]) {?>style="display: none;"<?php } ?>>
	<div class="panel panel-success">
		<div class="panel-heading">
			Gestion des tâches&nbsp;
		</div>
		<div class="panel-body">

				<fieldset>

					<div class="form-group">

						<label class="col-md-3 control-label" for="the_tasker_type">Gestionnaire de tâches : </label>
						<div class="col-md-3">
							<select name="the_tasker_type" id="the_tasker_type" class="form-control input-md">
								<option value="none"></option>
<?php
	$directoryHandler = dir("task_hooks/");

	while(($fileEntry = $directoryHandler->read()) !== false) {
		if($fileEntry != '.' && $fileEntry != '..' && strpos($fileEntry, ".php")) {
			require_once("task_hooks/" . $fileEntry);
		}
	}
	$directoryHandler->close();
	
	$types = array("none");
	
	foreach($taskHooks as $taskHook) {
		$taskHookType = $taskHook->getType();
		$types[] = $taskHookType;
?>
								<option value="<?=$taskHookType?>" <?=(($taskHook->getType() == $theme["the_tasker_type"]) ? "selected='selected'" : "")?>><?=$taskHook->getType()?></option>
<?php
	}
?>
							</select>
						</div>

						<label class="col-md-3 control-label" for="the_tasker_project_id">Projet : </label>
						<div class="col-md-3">
							<select name="the_tasker_project_id" id="the_tasker_project_id" class="form-control input-md">
								<option class="<?=implode(" ", $types)?>" value=""></option>

<?php
	foreach($taskHooks as $taskHook) {
		$projects = $taskHook->getProjects();

		foreach($projects as $project) {
?>
								<option class="<?=$taskHookType?>" value="<?=$project["id"]?>" <?=(($project["id"] == $theme["the_tasker_project_id"]) ? "selected='selected'" : "")?>><?=$project["name"]?></option>
<?php
		}
	}
?>
							</select>
						</div>

					</div>

				</fieldset>
		</div>
	</div>
</div>

</form>

<div class="method external_results">
	<div class="panel panel-default fixation">
		<div class="panel-heading">
			<?php echo lang("theme_admin_fixation_label"); ?>&nbsp;
		</div>
		<div class="panel-body">

			<form id="newFixationForm" action="do_set_new_fixation_theme.php" method="post">
				<input type="hidden" name="the_id" value="<?php echo $theme["the_id"]; ?>" />
				<button id="newFixationButton" class="btn btn btn-xs btn-success"><?php echo lang("theme_admin_fixation_new_button"); ?> <span class="glyphicon glyphicon-refresh"></span></button>

				<?php
						if ($fixation) {
							$date = new DateTime($fixation["fix_until_date"]);
							$date = $date->format("Y-m-d");
				?>
				<input type="hidden" name="fix_id" value="<?php echo $theme["the_current_fixation_id"]; ?>" />
				<input style="height: 20px; width: 130px;" class="input-date" type="date" id="fix_until_date" name="fix_until_date" value="<?php echo $date; ?>" />
				<button id="endDateButton" class="btn btn-xs btn-success"><?php echo lang("theme_admin_fixation_end_date_button"); ?> <i class="fa fa-calendar-check-o" aria-hidden="true"></i></button>
				<?php
						}
				?>
			</form>

			<div id="fixedMembers">
				<h3><?php echo lang("theme_admin_fixation_add_label"); ?></h3>

				<form id="addElectedForm" action="do_set_fixation_member.php" method="post" class="form-horizontal">
					<input type="hidden" name="action" value="add_member" />
					<input type="hidden" name="fme_fixation_id" value="<?php echo $theme["the_current_fixation_id"]; ?>" />
					<div class="form-group">
						<label class="col-md-4 control-label" for="tad_member_mail"><?php echo lang("theme_admin_fixation_new_button"); ?></label>
						<div class="col-md-4">
							<div class="input-group">
								<input type="text" id="fme_member_mail" name="fme_member_mail" placeholder="<?php echo lang("theme_admin_fixation_add_identity"); ?>"
								class="form-control"
								/><span class="input-group-btn"><button
									data-success-function="showFixedMemberFromSearchForm"
									data-success-label="Selectionner"
									data-selection-type="single"
									data-filter-with="true"
									data-filter-theme-id="<?php echo $theme["the_id"]; ?>"
									class="btn btn-default search-user"><span class="fa fa-search"></span></button></span>
							</div>
						</div>
						<div class="col-md-2">
							<input type="text" class="form-control"
								name="fme_power" placeholder="<?php echo lang("theme_admin_fixation_add_power"); ?>" style="text-align: right;"/>
						</div>
						<div class="col-md-2">
							<button id="addElectedButton" class=" btn btn-primary"><?php echo lang("theme_admin_fixation_add_button"); ?> <span class="glyphicon glyphicon-plus"></span></button>
						</div>
					</div>
				</form>

				<table class="no-pagination">
					<tbody>
						<?php 	if ($fixation) {
									foreach($fixation["members"] as $memberId => $member) {
										if (!$memberId) continue;
						?>
						<tr>
							<td><?php echo GaletteBo::showIdentity($member); ?></td>
							<td class="text-right"><?php echo $member["fme_power"]?></td>
							<td>&nbsp;<a href="#" class="removeElectedLink text-danger" data-fixation-id="<?php echo $theme["the_current_fixation_id"]; ?>" data-member-id="<?php echo $memberId; ?>"><span class="glyphicon glyphicon-remove" title="<?php echo lang("theme_admin_fixation_remove_user_button"); ?>"></span></td>
						</tr>
						<?php 		}
								}?>
					</tbody>
				</table>
			</div>
		</div>
	</div>
</div>

<div id="admins">
	<div class="panel panel-primary theme <?php echo $class ?>">
		<div class="panel-heading">
			Administrateurs du thème&nbsp;
		</div>
		<div class="panel-body">

			<form id="addAdminForm" action="do_set_theme_admin.php" method="post" class="form-horizontal">
				<fieldset>
					<input type="hidden" name="action" value="add_admin" />
					<input type="hidden" name="tad_theme_id" value="<?php echo $theme["the_id"]; ?>" />

					<div class="form-group">
						<label class="col-md-4 control-label" for="tad_member_mail">Utilisateur à ajouter en tant qu'administrateur : </label>
						<div class="col-md-6">
							<div class="input-group">
								<input type="text" name="tad_member_mail" placeholder="email ou pseudo"
									class="form-control"
								/><span class="input-group-btn"><button
									data-success-function="addThemeAdminFromSearchForm"
									data-success-label="Ajouter"
									class="btn btn-default search-user"><span class="fa fa-search"></span></button></span>
							</div>
						</div>
						<div class="col-md-2">
							<button id="addAdminButton" class="btn btn-primary">Ajouter <span class="glyphicon glyphicon-plus"></span></button>
						</div>
					</div>
				</fieldset>
			</form>

			<table>
				<tbody>
				</tbody>
			</table>
		</div>
	</div>
</div>

<div style="text-align: center;">
	<button class="btn btn-danger btn-delete-theme"><?=lang("theme_admin_remove_button")?> <i class="fa fa-remove"></i></button>

	<style>
		@media (min-width: 1024px) {
			#delete-theme-modal .modal-dialog {
				width: 900px;
			}
		}
		
		@media (min-width: 1600px) {
			#delete-theme-modal .modal-dialog {
				width: 1300px;
			}
		}
	</style>

	<div class="modal fade" tabindex="-1" role="dialog" id="delete-theme-modal">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="<?=lang("common_close")?>"><span aria-hidden="true">&times;</span></button>
					<h4 class="modal-title"><?=lang("theme_admin_remove_dialog_title")?></h4>
				</div>
				<div class="modal-body">

					<form class="form-horizontal" action="do_delete_theme.php">
						<input type="hidden" name="the_id" value="<?=$theme["the_id"]?>" />
						<fieldset>
						</fieldset>
					</form>          

				</div>
				<div class="modal-footer">

					<button type="button" class="btn btn-default" data-dismiss="modal"><?=lang("common_close")?></button>
					<button type="button" class="btn btn-danger btn-confirm-delete-modal"><span class="glyphicon glyphicon-trash"></span> <?=lang("theme_admin_remove")?></button>

				</div>
			</div><!-- /.modal-content -->
		</div><!-- /.modal-dialog -->
	</div><!-- /.modal -->
	
</div>

<templates>
	<table>
		<tr data-template-id="template-theme-admin" class="template">
			<td>${tad_member_identity}</td>
			<td>&nbsp;<a href="#" class="removeAdminLink text-danger" data-theme-id="${tad_theme_id}" data-member-id="${tad_member_id}"><i class="fa fa-remove"></i></td>
		</tr>
	</table>
</templates>

<script>
var themeAdmins = [];

<?php 	foreach($admins as $admin) {?>
themeAdmins[themeAdmins.length] = {	tad_member_identity: "<?php echo GaletteBo::showIdentity($admin); ?>",
									tad_theme_id : <?php echo $admin["tad_theme_id"]; ?>,
									tad_member_id : <?php echo $admin["tad_member_id"]; ?>
								};
<?php 	}?>

</script>

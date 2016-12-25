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
    along with Congressus.  If not, see <http://www.gnu.org/licenses/>.
*/

require_once("config/database.php");
require_once("engine/bo/GaletteBo.php");
require_once("engine/bo/GroupBo.php");
require_once("engine/bo/ThemeBo.php");

$connection = openConnection();

$themeBo = ThemeBo::newInstance($connection, $config);
$groupBo = GroupBo::newInstance($connection, $config);
$galetteBo = GaletteBo::newInstance($connection, $config["galette"]["db"]);

$themes = $themeBo->getThemes();
$groups = $groupBo->getGroups();
$galetteGroups = $galetteBo->getGroups();

?>

	<form action="" method="post" class="form-horizontal">
	<?php if (isset($_REQUEST["meetingId"])) {?>
		<input type="hidden" name="not_meeting_id" value="<?php echo $_REQUEST["meetingId"]; ?>"/>
	<?php }?>
	<?php if (isset($_REQUEST["noticeId"])) {?>
		<input type="hidden" name="not_id" value="<?php echo $_REQUEST["noticeId"]; ?>"/>
	<?php }?>

		<div class="form-group">
			<label for="not_target_type" class="col-md-4 control-label">Source primaire :</label>
			<div class="col-md-8">
				<select class="form-control input-md" id="not_target_type" name="not_target_type">
					<option value="dlp_groups">Groupe</option>
					<option value="dlp_themes">Theme</option>
					<option value="galette_groups">Groupe Galette</option>
<!-- 					
					<option value="galette_adherents">Adh&eacute;rents Galette</option>
					<option value="con_external">Par mail</option>
 -->					
				</select>
			</div>
		</div>

		<div class="form-group not_mails">
			<label for="not_target_id" class="col-md-4 control-label">Source secondaire :</label>
			<div class="col-md-8">
				<select class="form-control input-md" id="not_target_id" name="not_target_id">

					<!-- Groups -->
					<option class="dlp_groups" value="0" >Veuillez choisir un groupe</option>
			<?php 	foreach($groups as $listGroup) {?>
					<option class="dlp_groups"
						value="<?php echo $listGroup["gro_id"]; ?>"
					><?php echo $listGroup["gro_label"]; ?></option>
			<?php 	}?>

					<!-- Themes -->
					<option class="dlp_themes" value="0" >Veuillez choisir un theme</option>
			<?php 	foreach($themes as $listTheme) {?>
					<option class="dlp_themes"
						value="<?php echo $listTheme["the_id"]; ?>"
					><?php echo $listTheme["the_label"]; ?></option>
			<?php 	}?>

					<!-- Galette groups -->
					<option class="galette_groups" value="0" >Veuillez choisir un groupe</option>
			<?php 	foreach($galetteGroups as $listGroup) {?>
					<option class="galette_groups"
						value="<?php echo $listGroup["id_group"]; ?>"
					><?php echo $listGroup["group_name"]; ?></option>
			<?php 	}?>


					<option class="galette_adherents" value="0" >Tous les adh&eacute;rents</option>
				</select>
			</div>
		</div>

		<div class="form-group mails">
			<label for="not_external_mails" class="col-md-4 control-label">Mails :</label>
			<div class="col-md-8">
				<input type="text" class="form-control input-md" id="not_external_mails" name="not_external_mails" />
			</div>
		</div>

		<div class="form-group">
			<div class="col-md-4 text-right">
				<input type="checkbox" name="not_voting" id="not_voting"
					placeholder="" class=""
					value="1"/>
			</div>
			<div class="col-md-6">
				<label class="form-control labelForCheckbox" for="not_voting">A le droit de vote</label>
			</div>
		</div>


	</form>
<?php /*
    Copyright 2018-2019 Cédric Levieux, Parti Pirate

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

@include_once("config/mumble.structure.php");
@include_once("config/discord.structure.php");

?>

		<div class="form-group">
			<label for="loc_type" class="col-md-4 control-label"><?php echo lang("createMeeting_place"); ?></label>
			<div class="col-md-4">
				<select class="form-control input-md" id="loc_type" name="loc_type">
					<option value=""><?php echo lang("createMeeting_place_none"); ?></option>
<?php 	if (isset($config["mumble"]["usable"]) && $config["mumble"]["usable"]) { ?>
					<option value="mumble"><?php echo lang("loc_type_mumble"); ?></option>
<?php	} ?>
					<option value="afk"><?php echo lang("loc_type_afk"); ?></option>
					<!--
					<option value="framatalk"><?php echo lang("loc_type_framatalk"); ?></option>
					-->
<?php 	if (isset($config["discord"]["usable"]) && $config["discord"]["usable"]) { ?>
					<option value="discord"><?php echo lang("loc_type_discord"); ?></option>
<?php	} ?>
					<!--
					<option value="irc">IRC</option>
					 -->
				</select>
			</div>
		</div>

<?php 	if (isset($config["mumble"]["usable"]) && $config["mumble"]["usable"]) { ?>
		<div class="form-group" id="loc_channel_form">
			<label for="loc_channel" class="col-md-4 control-label"><?php echo lang("createMeeting_mumblePlace"); ?></label>
			<div class="col-md-4">
				<select class="form-control input-md" id="loc_channel" name="loc_channel">
					<?php
					foreach ($mumble as $channel => $channelLink) {
						if (in_array($channel, $mumble_disabled)){
							$disabled = "disabled";
						} else {
							$disabled = "";
						}
						echo "<option $disabled value='$channel'>$channel</option>";
					}
					?>
				</select>
			</div>
		</div>
<?php   } ?>

<?php 	if (isset($config["discord"]["usable"]) && $config["discord"]["usable"]) { ?>
		<div class="form-group" id="loc_discord_form">
			<label for="loc_discord_text_channel" class="col-md-4 control-label"><?php echo lang("createMeeting_discordPlace"); ?></label>
			<div class="col-md-4">
				<div class="input-group">
					<span class="input-group-addon"><i class='fa fa-hashtag' aria-hidden='true'></i></span>
					<select class="form-control input-md" id="loc_discord_text_channel" name="loc_discord_text_channel">
						<?php
						foreach ($discord_text_channels as $channel => $channelLink) {
							echo "<option value='$channel'>$channel</option>";
						}
						?>
					</select>
				</div>
			</div>
			<div class="col-md-4">
				<div class="input-group">
					<span class="input-group-addon"><i class='fa fa-volume-up' aria-hidden='true'></i></span>
					<select class="form-control input-md" id="loc_discord_vocal_channel" name="loc_discord_vocal_channel">
						<?php
						foreach ($discord_vocal_channels as $channel => $channelLink) {
							echo "<option value='$channel'>$channel</option>";
						}
						?>
					</select>
				</div>
			</div>
		</div>
<?php   } ?>

		<div class="form-group" id="loc_extra_group">
			<label class="col-md-4 control-label" for="loc_extra"><?php echo lang("createMeeting_placeAddress"); ?></label>
			<div class="col-md-4">
		    	<textarea class="form-control" rows="4"
		    		id="loc_extra" name="loc_extra"></textarea>
		  	</div>
		</div>


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
include_once("header.php");
include("config/mumble.structure.php")
?>

<div class="container theme-showcase meeting" role="main">
	<ol class="breadcrumb">
		<li><a href="index.php"><?php echo lang("breadcrumb_index"); ?></a></li>
		<li class="active"><?php echo lang("breadcrumb_createMeeting"); ?></li>
	</ol>

	<form action="meeting/do_createMeeting.php" method="post" class="form-horizontal" id="create-meeting-form">

		<div class="form-group">
			<label for="mee_label" class="col-md-4 control-label"><?php echo lang("meeting_name"); ?> :</label>
			<div class="col-md-8">
				<input type="text" class="form-control input-md" id="mee_label" name="mee_label" />
			</div>
		</div>
		<div class="form-group">
			<label for="mee_date" class="col-md-4 control-label"><?php echo lang("createMeeting_datetime"); ?></label>
			<div class="col-md-2">
				<input type="date" class="form-control input-md"
					placeholder="aaaa-mm-jj" id="mee_date" name="mee_date" />
			</div>
			<div class="col-md-2">
				<input type="time" class="form-control input-md"
					placeholder="hh:mm" id="mee_time" name="mee_time" />
			</div>
		</div>
		<div class="alert alert-danger simply-hidden" id="date-time-error-alert">
			<?php echo lang("createMeeting_datetimeError"); ?>
		</div>
		<div class="form-group">
			<label for="mee_expected_duration" class="col-md-4 control-label"><?php echo lang("createMeeting_length"); ?></label>
			<div class="col-md-4">
				<select class="form-control input-md" id="mee_expected_duration" name="mee_expected_duration">
					<option value="60">1 <?php echo lang("createMeeting_length_hour"); ?></option>
					<option value="120">2 <?php echo lang("createMeeting_length_hours"); ?></option>
					<option value="180">3 <?php echo lang("createMeeting_length_hours"); ?></option>
					<option value="240">4 <?php echo lang("createMeeting_length_hours"); ?></option>
					<option value="480">8 <?php echo lang("createMeeting_length_hours"); ?></option>
					<option value="1440">1 <?php echo lang("createMeeting_length_day"); ?></option>
					<option value="2880">2 <?php echo lang("createMeeting_length_days"); ?></option>
					<option value="4320">3 <?php echo lang("createMeeting_length_days"); ?></option>
				</select>
			</div>
		</div>

		<div class="form-group">
			<label for="mee_meeting_type_id" class="col-md-4 control-label"><?php echo lang("createMeeting_type"); ?></label>
			<div class="col-md-4">
				<select class="form-control input-md" id="mee_meeting_type_id" name="mee_meeting_type_id">
					<option value="1"><?php echo lang("createMeeting_type_meeting"); ?></option>
					<option value="2"><?php echo lang("createMeeting_type_aperitif"); ?></option>
					<option value="3"><?php echo lang("createMeeting_type_generalMeeting"); ?></option>
					<option value="4"><?php echo lang("createMeeting_type_extraordinaryGeneralMeeting"); ?></option>
				</select>
			</div>
		</div>

		<div class="form-group">
			<label for="mee_class" class="col-md-4 control-label"><?php echo lang("createMeeting_visualIndication"); ?></label>
			<div class="col-md-4">
				<select class="form-control input-md" id="mee_class" name="mee_class">
					<option class="event-info" style="color: black;" value="event-info"><?php echo lang("createMeeting_visualIndication_info"); ?></option>
					<option class="event-important" style="color: white;" value="event-important"><?php echo lang("createMeeting_visualIndication_important"); ?></option>
					<option class="event-warning" style="color: white;" value="event-warning"><?php echo lang("createMeeting_visualIndication_warning"); ?></option>
					<option class="event-inverse" style="color: white;" value="event-inverse"><?php echo lang("createMeeting_visualIndication_reversed"); ?></option>
					<option class="event-success" style="color: white;" value="event-success"><?php echo lang("createMeeting_visualIndication_success"); ?></option>
					<option class="event-special" style="color: white;" value="event-special"><?php echo lang("createMeeting_visualIndication_special"); ?></option>
				</select>
			</div>
		</div>

		<div class="form-group">
			<label for="loc_type" class="col-md-4 control-label"><?php echo lang("createMeeting_place"); ?></label>
			<div class="col-md-4">
				<select class="form-control input-md" id="loc_type" name="loc_type">
					<option value="mumble"><?php echo lang("loc_type_mumble"); ?></option>
					<option value="afk"><?php echo lang("loc_type_afk"); ?></option>
					<option value="framatalk"><?php echo lang("loc_type_framatalk"); ?></option>
					<!--
					<option value="irc">IRC</option>
					 -->
				</select>
			</div>
		</div>

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

		<div class="form-group" id="loc_extra_group">
			<label class="col-md-4 control-label" for="loc_extra"><?php echo lang("createMeeting_placeAddress"); ?></label>
			<div class="col-md-4">
		    	<textarea class="form-control" rows="4"
		    		id="loc_extra" name="loc_extra"></textarea>
		  	</div>
		</div>

		<div class="row text-center">
			<button class="btn btn-primary" type="submit"><?php echo lang("common_create"); ?></button>
		</div>

	</form>


</div>

<div class="container otbHidden">
</div>

<div class="lastDiv"></div>

<script>
var mumble_count = "<?php echo count($mumble);?>";
var mumble_server = "<?php echo $mumble_server;?>";
var mumble_title = "<?php echo $mumble_title;?>";
var mumble_version = "<?php echo $mumble_version;?>";
</script>
<?php include("footer.php");?>
</body>
</html>

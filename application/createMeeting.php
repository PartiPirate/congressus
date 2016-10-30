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
?>

<div class="container theme-showcase meeting" role="main">
	<ol class="breadcrumb">
		<li><a href="index.php"><?php echo lang("breadcrumb_index"); ?></a></li>
		<li class="active"><?php echo lang("breadcrumb_createMeeting"); ?></li>
	</ol>

	<form action="meeting/do_createMeeting.php" method="post" class="form-horizontal" id="create-meeting-form">

		<div class="form-group">
			<label for="mee_label" class="col-md-4 control-label">Nom de la réunion :</label>
			<div class="col-md-8">
				<input type="text" class="form-control input-md" id="mee_label" name="mee_label" />
			</div>
		</div>
		<div class="form-group">
			<label for="mee_date" class="col-md-4 control-label">Date et heure de la réunion :</label>
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
			Veuillez entre une date au format AAAA-MM-JJ et un horaire au format HH:MM
		</div>
		<div class="form-group">
			<label for="mee_expected_duration" class="col-md-4 control-label">Durée prévue :</label>
			<div class="col-md-4">
				<select class="form-control input-md" id="mee_expected_duration" name="mee_expected_duration">
					<option value="60">1 heure</option>
					<option value="120">2 heures</option>
					<option value="180">3 heures</option>
					<option value="240">4 heures</option>
					<option value="480">8 heures</option>
					<option value="1440">1 jour</option>
					<option value="2880">2 jours</option>
					<option value="4320">3 jours</option>
				</select>
			</div>
		</div>

		<div class="form-group">
			<label for="mee_meeting_type_id" class="col-md-4 control-label">Type événement :</label>
			<div class="col-md-4">
				<select class="form-control input-md" id="mee_meeting_type_id" name="mee_meeting_type_id">
					<option value="1">Réunion</option>
					<option value="2">Apéro</option>
					<option value="3">Assemblée Générale Ordinaire</option>
					<option value="4">Assemblée Générale Extra-ordinaire</option>
				</select>
			</div>
		</div>

		<div class="form-group">
			<label for="mee_class" class="col-md-4 control-label">Indication visuelle :</label>
			<div class="col-md-4">
				<select class="form-control input-md" id="mee_class" name="mee_class">
					<option class="event-info" style="color: black;" value="event-info">Info</option>
					<option class="event-important" style="color: white;" value="event-important">Important</option>
					<option class="event-warning" style="color: white;" value="event-warning">Avertissement</option>
					<option class="event-inverse" style="color: white;" value="event-inverse">Inversé</option>
					<option class="event-success" style="color: white;" value="event-success">Succès</option>
					<option class="event-special" style="color: white;" value="event-special">Spécial</option>
				</select>
			</div>
		</div>

		<div class="form-group">
			<label for="loc_type" class="col-md-4 control-label">Type de lieu :</label>
			<div class="col-md-4">
				<select class="form-control input-md" id="loc_type" name="loc_type">
					<option value="afk">En espace réel</option>
					<option value="mumble">Sur mumble</option>
					<option value="mumble">Sur framatalk</option>
					<!--
					<option value="irc">IRC</option>
					 -->
				</select>
			</div>
		</div>

		<div class="form-group">
			<label class="col-md-4 control-label" for="loc_extra">Adresse du lieu :</label>
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
</script>
<?php include("footer.php");?>

</body>
</html>
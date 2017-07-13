<?php /*
	Copyright 2017 Nino Treyssat-Vincent, Parti Pirate

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

include_once("config/discourse.structure.php");

if (!$meeting) {
	// Ask for creation
	$meeting = array("mee_label" => lang("meeting_eventNew"));
}
else {
	$start = new DateTime($meeting["mee_datetime"]);
	$end = new DateTime($meeting["mee_datetime"]);
	$duration = new DateInterval("PT" . ($meeting["mee_expected_duration"] ? $meeting["mee_expected_duration"] : 60) . "M");
	$end = $end->add($duration);

	if ($meeting["loc_type"] == "framatalk") {
		$framachan = sha1($meeting["mee_id"] . "framatalk" . $meeting["mee_id"]);
	}
}
$userId = SessionUtils::getUserId($_SESSION);
?>
<div class="container theme-showcase meeting" role="main">
	<ol class="breadcrumb">
	  <li><a href="index.php"><?php echo lang("breadcrumb_index"); ?></a></li>
	  <li><a href="meeting.php?id=<?php echo $meeting["mee_id"]; ?>"><?php echo $meeting["mee_label"]; ?></a></li>
	  <li class="active"><?php echo lang("export_discourse"); ?></li>
	</ol>

	<?php
	if (!isset($userId)) {?>
		<div class="container">
			<div class="jumbotron alert-danger">
				<h2><?php echo lang("export_login_ask"); ?></h2>
				<p><?php echo lang("export_permission_guests"); ?></p>
				<p><a class='btn btn-danger btn-lg' href='connect.php' role='button'><?php echo lang("login_title"); ?></a></p>
			</div>
		</div>
	  <?php die("error : not_enough_right");
	} elseif (($userId !== $meeting["mee_president_member_id"]) AND ($userId !== $meeting["mee_secretary_member_id"])) {?>
		<div class="container">
			<div class="jumbotron alert-danger">
				<h2><?php echo lang("export_permission"); ?></h2>
				<p><?php echo lang("export_permission_description"); ?></p>
				<p><a class='btn btn-danger btn-lg' href='meeting.php?id=<?php echo $meeting["mee_id"]; ?>' role='button'><?php echo lang("common_back"); ?></a></p>
			</div>
		</div>
		<?php die("error : not_enough_right");
	} ?>

	<p class="col-md-12"><?php echo lang("export_description"); ?></p>

	<div class="col-xs-12 col-md-6">
	  <h2><?php echo lang("export_preview"); ?> :</h2>
	  <iframe width="100%" id="iframe_discourse" src="meeting/do_export.php?template=discourse&id=<?php echo $meeting["mee_id"]; ?>&textarea=true"><?php echo lang("export_iframes"); ?></iframe>
	</div>

	<div class="col-xs-12 col-md-6">
	  <h2><?php echo lang("export_send_discourse"); ?> :</h2>

	    <form action="meeting_api.php?method=do_discoursePost" method="post" class="form-horizontal" id="export-to-discourse">

	      <div class="form-group">
	        <label for="discourse_title" class="col-md-4 control-label"><?php echo lang("meeting_name"); ?> :</label>
	        <div class="col-md-6">
	          <input type="text" class="form-control input-md" id="discourse_title" name="discourse_title" value="[CR] <?php echo $meeting["mee_label"];?> du <?php echo @$start->format(lang("date_format"))?>"/>
	        </div>
	      </div>

	      <div class="form-group" id="loc_channel_form">
	        <label for="discourse_category" class="col-md-4 control-label"><?php echo lang("export_category"); ?> : </label>
	        <div class="col-md-6">
	          <select class="form-control input-md" id="discourse_category" name="discourse_category">
	            <?php
	            foreach ($categories as $categoy) {
	                echo "<option value='$categoy[id]'>$categoy[name]</option>";
	            }
	            ?>
	          </select>
	        </div>
	      </div>

	      <div class="row text-center">
	        <button id="discourseSubmit" type="submit" class="btn btn-primary"><?php echo lang("common_create"); ?></button>
	      </div>

	    </form>
	  </div>

	<div id="result"></div>

</div>
<div class="lastDiv"></div>

<?php include("footer.php");?>

<script>
var meeting_id = "<?php echo $meeting["mee_id"]; ?>";
</script>

<script src="assets/js/perpage/meeting_export_discourse.js"></script>

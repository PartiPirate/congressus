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

?>

<ol class="breadcrumb">
  <li><a href="index.php"><?php echo lang("breadcrumb_index"); ?></a></li>
  <li><?php echo $meeting["mee_label"]; ?></li>
  <li class="active">Export Discourse</li>
</ol>

<p class="col-md-12">Le compte rendu sera publié par Congressus dans la catégorie choisie ci-dessous.</p>

<div class="col-xs-12 col-md-6">
  <h2>Preview :</h2>
  <iframe width="100%" id="iframe_discourse" src="meeting/do_export.php?template=discourse&id=<?php echo $meeting["mee_id"]; ?>&textarea=true"><?php echo lang("export_iframes"); ?></iframe>
</div>
<div class="col-xs-12 col-md-6">
  <h2>Send to Discourse :</h2>
    <form action="meeting_api.php?method=do_discourseCr" method="post" class="form-horizontal" id="export-to-discourse">
      <div class="form-group">
        <label for="discourse_title" class="col-md-4 control-label"><?php echo lang("meeting_name"); ?> :</label>
        <div class="col-md-6">
          <input type="text" class="form-control input-md" id="discourse_title" name="discourse_title" value="[CR] <?php echo $meeting["mee_label"];?> du <?php echo @$start->format(lang("date_format"))?>"/>
        </div>
      </div>
      <div class="form-group" id="loc_channel_form">
        <label for="discourse_category" class="col-md-4 control-label">Category : </label>
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

<div id="null"></div>

<script>
$( "#discourseSubmit" ).click(function( event ) {
  event.preventDefault();

  var $form = $( this ),
    discourse_title = $('input[name="discourse_title"]').val(),
    discourse_category = $('select[name="discourse_category"]').val(),
    meetingId = <?php echo $meeting["mee_id"]; ?>,
    url = "meeting_api.php?method=do_discourseCr";
    alert(discourse_title);

  $.post( url, { discourse_title: discourse_title, discourse_category: discourse_category, meetingId: meetingId } );

});


</script>

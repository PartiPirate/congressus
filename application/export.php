<?php /*
    Copyright 2017 Cédric Levieux, Nino Treyssat-Vincent, Parti Pirate

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
session_start();

include_once("config/database.php");
@include_once("config/mediawiki.config.php");
@include_once("config/discourse.config.php");
include_once("config/database.php");
include_once("language/language.php");
require_once("engine/bo/MeetingBo.php");
require_once("engine/utils/SessionUtils.php");
require_once("config/config.php");

$connection = openConnection();
$meetingBo = MeetingBo::newInstance($connection, $config);
$meeting = $meetingBo->getById($_REQUEST["id"], true);
if (!$meeting) {
	// Ask for creation
	$meeting = array("mee_label" => lang("meeting_eventNew"));
}
else {
	$start = new DateTime($meeting["mee_datetime"]);
}
$userId = SessionUtils::getUserId($_SESSION);

//unset($_GET["id"]);

if (isset($_GET["template"]) && isset($_GET["textarea"]) && isset($_GET["id"])) {
	$template = $_GET["template"];
	$url = "meeting/do_export.php?template=" . $template . "&id=" . $_GET["id"] . "&textarea=" . $_GET["textarea"];
} 
else {
  die();
} ?>

<div class="export_container" id="export_container">
  <div class="export_content">
    <div class="navbar navbar-inverse">
      <div class="navbar-header pull-left hidden-xs">
        <a class="navbar-brand" href="#">Export <?php echo $template; ?></a>
      </div>
      <div class="navbar-header pull-left">

		  <?php 	if ($template == "html"){?>
          <a id="rendering" data-template="html" data-tab="rendering" class="btnTab hidden-xs btn btn-default navbar-btn btn-active" href="#"><?php echo lang("export_rendering"); ?></a>
          <a id="html-code" data-template="html" data-tab="html-code" class="btnTab btn btn-default navbar-btn" href="#">Code HTML</a>
	     <?php		} 
        			else if ($template == "discourse" && isset($config["discourse"]["exportable"]) && $config["discourse"]["exportable"]) {?>
          <a id="preview" data-template="discourse" data-tab="preview" class="btnTab hidden-xs btn btn-default navbar-btn btn-active" href="#"><?php echo lang("export_preview"); ?></a>
          <a id="send_discourse" data-template="discourse" data-tab="send_discourse" class="btnTab btn btn-default navbar-btn simply-hidden" href="#"><?php echo lang("export_send_discourse"); ?> <span class="glyphicon glyphicon-share"></span></a>
	      <?php 	}         			
      				else if ($template == "markdown" && isset($config["mediawiki"]["exportable"]) && $config["mediawiki"]["exportable"]) {?>
          <a id="preview" data-template="markdown" data-tab="preview" class="btnTab hidden-xs btn btn-default navbar-btn btn-active" href="#"><?php echo lang("export_preview"); ?></a>
          <a id="send_wiki" data-template="markdown" data-tab="send_wiki" class="btnTab btn btn-default navbar-btn simply-hidden" href="#"><?php echo lang("export_send_wiki"); ?> <span class="glyphicon glyphicon-share"></span></a>
 	     <?php 		} ?>

        <a id="newpage" class="btn btn-default navbar-btn" href="<?php echo $url ?>" target="_blank"><?php echo lang("export_open"); ?></a>
      </div>
      <div class="navbar-header pull-right">
        <a title="<?php echo lang("common_close"); ?>" class="btn btn-default navbar-btn exportClose" href="#"><span class="glyphicon glyphicon-remove"></span></a>
      </div>
    </div>
	<?php 	if ($template !== "pdf") { ?>
		<div id="export_area" class="simply-hidden"></div>
	<?php 	} ?>
	<?php 	if ($template == "pdf") { ?>
    	<iframe id="export_iframe" class="simply-hidden" src=""><?php echo lang("export_iframes"); ?></iframe>
	<?php 	} ?>

	<?php if ($template == "discourse") {
			include_once("config/discourse.structure.php");
			include_once("config/discourse.config.php");
	?>
	    <div id="discourse_post" class="simply-hidden">
	      <?php				if (!isset($userId)) {?>
	    		<div class="container">
	    			<div class="jumbotron alert-danger">
	    				<h2><?php echo lang("export_login_ask"); ?></h2>
	    				<p><?php echo lang("export_permission_guests"); ?></p>
	    				<p><a class='btn btn-danger btn-lg' href='connect.php' role='button'><?php echo lang("login_title"); ?></a></p>
	    			</div>
	    		</div>
	    	  <?php // die("error : not_enough_right");
	    					} 
	    					else if (($userId !== $meeting["mee_president_member_id"]) AND ($userId !== $meeting["mee_secretary_member_id"])) { ?>
	    		<div class="container">
	    			<div class="jumbotron alert-danger">
	    				<h2><?php echo lang("export_permission"); ?></h2>
	    				<p><?php echo lang("export_permission_description"); ?></p>
	    				<p><a class='btn btn-danger btn-lg' href='meeting.php?id=<?php echo $meeting["mee_id"]; ?>' role='button'><?php echo lang("common_back"); ?></a></p>
	    			</div>
	    		</div>
	    		<?php // die("error : not_enough_right");
	    					} ?>
	      <p class="col-md-12"><?php echo lang("export_description"); ?></p>
	      <form action="meeting_api.php?method=do_discoursePost" method="post" class="form-horizontal" id="export-to-discourse">

		      <div class="form-group">
		        <label for="discourse_title" class="col-md-4 control-label"><?php echo lang("export_discourse_title"); ?> :</label>
		        <div class="col-md-6">
		          <input required type="text" class="form-control input-md" id="discourse_title" name="discourse_title" value="[CR] <?php echo $meeting["mee_label"];?> du <?php echo @$start->format(lang("date_format"))?>"/>
		        </div>
		      </div>

		      <div class="form-group" id="loc_channel_form">
		        <label for="discourse_category" class="col-md-4 control-label"><?php echo lang("export_category_choose"); ?> : </label>
		        <div class="col-md-6">
		          <select required class="form-control input-md" id="discourse_category" name="discourse_category">
	              <option value=""><?php echo lang("export_category_choose"); ?></option>
					<?php
								foreach ($categories as $category) {
		            				echo "<option value='$category[id]'>$category[name]</option>";
		        			    }
					?>
		          </select>
		        </div>
		      </div>

		      <div class="row text-center">
		        <button id="discourseSubmit" type="submit" class="btn btn-primary"><?php echo lang("export_send"); ?></button>
		      </div>

		    </form>
	      <div id="result"></div>
	    </div>
	<?php }?>
	<?php if ($template == "markdown") {
			include_once("config/mediawiki.config.php");
	?>
	    <div id="wiki_post" class="simply-hidden">
	      <?php				if (!isset($userId)) {?>
	    		<div class="container">
	    			<div class="jumbotron alert-danger">
	    				<h2><?php echo lang("export_login_ask"); ?></h2>
	    				<p><?php echo lang("export_permission_guests"); ?></p>
	    				<p><a class='btn btn-danger btn-lg' href='connect.php' role='button'><?php echo lang("login_title"); ?></a></p>
	    			</div>
	    		</div>
	    	  <?php // die("error : not_enough_right");
	    					} 
	    					else if (($userId !== $meeting["mee_president_member_id"]) AND ($userId !== $meeting["mee_secretary_member_id"])) { ?>
	    		<div class="container">
	    			<div class="jumbotron alert-danger">
	    				<h2><?php echo lang("export_permission"); ?></h2>
	    				<p><?php echo lang("export_permission_description"); ?></p>
	    				<p><a class='btn btn-danger btn-lg' href='meeting.php?id=<?php echo $meeting["mee_id"]; ?>' role='button'><?php echo lang("common_back"); ?></a></p>
	    			</div>
	    		</div>
	    		<?php // die("error : not_enough_right");
	    					} ?>
	      <p class="col-md-12"><?php echo lang("export_wiki_description"); ?></p>
	      <form action="meeting_api.php?method=do_wikiPost" method="post" class="form-horizontal" id="export-to-wiki">

		      <div class="form-group">
		        <label for="wiki_title" class="col-md-4 control-label"><?php echo lang("export_wiki_title"); ?> :</label>
		        <div class="col-md-6">
		          <input required type="text" class="form-control input-md" id="wiki_title" name="wiki_title" value="<?php echo $meeting["mee_label"];?> du <?php echo @$start->format(lang("date_format"))?>"/>
		        </div>
		      </div>

		      <div class="form-group" id="loc_channel_form">
		        <label for="wiki_categories" class="col-md-4 control-label"><?php echo lang("export_categories_choose"); ?> : </label>
		        <div class="col-md-6" style="max-height: 380px; overflow: auto;">
					<?php		foreach($config["mediawiki"]["categories"] as $index => $category) {?>
							<div class="checkbox">
								<label for="checkboxes-<?php echo $index; ?>">
									<input type="checkbox" name="wiki_categories[]" id="checkboxes-<?php echo $index; ?>" value="<?php echo $category; ?>">
										<?php echo $category; ?>
								</label>
							</div>
					<?php 		}?>					
		        	
		        </div>
		      </div>

		      <div class="row text-center">
		        <button id="wikiSubmit" type="submit" class="btn btn-primary"><?php echo lang("export_send"); ?></button>
		      </div>

		    </form>
	      <div id="result"></div>
	    </div>
	<?php }?>

  </div>
</div>
<script>
var template = "<?php echo $template;?>";
var textarea = "<?php echo $_GET["textarea"];?>";
var export_discourse_shortTitle = "<?php echo lang("export_discourse_shortTitle"); ?>";
var export_category_choose = "<?php echo lang("export_category_choose"); ?>";
var export_wiki_shortTitle = "<?php echo lang("export_wiki_shortTitle"); ?>";
</script>
<script src="assets/js/perpage/export.js"></script>

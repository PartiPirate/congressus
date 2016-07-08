<?php /*
	Copyright 2014 Cédric Levieux, Jérémy Collot, ArmagNet

	This file is part of OpenTweetBar.

    OpenTweetBar is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    OpenTweetBar is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with OpenTweetBar.  If not, see <http://www.gnu.org/licenses/>.
*/
include_once("header.php");
require_once("engine/utils/SessionUtils.php");

?>
<div class="container theme-showcase" role="main">
	<ol class="breadcrumb">
		<li><a href="index.php"><?php echo lang("breadcrumb_index"); ?> </a></li>
		<li class="active"><?php echo lang("breadcrumb_forgotten"); ?></li>
	</ol>

	<div class="well well-sm">
		<p>
			<?php echo lang("forgotten_guide"); ?>
		</p>
	</div>

	<form id="formPanel" class="form-horizontal">
		<fieldset>

			<!-- Form Name -->
			<legend><?php echo lang("forgotten_form_legend"); ?></legend>

			<!-- Email input-->
			<div class="form-group">
				<label class="col-md-4 control-label" for="mailInput"><?php echo lang("forgotten_form_mailInput"); ?></label>
				<div class="col-md-6">
					<input id="mailInput" name="mailInput" value="" type="email"
						placeholder="" class="form-control input-md">
				</div>
			</div>

			<!-- Button (Double) -->
			<div class="form-group">
				<label class="col-md-4 control-label" for="forgottenButton"></label>
				<div class="col-md-8">
					<button id="forgottenButton" name="forgottenButton" class="btn btn-default"><?php echo lang("forgotten_save"); ?></button>
				</div>
			</div>
		</fieldset>
	</form>

	<div id="successPanel" class="panel panel-success otbHidden">
		<div class="panel-heading">
			<?php echo lang("forgotten_success_title"); ?>
		</div>
		<div class="panel-body">
			<?php echo lang("forgotten_success_information"); ?>
		</div>
	</div>

</div>

<?php include("footer.php");?>
</body>
</html>
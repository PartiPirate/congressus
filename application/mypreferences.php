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
		<li><a href="index.php"><?php echo lang("breadcrumb_index"); ?></a></li>
		<li class="active"><?php echo lang("breadcrumb_mypreferences"); ?></li>
	</ol>

	<div class="well well-sm">
		<p><?php echo lang("mypreferences_guide"); ?></p>
	</div>

	<?php 	if (isset($sessionUser)) {?>

	<form class="form-horizontal">
		<fieldset>

			<!-- Form Name -->
			<legend><?php echo lang("mypreferences_form_legend"); ?></legend>

			<!-- Password input-->
			<div class="form-group">
				<label class="col-md-4 control-label" for="userOldInput"><?php echo lang("mypreferences_form_oldInput"); ?></label>
				<div class="col-md-6">
					<input id="userOldInput" name="userOldInput" value="" type="password"
						placeholder="<?php echo lang("mypreferences_form_oldPlaceholder"); ?>" class="form-control input-md">
				</div>
			</div>
			<div class="form-group">
				<label class="col-md-4 control-label" for="userPasswordInput"><?php echo lang("mypreferences_form_passwordInput"); ?></label>
				<div class="col-md-6">
					<input id="userPasswordInput" name="userPasswordInput" value="" type="password"
						placeholder="<?php echo lang("mypreferences_form_passwordPlaceholder"); ?>" class="form-control input-md">
				</div>
			</div>
			<div class="form-group">
				<label class="col-md-4 control-label" for="userConfirmationInput"><?php echo lang("mypreferences_form_confirmationInput"); ?></label>
				<div class="col-md-6">
					<input id="userConfirmationInput" name="userConfirmationInput" value="" type="password"
						placeholder="<?php echo lang("mypreferences_form_confirmationPlaceholder"); ?>" class="form-control input-md">
				</div>
			</div>

			<!-- Language input-->
			<!--
			<div class="form-group">
				<label class="col-md-4 control-label" for="userLanguageInput"><?php echo lang("mypreferences_form_languageInput"); ?></label>
				<div class="col-md-8">
					<input id="userLanguageInput" name="userLanguageInput"
						value="<?php echo $language; ?>" type="hidden">
					<div id="userLanguageButtons" class="btn-group" role="group" aria-label="...">
						<button value="en" type="button" class="btn btn-default <?php if ($language == "en") { echo "active"; } ?>"><span class="flag en" title="<?php echo lang("language_en"); ?>"></span></button>
						<button value="fr" type="button" class="btn btn-default <?php if ($language == "fr") { echo "active"; } ?>"><span class="flag fr" title="<?php echo lang("language_fr"); ?>"></span></button>
					</div>
				</div>
			</div>
			 -->

			<!-- Button (Double) -->
			<div class="form-group">
				<label class="col-md-4 control-label" for="savePreferencesButton"></label>
				<div class="col-md-8">
					<button id="savePreferencesButton" name="savePreferencesButton" class="btn btn-default"><?php echo lang("mypreferences_save"); ?></button>
				</div>
			</div>
		</fieldset>
	</form>

	<?php echo addAlertDialog("error_cant_change_passwordAlert", lang("error_cant_change_password"), "danger"); ?>
	<?php echo addAlertDialog("ok_operation_successAlert", lang("ok_operation_success"), "success"); ?>

	<?php 	} ?>

</div>

<script type="text/javascript">
var userLanguage = '<?php echo SessionUtils::getLanguage($_SESSION); ?>';
var mypreferences_validation_mail_already_taken = "<?php echo lang("mypreferences_validation_mail_already_taken"); ?>";
var mypreferences_validation_mail_not_valid = "<?php echo lang("mypreferences_validation_mail_not_valid"); ?>";
var mypreferences_validation_mail_empty = "<?php echo lang("mypreferences_validation_mail_empty"); ?>";
</script>
<?php include("footer.php");?>
</body>
</html>
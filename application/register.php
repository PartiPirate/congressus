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
include_once("header.php");
require_once("engine/utils/SessionUtils.php");
$notification = "dm";

?>
<div class="container theme-showcase" role="main">
	<ol class="breadcrumb">
		<li><a href="index.php"><?php echo lang("breadcrumb_index"); ?> </a></li>
		<li class="active"><?php echo lang("breadcrumb_register"); ?></li>
	</ol>

	<div class="well well-sm">
		<p>
			<?php echo lang("register_guide"); ?>
		</p>
	</div>

	<form id="formPanel" class="form-horizontal">
		<fieldset>

			<!-- Form Name -->
			<legend><?php echo lang("register_form_legend"); ?></legend>

			<input id="mail" name="mail" value="" type="text" class="mailForm" />

			<!-- Text input-->
			<div class="form-group has-feedback">
				<label class="col-md-4 control-label" for="userLoginInput"><?php echo lang("register_form_loginInput"); ?></label>
				<div class="col-md-6">
					<input id="userLoginInput" name="userLoginInput" value="" type="text"
						placeholder="" class="form-control input-md">
					<span id="userLoginStatus"
						class="glyphicon glyphicon-ok form-control-feedback otbHidden" aria-hidden="true"></span>
					<p id="userLoginHelp" class="help-block otbHidden"></p>
				</div>
			</div>

			<!-- Email input-->
			<div class="form-group has-feedback">
				<label class="col-md-4 control-label" for="xxxInput"><?php echo lang("register_form_mailInput"); ?></label>
				<div class="col-md-6">
					<input id="xxxInput" name="xxxInput" value="" type="email"
						placeholder="" class="form-control input-md">
					<span id="mailStatus" class="glyphicon glyphicon-ok form-control-feedback otbHidden" aria-hidden="true"></span>
					<p id="mailHelp" class="help-block otbHidden"></p>
				</div>
 			</div>

			<!-- Password input-->
			<div class="form-group has-feedback">
				<label class="col-md-4 control-label" for="userPasswordInput"><?php echo lang("register_form_passwordInput"); ?></label>
				<div class="col-md-6">
					<input id="userPasswordInput" name="userPasswordInput" value="" type="password"
						placeholder="" class="form-control input-md">
					<span id="passwordStatus" class="glyphicon glyphicon-ok form-control-feedback otbHidden" aria-hidden="true"></span>
					<p class="help-block"><?php echo lang("register_form_passwordHelp");?></p>
				</div>
			</div>

			<!-- Password input-->
			<div class="form-group has-feedback">
				<label class="col-md-4 control-label" for="userConfirmationInput"><?php echo lang("register_form_confirmationInput"); ?></label>
				<div class="col-md-6">
					<input id="userConfirmationInput" name="userConfirmationInput" value="" type="password"
						placeholder="" class="form-control input-md">
					<span id="confirmationStatus" class="glyphicon glyphicon-ok form-control-feedback otbHidden" aria-hidden="true"></span>
				</div>
			</div>

			<!-- Language input-->
			<div class="form-group">
				<label class="col-md-4 control-label" for="userLanguageInput"><?php echo lang("register_form_languageInput"); ?></label>
				<div class="col-md-8">
					<input id="userLanguageInput" name="userLanguageInput"
						value="<?php echo $language; ?>" type="hidden">
					<div id="userLanguageButtons" class="btn-group" role="group" aria-label="...">
						<button value="en" type="button" class="btn btn-default <?php if ($language == "en") { echo "active"; } ?>"><span class="flag en" title="<?php echo lang("language_en"); ?>"></span></button>
						<button value="fr" type="button" class="btn btn-default <?php if ($language == "fr") { echo "active"; } ?>"><span class="flag fr" title="<?php echo lang("language_fr"); ?>"></span></button>
<!--
						<button value="de" type="button" class="btn btn-default <?php if ($language == "de") { echo "active"; } ?>"><span class="flag de" title="<?php echo lang("language_de"); ?>"></span></button>
-->
					</div>
				</div>
			</div>

			<!-- Checkbox input-->
			<div class="form-group">
				<div class="col-md-4 control-label">
					<input id="cgvInput" name="cgvInput" value="cgv" type="checkbox"
						placeholder="" class="input-md" checked="checked">
				</div>
				<div class="col-md-6 padding-left-0">
					<label class="form-control labelForCheckbox" for="cgvInput"><?php echo lang("register_form_iamabot"); ?> </label>
				</div>
			</div>

			<!-- Button (Double) -->
			<div class="form-group">
				<label class="col-md-4 control-label" for="registerButton"></label>
				<div class="col-md-8">
					<button id="registerButton" name="registerButton" class="btn btn-default"><?php echo lang("register_save"); ?></button>
				</div>
			</div>
		</fieldset>
	</form>

	<div id="successPanel" class="panel panel-success otbHidden">
		<div class="panel-heading">
			<?php echo lang("register_success_title"); ?>
		</div>
		<div class="panel-body">
			<?php echo lang("register_success_information"); ?>
		</div>
	</div>

	<?php echo addAlertDialog("error_passwords_not_equalAlert", lang("error_passwords_not_equal"), "danger"); ?>
	<?php echo addAlertDialog("error_cant_send_mailAlert", lang("error_cant_send_mail"), "danger"); ?>
	<?php echo addAlertDialog("error_cant_registerAlert", lang("error_cant_register"), "danger"); ?>

</div>

<div class="lastDiv"></div>

<script type="text/javascript">
var register_validation_user_empty = "<?php echo lang("register_validation_user_empty"); ?>";
var register_validation_user_already_taken = "<?php echo lang("register_validation_user_already_taken"); ?>";
var register_validation_mail_empty = "<?php echo lang("register_validation_mail_empty"); ?>";
var register_validation_mail_not_valid = "<?php echo lang("register_validation_mail_not_valid"); ?>";
var register_validation_mail_already_taken = "<?php echo lang("register_validation_mail_already_taken"); ?>";
</script>
<?php include("footer.php");?>
</body>
</html>
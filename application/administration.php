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
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once("config/config.php");
require_once("config/mail.config.php");
require_once("config/discourse.config.php");
require_once("config/mediawiki.config.php");

require_once("header.php");
require_once("config/discourse.structure.php");

?>

<div class="container theme-showcase" role="main">
	<ol class="breadcrumb">
		<li class="active"><?php echo lang("breadcrumb_administration"); ?></li>
	</ol>

	<div class="well well-sm">
		<p><?php echo lang("administration_guide"); ?></p>
	</div>

	<br />

	<form id="administration-form" class="form-horizontal">

		<div id="server-panel" class="panel panel-default">
			<div class="panel-heading">
				<a data-toggle="collapse" data-target="#server-panel-body" href="#"><?php echo lang("administration_server"); ?></a>
			</div>
			<div class="panel-body panel-collapse collapse in" id="server-panel-body">

				<div class="form-group">
					<label class="col-md-2 control-label" for="server_base_input"><?php echo lang("administration_server_base"); ?></label>
					<div class="col-md-10">
						<input id="server_base_input" name="server_base_input" type="text" value="<?php echo $config["server"]["base"]; ?>"  class="form-control input-md">
						<!-- <span class="help-block">help</span> -->
					</div>
				</div>

				<div class="form-group">
					<label class="col-md-2 control-label" for="server_line_input"><?php echo lang("administration_server_line"); ?></label>
					<div class="col-md-4">
						<select id="server_line_input" name="server_line_input" class="form-control">
							<option value="dev"  <?php if ("dev"  == $config["server"]["line"]) echo "selected"; ?>><?php echo lang("administration_server_line_dev"); ?></option>
							<option value="beta" <?php if ("beta" == $config["server"]["line"]) echo "selected"; ?>><?php echo lang("administration_server_line_beta"); ?></option>
							<option value=""     <?php if (""     == $config["server"]["line"]) echo "selected"; ?>><?php echo lang("administration_server_line_prod"); ?></option>
						</select>
					</div>
				</div>

				<div class="form-group">
					<label class="col-md-2 control-label" for="server_timezone_input"><?php echo lang("administration_server_timezone"); ?></label>
					<div class="col-md-4">
						<select id="server_timezone_input" name="server_timezone_input" class="form-control">
							<option value=""    		 <?php if (!$config["server"]["timezone"]) 					echo "selected"; ?>><?php echo lang("administration_server_timezone_none"); ?></option>
							<option value="Europe/Paris" <?php if ("Europe/Paris" == $config["server"]["timezone"]) echo "selected"; ?>><?php echo lang("administration_server_timezone_europeparis"); ?></option>
						</select>
					</div>
				</div>

				<div class="form-group">
					<label class="col-md-2 control-label" for="congressus_ballot_majorities_input"><?php echo lang("administration_congressus_ballot_majorities"); ?></label>
					<div class="col-md-10">
						<input id="congressus_ballot_majorities_input" name="congressus_ballot_majorities_input" type="text" value="<?php echo implode(", ", $config["congressus"]["ballot_majorities"]); ?>"  class="form-control input-md">
						<!-- <span class="help-block">help</span> -->
					</div>
				</div>

			</div>
		</div>

		<div id="database-panel" class="panel panel-default">
			<div class="panel-heading">
				<a data-toggle="collapse" data-target="#database-panel-body" class="collapsed" href="#"><?php echo lang("administration_database"); ?></a>
			</div>
			<div class="panel-body panel-collapse collapse " id="database-panel-body">

				<div class="form-group">
					<label class="col-md-2 control-label" for="database_host_input"><?php echo lang("administration_database_host"); ?></label>
					<div class="col-md-4">
						<input id="database_host_input" name="database_host_input" type="text" value="<?php echo $config["database"]["host"]; ?>"  class="form-control input-md">
						<!-- <span class="help-block">help</span> -->
					</div>
					<label class="col-md-2 control-label" for="database_port_input"><?php echo lang("administration_database_port"); ?></label>
					<div class="col-md-4">
						<input id="database_port_input" name="database_port_input" type="text" value="<?php echo $config["database"]["port"]; ?>"  class="form-control input-md">
						<!-- <span class="help-block">help</span> -->
					</div>
				</div>

				<div class="form-group">
					<label class="col-md-2 control-label" for="database_database_input"><?php echo lang("administration_database_database"); ?></label>
					<div class="col-md-10">
						<input id="database_database_input" name="database_database_input" type="text" value="<?php echo $config["database"]["database"]; ?>"  class="form-control input-md">
						<!-- <span class="help-block">help</span> -->
					</div>
				</div>

				<div class="form-group">
					<label class="col-md-2 control-label" for="database_login_input"><?php echo lang("administration_database_login"); ?></label>
					<div class="col-md-4">
						<input id="database_login_input" name="database_login_input" type="text" value="<?php echo $config["database"]["login"]; ?>"  class="form-control input-md">
						<!-- <span class="help-block">help</span> -->
					</div>
					<label class="col-md-2 control-label" for="database_password_input"><?php echo lang("administration_database_password"); ?></label>
					<div class="col-md-4">
						<input id="database_password_input" name="database_password_input" type="text" value="<?php echo $config["database"]["password"]; ?>"  class="form-control input-md">
						<!-- <span class="help-block">help</span> -->
					</div>
				</div>

				<div class="form-group">
					<label class="col-md-2 control-label" for="galette_db_input"><?php echo lang("administration_database_galette"); ?></label>
					<div class="col-md-10">
						<input id="galette_db_input" name="galette_db_input" type="text" value="<?php echo $config["galette"]["db"]; ?>"  class="form-control input-md">
						<!-- <span class="help-block">help</span> -->
					</div>
				</div>

				<div class="form-group">
					<label class="col-md-2 control-label" for="personae_db_input"><?php echo lang("administration_database_personae"); ?></label>
					<div class="col-md-10">
						<input id="personae_db_input" name="personae_db_input" type="text" value="<?php echo $config["personae"]["db"]; ?>"  class="form-control input-md">
						<!-- <span class="help-block">help</span> -->
					</div>
				</div>

				<div class="row text-center">
					<button id="btn-ping-database" class="btn btn-primary btn-primary" disabled="disabled"><?php echo lang("administration_ping_database"); ?></button>
				</div>

			</div>
		</div>

		<div id="memcached-panel" class="panel panel-default">
			<div class="panel-heading">
				<a data-toggle="collapse" data-target="#memcached-panel-body" class="collapsed" href="#"><?php echo lang("administration_memcached"); ?></a>
			</div>
			<div class="panel-body panel-collapse collapse " id="memcached-panel-body">

				<div class="form-group">
					<label class="col-md-2 control-label" for="memcached_host_input"><?php echo lang("administration_memcached_host"); ?></label>
					<div class="col-md-4">
						<input id="memcached_host_input" name="memcached_host_input" type="text" value="<?php echo $config["memcached"]["host"]; ?>"  class="form-control input-md">
						<!-- <span class="help-block">help</span> -->
					</div>
					<label class="col-md-2 control-label" for="memcached_port_input"><?php echo lang("administration_memcached_port"); ?></label>
					<div class="col-md-4">
						<input id="memcached_port_input" name="memcached_port_input" type="text" value="<?php echo $config["memcached"]["port"]; ?>"  class="form-control input-md">
						<!-- <span class="help-block">help</span> -->
					</div>
				</div>

			</div>
		</div>

		<div id="mail-panel" class="panel panel-default">
			<div class="panel-heading">
				<a data-toggle="collapse" data-target="#mail-panel-body" class="collapsed" href="#"><?php echo lang("administration_mail"); ?></a>
			</div>
			<div class="panel-body panel-collapse collapse " id="mail-panel-body">

				<div class="form-group">
					<label class="col-md-2 control-label" for="smtp_host_input"><?php echo lang("administration_mail_host"); ?></label>
					<div class="col-md-4">
						<input id="smtp_host_input" name="smtp_host_input" type="text" value="<?php echo $config["smtp"]["host"] ?>"  class="form-control input-md">
						<!-- <span class="help-block">help</span> -->
					</div>
					<label class="col-md-2 control-label" for="smtp_port_input"><?php echo lang("administration_mail_port"); ?></label>
					<div class="col-md-4">
						<input id="smtp_port_input" name="smtp_port_input" type="text" value="<?php echo $config["smtp"]["port"] ?>"  class="form-control input-md">
						<!-- <span class="help-block">help</span> -->
					</div>
				</div>

				<div class="form-group">
					<label class="col-md-2 control-label" for="smtp_secure_input"><?php echo lang("administration_mail_secure"); ?></label>
					<div class="col-md-4">
						<select id="smtp_secure_input" name="smtp_secure_input" class="form-control">
							<option value=""    <?php if ("" ==    $config["smtp"]["secure"]) echo "selected"; ?>><?php echo lang("administration_mail_secure_none"); ?></option>
							<option value="ssl" <?php if ("ssl" == $config["smtp"]["secure"]) echo "selected"; ?>>SSL</option>
							<option value="tls" <?php if ("tls" == $config["smtp"]["secure"]) echo "selected"; ?>>TLS</option>
						</select>
					</div>
					<div class="col-md-6">
						<p class="bg-danger form-alert simply-hidden secure-message secure-value-"><?php echo lang("administration_mail_secure_none_alert"); ?></p>
						<p class="bg-warning form-alert simply-hidden secure-message secure-value-ssl"><?php echo lang("administration_mail_secure_ssl_alert"); ?></p>
						<p class="bg-success form-alert simply-hidden secure-message secure-value-tls"><?php echo lang("administration_mail_secure_tls_alert"); ?></p>
					</div>
				</div>

				<div class="form-group">
					<label class="col-md-2 control-label" for="smtp_username_input"><?php echo lang("administration_mail_username"); ?></label>
					<div class="col-md-4">
						<input id="smtp_username_input" name="smtp_username_input" type="text" value="<?php echo $config["smtp"]["username"]; ?>"  class="form-control input-md">
						<!-- <span class="help-block">help</span> -->
					</div>
					<label class="col-md-2 control-label" for="smtp_password_input"><?php echo lang("administration_mail_password"); ?></label>
					<div class="col-md-4">
						<input id="smtp_password_input" name="smtp_password_input" type="text" value="<?php echo $config["smtp"]["password"]; ?>"  class="form-control input-md">
						<!-- <span class="help-block">help</span> -->
					</div>
				</div>

				<div class="form-group">
					<label class="col-md-2 control-label" for="smtp_from_address_input"><?php echo lang("administration_mail_from_address"); ?></label>
					<div class="col-md-4">
						<input id="smtp_from_address_input" name="smtp_from_address_input" type="text" value="<?php echo $config["smtp"]["from.address"]; ?>"  class="form-control input-md">
						<!-- <span class="help-block">help</span> -->
					</div>
					<label class="col-md-2 control-label" for="smtp_from_name_input"><?php echo lang("administration_mail_from_name"); ?></label>
					<div class="col-md-4">
						<input id="smtp_from_name_input" name="smtp_from_name_input" type="text" value="<?php echo $config["smtp"]["from.name"]; ?>"  class="form-control input-md">
						<!-- <span class="help-block">help</span> -->
					</div>
				</div>

			</div>
		</div>

		<div id="discourse-panel" class="panel panel-default">
			<div class="panel-heading">
				<a data-toggle="collapse" data-target="#discourse-panel-body" class="collapsed" href="#"><?php echo lang("administration_discourse"); ?></a>
				<input id="discourse_exportable_button" type="checkbox" <?php echo ($config["discourse"]["exportable"] ? "checked='checked'" : ""); ?>  data-toggle="toggle" data-size="mini" data-height="10">
			</div>
			<div class="panel-body panel-collapse collapse " id="discourse-panel-body">

				<input id="discourse_exportable_input" name="discourse_exportable_input" type="hidden" value="<?php echo ($config["discourse"]["exportable"] ? "true" : "false"); ?>">

				<div class="form-group">
					<label class="col-md-2 control-label" for="discourse_api_key_input"><?php echo lang("administration_discourse_api_key"); ?></label>
					<div class="col-md-10">
						<input id="discourse_api_key_input" name="discourse_api_key_input" type="text" value="<?php echo $config["discourse"]["api_key"]; ?>"  class="form-control input-md">
					</div>
				</div>

				<div class="form-group">
					<label class="col-md-2 control-label" for="discourse_url_input"><?php echo lang("administration_discourse_url"); ?></label>
					<div class="col-md-10">
						<input id="discourse_url_input" name="discourse_url_input" type="text" value="<?php echo $config["discourse"]["url"]; ?>"  class="form-control input-md">
					</div>
				</div>

				<div class="form-group">
					<label class="col-md-2 control-label" for="discourse_protocol_input"><?php echo lang("administration_discourse_protocol"); ?></label>
					<div class="col-md-10">
						<input id="discourse_protocol_input" name="discourse_protocol_input" type="text" value="<?php echo $config["discourse"]["protocol"]; ?>"  class="form-control input-md">
					</div>
				</div>

				<div class="form-group">
					<label class="col-md-2 control-label" for="discourse_user_input"><?php echo lang("administration_discourse_user"); ?></label>
					<div class="col-md-10">
						<input id="discourse_user_input" name="discourse_user_input" type="text" value="<?php echo $config["discourse"]["user"]; ?>"  class="form-control input-md">
					</div>
				</div>

				<div class="form-group">
					<label class="col-md-2 control-label" for="discourse_base_input"><?php echo lang("administration_discourse_base"); ?></label>
					<div class="col-md-10">
						<input id="discourse_base_input" name="discourse_base_input" type="text" value="<?php echo $config["discourse"]["base"]; ?>"  class="form-control input-md">
					</div>
				</div>

				<div class="form-group">
					<label class="col-md-2 control-label" for="allowed_categories_input[]"><?php echo lang("administration_discourse_allowed_categories"); ?></label>
					<div class="col-md-10">
						<?php foreach ($categories_all as $category) {?>
							<input type="checkbox" name="allowed_categories_input[]" value="<?php echo $category['id']; ?>" <?php if (in_array($category['id'], $config["discourse"]["allowed_categories"])) echo "checked"; ?>> <?php echo $category['name']; ?><br>
							<?php if (isset($category['subcategory'])) {
					      foreach ($category['subcategory'] as $subcategoy):?>
								 <input type="checkbox" name="allowed_categories_input[]" value="<?php echo $subcategoy['id']; ?>" <?php if (in_array($subcategoy['id'], $config["discourse"]["allowed_categories"])) echo "checked"; ?>> - &nbsp;&nbsp;<?php echo $subcategoy['name']; ?><br>
					      <?php endforeach;
					    }
						}?>
					</div>
				</div>

			</div>
		</div>

		<div id="mediawiki-panel" class="panel panel-default">
			<div class="panel-heading">
				<a data-toggle="collapse" data-target="#mediawiki-panel-body" class="collapsed" href="#"><?php echo lang("administration_mediawiki"); ?></a>
				<input id="mediawiki_exportable_button" type="checkbox" <?php echo ($config["mediawiki"]["exportable"] ? "checked='checked'" : ""); ?>  data-toggle="toggle" data-size="mini" data-height="10">
			</div>
			<div class="panel-body panel-collapse collapse " id="mediawiki-panel-body">

				<input id="mediawiki_exportable_input" name="mediawiki_exportable_input" type="hidden" value="<?php echo ($config["mediawiki"]["exportable"] ? "true" : "false"); ?>">


				<div class="form-group">
					<label class="col-md-2 control-label" for="mediawiki_url_input"><?php echo lang("administration_mediawiki_url"); ?></label>
					<div class="col-md-10">
						<input id="mediawiki_url_input" name="mediawiki_url_input" type="text" value="<?php echo $config["mediawiki"]["url"]; ?>"  class="form-control input-md">
					</div>
				</div>

				<div class="form-group">
					<label class="col-md-2 control-label" for="mediawiki_login_input"><?php echo lang("administration_mediawiki_login"); ?></label>
					<div class="col-md-10">
						<input id="mediawiki_login_input" name="mediawiki_login_input" type="text" value="<?php echo $config["mediawiki"]["login"]; ?>"  class="form-control input-md">
					</div>
				</div>

				<div class="form-group">
					<label class="col-md-2 control-label" for="mediawiki_password_input"><?php echo lang("administration_mediawiki_password"); ?></label>
					<div class="col-md-10">
						<input id="mediawiki_password_input" name="mediawiki_password_input" type="text" value="<?php echo $config["mediawiki"]["password"]; ?>"  class="form-control input-md">
					</div>
				</div>

				<div class="form-group">
					<label class="col-md-2 control-label" for="mediawiki_base_input"><?php echo lang("administration_mediawiki_base"); ?></label>
					<div class="col-md-10">
						<input id="mediawiki_base_input" name="mediawiki_base_input" type="text" value="<?php echo $config["mediawiki"]["base"]; ?>"  class="form-control input-md">
					</div>
				</div>

				<div class="form-group">
					<label class="col-md-2 control-label" for="mediawiki_categories_input"><?php echo lang("administration_mediawiki_categories"); ?></label>
					<div class="col-md-10">
						<textarea name="mediawiki_categories_input" id="mediawiki_categories_input" class="form-control" rows="10"><?php	foreach($config["mediawiki"]["categories"] as $category) {
										echo $category . "\n";
									} ?></textarea>
					</div>
				</div>

			</div>
		</div>

		<div id="account-panel" class="panel panel-default">
			<div class="panel-heading">
				<a data-toggle="collapse" data-target="#account-panel-body" class="collapsed" href="#"><?php echo lang("administration_account"); ?></a>
			</div>
			<div class="panel-body panel-collapse collapse " id="account-panel-body">

				<div class="form-group">
					<label class="col-md-2 control-label" for="administrator_login_input"><?php echo lang("administration_account_login"); ?></label>
					<div class="col-md-4">
						<input id="administrator_login_input" name="administrator_login_input" type="text" value="<?php echo $config["administrator"]["login"]; ?>"  class="form-control input-md">
						<!-- <span class="help-block">help</span> -->
					</div>
					<label class="col-md-2 control-label" for="administrator_password_input"><?php echo lang("administration_account_password"); ?></label>
					<div class="col-md-4">
						<input id="administrator_password_input" name="administrator_password_input" type="text" value="<?php echo $config["administrator"]["password"]; ?>"  class="form-control input-md">
						<!-- <span class="help-block">help</span> -->
					</div>
				</div>

			</div>
		</div>

		<div class="row text-center">
			<button id="btn-administration-save" class="btn btn-primary btn-primary" disabled="disabled"><?php echo lang("common_save"); ?></button>
		</div>

	</form>

	<?php echo addAlertDialog("administration_save_successAlert", lang("administration_alert_ok"), "success"); ?>

	<?php echo addAlertDialog("administration_ping_successAlert", 			lang("administration_alert_ping_ok"), "success"); ?>
	<?php echo addAlertDialog("administration_ping_no_hostAlert", 			lang("administration_alert_ping_no_host"), "danger"); ?>
	<?php echo addAlertDialog("administration_ping_bad_credentialsAlert", 	lang("administration_alert_ping_bad_credentials"), "danger"); ?>
	<?php echo addAlertDialog("administration_ping_no_databaseAlert", 		lang("administration_alert_ping_no_database"), "warning"); ?>

</div>

<div class="lastDiv"></div>

<?php include("footer.php"); ?>

</body>
</html>

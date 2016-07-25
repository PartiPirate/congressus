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
@include_once("config/mail.config.php");

include_once("header.php");

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
						<input id="server_base_input" name="server_base_input" type="text" value="<?php echo $config["server"]["base"] ?>" placeholder="placeholder" class="form-control input-md"> 
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
						<input id="database_host_input" name="database_host_input" type="text" value="<?php echo $config["database"]["host"] ?>" placeholder="placeholder" class="form-control input-md"> 
						<!-- <span class="help-block">help</span> -->
					</div>
					<label class="col-md-2 control-label" for="database_port_input"><?php echo lang("administration_database_port"); ?></label>
					<div class="col-md-4">
						<input id="database_port_input" name="database_port_input" type="text" value="<?php echo $config["database"]["port"] ?>" placeholder="placeholder" class="form-control input-md"> 
						<!-- <span class="help-block">help</span> -->
					</div>
				</div>

				<div class="form-group">
					<label class="col-md-2 control-label" for="database_database_input"><?php echo lang("administration_database_database"); ?></label>
					<div class="col-md-10">
						<input id="database_database_input" name="database_database_input" type="text" value="<?php echo $config["database"]["database"] ?>" placeholder="placeholder" class="form-control input-md"> 
						<!-- <span class="help-block">help</span> -->
					</div>
				</div>

				<div class="form-group">
					<label class="col-md-2 control-label" for="database_login_input"><?php echo lang("administration_database_login"); ?></label>
					<div class="col-md-4">
						<input id="database_login_input" name="database_login_input" type="text" value="<?php echo $config["database"]["login"] ?>" placeholder="placeholder" class="form-control input-md"> 
						<!-- <span class="help-block">help</span> -->
					</div>
					<label class="col-md-2 control-label" for="database_password_input"><?php echo lang("administration_database_password"); ?></label>
					<div class="col-md-4">
						<input id="database_password_input" name="database_password_input" type="text" value="<?php echo $config["database"]["password"] ?>" placeholder="placeholder" class="form-control input-md"> 
						<!-- <span class="help-block">help</span> -->
					</div>
				</div>

				<div class="form-group">
					<label class="col-md-2 control-label" for="galette_db_input"><?php echo lang("administration_database_galette"); ?></label>
					<div class="col-md-10">
						<input id="galette_db_input" name="galette_db_input" type="text" value="<?php echo $config["galette"]["db"] ?>" placeholder="placeholder" class="form-control input-md"> 
						<!-- <span class="help-block">help</span> -->
					</div>
				</div>

				<div class="form-group">
					<label class="col-md-2 control-label" for="personae_db_input"><?php echo lang("administration_database_personae"); ?></label>
					<div class="col-md-10">
						<input id="personae_db_input" name="personae_db_input" type="text" value="<?php echo $config["personae"]["db"] ?>" placeholder="placeholder" class="form-control input-md"> 
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
						<input id="memcached_host_input" name="memcached_host_input" type="text" value="<?php echo $config["memcached"]["host"] ?>" placeholder="placeholder" class="form-control input-md"> 
						<!-- <span class="help-block">help</span> -->
					</div>
					<label class="col-md-2 control-label" for="memcached_port_input"><?php echo lang("administration_memcached_port"); ?></label>
					<div class="col-md-4">
						<input id="memcached_port_input" name="memcached_port_input" type="text" value="<?php echo $config["memcached"]["port"] ?>" placeholder="placeholder" class="form-control input-md"> 
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
						<input id="smtp_host_input" name="smtp_host_input" type="text" value="<?php echo $config["smtp"]["host"] ?>" placeholder="placeholder" class="form-control input-md"> 
						<!-- <span class="help-block">help</span> -->
					</div>
					<label class="col-md-2 control-label" for="smtp_port_input"><?php echo lang("administration_mail_port"); ?></label>
					<div class="col-md-4">
						<input id="smtp_port_input" name="smtp_port_input" type="text" value="<?php echo $config["smtp"]["port"] ?>" placeholder="placeholder" class="form-control input-md"> 
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
						<input id="smtp_username_input" name="smtp_username_input" type="text" value="<?php echo $config["smtp"]["username"] ?>" placeholder="placeholder" class="form-control input-md"> 
						<!-- <span class="help-block">help</span> -->
					</div>
					<label class="col-md-2 control-label" for="smtp_password_input"><?php echo lang("administration_mail_password"); ?></label>
					<div class="col-md-4">
						<input id="smtp_password_input" name="smtp_password_input" type="text" value="<?php echo $config["smtp"]["password"] ?>" placeholder="placeholder" class="form-control input-md"> 
						<!-- <span class="help-block">help</span> -->
					</div>
				</div>

				<div class="form-group">
					<label class="col-md-2 control-label" for="smtp_from_address_input"><?php echo lang("administration_mail_from_address"); ?></label>
					<div class="col-md-4">
						<input id="smtp_from_address_input" name="smtp_from_address_input" type="text" value="<?php echo $config["smtp"]["from.address"] ?>" placeholder="placeholder" class="form-control input-md"> 
						<!-- <span class="help-block">help</span> -->
					</div>
					<label class="col-md-2 control-label" for="smtp_from_name_input"><?php echo lang("administration_mail_from_name"); ?></label>
					<div class="col-md-4">
						<input id="smtp_from_name_input" name="smtp_from_name_input" type="text" value="<?php echo $config["smtp"]["from.name"] ?>" placeholder="placeholder" class="form-control input-md"> 
						<!-- <span class="help-block">help</span> -->
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
						<input id="administrator_login_input" name="administrator_login_input" type="text" value="<?php echo $config["administrator"]["login"] ?>" placeholder="placeholder" class="form-control input-md"> 
						<!-- <span class="help-block">help</span> -->
					</div>
					<label class="col-md-2 control-label" for="administrator_password_input"><?php echo lang("administration_account_password"); ?></label>
					<div class="col-md-4">
						<input id="administrator_password_input" name="administrator_password_input" type="text" value="<?php echo $config["administrator"]["password"] ?>" placeholder="placeholder" class="form-control input-md"> 
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

<?php include("footer.php");?>

</body>
</html>
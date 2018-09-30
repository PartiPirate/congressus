/*
	Copyright 2015-2017 Cédric Levieux, Parti Pirate

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
/* global $ */

function checkBasicConfiguration() {

	if (	!$("#server_base_input").val()
		||	!$("#congressus_ballot_majorities_input").val()) {
		$("#server-panel").removeClass("panel-default").addClass("panel-danger");
	}
	else {
		$("#server-panel").removeClass("panel-default").removeClass("panel-danger").addClass("panel-success");
	}
}

function checkMemcachedConfiguration() {

	if (	!$("#memcached_host_input").val()
		||	!$("#memcached_port_input").val()) {
		$("#memcached-panel").removeClass("panel-default").addClass("panel-danger");
		$("#btn-ping-memcached").prop("disabled", true);
	}
	else {
		$("#memcached-panel").removeClass("panel-default").removeClass("panel-danger").addClass("panel-success");
		$("#btn-ping-memcached").prop("disabled", false);
	}
}

function checkDatabaseConfiguration() {

	if (	!$("#database_host_input").val()
		||	!$("#database_database_input").val()
		||	!$("#database_login_input").val()
		||	!$("#database_password_input").val()) {
		$("#database-panel").removeClass("panel-default").addClass("panel-danger");
	}
	else {
		$("#database-panel").removeClass("panel-default").removeClass("panel-danger").addClass("panel-success");
	}
}

function checkAdministratorConfiguration() {

	if (	!$("#administrator_login_input").val()
		||	!$("#administrator_password_input").val()) {
		$("#account-panel").removeClass("panel-default").addClass("panel-danger");
	}
	else {
		$("#account-panel").removeClass("panel-default").removeClass("panel-danger").addClass("panel-success");
	}
}

$(function() {
	$("#discourse_exportable_button").change(function() {
		$("#discourse_exportable_input").val($(this).prop('checked') ? "true" : "false");
	});

	var pingMemcached = function() {
		$("#btn-ping-memcached").prop("disabled", true);

		$.post("administration_api.php?method=do_pingMemcached", $("#administration-form").serialize(), function(data) {

			if (data.ok) {
				$("#administration_memcached_successAlert").show().delay(2000).fadeOut(1000);
			}
			else {
				$("#administration_memcached_" + data.error + "Alert").show().delay(2000).fadeOut(1000);
			}

			$("#btn-ping-memcached").prop("disabled", false);
			
		}, "json");
	}

	var pingDatabase = function() {
		$("#btn-ping-database").prop("disabled", true);
		
		$.post("administration_api.php?method=do_pingDatabase", $("#administration-form").serialize(), function(data) {

			if (data.ok) {
				$("#administration_ping_successAlert").show().delay(2000).fadeOut(1000);
				$("#btn-deploy-database").prop("disabled", false);
			}
			else {
				$("#administration_ping_" + data.error + "Alert").show().delay(2000).fadeOut(1000);
				
				if (data.error == "no_database") {
					$("#btn-create-database").prop("disabled", false);
				}
			}

			$("#btn-ping-database").prop("disabled", false);
			
		}, "json");
	}
	
	var createDatabase = function() {
		$("#btn-create-database").prop("disabled", true);
		
		$.post("administration_api.php?method=do_createDatabase", $("#administration-form").serialize(), function(data) {

			if (data.ok) {
				$("#administration_create_successAlert").show().delay(2000).fadeOut(1000);
				$("#btn-deploy-database").prop("disabled", false);
				$("#btn-create-database").prop("disabled", true);
			}
			else {
				$("#administration_ping_" + data.error + "Alert").show().delay(2000).fadeOut(1000);
				$("#btn-create-database").prop("disabled", false);
			}

		}, "json");
	}
	
	var deployDatabase = function() {
		$("#btn-deploy-database").prop("disabled", true);
		
		$.post("administration_api.php?method=do_deployDatabase", $("#administration-form").serialize(), function(data) {

			if (data.ok) {
				$("#administration_deploy_successAlert").show().delay(2000).fadeOut(1000);
//				$("#btn-deploy-database").prop("disabled", true);
			}
			else {
				$("#administration_ping_" + data.error + "Alert").show().delay(2000).fadeOut(1000);
				$("#btn-deploy-database").prop("disabled", false);
			}

		}, "json");
	}
	
	var checkMailSecure = function() {
		$(".secure-message").hide();
		$(".secure-value-" + $("#smtp_secure_input").val()).show();
	}
	
	var submitAdministrationForm = function() {
		$.post("do_updateAdministration.php", $("#administration-form").serialize(), function(data) {
			$("#administration_save_successAlert").show().delay(2000).fadeOut(1000);
		}, "json");
	}
	
	$("#smtp_secure_input").change(checkMailSecure);
	
	$("#administration-form").submit(function(event) {
		event.preventDefault();
		submitAdministrationForm();
	})

	$("#btn-ping-memcached").click(function(event) {
		event.preventDefault();
		pingMemcached();
	});

	$("#btn-ping-database").click(function(event) {
		event.preventDefault();
		pingDatabase();
	});

	$("#btn-create-database").click(function(event) {
		event.preventDefault();
		createDatabase();
	});

	$("#btn-deploy-database").click(function(event) {
		event.preventDefault();
		deployDatabase();
	});

	$("#btn-administration-save").click(function(event) {
		event.preventDefault();
		submitAdministrationForm();
	});

	checkMailSecure();

	checkDatabaseConfiguration();

	$("#database_host_input").change(checkDatabaseConfiguration);
	$("#database_database_input").change(checkDatabaseConfiguration);
	$("#database_login_input").change(checkDatabaseConfiguration);
	$("#database_password_input").change(checkDatabaseConfiguration);

	checkMemcachedConfiguration();

	$("#memcached_host_input").change(checkMemcachedConfiguration);
	$("#memcached_port_input").change(checkMemcachedConfiguration);

	checkAdministratorConfiguration();

	$("#administrator_login_input").change(checkAdministratorConfiguration);
	$("#administrator_password_input").change(checkAdministratorConfiguration);

	checkBasicConfiguration();

	$("#server_base_input").change(checkBasicConfiguration);
	$("#congressus_ballot_majorities_input").change(checkBasicConfiguration);

	$("#btn-ping-database").prop("disabled", false);
	$("#btn-administration-save").prop("disabled", false);
	
	// hide starting visible alerts
	$(".alert:visible").delay(5000).fadeOut(1000);
})
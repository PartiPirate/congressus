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
    along with Congressus.  If not, see <https://www.gnu.org/licenses/>.
*/
/* global $ */

function checkModuleGroups() {
	var authenticator = $("#modules_authenticator_input").val();
	
	switch(authenticator) {
		case "Galette":
			$("#module_groups_boxes_PersonaeGroups, #module_groups_boxes_PersonaeThemes, #module_groups_boxes_GaletteGroups, #module_groups_boxes_GaletteAllMembersGroups").prop("disabled", false);
			$("#module_groups_boxes_CustomGroups").prop("disabled", true).prop("checked", false);
			break;
		case "Custom":
			$("#module_groups_boxes_PersonaeGroups, #module_groups_boxes_PersonaeThemes, #module_groups_boxes_GaletteGroups, #module_groups_boxes_GaletteAllMembersGroups").prop("disabled", true).prop("checked", false);
			$("#module_groups_boxes_CustomGroups").prop("disabled", false);
			break;
	}
}

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

	var testMail = function() {
		$("#btn-mail-test").prop("disabled", true);

		$.post("administration_api.php?method=do_testMail", $("#administration-form").serialize(), function(data) {

			if (data.ok) {
				$("#administration_mail_successAlert").show().delay(2000).fadeOut(1000);
			}
			else {
				$("#administration_mail_" + data.error + "Alert").show().delay(2000).fadeOut(1000);
			}

			$("#btn-mail-test").prop("disabled", false);
			
		}, "json");
	}

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
				$("#btn-test-database").prop("disabled", false);
				$(".btn-deploy-database").prop("disabled", false);
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
				$(".btn-deploy-database").prop("disabled", false);
				$("#btn-create-database").prop("disabled", true);
			}
			else {
				$("#administration_ping_" + data.error + "Alert").show().delay(2000).fadeOut(1000);
				$("#btn-create-database").prop("disabled", false);
			}

		}, "json");
	}

	var testDatabase = function() {
		$("#btn-test-database").prop("disabled", true);
		$(".btn-deploy-database").prop("disabled", true);

		$.post("administration_api.php?method=do_deployDatabase&dry=", $("#administration-form").serialize(), function(data) {

			if (data.ok) {
//				$("#administration_deploy_successAlert").show().delay(2000).fadeOut(1000);
//				$(".btn-deploy-database").prop("disabled", true);

				$("#check-database-tbody").html("");

				let tableDivs = "";
				let errorCount = 0;
				

				for(let table in data.tables) {
					const tableInformations = data.tables[table];

					var trs = [];

					if (tableInformations["status"] == "exists") {
						for(let fieldName in tableInformations["compare"]["modify"]) {
							trs.push("<td>" + fieldName + "</td><td><i class='text-warning fa fa-refresh' title='Modification de la colonne : "+ tableInformations["compare"]["modify"][fieldName]["attributes"].join(', ')+"'><i></td>");
							errorCount++;
						}
						for(let fieldName in tableInformations["compare"]["add"]) {
							trs.push("<td>" + fieldName + "</td><td><i class='text-success fa fa-plus' title='Ajout de la colonne'><i></td>");
							errorCount++;
						}
						for(let fieldName in tableInformations["compare"]["delete"]) {
							trs.push("<td>" + fieldName + "</td><td><i class='text-danger fa fa-ban' title='Suppression de la colonne'><i></td>");
							errorCount++;
						}
					}
					else if (tableInformations["status"] == "created") {
						tableDivs += "<tr><td>" + table + "</td><td></td><td><i class='text-success fa fa-plus' title='Ajout de la table'><i></td></tr>";
						errorCount++;
					}
					else {
					}

					for(let index = 0; index < trs.length; ++index) {
						tableDivs += "<tr>";

						if (index == 0) {
							tableDivs += "<td rowspan='" + trs.length + "'>" + table + "</td>";
						}

						tableDivs += trs[index];

						tableDivs += "</tr>";
					}
				}

				if (errorCount) {
					$("#check-database-tbody").html(tableDivs);
					$("#check-database-modal").modal("show");
				}
				else {
					addEventAlert("La bdd est carrée", "success", 2000);
				}
			}
			else {
				$("#administration_ping_" + data.error + "Alert").show().delay(2000).fadeOut(1000);
			}

			$("#btn-test-database").prop("disabled", false);
			$(".btn-deploy-database").prop("disabled", false);

		}, "json");
	}

	var deployDatabase = function() {
		$("#btn-test-database").prop("disabled", true);
		$(".btn-deploy-database").prop("disabled", true);
		
		$.post("administration_api.php?method=do_deployDatabase", $("#administration-form").serialize(), function(data) {

			if (data.ok) {
				$("#administration_deploy_successAlert").show().delay(2000).fadeOut(1000);
//				$(".btn-deploy-database").prop("disabled", true);
			}
			else {
				$("#administration_ping_" + data.error + "Alert").show().delay(2000).fadeOut(1000);
			}

			$("#btn-test-database").prop("disabled", false);
			$(".btn-deploy-database").prop("disabled", false);

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

	$("#btn-mail-test").click(function(event) {
		event.preventDefault();
		testMail();
	});

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

	$(".btn-deploy-database").click(function(event) {
		event.preventDefault();
		deployDatabase();
	});

	$("#btn-test-database").click(function(event) {
		event.preventDefault();
		testDatabase();
	});

	$("#btn-administration-save").click(function(event) {
		event.preventDefault();
		submitAdministrationForm();
	});

	$("#modules_authenticator_input").change(checkModuleGroups);
	checkModuleGroups();

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
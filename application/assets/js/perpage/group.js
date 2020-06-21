/* global $ */
/* global keyupTimeoutId */
/* global updateGroupTree */
/* global openThemeUrl */

let keyupTimeoutId = null;
let groupLabelChanged = false;

function clearKeyup() {
	if (keyupTimeoutId) {
		clearTimeout(keyupTimeoutId);
		keyupTimeoutId = null;
	}
}

function saveTaskGroupFormHandlers() {
//	$("#saveGroupForm").change(saveGroup);

/*
	$("#saveTaskForm input[type=text]").keyup(function() {

		if ($(this).attr("id") == "gro_label") {
			groupLabelChanged = true;
		}

		clearKeyup();
		keyupTimeoutId = setTimeout(saveTask, 1500);
	});

	$("#saveTaskForm input[type=checkbox]").change(function() {
		saveTask();
	});

	$("#saveTaskForm select").change(function() {
		saveTask();
	});
*/

	$("#saveTaskForm select#gro_tasker_type").change(function() {
		saveGroupProperty($("#saveTaskForm #task_gro_id").val(), "gro_tasker_type", $(this).val());
	});

	$("#saveTaskForm select#gro_tasker_project_id").change(function() {
		saveGroupProperty($("#saveTaskForm #task_gro_id").val(), "gro_tasker_project_id", $(this).val());
	});

}

function saveGroupFormHandlers() {
//	$("#saveGroupForm").change(saveGroup);

	$("#saveGroupForm input[type=text], #saveGroupForm textarea").keyup(function() {

		if ($(this).attr("id") == "gro_label") {
			groupLabelChanged = true;
		}

		clearKeyup();
		keyupTimeoutId = setTimeout(saveGroup, 1500);
	});

	$("#saveGroupForm input[type=text], #saveGroupForm textarea").blur(function() {

		if ($(this).attr("id") == "gro_label") {
			groupLabelChanged = true;
		}

		saveGroup();
	});

	$("#saveGroupForm input[type=checkbox]").change(function() {
		saveGroup();
	});

	$("#saveGroupForm select").change(function() {
		saveGroup();
	});
}

function changeContactMethod() {
	$(".contact-type").hide();
	$(".contact-type." + $("#gro_contact_type").val()).show();
}

function addGroupHandlers() {
	$(".addThemeButton").click(function(event) {
		const groupId = $(this).data("group-id");
		const url ="theme.php?groupId=" + groupId + "&id=0&admin=";

		if (typeof openThemeUrl == "undefined") {
			window.location.href = url;
		}
		else {
			openThemeUrl(url);
		}
	});
	
	$("#gro_contact_type").change(function() {
		changeContactMethod();
	});
}

function savePower(myform) {
	clearKeyup();

	$.post(myform.attr("action"), myform.serialize(), function(data) {
//		myform.find(".saved").show().delay(1400).fadeOut(700);
		$("#success_group_groupAlert").parents(".container").show();
		$("#success_group_groupAlert").show().delay(2000).fadeOut(1000, function() {
			$(this).parents(".container").hide();
		});
	}, "json");
}

function savePowerHandlers() {
	let launchSavePower = function() {
		var myform = $(this).parents("form");
		clearKeyup();
		keyupTimeoutId = setTimeout(function() {
			savePower(myform);
		}, 1500);
	};

	$("input[name=gth_power]").keyup(launchSavePower);
	$("input[name=gth_power]").change(launchSavePower);
}

function excludeHandlers() {
	$(".excludeButton").click(function(event) {
		event.stopPropagation();
		event.preventDefault();

		var myform = $(this).parents("form");
		var themeId = myform.find("input[name=gth_theme_id]").val();
		$.post(myform.attr("action"), myform.serialize(), function(data) {
			$("#theme-" + themeId).remove();
		}, "json");
	});
}

function addGroupAdminFromSearchForm(rows) {
	var ids = "";
	var separator = "";
	for(var index = 0; index < rows.length; ++index) {
		ids += separator;
		ids += rows.eq(index).data("row").id;
		separator=",";
	}

	if (ids) {
		var myform = {"action": "add_admin"};
		myform["gad_group_id"] = $("#admins form input[name=gad_group_id]").val();
		myform["gad_member_id"] = ids;

		$.post("do_set_group_admin.php", myform, function(data) {
			if (data.ok) {
				addGroupAdmins(data.admins);
			}
		}, "json");
	}
}

function addGroupAdmins(admins) {
	var adminTBody = $("#admins table tbody");

	for(var index = 0; index < admins.length; ++index) {
		var admin = admins[index];

		var link = $("tr[data-template-id=template-group-admin]").template("use", {data : admin});
		adminTBody.append(link);
	}
}

function adminFormHandlers() {
	$("#addAdminButton").click(function(event) {
		event.preventDefault();
		event.stopPropagation();

		var form = $("#addAdminForm");

		$.post(form.attr("action"), form.serialize(), function(data) {
			if (data.ok) {
				addGroupAdmins(data.admins);
//				$("#admins table tbody").append(link);

				form.get(0).reset();
			}
		}, "json");
	});

	addRemoveAdminLinkHandlers();
	if (typeof groupAdmins != "undefined") {
		addGroupAdmins(groupAdmins);
	}
}

function addRemoveAdminLinkHandlers(selector) {
	$("#admins").on("click", ".removeAdminLink", function(event) {
		event.preventDefault();
		event.stopPropagation();

		var mylink = $(this);
		var myform = {"action": "remove_admin"};
		myform["gad_group_id"] = $(this).data("group-id");
		myform["gad_member_id"] = $(this).data("member-id");

		$.post("do_set_group_admin.php", myform, function(data) {
			if (data.ok) {
				mylink.parents("tr").remove();
			}
		}, "json");
	});
}

function addGroupAuthoritatives(authoritatives) {
	var authoritativeTBody = $("#authoritative table tbody");

	for(var index = 0; index < authoritatives.length; ++index) {
		var authoritative = authoritatives[index];

		var link = $("tr[data-template-id=template-group-authoritative]").template("use", {data : authoritative});
		authoritativeTBody.append(link);
	}
}

function authoritativeFormHandlers() {
	$("#addAuthoritativeButton").click(function(event) {
		event.preventDefault();
		event.stopPropagation();

		var form = $("#addAuthoritativeForm");
		if (form.find("#gau_authoritative_id").val() == "") return;

		$.post(form.attr("action"), form.serialize(), function(data) {
			if (data.ok) {
				addGroupAuthoritatives(data.authoritatives);
//				$("#authoritatives table tbody").append(link);

				form.get(0).reset();
			}
		}, "json");
	});

	addRemoveAuthoritativeLinkHandlers();
	if (typeof groupAuthoritatives != "undefined") {
		addGroupAuthoritatives(groupAuthoritatives);
	}
}

function addRemoveAuthoritativeLinkHandlers(selector) {
	$("#authoritative").on("click", ".removeAuthoritativeLink", function(event) {
		event.preventDefault();
		event.stopPropagation();

		var mylink = $(this);
		var myform = {"action": "remove_authoritative"};
		myform["gau_group_id"] = $(this).data("group-id");
		myform["gau_authoritative_id"] = $(this).data("authoritative-id");

		$.post("do_set_group_authority.php", myform, function(data) {
			if (data.ok) {
				mylink.parents("tr").remove();
			}
		}, "json");
	});
}

function saveGroup() {
	clearKeyup();

	const myform = $("#saveGroupForm");
	const groupId = $("#gro_id").val();
	const groupLabel = $("#gro_label").val();

	$.post(myform.attr("action"), myform.serialize(), function(data) {
		$("#success_group_groupAlert").parents(".container").show();
		$("#success_group_groupAlert").show().delay(2000).fadeOut(1000, function() {
			$(this).parents(".container").hide();
		});

/*
		$("#group_id").val(data.group.gro_id);
		$("#gau_group_id").val(data.group.gro_id);
		$(".breadcrumb .group-link").text(data.group.gro_label).attr("href", "group.php?id=" + data.group.gro_id);

		$("#admins,#authoritative").show();
*/

		// we have now an id, so it's a new group, update the form
		if (!groupId || groupId == "0") {
			openGroupUrl("group.php?id=" + data.group.gro_id + "&admin=");
			if (typeof updateGroupTree != "undefined") {
				updateGroupTree();
			}
			groupLabelChanged = false;
		}
		else {
			// if we change the label, change the tree
			if (groupLabelChanged) {

				$(".group-li-" + groupId + " > a").text(groupLabel);
	
				groupLabelChanged = false;
			}
		}
	}, "json");
}

function saveGroupProperty(id, property, value) {
	const myform = {gro_id: id, property: property, value: value};

	$.post("do_save_group_property.php", myform, function(data) {
		$("#success_group_groupAlert").parents(".container").show();
		$("#success_group_groupAlert").show().delay(2000).fadeOut(1000, function() {
			$(this).parents(".container").hide();
		});
	}, "json");
}

function changeGroupProjectHandler() {
	$("#gro_tasker_type").change(function() {
		const projectType = $(this).val();
		$("#gro_tasker_project_id option").hide();
		$("#gro_tasker_project_id option." + projectType).show();

		if (!$("#gro_tasker_project_id option:selected").hasClass(projectType)) {
			$("#gro_tasker_project_id").val("");
			$("#gro_tasker_project_id").change();
		}
	});
	
	$("#gro_tasker_type").change();
}

$(function() {
	
	$(".btn-delete-group").click(function(event) {
		event.preventDefault();
		event.stopPropagation();

		$("#delete-group-modal span.gro_label").text($("#gro_label").val());

		$("#delete-group-modal").modal("show");

//		alert("Toto");
	});

	$(".btn-confirm-delete-modal").click(function(event) {
		event.preventDefault();
		event.stopPropagation();

		var form = $("#delete-group-modal form");

		$.post(form.attr("action"), form.serialize(), function(data) {
			window.location.reload();
		}, "json");

	});
	
	addGroupHandlers();
	savePowerHandlers();
	excludeHandlers();
	adminFormHandlers();
	authoritativeFormHandlers();
	changeGroupProjectHandler();
	saveGroupFormHandlers();
	$("textarea[data-provide=markdown]").markdown();
	saveTaskGroupFormHandlers();
	changeContactMethod();
});

/*
    Copyright 2015-2020 Cédric Levieux, Parti Pirate

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
/* global keyupTimeoutId */
/* global updateGroupTree */

keyupTimeoutId = null;

let themeLabelChanged = false;

function clearKeyup() {
	if (keyupTimeoutId) {
		clearTimeout(keyupTimeoutId);
		keyupTimeoutId = null;
	}
}

function saveTheme() {
	clearKeyup();
	var myform = $("#saveThemeForm");
	const themeId = $("#the_id").val();
	const themeLabel = $("#the_label").val();

	$.post(myform.attr("action"), myform.serialize(), function(data) {
		$("#success_theme_themeAlert").parents(".container").show();
		$("#success_theme_themeAlert").show().delay(2000).fadeOut(1000, function() {
			$(this).parents(".container").hide();
		});

		$("input[name=the_id]").val(data.theme.the_id);
		$("#theme_link").text(data.theme.the_label);



		// we have now an id, so it's a new group, update the form
		if (!themeId || themeId == "0") {
/*			
			openGroupUrl("group.php?id=" + data.group.gro_id + "&admin=");
*/			
			if (typeof updateGroupTree != "undefined") {
				updateGroupTree();
			}
			themeLabelChanged = false;
		}
		else {
			// if we change the label, change the tree
			if (themeLabelChanged) {

				$(".theme-li-" + themeId + " > a").text(themeLabel);
	
				themeLabelChanged = false;
			}
		}

		toggleAdmins();
	}, "json");
}

function changeDateMethod() {
	$(".type-date").hide();
	if ($("#the_type_date").val()) {
		$("." + $("#the_type_date").val()).show();
	}
}

function changeVotingMethod() {
	$(".method").hide();
	
	const votingMethod = $("#the_voting_method").val();
	
	if (votingMethod) {
		$("." + votingMethod).show();

		if (votingMethod == "external_results" && $("#the_free_fixed").is(":checked")) {
			$("#the_entry_condition").parents(".form-group").hide();
		}
	}
}

function saveThemeFormHandlers() {
//	$("#saveThemeForm").change(saveTheme);

	$("#saveThemeForm input[type=text], #saveThemeForm textarea").keyup(function() {
		clearKeyup();
		if ($(this).attr("id") == "the_label") themeLabelChanged = true;
		keyupTimeoutId = setTimeout(saveTheme, 1500);
	});

	$("#saveThemeForm input[type=text], #saveThemeForm textarea").blur(function() {
		saveTheme();
	});

	$("#saveThemeForm input[type=checkbox]").change(function() {
		saveTheme();
	});

	$("#saveThemeForm #the_free_fixed").change(function() {
		if (!$("#the_free_fixed").is(":checked")) {
			$("#the_entry_condition").parents(".form-group").show();
		}
		else {
			$("#the_entry_condition").parents(".form-group").hide();
		}
	});

	$("#saveThemeForm select").change(function() {
		saveTheme();
	});

	$("button.btn-periodicity").click(function() {
		$("button.btn-periodicity").removeClass("active");
		$(this).addClass("active");

		$("#the_type_date").val($(this).data("value"));
		changeDateMethod();
		saveTheme();
	})

	$("#the_voting_method").change(function() {
		changeVotingMethod();
	});
}

function saveCandidate() {
	clearKeyup();

	var myform = $("#candidateForm");
	myform.find("input[name=can_text]").val($("#can_text").Editor("getText"));

	$.post(myform.attr("action"), myform.serialize(), function(data) {
		$("#success_theme_candidateAlert").parents(".container").show();
		$("#success_theme_candidateAlert").show().delay(2000).fadeOut(1000, function() {
			$(this).parents(".container").hide();
		});
	}, "text");
}

function addCandidateFormHandlers() {

	if ($("#candidateForm #can_text").length == 0) return;

	var checkStatus = function() {
		if ($("#candidateForm #can_status_candidate:checked").length) {
			$("#can_text").next().show();
		}
		else {
			$("#can_text").next().hide();
		}
	};

	$("#candidateForm #can_status_candidate").click(function() {
		$("#candidateForm #can_status_anti").removeAttr("checked");
		checkStatus();
		saveCandidate();
	});
	$("#candidateForm #can_status_anti").click(function() {
		$("#candidateForm #can_status_candidate").removeAttr("checked");
		checkStatus();
		saveCandidate();
	});

	var candidate = $("#can_text").html();
	$("#can_text").Editor();
	$("#can_text").Editor("setText", candidate);

	$("#candidateForm #can_text").next().find("*[contenteditable=true]").keyup(function() {
//		$("#candidateForm #can_text").keyup(function() {
			clearKeyup();
			keyupTimeoutId = setTimeout(saveCandidate, 1500);
		});

	checkStatus();
}

function saveDelegation() {
	var myform = $("#votingForm");

	if (myform.find("#del_power").val() < 0) {
		myform.find("#del_power").val(0);
	}
	$("#delegative-" + $("#del_member_to").val() + " #delegative-power").val($("#del_power").val());

	if (
			(myform.find("#del_power").val() - myform.find("#del_previous_power").val())
				>
			(myform.find("#delegative-remaining-power").text() - 0)
		) {
		alert("Trop de pouvoir distribué");
		return;
	}

	$.post(myform.attr("action"), myform.serialize(), function(data) {
		if (data.ok) {
			$("#success_theme_votingAlert").parents(".container").show();
			$("#success_theme_votingAlert").show().delay(2000).fadeOut(1000, function() {
				$(this).parents(".container").hide();
			});

			$("#delegative-" + $("#del_member_to").val() + " #delegative-previous-power").val($("#del_power").val());
			$("#del_previous_power").val($("#del_power").val());

			computeDelegations(themePower);

			$.get("theme.php?id=" + $("#can_theme_id").val(), {}, function(data) {
				var currentTable = $("html #powerInProgressTable");
				var newTableChildren = $(data).find("#powerInProgressTable").children();

				currentTable.children().remove();
				currentTable.append(newTableChildren);
			}, "html");
		}
		else {
			$("#" + data.error + "Alert").parents(".container").show();
			$("#" + data.error + "Alert").show().delay(2000).fadeOut(1000, function() {
				$(this).parents(".container").hide();
			});
		}
	}, "json");
}

function setDelegationPowerHandlers() {
	$("#delegated_member_nickname").keyup(function() {
		var val = $(this).val();

		var id = val ? $(".delegative[data-nickname='"+val.toLowerCase()+"']").data("id") : 0;
		if (!id) {
			id = val ? $(".delegative[data-mail='"+val.toLowerCase()+"']").data("id") : 0;
		}

		if (id) {
			$("#del_member_to").val(id);
		}
		else {
			$("#del_member_to").val("0");
		}

		$("#del_member_to").change();
	});

	$("#del_member_to").change(function() {
		if ($("#del_member_to").val() && $("#del_member_to").val() != "0") {
			$(".delegative").hide();
			$("#delegative-" + $("#del_member_to").val()).show();
		}
		else {
			$(".delegative[data-eligible!=candidate]").hide();
			$(".delegative[data-eligible=candidate]").show();
		}
		$("#del_power").val($("#delegative-" + $("#del_member_to").val() + " #delegative-power").val());
		$("#del_previous_power").val($("#delegative-" + $("#del_member_to").val() + " #delegative-previous-power").val());
	});

	$(".delegative #delegative-power").change(function() {
		$("#del_power").val($(this).val());
		$(this).parents(".delegative").find("#delegateButton").removeClass("btn-primary").addClass("btn-warning");
	});

	$(".delegative #delegateButton").click(function() {
		$("#del_member_to").val($(this).data("id"));

		// TODO better on the handler
		$(this).addClass("btn-primary").removeClass("btn-warning");

		saveDelegation();
	});
}

function computeDelegations(themePower) {
	if (!themePower) themePower= 1;

	var remainingPower = themePower;
	var delegations = "";
	$(".delegative").each(function() {
		var delegativePower = $(this).find("#delegative-power").val() - 0;
		if (delegativePower != 0) {
			if (delegations != "") {
				delegations += ", ";
			}
			delegations += "<a href='#' data-id='"+$(this).data("id")+"' data-mail='"+$(this).data("mail")+"' data-nickname='"+$(this).data("nickname")+"' class='link-to-delegative'>";
			delegations += $(this).find("#delegate-name").text();
			delegations += " (" + delegativePower + ")";
			delegations += "</a>";

			delegations += "<span>&nbsp;</span>";

//			delegations += "<button class=\"btn btn-danger btn-xs btn-remove-delegation\" data-id='"+$(this).data("id")+"' style=\"display: none;\" title=\"Supprimer cette délégation\"><span class=\"glyphicon glyphicon-remove\" ></span></button>";
			if (! $(this).find("#delegateButton").prop("disabled")) {
				delegations += "<a href='#' class='link-delete-delegation text-danger' data-id='"+$(this).data("id")+"' data-mail='"+$(this).data("mail")+"' data-nickname='"+$(this).data("nickname")+"' title=\"Supprimer cette délégation\"><span class=\"glyphicon glyphicon-remove\" ></span></a>";
			}

			remainingPower -= delegativePower;
		}
	});

	if (delegations == "") {
		delegations = "<span>Aucune</span>";
	}

	$("#votingForm #delegations").html($(delegations));
	$("#votingForm #delegative-remaining-power").text(remainingPower);
}


function deleteThemeFormHandlers() {
	$(".btn-delete-theme").click(function(event) {
		event.preventDefault();
		event.stopPropagation();

		$("#delete-theme-modal span.the_label").text($("#the_label").val());

		$("#delete-theme-modal").modal("show");

//		alert("Toto");
	});

	$(".btn-confirm-delete-modal").click(function(event) {
		event.preventDefault();
		event.stopPropagation();

		var form = $("#delete-theme-modal form");

		$.post(form.attr("action"), form.serialize(), function(data) {
			window.location.reload();
		}, "json");

	});
}

function toggleAdmins() {
	if ($("#the_id").val() > 0) {
		$("#admins").show();
		$("#deleteTheme").show();
	}
	else {
		$("#admins").hide();
		$("#deleteTheme").hide();
	}
}

function addThemeAdminFromSearchForm(rows) {
	var ids = "";
	var separator = "";
	for(var index = 0; index < rows.length; ++index) {
		ids += separator;
		ids += rows.eq(index).data("row").id;
		separator=",";
	}

	if (ids) {
		var myform = {"action": "add_admin"};
		myform["tad_theme_id"] = $("#admins form input[name=tad_theme_id]").val();
		myform["tad_member_id"] = ids;

		$.post("do_set_theme_admin.php", myform, function(data) {
			if (data.ok) {
				addThemeAdmins(data.admins);
			}
		}, "json");
	}
}

function addThemeAdmins(admins) {
	var adminTBody = $("#admins table tbody");

	for(var index = 0; index < admins.length; ++index) {
		var admin = admins[index];

		var link = $("tr[data-template-id=template-theme-admin]").template("use", {data : admin});
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
				addThemeAdmins(data.admins);
				form.get(0).reset();
			}
		}, "json");
	});

	addRemoveAdminLinkHandlers();
	if (typeof themeAdmins != "undefined") {
		addThemeAdmins(themeAdmins);
	}
}

function addRemoveAdminLinkHandlers() {
	$("#admins").on("click", ".removeAdminLink", function(event) {
		event.preventDefault();
		event.stopPropagation();

		var mylink = $(this);
		var myform = {"action": "remove_admin"};
		myform["tad_theme_id"] = $(this).data("theme-id");
		myform["tad_member_id"] = $(this).data("member-id");

		$.post("do_set_theme_admin.php", myform, function(data) {
			if (data.ok) {
				mylink.parents("tr").remove();
			}
		}, "json");
	});
}

function showFixedMemberFromSearchForm(rows) {
	if (rows.length != 1) return;

	var row = rows.eq(0).data("row");

	var value = row.nickname ? row.nickname : row.mail;

	$("#fme_member_mail").val(value);
}

function electedFormHandlers() {
	$("#addElectedButton").click(function(event) {
		event.preventDefault();
		event.stopPropagation();

		var form = $("#addElectedForm");

		$.post(form.attr("action"), form.serialize(), function(data) {
			if (data.ok) {
				var link = 	"<tr>\n";
				link += 	"	<td>"+data.fixationMember.fme_member_pseudo+"</td>\n";
				link += 	"	<td class=\"text-right\">"+data.fixationMember.fme_power+"</td>\n";
				link += 	"	<td>&nbsp;<a href=\"#\" class=\"removeElectedLink text-danger\" data-fixation-id=\""+data.fixationMember.fme_fixation_id+"\" data-member-id=\""+data.fixationMember.fme_member_id+"\"><span class=\"glyphicon glyphicon-remove\"></span></td>\n";
				link += 	"</tr>\n";

				link = $(link);
				addRemoveElectedLinkHandlers(link.find("a"));

				$("#fixedMembers table tbody").append(link);

				form.get(0).reset();
			}
		}, "json");
	});

	addRemoveElectedLinkHandlers();
}

function addRemoveElectedLinkHandlers(selector) {
	if (!selector) {
		selector = $(".removeElectedLink");
	}
	selector.click(function(event) {
		event.preventDefault();
		event.stopPropagation();

		var mylink = $(this);
		var myform = {"action": "remove_member"};
		myform["fme_fixation_id"] = $(this).data("fixation-id");
		myform["fme_member_id"] = $(this).data("member-id");
		myform["fme_power"] = 0;

		$.post("do_set_fixation_member.php", myform, function(data) {
			if (data.ok) {
				mylink.parents("tr").remove();
			}
		}, "json");
	});
}

function showDelegationFromSearchForm(rows) {
	if (rows.length != 1) return;

	var row = rows.eq(0).data("row");

	$("#delegated_member_nickname").val(row.nickname);
	$("#del_member_to").val(row.id);
	$("#del_member_to").change();
}

function fixationHandlers() {
	$("#endDateButton").click(function(event) {
		event.preventDefault();
		event.stopPropagation();

		var form = $("#newFixationForm");

		var formData = {"fix_id": form.find("input[name=fix_id]").val(), "property": "fix_until_date", "value": form.find("input[name=fix_until_date]").val()};

		$.post("do_set_fixation_property.php", formData, function(data) {
			if (data.ok) {
				$("#success_theme_fixationAlert").parents(".container").show();
				$("#success_theme_fixationAlert").show().delay(2000).fadeOut(1000, function() {
					$(this).parents(".container").hide();
				});
			}
		}, "json");

	});
}

function newFixationFormHandlers() {
	$("#newFixationButton").click(function(event) {
		event.preventDefault();
		event.stopPropagation();

		var form = $("#newFixationForm");

		$.post(form.attr("action"), form.serialize(), function(data) {
			if (data.ok) {
				$("#fixedMembers input[name=fme_fixation_id]").val(data.fixation.fix_id);
				$("#fixedMembers table tbody").children().remove();
				toggleElecteds();
			}
		}, "json");
	});
}

function toggleElecteds() {
	if ($("#fixedMembers input[name=fme_fixation_id]").val() > 0) {
		$("#fixedMembers").show();
	}
	else {
		$("#fixedMembers").hide();
	}
}

function addDelegativeHandlers() {
	$("#delegations").on("click", "a.link-to-delegative", function(event) {
		event.preventDefault();
		event.stopPropagation();

		$("#delegated_member_nickname").val($(this).data("nickname") ? $(this).data("nickname") : $(this).data("mail"));
		$("#del_member_to").val($(this).data("id"));
		$("#del_member_to").change();
	});

	$("#delegations").on("click", "a.link-delete-delegation", function(event) {
		event.preventDefault();
		event.stopPropagation();

		$("#delegated_member_nickname").val($(this).data("nickname") ? $(this).data("nickname") : $(this).data("mail"));
		$("#del_member_to").val($(this).data("id"));
		$("#del_member_to").change();
		$("#del_power").val(0);

//		$("#delegative-" + $("#del_member_to").val() + " #delegative-power").val(0);

		saveDelegation();
	});
}

function changeThemeProjectHandler() {
	$("#the_tasker_type").change(function() {
		const projectType = $(this).val();
		$("#the_tasker_project_id option").hide();
		$("#the_tasker_project_id option." + projectType).show();

		if (!$("#the_tasker_project_id option:selected").hasClass(projectType)) {
			$("#the_tasker_project_id").val("");
			$("#the_tasker_project_id").change();
		}
	});
	
	$("#the_tasker_type").change();
}

$(function() {

	$("#del_no_delegation_on_vote").change(function() {
		$.post("do_end_delegation.php", $("#votingForm").serialize(), function(data) {
			console.log(data);
		});
	});

	$("#the_eligible_group_type").change(function() {
		var groupType = $(this).val();

		$("#the_eligible_group_id option").hide();
		$("#the_eligible_group_id option." + groupType).show();

		var groupId = $("#the_eligible_group_id").val();
		var selectedItem = $("#the_eligible_group_id option[value="+groupId+"]:selected");

		if (selectedItem.css("display") == "none") {
			selectedItem.removeAttr("selected");
			$("#the_eligible_group_id option." + groupType + "[value=0]").attr("selected", "selected");
		}
	});
	$("#the_eligible_group_type").change();

	$("#the_voting_group_type").change(function() {
		var groupType = $(this).val();

		$("#the_voting_group_id option").hide();
		$("#the_voting_group_id option." + groupType).show();

		var groupId = $("#the_voting_group_id").val();
		var selectedItem = $("#the_voting_group_id option[value="+groupId+"]:selected");

		if (selectedItem.css("display") == "none") {
			selectedItem.removeAttr("selected");
			$("#the_voting_group_id option." + groupType + "[value=0]").attr("selected", "selected");
		}
	});
	$("#the_voting_group_type").change();

	$("body").on("click", ".btn-open-givers", function(event) {
		event.stopImmediatePropagation();
		event.preventDefault();

		var id = $(this).data("id");
		
		$(this).hide();
		$("span#span-" + id).show();
		$("ul#ul-" + id).show();
	});
	$("body").on("click", ".btn-close-givers", function(event) {
		event.stopImmediatePropagation();
		event.preventDefault();

		var id = $(this).data("id");

		$("button#button-" + id).show();
		$("span#span-" + id).hide();
		$("ul#ul-" + id).hide();
	});

	$("#advanced-link").click(function(event) {
	    event.stopImmediatePropagation();
	    event.preventDefault();
	    $("#advanced-tab").click();
	    $("#standard-link").show();
	    $(this).hide();
	});
	$("#standard-link").click(function(event) {
	    event.stopImmediatePropagation();
	    event.preventDefault();
	    $("#standard-tab").click();
	    $("#advanced-link").show();
	    $(this).hide();
	});

	changeThemeProjectHandler();
	computeDelegations(themePower);
	addCandidateFormHandlers();
	addDelegativeHandlers();
	setDelegationPowerHandlers();
	saveThemeFormHandlers();
	adminFormHandlers();
	electedFormHandlers();
	newFixationFormHandlers();
	fixationHandlers();
	deleteThemeFormHandlers();
	toggleAdmins();
	toggleElecteds();
	changeVotingMethod();
	changeDateMethod();
	$("textarea[data-provide=markdown]").markdown();
});

var peoples = {};


function getMemberLi(people) {
	var userId = getUserId();

	var memberContainer = null;
	if (people.mem_id == userId) {
		memberContainer = $("li[data-template-id=me-member]").template("use", {data: people});
	}
	else {
		memberContainer = $("li[data-template-id=member]").template("use", {data: people});
	}
	memberContainer.find("*").tooltip({placement: "top"});

	return memberContainer;
}

function notSoDifferent(people, previousPeople) {
	if (!previousPeople) return false;

	if (previousPeople.mem_connected != people.mem_connected) return false;
	if (previousPeople.mem_speaking != people.mem_speaking) return false;
	if (previousPeople.mem_speaking_request != people.mem_speaking_request) return false;
	if (previousPeople.mem_voting != people.mem_voting) return false;
	if (previousPeople.mem_power != people.mem_power) return false;
	if (previousPeople.mem_meeting_president != people.mem_meeting_president) return false;
	if (previousPeople.mem_meeting_secretary != people.mem_meeting_secretary) return false;
	if (previousPeople.mem_nickname != people.mem_nickname) return false;
	if (previousPeople.mem_id != people.mem_id) return false;

	// TODO
	return true;
}

function updateMemberLi(member, people) {
	var userId = getUserId();

	// The member is updated, do not delete it
	member.removeClass("to-deleted");
	var previousPeople = member.data("people");

	if (notSoDifferent(people, previousPeople)) return member;

	member.data("people", people);

	member.removeClass("text-success").removeClass("text-danger").addClass(people.mem_connected ? "text-success" : "text-danger");

	var option = $(".president select").find("option#people-" + people.mem_id);
	if (!option.length) {
		option = $("<option />", {"class" : "",
			id: "people-" + people.mem_id,
			"data-id": people.mem_id,
			"value": people.mem_id});
		option.text(people.mem_nickname);

		if (people["mem_voting"] && people["mem_voting"] != "0") {
			$(".president select .voting").append(option);
		}
		else if (typeof people.mem_id != "number") {
			$(".president select .unknown").append(option);
		}
		else if (people["mem_noticed"]){
			$(".president select .noticed").append(option);
		}
		else if (people["mem_connected"]) {
			$(".president select .connected").append(option);
		}
	}

	var option = $(".secretary select").find("option#people-" + people.mem_id);
	if (!option.length) {
		option = $("<option />", {"class" : "",
			id: "people-" + people.mem_id,
			"data-id": people.mem_id,
			"value": people.mem_id});
		option.text(people.mem_nickname);

		if (people["mem_voting"] && people["mem_voting"] != "0") {
			$(".secretary select .voting").append(option);
		}
		else if (typeof people.mem_id != "number") {
			$(".secretary select .unknown").append(option);
		}
		else if (people["mem_noticed"]){
			$(".secretary select .noticed").append(option);
		}
		else if (people["mem_connected"]) {
			$(".secretary select .connected").append(option);
		}
	}

	$("select.chat-select-member").each(function() {
		if($(this).parents(".template").length) return;

		var select = $(this);
		var chat = select.parents(".chat").data("json");
		var option = select.find("option#people-" + people.mem_id);

		if (!option.length) {
			option = $("<option />", {"class" : "",
				id: "people-" + people.mem_id,
				"data-id": people.mem_id,
				"value": people.mem_id});
			option.text(people.mem_nickname);

			if (people["mem_voting"]) {
				if (people["mem_voting"] != "0") {
					select.children(".voting").append(option);
				}
				else {
					select.children(".noticed").append(option);
				}
			}
			else {
				if (typeof people.mem_id != "number") {
					select.children(".unknown").append(option);
				}
				else {
					select.children(".connected").append(option);
				}
			}
		}

		if (chat.mem_id == people.mem_id) {
			select.find("option").removeAttr("selected");
			select.find("option#people-" + people.mem_id).attr("selected", "selected");
		}
	});

	if (people.mem_meeting_president == 1) {
		$(".mee_president_member_id").data("id", people.mem_id);
		$(".mee_president_member_id").text(people.mem_nickname);
		$(".president select").val(people.mem_id);
		$(".president select option").removeAttr("checked");
		$(".president select option[val='"+people.mem_id+"']").attr("checked", "checked");
	}

	if (people.mem_meeting_secretary == 1) {
		$(".mee_secretary_member_id").data("id", people.mem_id);
		$(".mee_secretary_member_id").text(people.mem_nickname);
		$(".secretary select").val(people.mem_id);
		$(".secretary select option").removeAttr("checked");
		$(".secretary select option[val='"+people.mem_id+"']").attr("checked", "checked");
	}

	member.children(".member-nickname").text(people.mem_nickname);

	if (people.mem_speaking && people.mem_speaking != "0") {
		$("#speaking-panel .speaker").text(people.mem_nickname);
		member.children("span.fa-commenting-o").show();
	}
	else {
		member.children("span.fa-commenting-o").hide();
	}

	if (people.mem_id == userId) {
		var buttons = [];
		buttons[buttons.length] = member.children("button.request-speaking");
		buttons[buttons.length] = $("#meeting-status-panel button.request-speaking");

		for(var bIndex = 0; bIndex < buttons.length; ++bIndex) {
			var button = buttons[bIndex];

			button.show();

			if (people.mem_speaking_request && people.mem_speaking_request != "0") {
				button.addClass("active");
				button.children(".badge").show().text(people.mem_speaking_request);
			}
			else {
				button.removeClass("active");
				button.children(".badge").hide();
			}

			if (people.mem_speaking && people.mem_speaking != "0") {
				button.addClass("disabled");
			}
			else {
				button.removeClass("disabled");
			}
		}
	}
	else if (people.mem_speaking_request && people.mem_speaking_request != "0") {
		member.children("span.fa-hand-paper-o").show();
		member.children("span.fa-hand-paper-o").children().text(people.mem_speaking_request);
	}
	else {
		member.children("span.fa-hand-paper-o").hide();
	}

	if (people.mem_speaking_request && people.mem_speaking_request != "0") {
		var speakerButton = $("#speaking-panel .speaking-requesters button#requester-" + people.mem_id);
		if (!speakerButton.length) {
			speakerButton = $("<button />", {"class": "btn btn-default",
				style: "margin-right: 5px;",
				id: "requester-" + people.mem_id,
				"data-id": people.mem_id}).text(people.mem_nickname);
			speakerButton.append($("<span />", {"class": "fa fa-hand-paper-o", style: ""}));
			$("#speaking-panel .speaking-requesters").append(speakerButton);
		}
		speakerButton.data("order", people.mem_speaking_request);

		if (!hasWritingRight(getUserId())) {
			speakerButton.addClass("disabled");
		}
	}
	else {
		var speakerButton = $("#speaking-panel .speaking-requesters button#requester-" + people.mem_id);
		speakerButton.remove();
	}

	if (people.mem_power) {
		powerSpan = member.find(".fa-archive.voting .power");
		powerSpan.text(people.mem_power);
	}

	return member;
}

function handlePages(notice) {
	var members = notice.children("ul.members");
	var memberPages = notice.children("div.member-pages");

	var currentPage = memberPages.data("current-page");

	var memberLists = members.children(".member");
	var numberOfMembers = 0;
	memberLists.each(function() {
		if (!$(this).hasClass("hide-missing")) {
			numberOfMembers++;
		}
	});

	var numberOfPages = Math.ceil(numberOfMembers / 10);
//	console.log("Number of members : " + numberOfMembers);

	memberPages.children("a").each(function() {
		var page = $(this);
		var pagePage = page.data("page");

		if (pagePage > numberOfPages) {
			page.hide();
		}
		else if (pagePage < 0) {
			page.show();
		}
		else if (pagePage < currentPage - 2) {
			page.hide();
		}
		else if (pagePage > currentPage + 2) {
			page.hide();
		}
		else {
			page.show();
		}

		if (pagePage == currentPage) {
			page.addClass("active");
		}
		else {
			page.removeClass("active");
		}
	});

	var index = 0;
	memberLists.each(function() {
		if (!$(this).hasClass("hide-missing")) {
			if (index >= ((currentPage - 1) * 10) && index < currentPage * 10) {
				$(this).removeClass("hide-paged");
			}
			else {
				$(this).addClass("hide-paged");
			}

			index++;
		}
	});

	memberPages.find(".page-first").attr("disabled", "disabled");
	memberPages.find(".page-previous").attr("disabled", "disabled");
	memberPages.find(".page-next").attr("disabled", "disabled");
	memberPages.find(".page-last").attr("disabled", "disabled");
	if (currentPage != 1) {
		memberPages.find(".page-first").removeAttr("disabled");
		memberPages.find(".page-previous").removeAttr("disabled");
	}
	if (currentPage != numberOfPages) {
		memberPages.find(".page-next").removeAttr("disabled");
		memberPages.find(".page-last").removeAttr("disabled");
	}
	
	if (numberOfPages < 2) {
		memberPages.hide();
	}
	else {
		memberPages.show();
	}
}

function addNotice(notice, parent) {

	var showMissing = $("#noticed-people .btn-hide-missing span").hasClass("glyphicon-eye-close");

	var noticeId = "notice-" + (notice.not_id ? notice.not_id : notice.not_label.replace(/ /g, "_"));

	var noticeHtml = parent.find("#" + noticeId);
	if (!noticeHtml.length) {
		noticeHtml = $("<li />", {"class": "list-group-item notice",
			id: noticeId,
			style: "padding-top: 2px; padding-bottom: 2px;"});
		var title = $("<h5 />", {style: "margin: 0;"});
		title.append($("<span />", {"class": "notice-name"}).append(notice.not_label));

		if (notice.not_power && notice.not_voting == 1) {
			power = $("<span />", {style: "margin-left: 5px;"});

			var powerSpan = $("<span />", {"class": "power"});
			powerSpan.append(notice.not_power);
			power.append(powerSpan);
			power.append($("<span />", {"class": "fa fa-ils"}));
			title.append(power);
		}

		if (!parent.hasClass("members")) {
			title.append($("<button />", {"data-id": notice.not_id, "class": "btn btn-default btn-xs btn-modify-voting", style: "margin-left: 5px; display: none;"}).append($("<span />", {"class": "fa fa-archive"})));
			title.append($("<button />", {"data-id": notice.not_id, "class": "btn btn-primary btn-xs btn-modify-notice", style: "margin-left: 5px; display: none;"}).append($("<span />", {"class": "glyphicon glyphicon-pencil"})));
			title.append($("<button />", {"data-id": notice.not_id, "class": "btn btn-danger btn-xs btn-remove-notice", style: "margin-left: 5px; display: none;"}).append($("<span />", {"class": "glyphicon glyphicon-remove"})));
		}

		title.children("button.btn-modify-notice").data("json", notice);

		if (notice.not_voting == 1) {
			title.children(".btn-modify-voting").addClass("active");
		}

		noticeHtml.append(title);

		var members = $("<ul />", {"class": "list-group members", style: "margin: 0;"});
		noticeHtml.append(members);

		var memberPages = $("<div />", {"class": "member-pages text-center", style: "margin: 0;"});
		memberPages.data("current-page", 1);

		noticeHtml.append(memberPages);

		parent.append(noticeHtml);
	}

	var title = noticeHtml.children("h5");
	title.find("span.notice-name").text(notice.not_label);

	var members = noticeHtml.children("ul.members");
	var memberPages = noticeHtml.children("div.member-pages");

	if (notice.not_children) {
		for(var index = 0; index < notice.not_children.length; ++index) {
			addNotice(notice.not_children[index], members);
		}
	}

//	console.log(notice.not_label + " members " + members.children().length);

	if (notice.not_people.length) {
		memberPages.show();
		memberPages.children().remove();

		var currentPage = memberPages.data("current-page");

		for(var page = 1; page < Math.ceil(notice.not_people.length / 10) + 1; ++page) {
			var pageLink = $("<a />", {"class": "page"});

			if (page == currentPage) {
				pageLink.addClass("active");
			}
			else if (page < currentPage - 2 || page > currentPage + 2) {
				pageLink.hide();
			}

			pageLink.attr("href", "#");
			pageLink.data("page", page);
			pageLink.text(page);

			memberPages.append(pageLink);
		}

		memberPages.append($("<a href='#' data-page='-2' class='pull-left page-first'><span class='glyphicon glyphicon-backward'></span></span></a>"));
		memberPages.append($("<a href='#' data-page='-1' class='pull-left page-previous'><span class='glyphicon glyphicon-triangle-left'></span></a>"));

		memberPages.append($("<a href='#' data-page='-4' class='pull-right page-last'><span class='glyphicon glyphicon-forward'></span></span></a>"));
		memberPages.append($("<a href='#' data-page='-3' class='pull-right page-next'><span class='glyphicon glyphicon-triangle-right'></span></a>"));

//		if (memberPages.children("a.page").length < 2) {
//			memberPages.hide();
//		}
	}
	else {
		memberPages.hide();
	}

	var userId = getUserId();

	for(var index = 0; index < notice.not_people.length; ++index) {
		var people = notice.not_people[index];

		var previousPeople = peoples[people.mem_id];
		var member = members.find("li.member#member-" + people.mem_id);

		if (showMissing || people.mem_connected) {
			member.removeClass("hide-missing");
		}
		else {
			member.addClass("hide-missing");
		}
//		var member = previousPeople ? previousPeople.member : [];

		if (people.mem_id == userId) {
			if (people.mem_meeting_president == 1 || people.mem_meeting_secretary == 1) {
//				$(".president-panels").show();
				$(".president-panels .speaking-requesters button").removeClass("disabled");
			}
			else {
//				$(".president-panels").show();
				$(".president-panels .speaking-requesters button").addClass("disabled");

				$("#meeting-status-panel .btn-waiting-meeting").addClass("disabled");
				$("#meeting-status-panel .btn-open-meeting").addClass("disabled");
				$("#meeting-status-panel .btn-close-meeting").addClass("disabled");
//				$(".president-panels").hide();
			}
		}

		if (notSoDifferent(people, previousPeople) && member.length) {
			member.removeClass("to-deleted");
			continue;
		}

		peoples[people.mem_id] = people;

		if (!member.length) {
			member = getMemberLi(people);
			members.append(member);
		}

		updateMemberLi(member, people);

		if (notice.not_voting == 1) {
			member.find(".voting").addClass("can-vote").show();
		}
		else {
			member.find(".voting").removeClass("can-vote").hide();
		}

//		console.log("Handled member @ " + new Date().getTime());
	}

	handlePages(noticeHtml);

	noticeHtml.data("noticed", notice.not_noticed == 1);
	noticeHtml.removeClass("to-deleted");
}

function updatePeople() {
	var meetingId = $(".meeting").data("id");

//	console.log("Do get people @ " + new Date());

	$.get("meeting_api.php?method=do_getPeople", {id: meetingId}, function(data) {
//		console.log("Get people @ " + new Date());

		var parent = $("#noticed-people > ul");
//		parent.children().remove();
		parent.find("li").addClass("to-deleted");

		for(var index = 0; index < data.notices.length; ++index) {
			var notice = data.notices[index];
			addNotice(notice, parent);
		}

		parent.find(".to-deleted").remove();

//		console.log("Noticed people done @ " + new Date());

		var parent = $("#visitors ul");
//		parent.children().remove();
		parent.children().addClass("to-deleted");

		for(var index = 0; index < data.visitors.length; ++index) {
			var people = data.visitors[index];

			var member = parent.children("li#member-" + people.mem_id);

			if (!member.length) {
				member = getMemberLi(people);
				parent.append(member);
			}

			updateMemberLi(member, people);
			member.find(".voting").hide();
		}
		parent.children(".to-deleted").remove();

		var speakingRequestersContainer = $(".speaking-requesters");
		var speakingRequesters = speakingRequestersContainer.find("button");

		for(var index = 0; index < speakingRequesters.length; ++index) {
			var speakingRequester = speakingRequesters.eq(index);
			var order = speakingRequester.data("order") - 1;

			if (order > index) {
				speakingRequester.detach();
				speakingRequestersContainer.find("button").eq(order - 1).after(speakingRequester);
				index = -1;
				speakingRequesters = speakingRequestersContainer.find("button");
			}
		}

		var userId = $(".meeting").data("user-id");

		if (hasWritingRight(userId)) {
			$(".btn-add-motion, .btn-add-task").show();
		}
		else {
			$(".btn-add-motion, .btn-add-task").hide();
		}

		var isPeopleNoticed = true;
		$("#noticed-people > ul > li").each(function() {
			isPeopleNoticed &= $(this).data("noticed");
		});

		var meeting = $(".meeting").data("json");
		if (!meeting) {
			meeting = {mee_status: ''};
		}

		if (meeting.mee_status != "construction" || isPeopleNoticed || !hasRight(getUserId(), "handle_notice")) {
			$("#noticed-people .panel-footer").hide();
		}
		else {
			$("#noticed-people .panel-footer").show();
			$("#noticed-people .panel-footer .btn-notice-people").removeClass("disabled");
		}

//		console.log("Toggle missing people @ " + new Date());

		toggleMissingPeople();

//		console.log("Handled people @ " + new Date());

		isPeopleReady = true;
		testMeetingReady();

	}, "json");
}

function ping() {
	var meetingId = $(".meeting").data("id");
	$.post("meeting_api.php?method=do_ping.php", {id: meetingId}, function(data) {
	}, "json");
}

function setSpeaking(event) {
	event.preventDefault();

	if (!hasWritingRight(getUserId())) return;

	$(this).addClass("disabled");

	var meetingId = $(".meeting").data("id");
	var userId = $(this).data("id");

	$.get("meeting_api.php?method=do_setSpeaking", {meetingId: meetingId, userId: userId}, function(data) {
	}, "json");
}

function requestSpeaking(event) {
	event.preventDefault();
	event.stopPropagation();

	$(this).addClass("disabled");

	var meetingId = $(".meeting").data("id");
	$.get("meeting_api.php?method=do_requestSpeaking", {id: meetingId}, function(data) {
	}, "json");
}

function changeOffice(event) {
	var type = $(this).data("type");
	var memberId = $(this).val();
	var meetingId = $(".meeting").data("id");
	var userId = $(".meeting").data("user-id");

	if (hasWritingRight(userId)) {
		$.post("meeting_api.php?method=do_changeOfficeMember", {meetingId: meetingId, memberId: memberId, type: type}, function(data) {
		}, "json");
	}
}

function addPresidentHandlers() {
	$(".president select, .secretary select").hide();
	$(".president, .secretary").hover(function() {
		if (hasWritingRight($(".meeting").data("user-id"))) {
			$(this).find("select").show();
			$(this).find(".read-data").hide();
		}
	}, function() {
		$(this).find("select").hide();
		$(this).find(".read-data").show();
	});
	$(".president select, .secretary select").change(changeOffice);
}

function toggleMissingPeople() {
	if ($("#noticed-people .btn-hide-missing span").hasClass("glyphicon-eye-close")) {
		$("#noticed-people .member.text-danger").removeClass("hide-missing");
	}
	else {
		$("#noticed-people .member.text-danger").addClass("hide-missing");
	}

	$(".notice").each(function() {
		handlePages($(this));
	});
}

function addNoticeHandlers() {
	$(".meeting .row").on("click", "button.request-speaking", requestSpeaking);

	$("#noticed-people").on("click", ".page", function(event) {
		event.stopPropagation();
		event.preventDefault();

		var page = $(this).data("page");
		$(this).parent().data("current-page", page);
		toggleMissingPeople();
	});

	$("#noticed-people").on("click", ".page-first", function(event) {
		event.stopPropagation();
		event.preventDefault();

		$(this).parent().data("current-page", 1);
		toggleMissingPeople();
	});

	$("#noticed-people").on("click", ".page-previous", function(event) {
		event.stopPropagation();
		event.preventDefault();

		var page = $(this).parent().data("current-page") - 1;
		if (page < 1) page = 1;
		
		$(this).parent().data("current-page", page);
		toggleMissingPeople();
	});

	$("#noticed-people").on("click", ".page-next", function(event) {
		event.stopPropagation();
		event.preventDefault();

		var page = $(this).parent().data("current-page") - (-1);

		var numberOfMembers = 0;
		$(this).parent().parent().children(".members").children("li.member").each(function() {
			if (!$(this).hasClass("hide-missing")) {
				numberOfMembers++;
			}
		});

		var numberOfPages = Math.ceil(numberOfMembers / 10);

		if (page > numberOfPages) page = numberOfPages;
		
		$(this).parent().data("current-page", page);
		toggleMissingPeople();
	});

	$("#noticed-people").on("click", ".page-last", function(event) {
		event.stopPropagation();
		event.preventDefault();

		var numberOfMembers = 0;
		$(this).parent().parent().children(".members").children("li.member").each(function() {
			if (!$(this).hasClass("hide-missing")) {
				numberOfMembers++;
			}
		});

		var numberOfPages = Math.ceil(numberOfMembers / 10);

		$(this).parent().data("current-page", numberOfPages);
		toggleMissingPeople();
	});

	$("#noticed-people").on("mouseenter", ".panel-heading", function() {
		if (!hasRight(getUserId(), "handle_notice")) {
			$(this).children("button.btn-hide-missing").show();

			return;
		}

		$(this).children("button").show();
	});

	$("#noticed-people").on("mouseleave", ".panel-heading", function() {
		$(this).children("button").hide();
	});

	$("#noticed-people").on("mouseenter", "li h5", function() {
		if (!hasRight(getUserId(), "handle_notice")) return;

		$(this).children("button").show();
	});

	$("#noticed-people").on("mouseleave", "li h5", function() {
		$(this).children("button").hide();
	});

	var targetChangeHandler = function(type) {
		if (type != "con_external") {
			$(".bootbox .not_mails").show();
			$(".bootbox .mails").hide();
			$(".bootbox #not_target_id option, .bootbox #not_target_id optgroup").hide();
			$(".bootbox #not_target_id option." + type + ", .bootbox #not_target_id optgroup." + type).show();
			$(".bootbox #not_target_id option").removeAttr("selected");
			$(".bootbox #not_target_id option." + type).eq(0).attr("selected", "selected");
		}
		else {
			$(".bootbox .not_mails").hide();
			$(".bootbox .mails").show();
		}
	};

	$("body").on("change", ".bootbox #not_target_type", function() {
		var type = $(this).val();
		targetChangeHandler(type);
	});

	$("#noticed-people").on("click", ".btn-hide-missing", function(event) {
		$("#noticed-people .btn-hide-missing span").toggleClass("glyphicon-eye-close").toggleClass("glyphicon-eye-open");

		$("#noticed-people .member-pages").each(function() {
			$(this).data("current-page", 1);
		});

		toggleMissingPeople();
	});

	$("#noticed-people").on("click", ".btn-modify-notice", function(event) {
		if (!hasRight(getUserId(), "handle_notice")) return;

		var meetingId = $(".meeting").data("id");
		var noticeId = $(this).data("id");

		var notice = $(this).data("json");
		
		$.get("notice.php", {meetingId: meetingId, noticeId: noticeId}, function(data) {
			var dialog = $(data);

			bootbox.dialog({
		        title: "Convocation",
		        message: dialog,
		        buttons: {
		            success: {
		                label: "Modifier",
		                className: "btn-primary",
		                callback: function () {
		                		var dialog = $(this);

		                		var form = dialog.find("form");

		                		$.post("meeting_api.php?method=do_changeNotice", form.serialize(), function(data) {
		                		}, "json");
		                    }
		                },
		            close: {
		                label: "Fermer",
		                className: "btn-default",
		                callback: function () {

		                    }
		                }
		        },
		        className: "not-large-dialog"
			});

			dialog.find("#not_target_id option, #not_target_id optgroup").hide();
			dialog.find("#not_target_id option.dlp_groups").show();
			dialog.find(".mails").hide();

			dialog.find("#not_target_type option[value='"+notice.not_target_type+"']").attr("selected", "selected");
			dialog.find("#not_target_type").change();
			dialog.find("#not_target_id option."+notice.not_target_type+"[value='"+notice.not_target_id+"']").attr("selected", "selected");
			dialog.find("#not_external_mails").val(notice.not_external_mails);

			if (notice.not_voting == 1) {
				dialog.find("#not_voting").click();
			}

		}, "html");

	});

	$("#noticed-people").on("click", ".panel-heading .btn-add-notice", function() {
		if (!hasRight(getUserId(), "handle_notice")) return;

		var meetingId = $(".meeting").data("id");

		$.get("notice.php", {meetingId: meetingId}, function(data) {
			var dialog = $(data);

			dialog.find("#not_target_id option, #not_target_id optgroup").hide();
			dialog.find("#not_target_id option.dlp_groups").show();
			dialog.find(".mails").hide();

			bootbox.dialog({
		        title: "Convocation",
		        message: dialog,
		        buttons: {
		            success: {
		                label: "Ajouter",
		                className: "btn-primary",
		                callback: function () {
		                		var dialog = $(this);

		                		var form = dialog.find("form");

		                		$.post("meeting_api.php?method=do_addNotice", form.serialize(), function(data) {
		                		}, "json");
		                    }
		                },
		            close: {
		                label: "Fermer",
		                className: "btn-default",
		                callback: function () {

		                    }
		                }
		        },
		        className: "not-large-dialog"
			});

		}, "html");
	});

	$("#noticed-people").on("click", ".btn-remove-notice", function(event) {
		if (!hasRight(getUserId(), "handle_notice")) return;

		var noticeId = $(this).data("id");
		var meetingId = $(".meeting").data("id");

		var text = $(this).parent().text();

		bootbox.setLocale("fr");
		bootbox.confirm("Supprimer la convocation de \"" + text + "\" ?", function(result) {
			if (result) {
				$.post("meeting_api.php?method=do_removeNotice", {meetingId: meetingId, noticeId: noticeId}, function(data) {
					$("#notice-" + noticeId).remove();
				}, "json");
			}
		});
	});

	$("#noticed-people").on("click", ".btn-modify-voting", function(event) {
		if (!hasRight(getUserId(), "handle_notice")) return;

		var meetingId = $(".meeting").data("id");
		var noticeId = $(this).data("id");

		var voting = 0;
		if ($(this).hasClass("active")) {
			voting = 0;
			$(this).removeClass("active");
		}
		else {
			voting = 1;
			$(this).addClass("active");
		}
		$(this).addClass("disabled");

		var form = {not_meeting_id: meetingId, not_id: noticeId, not_voting: voting};

		$.post("meeting_api.php?method=do_changeNotice", form, function(data) {}, "json");
	});

	$("#noticed-people .panel-footer").on("click", ".btn-notice-people", function(event) {
		if (!hasRight(getUserId(), "handle_notice")) return;

		$(this).addClass("disabled");

		var meetingId = $(".meeting").data("id");

		$.post("meeting_api.php?method=do_noticePeople", {meetingId: meetingId}, function(data) {}, "json");
	});

	$("#visitors").on("mouseenter", "li.member", function() {
		if ($(this).data("id") == getUserId() && (""+getUserId()).substr(0, 1) == "G") {
			$(this).find(".glyphicon-pencil").show();
		}
	});

	$("#visitors").on("mouseleave", "li.member", function() {
		if ($(this).data("id") == getUserId() && (""+getUserId()).substr(0, 1) == "G") {
			$(this).find(".glyphicon-pencil").hide();
		}
	});

	$("#visitors").on("click", "li.member", function(event) {

		if ($(event.target).hasClass("btn")) return;
		if ($(event.target).hasClass("fa")) return;
		if ($(event.target).hasClass("badge")) return;
		if ($(event.toElement).hasClass("btn")) return;
		if ($(this).data("id") != getUserId()) return;
		if ((""+getUserId()).substr(0, 1) != "G") return;

		if ($(this).find("input").length) {
			$(this).find("input").focus();
			return;
		}

		var input = $("<input />", {"class": "form-control", "style": "display: inline-block;"});
		var propertyText = $(this).children("span.member-nickname");

		var meetingId = $(".meeting").data("id");

		input.val(propertyText.text());
		input.blur(function() {
//			return;
			clearKeyup();
			// update the text into the server
			var newText = input.val();

			if (newText == "") {
				newText == "Guest";
			}
			
			$.post("meeting_api.php?method=do_changeGuest", {meetingId: meetingId, text: newText}, function(data) {
				propertyText.text(newText);
				propertyText.show();
				input.remove();
			}, "json");
		});

		input.keyup(function() {
//			return;
			clearKeyup();
			keyupTimeoutId = setTimeout(function() {
				var newText = input.val();

				if (newText == "") {
					newText == "Guest";
				}
				
				$.post("meeting_api.php?method=do_changeGuest", {meetingId: meetingId, text: newText}, function(data) {
				}, "json");
			}, 1500);
		});

		propertyText.after(input);
		propertyText.hide();

		input.focus();
	});
}

$(function() {
	var getPeopleTimer = $.timer(updatePeople);
	getPeopleTimer.set({ time : 2500, autostart : true });

	var pingTimer = $.timer(ping);
	pingTimer.set({ time : 40000, autostart : true });

	$(".speaking-requesters").on("click", "button", setSpeaking);

	addPresidentHandlers();
	addNoticeHandlers();

	ping();
	updatePeople();

	// TODO
	$("#videoDock").hide();
});

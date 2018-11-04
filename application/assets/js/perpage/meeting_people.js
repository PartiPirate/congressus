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
/* global getUserId */
/* global hasWritingRight */
/* global hasRight */
/* global speakingStats */
/* global clearKeyup */
/* gloabl keyupTimeoutId */

var peoples = {};
var numberOfConnected = 0;

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
		
		var previous = $("#speaking-panel .speaker").text();
		$("#speaking-panel .speaker").text(people.mem_nickname);
		$(".meeting").data("speaking-id", people.mem_id);

		if (people.mem_nickname != previous) {
			speakingTime = 0;
		}

		member.children("span.fa-commenting-o").show();
		member.children(".set-speaking").hide();
	}
	else {
		member.children("span.fa-commenting-o").hide();

		if (hasWritingRight(getUserId()) && people["mem_connected"]) {
			member.children(".set-speaking").show();
		}
		else {
			member.children(".set-speaking").hide();
		}
	}

	if (people.mem_id == userId) {
		var buttons = [];
		buttons[buttons.length] = member.children("button.request-speaking");
		buttons[buttons.length] = $("#meeting-status-panel button.request-speaking, #speaking-panel button.request-speaking");

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
			speakerButton = $("<button />", {"class": "btn btn-default btn-sm",
				style: "margin-right: 5px; padding: 3px 10px; font-size: 13px;",
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
		var powerSpan = member.find(".fa-archive.voting .power");
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
			var power = $("<span />", {style: "margin-left: 5px;"});

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

	// Set paging system
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

		updateSpeaking(people);

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

function updateSpeaking(people) {
	if (people.mem_speaking_time || people.mem_current_speaking_time) {
		speakingStats.speakingTimePerPerson[people.mem_nickname] = people.mem_speaking_time + people.mem_current_speaking_time;
	}

	if (people.mem_speaking) {
		var speakerLabel = $(".speaker-container[data-id=" + people.mem_id + "]");
		
		if (speakerLabel.length) {
			speakerLabel.find(".speaking-time").text(people.mem_current_speaking_time_string);
		}
		else {
			speakerLabel = $("div[data-template-id=speaker]").template("use", {data: people});
			$("#speaker-label").after(speakerLabel);
		}

		speakerLabel.removeClass("to-deleted");
	}	
}

function getSpeakingUserId() {
	return $(".meeting").data("speaking-id");
}

function updatePeople() {
	var meetingId = $(".meeting").data("id");

//	console.log("Do get people @ " + new Date());

	$.get("meeting_api.php?method=do_getPeople", {id: meetingId}, function(data) {
//		console.log("Get people @ " + new Date());

		var numberOfPresents = $("#speaking-panel .number-of-presents");
		if (numberOfPresents.eq(0).text() != ("" + data["numberOfPresents"])) {
			numberOfPresents.text(data["numberOfPresents"]);
		}

		var numberOfVoters = $("#speaking-panel .number-of-voters");
		if (numberOfVoters.eq(0).text() != ("" + data["numberOfVoters"])) {
			numberOfVoters.text(data["numberOfVoters"]);
		}

		var quorum = $("#speaking-panel .quorum");
		if (quorum.eq(0).text() != ("" + data["mee_computed_quorum"])) {
			if (data["mee_computed_quorum"]) {
				$(".quorum-container").show();
				quorum.text(data["mee_computed_quorum"]);
				
				if (data["mee_computed_quorum"] <= data["numberOfVoters"]) {
					$(".quorum-container *").addClass("text-success").removeClass("text-danger");
				}
				else {
					$(".quorum-container *").addClass("text-danger").removeClass("text-success");
				}
			}
			else {
				$(".quorum-container").hide();
			}
		}

		$("#speaking-panel .speaker-container").addClass("to-deleted");
		
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

			updateSpeaking(people);

			if (!member.length) {
				member = getMemberLi(people);
				parent.append(member);
			}

			updateMemberLi(member, people);
			member.find(".voting").hide();
		}
		parent.children(".to-deleted").remove();

		$("#speaking-panel .speaker-container.to-deleted").remove();

		var speakers = $("#speaking-panel .speaker-container");

		if (speakers.length && hasWritingRight(getUserId())) {
			$(".btn-add-speaker-chat").show();
			$(".btn-add-speaker-chat").data("speaker-id", speakers.eq(0).data("id"));
			$(".btn-add-speaker-chat .speaker").text(speakers.eq(0).find(".speaker").text());
		}
		else {
			$(".btn-add-speaker-chat").hide();
		}

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

		if (hasWritingRight(userId) || userId == getSpeakingUserId()) {
			$(".btn-remove-speaker").show().removeAttr("disabled");
		}
		else {
			$(".btn-remove-speaker").hide().attr("disabled", "disabled");
		}

/*
		if ($(".speaker").text()) {
			$(".speaking-time span").show();
		}
		else {
			$(".speaking-time span").hide();
		}
*/

		var isPeopleNoticed = true;
		$("#noticed-people > ul > li").each(function() {
			isPeopleNoticed &= $(this).data("noticed");
		});

		var meeting = $(".meeting").data("json");
		if (!meeting) {
			meeting = {mee_status: ''};
		}

		if (((meeting.mee_status != "construction") && (meeting.mee_status != "waiting")) || isPeopleNoticed || !hasRight(getUserId(), "handle_notice")) {
			$("#noticed-people .panel-footer").hide();
		}
		else {
			$("#noticed-people .panel-footer").show();
			$("#noticed-people .panel-footer .btn-notice-people").removeClass("disabled");
		}

//		console.log("Toggle missing people @ " + new Date());

		toggleMissingPeople();

//		console.log("Handled people @ " + new Date());

		numberOfConnected = data.numberOfConnected;

		isPeopleReady = true;
		testMeetingReady();

		// Order person boxes
		$("select .voting, select .unknown, select .noticed, select .connected").each(function() {
			var optGroup = $(this);

			var options = optGroup.children();

			optGroup.html(options.sort(function (a, b) {
				var aText = a.text().removeAccent().toLowerCase();
				var bText = b.text().removeAccent().toLowerCase();
				
			    return aText == bText ? 0 : aText < bText ? -1 : 1;
			}));
		});

		if (!hasWritingRight(userId)) { // The user has not the right to manage the agenda
			$("#meeting-agenda ul").sortable("disable");
		}
		else if ($(".btn-agenda-mode").hasClass("btn-warning")) { // The manager mode is view only
			$("#meeting-agenda ul").sortable("disable");
		}
		else {
			$("#meeting-agenda ul").sortable("enable");
		}

	}, "json");
}

function ping() {
	var meetingId = $(".meeting").data("id");
	$.post("meeting_api.php?method=do_ping", {id: meetingId}, function(data) {
	}, "json");
}

function setSpeaking(event) {
	event.preventDefault();

	if (!hasWritingRight(getUserId())) return;

	var button = $(this);

	button.addClass("disabled");

	var meetingId = $(".meeting").data("id");
	var userId = $(this).data("id");

	$.get("meeting_api.php?method=do_setSpeaking", {meetingId: meetingId, userId: userId, speakingTime: speakingTime}, function(data) {
		button.removeClass("disabled");
		speakingTime = 0;
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

function changeOffice(select) {
	var type = select.data("type");
	var memberId = select.val();
	var meetingId = $(".meeting").data("id");
	var userId = $(".meeting").data("user-id");

	if (hasWritingRight(userId)) {
		$.post("meeting_api.php?method=do_changeOfficeMember", {meetingId: meetingId, memberId: memberId, type: type}, function(data) {
			select.parents(".president, .secretary").find(".read-data").text(select.find("option:selected").text());
		}, "json");
	}
}

function addPresidentHandlers() {
	$(".president select, .secretary select").hide();
	$(".president, .secretary").hover(function() {
		if (hasWritingRight($(".meeting").data("user-id")) && $(this).find(".read-data").is(":visible")) {
			$(this).find(".update-btn").show();
		}
	}, function() {
		$(this).find(".update-btn").hide();
	});
	$(".president select, .secretary select").change(function(e) {
		changeOffice($(this));
		$(this).parents(".president, .secretary").find("select").hide();
		$(this).parents(".president, .secretary").find(".read-data").show();
		$(this).parents(".president, .secretary").find(".cancel-btn").hide();
	});
	$(".president, .secretary").find(".update-btn").click(function() {
		$(this).parents(".president, .secretary").find(".read-data").hide();
		$(this).parents(".president, .secretary").find(".update-btn").hide();
		$(this).parents(".president, .secretary").find("select").show();
		$(this).parents(".president, .secretary").find(".cancel-btn").show();
	});
	$(".president, .secretary").find(".cancel-btn").click(function() {
		$(this).parents(".president, .secretary").find("select").hide();
		$(this).parents(".president, .secretary").find(".read-data").show();
		$(this).parents(".president, .secretary").find(".cancel-btn").hide();
		$(this).parents(".president, .secretary").find(".update-btn").show();
	});
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
	$(".meeting .row").on("click", "button.set-speaking", setSpeaking);

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
		        title: meeting_notification,
		        message: dialog,
		        buttons: {
		            success: {
		                label: common_edit,
		                className: "btn-primary",
		                callback: function () {
		                		var dialog = $(this);

		                		var form = dialog.find("form");

		                		$.post("meeting_api.php?method=do_changeNotice", form.serialize(), function(data) {
		                		}, "json");
		                    }
		                },
		            close: {
		                label: common_close,
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
		                label: common_close,
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
		bootbox.confirm(meeting_notificationDelete + " \"" + text + "\" ?", function(result) {
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

var speakingTime = 0;


function computeTimeString(time) {
	var seconds = time % 60;
	var minutes = (time - seconds) / 60

	seconds = (seconds < 10 ? "0" : "") + seconds;
	minutes = (minutes < 10 ? "0" : "") + minutes;

	return minutes + ":" + seconds;
}

function addSpeakerHandlers() {
	$("body").on("click", ".btn-remove-speaker", function(event) {
		$(this).attr("disabled", "disabled");

		var meetingId = $(".meeting").data("id");
		var speakerId = $(this).parents(".speaker-container").data("id");

		$.post("meeting_api.php?method=do_removeSpeaker", {meetingId: meetingId, speakerId : speakerId}, function(data) {
		}, "json");
	});
}

$(function() {
	$(".speaking-requesters").on("click", "button", setSpeaking);

	addPresidentHandlers();
	addNoticeHandlers();
	addSpeakerHandlers();

	ping();
	updatePeople();

	// TODO
	$("#videoDock").hide();
});

var lastEventTimestamp = 0;

function computeEventPositions() {
	var margin = 5;
	var currentPosition = 60;

	$(".congressus-event").each(function() {
		var eventAlert = $(this);

		eventAlert.css({"bottom" : currentPosition + "px"});

		currentPosition += (eventAlert.height() + margin + 16);

	});
}

function getEventText(event) {
	if (event.text) return event.text;
	
	var text = "";
	
	if (event.type == "speak_request") {
		var userNickname = $("li#member-"+event.options.userId+" .member-nickname").eq(0).text();
		text = "<i><b>" + userNickname + "</b></i> demande la parole";
	}
	else if (event.type == "speak_set") {
		var userNickname = $("li#member-"+event.options.userId+" .member-nickname").eq(0).text();
		text = "<i><b>" + userNickname + "</b></i> a la parole";
	}
	else if (event.type == "speak_renounce") {
		var userNickname = $("li#member-"+event.options.userId+" .member-nickname").eq(0).text();
		text = "<i><b>" + userNickname + "</b></i> a renoncé à prendre la parole";
	}
	else if (event.type == "join") {
		var userNickname = $("li#member-"+event.options.userId+" .member-nickname").eq(0).text();
		if (userNickname) {
			text = "<i><b>" + userNickname + "</b></i> vient d'arriver à la réunion";
		}
		else {
			text = "<i><b>Guest</b></i> vient d'arriver à la réunion";
		}
	}
	else if (event.type == "left") {
		var userNickname = $("li#member-"+event.options.userId+" .member-nickname").eq(0).text();
		if (userNickname) {
			text = "<i><b>" + userNickname + "</b></i> vient de sortir de la réunion";
		}
		else {
			text = "<i><b>Guest</b></i> vient de sortir de la réunion";
		}
	}
	
	return text;
}

function showEvent(event) {
	
	var eventClass = "success";
	
	switch(event.type) {
		case "motion_add":
		case "motion_to_vote":
		case "join": 
			eventClass = "success";
			break;
		case "motion_remove": 
		case "motion_close_vote":
		case "left": 
			eventClass = "danger";
			break;
		case "speak_renounce": 
			eventClass = "warning";
			break;
		default:
			eventClass = "info";
			break;
	}
	
	var text = getEventText(event);
	
	var eventAlert = $("<p style='width: 350px; height: 55px; z-index: 1000; position: fixed; right: 10px;' class='congressus-event form-alert simply-hidden bg-" + eventClass + "'>" + text + "</p>");
	var body = $("body");
	body.append(eventAlert);

	computeEventPositions();

	eventAlert.show().delay(5000).fadeOut(1000, function() {
		$(this).remove();
		computeEventPositions();
	});
}

function getEvents() {
	var meetingId = $(".meeting").data("id");

	$.post("meeting_api.php?method=do_getEvents", {meetingId : meetingId}, function(data) {
		if (data.ok) {
			for(var index = 0; index < data.events.length; ++index) {
				var event = data.events[index];
				
				if (event.timestamp <= lastEventTimestamp) continue;
				
				showEvent(event);
			}
			
			if (data.events.length > 0) {
				lastEventTimestamp = data.events[data.events.length - 1].timestamp;
			}
		}
	}, "json");
}

$(function() {
	var getEventsTimer = $.timer(getEvents);
	getEventsTimer.set({ time : 1000, autostart : true });

	var getAgendaPointTimer = $.timer(updateAgendaPoint);
	getAgendaPointTimer.set({ time : 1000, autostart : true });
});
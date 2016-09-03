function isDateValid(str) {
	var d = moment(str,'YYYY-MM-DD');
	if(d == null || !d.isValid()) return false;

	return true;
}

function isTimeValid(str) {
	var d = moment(str,'HH:mm');
	if(d == null || !d.isValid()) return false;

	return true;
}


$(function() {
	
	$("#create-meeting-form").submit(function(event) {
		
		var errorCount = 0;
		
		errorCount += isDateValid($("#mee_date").val()) ? 0 : 1;
		errorCount += isTimeValid($("#mee_time").val()) ? 0 : 1;
		
		if (errorCount) {
			
			$("#date-time-error-alert").show().delay(5000).fadeOut(1000, function() {
			});
			
			event.preventDefault();
		}
	})
	
});
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
/* global moment */

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
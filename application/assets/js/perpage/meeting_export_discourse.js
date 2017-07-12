/*
	Copyright 2017 Nino Treyssat-Vincent, Parti Pirate

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
$( "#discourseSubmit" ).click(function( event ) {
  event.preventDefault();

  var $form = $( this ),
    discourse_title = $('input[name="discourse_title"]').val(),
    discourse_category = $('select[name="discourse_category"]').val(),
    meetingId = meeting_id;
    url = "meeting_api.php?method=do_discoursePost";

  var posting = $.post( url, { discourse_title: discourse_title, discourse_category: discourse_category, meetingId: meetingId } );

	posting.done(function( data ) {
		var content = $(data);
		$("#result").empty().append(content);
	});
});

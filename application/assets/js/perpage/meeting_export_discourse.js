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

function loadExport(format, meeting_id){
  exportModal = $('#export_container_' + format);
	exportClose = $('#export_close_' + format);
  $('#iframe_' + format).attr("src", "meeting/do_export.php?template=" + format + "&id=" + meeting_id);
  exportModal.show();
}
function closeExport(exportModal){
  $('#iframe').attr("src", "");
  exportModal.hide();
}

$('.btnShowExport').click(function(event){
	switch (event.target.id) {
    case 'btnShowExport_html':
			loadExport("html", meeting_id);
			break;
    case 'btnShowExport_pdf':
			loadExport("pdf", meeting_id);
			break;
    case 'btnShowExport_markdown':
			loadExport("markdown", meeting_id);
			break;
		}
});

// closed by <span>X
$('.exportClose').click(function(){
closeExport(exportModal);
});
// closed by esc
$(document).keyup(function(e) {
  if (e.keyCode == 27) {
    closeExport(exportModal);
  }
});
// closed by click outside of modal
$(window).on('click', function(event){
    if(event.target.id.slice(0,16) == "export_container"){
        closeExport(exportModal);
    }
});

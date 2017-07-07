function loadExport(format, meeting_id, textarea){
  exportModal = $('#export_container_' + format);
	exportClose = $('#export_close_' + format);
  $('#iframe_' + format).attr("src", "meeting/do_export.php?template=" + format + "&id=" + meeting_id + "&textarea=" + textarea);
  if(format=='html'){$('#newpage').attr("href", $('#iframe_' + format).attr("src"));}
  exportModal.show();
}
function closeExport(exportModal){
  $('#iframe').attr("src", "");
  exportModal.hide();
}

$('.btnShowExport').click(function(){
  format = $(this).data("format");
  if (format=='markdown'){
    textarea = 'true';
  } else {
    textarea = 'false';
  }
  if (format=='html'){
    $('#rendering').addClass('btn-active');
    $('#html-code').removeClass('btn-active');
  }
  if (format=='html-code'){
    textarea = 'true';
    format = 'html';
    $('#html-code').addClass('btn-active');
    $('#rendering').removeClass('btn-active');
  }
  loadExport(format, meeting_id, textarea);
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

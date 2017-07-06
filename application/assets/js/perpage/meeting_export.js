$('.btnShowExport').click(function(event){
	switch (event.target.id) {
    case 'btnShowExport_html':
			exportModal = $('#exportHtml');
			exportClose = $('#export_close_html');
			exportModal.show();
			break;
    case 'btnShowExport_pdf':
			exportModal = $('#exportPdf');
			exportClose = $('#export_close_pdf');
			exportModal.show();
			break;
    case 'btnShowExport_markdown':
			exportModal = $('#exportMarkdown');
			exportClose = $('#export_close_markdown');
			exportModal.show();
			break;
		}
});
// closed by <span>X
$('.exportClose').click(function(){
exportModal.hide();
});
// closed by esc
$(document).keyup(function(e) {
  if (e.keyCode == 27) {
    exportModal.hide();
  }
});

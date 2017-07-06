// default values :
var modal = document.getElementById('exportHtml');
var span = document.getElementById("export_close_html");

function showExport(format){
  switch(format) {
    case 'html':
        modal = document.getElementById('exportHtml');
        span = document.getElementById("export_close_html");
        break;
    case 'pdf':
        modal = document.getElementById('exportPdf');
        span = document.getElementById("export_close_pdf");
        break;
    case 'markdown':
        modal = document.getElementById('exportMarkdown');
        span = document.getElementById("export_close_markdown");
    }
    modal.style.display = "block";
}

// closed by <span>X
span.onclick = function() {
    modal.style.display = "none";
};

// closed by click outside of modal
window.onclick = function(event) {
    if (event.target == modal) {
        modal.style.display = "none";
    }
};

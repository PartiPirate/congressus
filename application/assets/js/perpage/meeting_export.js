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
function loadExport(format, meeting_id, textarea){
  exportModal = $('#export_container_' + format);
  $('#iframe_' + format).attr("src", "meeting/do_export.php?template=" + format + "&id=" + meeting_id + "&textarea=" + textarea);
  if(format=='html'){$('#newpage_html').attr("href", $('#iframe_' + format).attr("src"));}
  exportModal.show();
}
function closeExport(exportModal){
  $('#iframe').attr("src", "");
  exportModal.hide();
}

$('.btnShowExport').click(function(){
  format = $(this).data("format");
  if (format=='markdown' || format=='discourse' || format=='html-code'){
    textarea = 'true';
  } else {
    textarea = 'false';
  }
  if (format=='html'){
    $('#rendering').addClass('btn-active');
    $('#html-code').removeClass('btn-active');
  }
  if (format=='html-code'){
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

<?php /*
    Copyright 2015-2020 Cédric Levieux, Parti Pirate

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
    along with Congressus.  If not, see <https://www.gnu.org/licenses/>.
*/
?>

<?php
    include("connect_modal.php");
?>


<div class="watermark">
    <div class="watermark-inside"></div>    
</div>

<nav id="footer" class="navbar navbar-inverse navbar-bottom" role="navigation">

	<ul class="nav navbar-nav">
		<li <?php if ($page == "about") echo 'class="active"'; ?>><a href="about.php"><?php echo lang("about_footer"); ?></a></li>
	</ul>
	<p class="navbar-text pull-right"><?php echo lang("congressus_footer"); ?></p>
</nav>
<!-- Include all compiled plugins (below), or include individual files as needed -->
<script>
	var sessionLanguage = "<?php echo $language; ?>";
	var fullDateFormat = "<?php echo lang("fulldate_format"); ?>";
</script>
<!--
<script src="assets/js/min.js.php"></script>
-->

<script src="assets/js/bootstrap.js"></script>
<script src="assets/js/bootstrap-markdown.js"></script>
<script src="assets/js/underscore-min.js"></script>
<script src="assets/js/calendar.min.js"></script>
<script src="assets/js/Chart.min.js"></script>
<script src="assets/js/language/fr-FR.js"></script>
<script src="assets/js/bootbox.min.js"></script>
<script src="assets/js/moment-with-locales.js"></script>
<script src="assets/js/bootstrap-datetimepicker.js"></script>
<script src="assets/js/bootstrap-toggle.min.js"></script>
<script src="assets/js/jquery-ui.min.js"></script>
<script src="assets/js/jquery.timer.js"></script>
<script src="assets/js/jquery.scrollTo.min.js"></script>
<script src="assets/js/jquery.template.js"></script>
<script src="assets/js/bootstrap-treeview.js"></script>
<script src="assets/js/strings.js"></script>
<script src="assets/js/user.js"></script>
<script src="assets/js/window.js"></script>
<script src="assets/js/editor.js"></script>
<script src="assets/js/search.js"></script>
<script src="assets/js/simplediff.js"></script>
<script src="assets/js/showdown.js"></script>
<script src="assets/js/autogrow.js"></script>
<script src="assets/js/emojione.min.js"></script>
<script src="assets/js/emojione.helper.js"></script>
<script src="assets/js/social.js"></script>
<script src="assets/js/events.js"></script>

<!-- JS pad -->
<script>
var PAD_WS = <?=json_encode(isset($config["server"]["pad"]["ws_url"]) ? $config["server"]["pad"]["ws_url"] : "")?>;
var disconnectedAlertId = null;

const alert_disconnected = <?=json_encode(lang("alert_disconnected"))?>;
const alert_connected    = <?=json_encode(lang("alert_connected"))?>;

function showConnectInlineModal() {
    $("#connect-modal .error-div").hide();
    $("#connect-modal").modal("show");
}

function reconnectShowAlert() {
    if (disconnectedAlertId) {
        removeEventAlert(disconnectedAlertId);
        disconnectedAlertId = null;
    }
    addEventAlert(alert_connected, "success", 5000);
}

$(function() {
    
	var nopTimer = $.timer(function() { $.get("nop.php", function(data) {
	    if (!data.user && $(".logoutLink").length == 2 && !disconnectedAlertId)  {
	        disconnectedAlertId = addEventAlert(alert_disconnected, "danger");
	    }
	    else if (data.user && disconnectedAlertId) {
	        reconnectShowAlert();
	    }
	}, "json"); });
	nopTimer.set({ time : 60000, autostart : true });

    $("#connect-modal .btn-connect-inline").click(function() {
        $("#connect-modal .error-div").hide();
        $.post("do_login.php", $("#connect-modal form").serialize(), function(data) {
            if (data.ok) {
                $("#connect-modal").modal("hide");
    	        reconnectShowAlert();
            }
            else {
                // show error
                $("#connect-modal .error-div").show();
            }
        }, "json");
    });
    
    $("body").on("click", ".form-alert .show-connect-modal-link", function(event) {
        event.preventDefault();
        showConnectInlineModal();
    });
});

</script>
<script src="assets/js/caret.js"></script>
<script src="assets/js/merger.js"></script>
<script src="assets/js/pad.js"></script>

<!--
<script src="assets/js/pagination.js"></script>
 -->

<!-- <?php echo "js/perpage/" . $page . ".js"; ?> -->
<?php
if (is_file("assets/js/perpage/" . $page . ".js")) {
	echo "<script src=\"assets/js/perpage/" . $page . ".js?r=".filemtime("assets/js/perpage/" . $page . ".js")."\"></script>\n";
}
?>

<script>
$(function() {
    const spacing = '10px'; 
    const styles = `padding: ${spacing}; background-color: #222222; color: #8814CC; font-weight: bold; border: 1px solid #222222; font-size: 2em;`; 
    console.log(<?=json_encode(lang("console_congressus_welcome"))?>, styles);
});
</script>

<!--
<style>
.gaypride {
    background-image: url(assets/images/gayflag.svg) !important; 
    background-size: unset; 
    background-repeat: repeat-x; 
    background-attachment: fixed;
    opacity: 0.15;
    position: absolute;
    top:0;
    left:0;
    right:0;
    bottom:0;
    z-index: -1;
}

.d-header {
    background-color: #ff00;
}    
</style>

<div class="gaypride"></div>
-->

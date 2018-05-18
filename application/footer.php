<?php /*
	Copyright 2014-2015 Cédric Levieux, Jérémy Collot, ArmagNet

	This file is part of OpenTweetBar.

    OpenTweetBar is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    OpenTweetBar is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with OpenTweetBar.  If not, see <http://www.gnu.org/licenses/>.
*/
?>
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
<script src="assets/js/canvasjs.min.js"></script>
<script src="assets/js/jquery.canvasjs.min.js"></script>
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

<!--
<script src="assets/js/pagination.js"></script>
 -->

<!-- <?php echo "js/perpage/" . $page . ".js"; ?> -->
<?php
if (is_file("assets/js/perpage/" . $page . ".js")) {
	echo "<script src=\"assets/js/perpage/" . $page . ".js\"></script>\n";
}
?>
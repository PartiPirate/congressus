<?php /*
    Copyright 2018-2019 Cédric Levieux, Parti Pirate

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
$page = "about";
include_once("header.php");
?>
<div class="container theme-showcase" role="main">
	<ol class="breadcrumb">
		<li><?php echo lang("breadcrumb_index"); ?></li>
		<li class="active"><?php echo lang("breadcrumb_about"); ?></li>
	</ol>

	<div class="col-md-12">
		<div class="panel panel-default">
			<div class="panel-heading"><?php echo lang("about_what_s_congressus_legend"); ?></div>
			<div class="panel-body"><?php echo lang("about_what_s_congressus_content"); ?></div>
		</div>
	</div>

	<div class="col-md-6">
		<div class="panel panel-default">
			<div class="panel-heading" id="helpus"><?php echo lang("about_help_us_legend"); ?></div>
			<div class="panel-body"><?php echo lang("about_help_us_content"); ?></div>
		</div>
	</div>

	<div class="col-md-6">
		<div class="panel panel-default">
			<div class="panel-heading"><?php echo lang("about_need_help_legend"); ?></div>
			<div class="panel-body"><?php echo lang("about_need_help_content"); ?></div>
		</div>
	</div>

	<div class="col-md-12">
		<div class="panel panel-default">
			<div class="panel-heading" id="contactus"><?php echo lang("about_contact_us_legend"); ?></div>
			<div class="panel-body"><?php echo lang("about_contact_us_content"); ?></div>
			<ul class="list-group">
				<li class="list-group-item"><a class="social grey twitter" href="https://www.twitter.com/@PartiPirate" target="_blank">@PartiPirate</a><span class="badge"><a class="color-inherit" href="https://www.twitter.com/@PartiPirate" target="_blank"><span class="glyphicon glyphicon-chevron-right"></span></a></span></li>
				<li class="list-group-item"><a class="social grey e-mail" href="mailto://contact[@]partipirate[.]fr" target="_blank">contact[@]partipirate[.]fr</a><span class="badge"><a class="color-inherit" href="mailto://contact[@]partipirate[.]fr" target="_blank"><span class="glyphicon glyphicon-chevron-right"></span></a></span></li>
			</ul>
		</div>
	</div>

	<div class="col-md-12">
		<div class="panel panel-default">
			<div class="panel-heading" id="releases"><?php echo lang("about_releases_legend"); ?></div>
			<div class="panel-body"><?php echo lang("about_releases_content"); ?></div>
			<ul class="list-group">
				<li class="list-group-item"><a href="https://github.com/PartiPirate/congressus"
						target="_blank">Github Repository</a><span class="badge"><a class="color-inherit"
						href="https://github.com/PartiPirate/congressus" target="_blank"><span
						class="glyphicon glyphicon-chevron-right"></span></a></span></li>
			</ul>
		</div>
	</div>
</div>

<div class="lastDiv"></div>

<?php include("footer.php");?>
<script>
$(function() {
	$(".panel").hover(function() {
					$(this).removeClass("panel-default");
					$(this).addClass("panel-success");
					$(this).find(".panel-body").addClass("text-success");
				}, function() {
					$(this).addClass("panel-default");
					$(this).removeClass("panel-success");
					$(this).find(".panel-body").removeClass("text-success");
				});
});
</script>
</body>
</html>
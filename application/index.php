<?php /*
	Copyright 2015 Cédric Levieux, Parti Pirate

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
include_once("header.php");

//include_once("groups.php");

?>

<div class="container theme-showcase" role="main">
	<ol class="breadcrumb">
		<li class="active"><?php echo lang("breadcrumb_index"); ?></li>
	</ol>

	<div class="well well-sm">
		<p><?php echo lang("index_guide"); ?></p>
	</div>

	<div class="calendar-nav clearfix">
		<div class="pull-right form-inline" style="margin-top: 15px;">
			<div class="btn-group">
				<button class="btn btn-primary" data-calendar-nav="prev">&lt;&lt; Précédent</button>
				<button class="btn btn-default" data-calendar-nav="today">Aujourd'hui</button>
				<button class="btn btn-primary" data-calendar-nav="next">Suivant &gt;&gt;</button>
			</div>
			<div class="btn-group">
				<button class="btn btn-warning" data-calendar-view="year">Année</button>
				<button class="btn btn-warning active" data-calendar-view="month">Mois</button>
				<button class="btn btn-warning" data-calendar-view="week">Semaine</button>
				<button class="btn btn-warning" data-calendar-view="day">Jour</button>
			</div>
		</div>
		<h3>&nbsp;</h3>
	</div>

	<br />

	<div id="calendar"></div>


<?php 	if ($isConnected) {?>


<?php 	} else {?>


<?php 	}?>

<?php include("connect_button.php"); ?>

</div>

<div class="lastDiv"></div>

<?php include("footer.php");?>
<script type="text/javascript">
$(function() {
	var calendar = $("#calendar").calendar(
            {
            	language: "fr-FR",
                tmpl_path: "tmpls/",
                events_source: "do_getMeetings.php",
                time_end: "23:30",
                onAfterViewLoad: function(view) {
        			$('.calendar-nav h3').text(this.getTitle());
        			$('.btn-group button').removeClass('active');
        			$('button[data-calendar-view="' + view + '"]').addClass('active');
        		},
            });

	$('.btn-group button[data-calendar-nav]').each(function() {
		var $this = $(this);
		$this.click(function() {
			calendar.navigate($this.data('calendar-nav'));
		});
	});

	$('.btn-group button[data-calendar-view]').each(function() {
		var $this = $(this);
		$this.click(function() {
			calendar.view($this.data('calendar-view'));
		});
	});

});
</script>

</body>
</html>
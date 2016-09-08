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

require_once("engine/bo/MeetingBo.php");
require_once("engine/bo/GuestBo.php");

$meetingBo = MeetingBo::newInstance($connection);

$meeting = $meetingBo->getById($_REQUEST["id"]);

if (!$meeting) {
	// Ask for creation
	$meeting = array("mee_label" => "Nouvel événement");
}
else {
	$start = new DateTime($meeting["mee_datetime"]);
	$end = new DateTime($meeting["mee_datetime"]);
	$duration = new DateInterval("PT" . ($meeting["mee_expected_duration"] ? $meeting["mee_expected_duration"] : 60) . "M");
	$end = $end->add($duration);
}

$userId = SessionUtils::getUserId($_SESSION);
if (!$userId) {
	if (!isset($_SESSION["guestId"])) {
		$guestBo = GuestBo::newInstance($connection);
		// Create guestId
		$guest = array();
		$guestBo->save($guest);

		$guestId = $guest[$guestBo->ID_FIELD];
		$nickname = "Guest $guestId";

		$_SESSION["guestId"] = $guestId;
		$_SESSION["guestNickname"] = $nickname;
	}
	$guestId = $_SESSION["guestId"];
}

?>

<div class="container theme-showcase meeting" role="main"
	data-id="<?php echo @$meeting[$meetingBo->ID_FIELD]; ?>"
	data-user-id="<?php echo $userId ? $userId : "G" . $guestId; ?>"
	>
	<ol class="breadcrumb">
		<li><a href="index.php"><?php echo lang("breadcrumb_index"); ?></a></li>
		<li class="active"><?php echo $meeting["mee_label"]; ?></li>
	</ol>

	<div class="row" style="margin-bottom: 5px;">
		<div class="col-md-6" style="/*padding-top: 7px; padding-bottom: 7px;*/">
			<span class="glyphicon glyphicon-time"></span> Date de début :
			<span class="mee_start datetime-control">
				le
				<span class="date-control">
					<span class="span-date"><?php echo @$start->format(lang("date_format"))?></span>
					<input style="display:none; height: 20px;" class="input-date" type="date" value="<?php echo @$start->format("Y-m-d"); ?>" />
				</span>
				à
				<span class="time-control">
					<span class="span-time"><?php echo @$start->format(lang("time_format"))?></span>
					<input style="display:none; height: 20px;" class="input-time" type="time" value="<?php echo @$start->format("H:i"); ?>" />
				</span>
			</span>
		</div>
		<div class="col-md-6" style="/*padding-top: 7px; padding-bottom: 7px;*/">
			<span class="glyphicon glyphicon-time"></span> Date de fin :
			<span class="mee_finish datetime-control">
				le
				<span class="date-control">
					<span class="span-date"><?php echo @$end->format(lang("date_format"))?></span>
					<input style="display:none; height: 20px;" class="input-date" type="date" value="<?php echo @$end->format("Y-m-d"); ?>" />
				</span>
				à
				<span class="time-control">
					<span class="span-time"><?php echo @$end->format(lang("time_format"))?></span>
					<input style="display:none; height: 20px;" class="input-time" type="time" value="<?php echo @$end->format("H:i"); ?>" />
				</span>
			</span>
		</div>
		<div class="col-md-6 president">
			<span class="fa fa-graduation-cap" style="margin-top: 10px;"></span> Président de séance :
			<span class="mee_president_member_id read-data" data-id="0"></span>
			<select class="form-control" data-type="president">
				<optgroup class="voting" label="Votants"></optgroup>
				<optgroup class="noticed" label="Convoqués"></optgroup>
				<optgroup class="connected" label="Connectés"></optgroup>
				<optgroup class="unknown" label="Inconnus"></optgroup>
			</select>
		</div>
		<div class="col-md-6 secretary">
			<span class="fa fa-user" style="margin-top: 10px;"></span> Secrétaire de séance :
			<span class="mee_secretary_member_id read-data" data-id="0"></span>
			<select class="form-control" data-type="secretary">
				<optgroup class="voting" label="Votants"></optgroup>
				<optgroup class="noticed" label="Convoqués"></optgroup>
				<optgroup class="connected" label="Connectés"></optgroup>
				<optgroup class="unknown" label="Inconnus"></optgroup>
			</select>
		</div>
	</div>

	<div class="row president-panels" style="margin-bottom: 5px; ">
		<div class="col-md-8">
			<div id="speaking-panel" class="panel panel-default">
				<div class="panel-heading">
					Gestion de la parole
				</div>
				<div class="panel-body">
					<div class="row form-horizontal">
						<label class="control-label col-md-3">A la parole : </label>
						<label class="control-label col-md-9 speaker" style="text-align: left;"></label>
					</div>
					<div class="row form-horizontal">
						<label class="control-label col-md-3">Demande la parole : </label>
						<div class="col-md-9 speaking-requesters">
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="col-md-4">
			<div id="meeting-status-panel" class="panel panel-default">
				<div class="panel-heading">
					Action sur la réunion
				</div>
				<div class="panel-body text-center">

					<button class="btn btn-primary btn-waiting-meeting simply-hidden">Prête</button>
					<button class="btn btn-danger btn-delete-meeting simply-hidden">Supprimer</button>
					<button class="btn btn-success btn-open-meeting simply-hidden">Ouvrir la séance</button>
					<button class="btn btn-danger btn-close-meeting simply-hidden">Clore la séance</button>
					<button class="btn btn-default request-speaking">Demander la parole
						<span class="fa fa-hand-paper-o"></span>
						<span class="badge" style="display: none;"></span>
					</button>
					<br />
					<button class="btn btn-default btn-local-anonymous" data-toggle="tooltip" data-placement="bottom"
						title="Se cacher les votes jusqu'à la résolution">J'ai peur d'être influencé <span class="fa fa-archive"></span>
					</button>
					<br />
					<span class="closed-meeting simply-hidden">Séance fermée</span>
					<br class="export-br simply-hidden">
					<a href="meeting/do_export.php?template=html&id=<?php echo $_REQUEST["id"]; ?>" target="_blank" class="export-link export-html simply-hidden">Export HTML</a>
					<a href="meeting/do_export.php?template=pdf&id=<?php echo $_REQUEST["id"]; ?>" target="_blank" class="export-link export-pdf simply-hidden">Export PDF</a>
					<a href="meeting/do_export.php?template=markdown&id=<?php echo $_REQUEST["id"]; ?>" target="_blank" class="export-link export-markdown simply-hidden">Export Markdown</a>
				</div>
			</div>
		</div>
	</div>

	<div class="row">
		<div class="col-md-8" id="main-panel">
			<div id="agenda_point" class="panel panel-default" data-id="0" style="display: none;">
				<div class="panel-heading">
					<?php echo lang("meeting_agenda_point"); ?><span class="agenda-label"></span>
					<button class="btn btn-default btn-xs pull-right btn-next-point"
						title="Point suivant" data-toggle="tooltip" data-placement="bottom"
						style="display: none; margin-left: 5px;"><span class="glyphicon glyphicon-chevron-right"></span></button>
					<button class="btn btn-default btn-xs pull-right btn-previous-point"
						title="Point précédent" data-toggle="tooltip" data-placement="bottom"
						style="display: none; margin-left: 5px;"><span class="glyphicon glyphicon-chevron-left"></span></button>
				</div>
				<ul class="list-group objects">
				</ul>
				<div class="panel-footer">
					<button class="btn btn-default btn-xs btn-add-motion disabled">Motion <span class="fa fa-archive"></span></button>
					<!--
					<button class="btn btn-default btn-xs btn-add-task disabled">Tâche <span class="fa fa-tasks"></span></button>
					 -->
					<button class="btn btn-default btn-xs btn-add-chat disabled">Parole <span class="fa fa-comment"></span></button>
					<button class="btn btn-default btn-xs btn-add-conclusion disabled">Conclusion <span class="fa fa-lightbulb-o"></span></button>
				</div>
			</div>
		</div>
		<div class="col-md-4" id="right-panel">
			<div id="meeting_rights" class="panel panel-default" style="display: none;">
				<div class="panel-heading">
<!--  				
					<button class="btn btn-warning btn-xs pull-right btn-hide-missing"
						title="Montrer / cacher les absents" data-toggle="tooltip" data-placement="bottom"
						style="display: none; margin-left: 5px;"><span class="glyphicon glyphicon-eye-open"></span></button>
					<button class="btn btn-primary btn-xs pull-right btn-add-notice" style="display: none; margin-left: 5px;"><span class="glyphicon glyphicon-plus"></span></button>
-->					
					<a data-toggle="collapse" data-target="#meeting_rights_list" href="#"><?php echo lang("meeting_rights"); ?></a>
				</div>
				<ul class="list-group panel-collapse collapse in" id="meeting_rights_list">
					<li class="list-group-item"><label for="handle_notice_checkbox" class="right-label"><input type="checkbox" id="handle_notice_checkbox" value="handle_notice" class="right" /> Ajout/Suppression/Gestion des convoqués</label></li>
					<li class="list-group-item"><label for="handle_agenda_checkbox" class="right-label"><input type="checkbox" id="handle_agenda_checkbox" value="handle_agenda" class="right" /> Ajout/Suppression/Gestion des points de la réunion</label></li>
					<li class="list-group-item"><label for="handle_motion_checkbox" class="right-label"><input type="checkbox" id="handle_motion_checkbox" value="handle_motion" class="right" /> Ajout/Suppression/Gestion des motions</label></li>
					<li class="list-group-item"><label for="handle_conclusion_checkbox" class="right-label"><input type="checkbox" id="handle_conclusion_checkbox" value="handle_conclusion" class="right" /> Ajout/Suppression des conclusion</label></li>
				</ul>
			</div>
		
			<div id="noticed-people" class="panel panel-default">
				<div class="panel-heading">
					<button class="btn btn-warning btn-xs pull-right btn-hide-missing"
						title="Montrer / cacher les absents" data-toggle="tooltip" data-placement="bottom"
						style="display: none; margin-left: 5px;"><span class="glyphicon glyphicon-eye-open"></span></button>
					<button class="btn btn-primary btn-xs pull-right btn-add-notice" style="display: none; margin-left: 5px;"><span class="glyphicon glyphicon-plus"></span></button>
					<a data-toggle="collapse" data-target="#noticed-people-list" href="#"><?php echo lang("meeting_noticed_people"); ?></a>
				</div>
				<ul class="list-group panel-collapse collapse in" id="noticed-people-list">
				</ul>
				<div class="panel-footer" style="display: none;">
					<button class="btn btn-primary btn-xs btn-notice-people">Convoquer <span class="glyphicon glyphicon-envelope"></span></button>
				</div>
			</div>

			<div id="meeting-agenda" class="panel panel-default">
				<div class="panel-heading">
				<!--
					<button class="btn btn-danger btn-xs pull-right btn-remove-point" style="display: none; margin-left: 5px;"><span class="glyphicon glyphicon-remove"></span></button>
				 -->
					<button class="btn btn-primary btn-xs pull-right btn-add-point" style="display: none; margin-left: 5px;"><span class="glyphicon glyphicon-plus"></span></button>
					<a data-toggle="collapse" data-target="#agenda-points-list" href="#"><?php echo lang("meeting_agenda"); ?></a>
				</div>
				<ul class="list-group panel-collapse collapse in" id="agenda-points-list">
				</ul>
			</div>

			<div id="visitors" class="panel panel-default">
				<div class="panel-heading">
					<a data-toggle="collapse" data-target="#visitors-list" href="#"><?php echo lang("meeting_visitors"); ?></a>
				</div>
				<ul class="list-group panel-collapse collapse in" id="visitors-list">
				</ul>
			</div>

		</div>
	</div>

<?php include("connect_button.php"); ?>

</div>

<div class="lastDiv"></div>

<div id="videoDockPlaceholder" style="height: 130px; display: none;">
</div>

<div id="videoDock"
	class="panel"
	style="display: none; width: 100%; position: fixed; bottom: 0px; opacity: 0.75; box-shadow: 0px -10px 5px 0px #c0c0c0;">
	<div class="dock" style="height: 120px; width: 100%; "></div>
	<div class="reductor"
		style="height: 5px; width: 100%; background: #000000; cursor: n-resize;"></div>
</div>

<div class="container otbHidden">
</div>


<templates>
	<div data-template-id="proposition" id="proposition-${mpr_id}"
			class="template row proposition text-success" data-id="${mpr_id}"
			style="margin: 2px; min-height:22px; display: block;">
		<button class="btn btn-primary btn-xs pull-right btn-vote"
			title="Voter"
			style="display: none;">
			Vote <span class="glyphicon glyphicon-envelope"></span>
		</button>
		<span class="pull-left fa fa-cube"></span>
		<span class="pull-left proposition-label"></span>
		<button class="btn btn-danger btn-xs pull-right btn-remove-proposition"
			title="Supprimer la proposition"
			style="margin-right: 5px; display: none;">
			<span class="glyphicon glyphicon-remove"></span>
		</button>
		<span
			class="pull-right glyphicon glyphicon-pencil"
			title="Cliquer pour éditer"
			style="margin-right: 5px; display: none;"></span>
		<span class="pull-left powers"></span>
		<span class="pull-left"> : </span>
		<ul class="pull-left vote-container">
		</ul>
	</div>

	<form data-template-id="vote-form" action="" class="template form-horizontal">
		<fieldset>
			<div class="form-group">
				<label class="col-md-4 control-label" for="mpr_label">Proposition :</label>
				<div class="col-md-4">
					<input id="mpr_label" name="mpr_label"
						value="${mpr_label}"
						type="text" disabled="disabled" class="form-control input-md disabled">
				</div>
			</div>

			<!-- Text input-->
			<div class="form-group">
				<label class="col-md-4 control-label" for="vot_power">Pouvoir :</label>
				<div class="col-md-4">
					<input id="vot_power" name="vot_power" type="number"
						class="form-control input-md power" required=""
						value="${vot_power}" min="0" max="${vot_power}">
				</div>
			</div>
		</fieldset>
	</form>

	<ul>
		<li data-template-id="chat" id="chat-${cha_id}"
				class="template list-group-item chat" data-id="${cha_id}" style="display: block;">
			<button class="btn btn-danger btn-xs btn-remove-chat pull-right"
				title="Supprimer le chat"
				style="margin-right: 5px; display: none;">
				<span class="glyphicon glyphicon-remove"></span>
			</button>
			<span class="glyphicon glyphicon-pencil pull-right"
				title="Cliquer pour éditer"
				style="margin-right: 5px; display: none;"></span>
			<span class="fa fa-comment"></span>
			<span class="chat-member"><span class="chat-nickname"></span><select class="chat-select-member" style="display: none;">
				<optgroup class="voting" label="Votants"></optgroup>
				<optgroup class="noticed" label="Convoqués"></optgroup>
				<optgroup class="connected" label="Connectés"></optgroup>
				<optgroup class="unknown" label="Inconnus"></optgroup>
			</select> </span> 
			<span> : </span>
			<span class="chat-text"></span>
		</li>

		<li data-template-id="conclusion" id="conclusion-${con_id}"
				class="template list-group-item conclusion" data-id="${con_id}" style="display: block;">
			<button class="btn btn-danger btn-xs btn-remove-conclusion pull-right"
				title="Supprimer la conclusion"
				style="margin-right: 5px; display:none;">
				<span class="glyphicon glyphicon-remove"></span>
			</button>
			<span class="glyphicon glyphicon-pencil pull-right"
				title="Cliquer pour éditer"
				style="margin-right: 5px; display: none;"></span>
			<span class="fa fa-lightbulb-o"></span>
			<span class="conclusion-text"></span>
		</li>

		<li data-template-id="motion" id="motion-${mot_id}" data-id="${mot_id}" class="template list-group-item motion simply-hidden">
			<h4>
				<span class="fa fa-archive"></span>
				<span class="motion-title"></span>
				<span class="glyphicon glyphicon-pencil"
					title="Cliquer pour éditer"
					style="font-size: smaller; display: none;"></span>
			</h4>

			<div class="motion-description">

				<div class="motion-description-text"></div>
				<span class="glyphicon glyphicon-pencil"
					title="Cliquer pour éditer"
					style="font-size: smaller; display: none;"></span>
			</div>

			<div class="motion-propositions">
			</div>
			<div class="motion-actions">
				<button class="btn btn-primary btn-xs btn-add-proposition"
					title="Ajouter une proposition"
					style="display: none;">
					Proposition&nbsp;<span class="glyphicon glyphicon-plus"></span>
				</button>

				<div id="motionLimitsButtons" class="btn-group" role="group">
					<button value="0" type="button" style="display: none;" class="btn btn-default btn-xs btn-motion-limits btn-motion-limit-0">La meilleure</button>
					<button value="50" type="button" style="display: none;" class="btn btn-default btn-xs btn-motion-limits btn-motion-limit-50">Majorité simple</button>
					<button value="66" type="button" style="display: none;" class="btn btn-default btn-xs btn-motion-limits btn-motion-limit-66">Majorité 66%</button>
				</div>

				<button value="0" type="button" class="btn btn-default btn-xs btn-motion-anonymous">Vote anonyme</button>

				<button class="btn btn-success btn-xs btn-do-vote"
					title="Passer la motion au vote"
					style="display: none;">
					Passer au vote&nbsp;<span class="fa fa-archive"></span>
				</button>
				<button class="btn btn-danger btn-xs btn-remove-motion"
					title="Supprimer la motion"
					style="display: none;">
					Supprimer la motion&nbsp;<span class="glyphicon glyphicon-remove"></span>
				</button>
				<button class="btn btn-danger btn-xs btn-do-close"
					title="Fermer la motion au vote"
					style="display: none;">
					Fermer le vote&nbsp;<span class="fa fa-archive"></span>
				</button>
				
				<span class="simply-hidden voters badge pull-right">
					<span class="number-of-voters">XX</span> votants
				</span>
			</div>
		</li>

		<li data-template-id="vote" class="template vote"
			id="vote-${vot_id}" data-id="${vot_id}"
			data-proposition-id="${vot_motion_proposition_id}"
			data-member-id="${vot_member_id}">
			<span class="nickname"></span>
			<span
				title="Pouvoir du vote"
				class="badge power"></span>
		</li>

		<li data-template-id="agenda-point" id="agenda-${age_id}" class="template list-group-item"
				style="padding-top: 2px; padding-bottom: 2px;" data-id="${age_id}">
			<a class="agenda-link" style="margin: 0;" href="#" id="agenda-link-${age_id}" data-id="${age_id}"></a>
			<span class="fa fa-archive to-vote"
				title="Contient au moins une motion à voter"
				style="margin-right: 5px; display: none;"></span>
			<span class="glyphicon glyphicon-pencil"
				title="Cliquer pour éditer"
				style="margin-right: 5px; display: none;"></span>
			<button class="btn btn-primary btn-xs btn-add-point" data-parent-id="${age_id}"
				title="Ajouter un point sous ce point"
				style="margin-right: 5px; display: none;">
				<span class="glyphicon glyphicon-plus"></span>
			</button>
			<button class="btn btn-danger btn-xs btn-remove-point" data-id="${age_id}"
				title="Enlever ce point (ainsi que ces enfants)"
				style="display: none;">
				<span class="glyphicon glyphicon-remove"></span>
			</button>
			<ul class="list-group points" style="margin: 0;"></ul>
		</li>

		<li data-template-id="me-member"
			class="template list-group-item member"
			style="padding-top: 2px; padding-bottom: 2px;"
			id="member-${mem_id}" data-id="${mem_id}">
			<span class="member-nickname" style="margin-right: 5px;"></span>
			<span class="glyphicon glyphicon-pencil"
				title="Cliquer pour éditer"
				style="margin-right: 5px; display: none;"></span>
			<span class="fa fa-commenting-o"
				title="A la parole"
				style="display: none;"></span>
			<button
				title="Demander la parole"
				class="btn btn-default btn-xs request-speaking">
				<span class="fa fa-hand-paper-o"></span><span class="badge"
					style="display: none;">0</span>
			</button>
			<span class="fa fa-archive voting"
				title="Droit de vote"
				style="display: none;">
				<span style="margin-left: 5px;">
					<span class="power">1</span><span class="fa fa-ils"></span>
				</span>
			</span>
		</li>

		<li data-template-id="member"
			class="template list-group-item member"
			style="padding-top: 2px; padding-bottom: 2px;"
			id="member-${mem_id}" data-id="${mem_id}">
			<span class="member-nickname" style="margin-right: 5px;"></span>
			<span class="glyphicon glyphicon-pencil"
				title="Cliquer pour éditer"
				style="margin-right: 5px; display: none;"></span>
			<span class="fa fa-commenting-o"
				title="A la parole"
				style="display: none;"></span>
			<span class="fa fa-hand-paper-o btn-xs"
				title="Demande la parole"
				style="display: none;">
				<span class="badge">0</span>
			</span>
			<span class="fa fa-archive voting"
				title="Droit de vote"
				style="display: none;">
				<span style="margin-left: 5px;">
					<span class="power">10</span><span class="fa fa-ils"></span>
				</span>
			</span>
		</li>

	</ul>

</templates>

<div class="modal fade" tabindex="-1" role="dialog" id="start-meeting-modal">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
<!--
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
 -->
        <h4 class="modal-title">Démarrage...</h4>
      </div>
      <div class="modal-body">
        <p>La page est en cours de préparation</p>
      </div>
      <div class="modal-footer">
<!--
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary">Save changes</button>
 -->
      </div>
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<script>
</script>
<?php include("footer.php");?>
<script>
$("#start-meeting-modal").modal({
	  keyboard: false
//	  ,
//	  show: true
	});
$("#start-meeting-modal").modal("show");
</script>
<script src="assets/js/perpage/meeting_time.js"></script>
<script src="assets/js/perpage/meeting_agenda.js"></script>
<script src="assets/js/perpage/meeting_people.js"></script>
<script src="assets/js/perpage/meeting_motion.js"></script>
<script src="assets/js/perpage/meeting_events.js"></script>

<script type="text/javascript">

var isPeopleReady = false;
var isAgendaReady = false;

var initAgenda = function() {
	var hash = window.location.hash;

	if (!hash) return;

	hash = hash.substring(1);

	if (!hash) return;

	parts = hash.split("|");

	for(var i = 0; i < parts.length; i++) {
		var part = parts[i];
		var subs = part.split("-");

		if (subs.length != 2) continue;

		switch(subs[0]) {
			case "agenda":
				$("#" + part + " a").click();
				break;
		}
	}
};

var initObject = function() {
	var hash = window.location.hash;

	if (!hash) return;

	hash = hash.substring(1);

	if (!hash) return;

	parts = hash.split("|");

	for(var i = 0; i < parts.length; i++) {
		var part = parts[i];
		var subs = part.split("-");

		if (subs.length != 2) continue;

		switch(subs[0]) {
			case "chat":
			case "conclusion":
			case "motion":
			case "proposition":
			case "task":
				$("#agenda_point ul").scrollTo("#" + part, 800);
				break;
		}
	}
};

</script>

</body>
</html>
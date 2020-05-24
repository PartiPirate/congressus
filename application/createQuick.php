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
include_once("header.php");
include_once("config/memcache.php");

$groupKeyLabels = array();

foreach($config["modules"]["groupsources"] as $groupSourceKey) {
	$groupSource = GroupSourceFactory::getInstance($groupSourceKey);
	
	if ($groupSource->getGroupKeyLabel()) {
		$groupKeyLabels[] = $groupSource->getGroupKeyLabel();
	}
}

$meetingBo = MeetingBo::newInstance($connection, $config);
$memcache = openMemcacheConnection();

$memcacheKey = "createMeetingMeetings";
$json = $memcache->get($memcacheKey);

$meetingTypes = array(MeetingBo::TYPE_MEETING, MeetingBo::TYPE_CONSTRUCTION, MeetingBo::TYPE_GATHERING);

if (!$json || true) {
	$filters = array();
    $filters["with_status"] = array(MeetingBo::STATUS_OPEN, MeetingBo::STATUS_CLOSED, MeetingBo::STATUS_TEMPLATE);
	$meetings = $meetingBo->getByFilters($filters);
	
	$sortedMeetings = array("template_meeting" => array(), "template_construction" => array(), "template_gathering" => array(), "meeting" => array(), "construction" => array());
	foreach($meetings as $meeting) {
		$superType = ($meeting["mee_status"] == "template" ? "template_" : "") . $meeting["mee_type"];
		$sortedMeetings[$superType][] = $meeting;
	}
	
	$json = json_encode($sortedMeetings, JSON_NUMERIC_CHECK);

	if (!$memcache->replace($memcacheKey, $json, 60)) {
		$memcache->set($memcacheKey, $json, 60);
	}
}
else {
	$sortedMeetings = json_decode($json, true);
}

$templateId = isset($_REQUEST["templateId"]) ? $_REQUEST["templateId"] : -1;

?>

<div class="container theme-showcase meeting" role="main">
	<ol class="breadcrumb">
		<li><a href="index.php"><?=lang("breadcrumb_index")?></a></li>
		<li class="active"><?=lang("breadcrumb_createQuick")?></li>
	</ol>

	<form action="meeting/do_createMeeting.php" method="post" class="form-horizontal" id="create-meeting-form">


		<!-- Tab panes -->
	<div role="tabpanel" class="tab-pane active padding-top-5" id="info">

		<div class="form-group">
			<label for="mee_label" class="col-md-4 control-label"><?=lang("meeting_name")?> :</label>
			<div class="col-md-8">
				<input type="text" class="form-control input-md" id="mee_label" name="mee_label" />
			</div>
		</div>
		<div class="alert alert-danger simply-hidden" id="label-error-alert">
			<?=lang("createMeeting_labelError")?>
		</div>

		<div class="form-group">
			<label for="mot_description" class="col-md-4 control-label"><?=lang("createQuick_motion_description")?></label>
			<div class="col-md-8">
				<textarea class="form-control input-md" id="mot_description" name="mot_description"></textarea>
			</div>
		</div>

		<hr>

		<div class="form-group text-center">
			<div class="btn-group btn-motion-type" role="group">
				<button type="button" class="btn btn-default btn-propositions"><?=lang("createQuick_type_proposition")?></button>
				<button type="button" class="btn btn-default btn-dates"><?=lang("createQuick_type_date")?></button>
			</div>
		</div>

		<div class="well well-types"><?=lang("createQuick_types")?></div>

		<div class="form-group dates text-center" style="display: none; overflow-x: auto;">
			<table style="display: inline-block;">
				<thead>
					<tr>
						<td></td>
						<th><input type="date" class="form-control input-md" style="width: 160px;" placeholder="aaaa-mm-jj" /></th>
						<th><button type="button" class="btn btn-success btn-xs btn-add-date"><i class="fa fa-plus"></i></button></th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<th><input type="time" class="form-control input-md" style="width: 95px;" placeholder="hh:mm" /></th>
						<td class="text-center"><input type="checkbox"></td>
						<th><button type="button" class="btn btn-danger btn-xs btn-remove-time"><i class="fa fa-minus"></i></button></th>
					</tr>					
				</tbody>
				<tfoot>
					<tr>
						<th class="text-center"><button type="button" class="btn btn-success btn-xs btn-add-time"><i class="fa fa-plus"></i></button></th>
						<th class="text-center"><button type="button" class="btn btn-danger btn-xs btn-remove-date"><i class="fa fa-minus"></i></button></th>
						<td></td>
					</tr>
				</tfoot>
			</table>
		</div>

		<div class="form-group propositions text-center" style="display: none; overflow-x: auto;">
			<div class="proposition">
				<div class=" col-md-10" style="margin-bottom: 5px;">
					<input type="text" class="form-control input-md proposition-input" />
				</div>
				<div class=" col-md-2 text-right">
					<button type="button" class="btn btn-danger btn-remove-proposition"><i class="fa fa-minus"></i></button></th>
					<button type="button" class="btn btn-success btn-add-proposition"><i class="fa fa-plus"></i></button></th>
				</div>
			</div>
			<div class="proposition">
				<div class=" col-md-10" style="margin-bottom: 5px;">
					<input type="text" class="form-control input-md proposition-input" />
				</div>
				<div class=" col-md-2 text-right">
					<button type="button" class="btn btn-danger btn-remove-proposition"><i class="fa fa-minus"></i></button></th>
					<button type="button" class="btn btn-success btn-add-proposition"><i class="fa fa-plus"></i></button></th>
				</div>
			</div>
		</div>

		<div class="form-group text-center">
			<div id="motionLimitsButtons" class="btn-group" role="group">
<?php 		foreach($config["congressus"]["ballot_majorities"] as $majority) { ?>
				<button value="<?=$majority?>" type="button" 
					class="btn btn-default btn-xs btn-motion-limits btn-motion-limit-<?=$majority?> <?=($majority==-4 ? "active" : "")?>"><?=lang("motion_ballot_majority_$majority")?></button>
<?php 		}?>
			</div>
		</div>

		<div class="alert alert-danger simply-hidden" id="no-date-error-alert">
			<?=lang("createMeeting_dateError")?>
		</div>

		<div class="alert alert-danger simply-hidden" id="no-proposition-error-alert">
			<?=lang("createMeeting_propositionError")?>
		</div>

		<div class="alert alert-danger simply-hidden" id="no-type-error-alert">
			<?=lang("createMeeting_typeError")?>
		</div>

		<hr>

		<div class="form-group">
			<label for="mee_date" class="col-md-4 control-label"><?=lang("createQuick_end_datetime")?></label>
			<div class="col-md-2">
				<input type="date" class="form-control input-md"
					placeholder="aaaa-mm-jj" id="mee_end_date" name="mee_end_date" />
			</div>
			<div class="col-md-2">
				<input type="time" class="form-control input-md"
					placeholder="hh:mm" id="mee_end_time" name="mee_end_time" />
			</div>
		</div>
		<div class="alert alert-danger simply-hidden" id="date-error-alert">
			<?=lang("createMeeting_datetimeError")?>
		</div>

		<div class="form-group notice-primary-sources">
			<label for="not_target_type" class="col-md-4 control-label"><?=lang("notice_source")?></label>
			<div class="col-md-4">
				<select class="form-control input-md" id="not_target_type" name="not_target_type">

					<?php	foreach($groupKeyLabels as $groupKeyLabel) { ?>
						<option value="<?=$groupKeyLabel["key"]?>"><?=$groupKeyLabel["label"]?></option>
					<?php	} ?>

				</select>
			</div>
		</div>

		<div class="form-group not_mails">
			<label for="not_target_id" class="col-md-4 control-label"><?=lang("notice_secondary_source")?></label>
			<div class="col-md-4">
				<select class="form-control input-md" id="not_target_id" name="not_target_id">

					<?php					
					foreach($config["modules"]["groupsources"] as $groupSourceKey) {
						$groupSource = GroupSourceFactory::getInstance($groupSourceKey);
						$groupSource->getGroupOptions();
					}
					?>

				</select>
			</div>
		</div>

		<div class="row text-center">
			<button class="btn btn-success btn-create" type="submit"><?=lang("common_create")?></button>
		</div>

	</div>

</form>


</div>

<div class="container otbHidden">
</div>

<div class="lastDiv"></div>

<script>
var mumble_count = "<?=count($mumble);?>";
var mumble_server = "<?=$mumble_server;?>";
var mumble_title = "<?=$mumble_title;?>";
var mumble_version = "<?=$mumble_version;?>";
</script>
<?php include("footer.php");?>
<script src="assets/js/perpage/location_form.js"></script>

</body>
</html>

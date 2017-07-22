<?php /*
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

if (!isset($api)) exit();

include_once("config/discourse.config.php");
include_once("config/discourse.structure.php");
require_once("engine/discourse/DiscourseAPI.php");
$discourseApi = new richp10\discourseAPI\DiscourseAPI($config["discourse"]["url"], $config["discourse"]["api_key"], $config["discourse"]["protocol"]);

require_once("engine/utils/LogUtils.php");
addLog($_SERVER, $_SESSION, null, $_POST);

$connection = openConnection();

$meetingBo = MeetingBo::newInstance($connection);

$meeting = $meetingBo->getById($_REQUEST["meetingId"]);

if (!$meeting) {
	echo json_encode(array("ko" => "ko", "message" => "meeting_does_not_exist"));
	exit();
}

$userId = SessionUtils::getUserId($_SESSION);

if (!isset($userId)) {?>
	<div class="container">
		<div class="jumbotron alert-danger col-xs-12">
			<h2><?php echo lang("export_login_ask"); ?></h2>
			<p><?php echo lang("export_permission_guests"); ?></p>
			<p><a class='btn btn-danger btn-lg' href='connect.php' role='button'><?php echo lang("login_title"); ?></a></p>
		</div>
	</div>
  <?php die("error : not_enough_right");
}
else if (($userId !== $meeting["mee_president_member_id"]) AND ($userId !== $meeting["mee_secretary_member_id"])) {?>
	<div class="container">
		<div class="jumbotron alert-danger col-xs-12">
			<h2><?php echo lang("export_permission"); ?></h2>
			<p><?php echo lang("export_permission_description"); ?></p>
			<p><a class='btn btn-danger btn-lg' href='meeting.php?id=<?php echo $meeting["mee_id"]; ?>' role='button'><?php echo lang("common_back"); ?></a></p>
		</div>
	</div>
	<?php die("error : not_enough_right");
}

$discourse_category = $_REQUEST["discourse_category"];
$discourse_title = $_REQUEST["discourse_title"];


if (!isset($categories[$discourse_category]['id']) OR ($categories[$discourse_category]['id'] != $discourse_category)) {
	echo "<div id='discourse-result' class='alert alert-danger' role='alert'>" . lang("export_permission_description") . " ($discourse_category)</div>";
	exit("Unauthorized discourse category ($discourse_category)");
}

$report = $_REQUEST["report"];

$new_topic = $discourseApi->createTopic($discourse_title, $report , $discourse_category, $config["discourse"]["user"], 0);

$topicId = $new_topic->apiresult->topic_id;

$http_code_topic = $discourseApi->getTopic($topicId)->http_code;

if ($http_code_topic=="200") {
	$topic_url = $config["discourse"]["base"] . "/t/" . $topicId . "?u=congressus";
	echo "<div id='discourse-result' class='alert alert-success' role='alert'>" . lang("export_discourse_success") . " <a target='_blank' href='$topic_url'>$topic_url</a></div>";
}
else {
	echo "<div id='discourse-result' class='alert alert-danger' role='alert'>" . lang("export_discourse_fail") . " (code http $http_code_topic)</div>";
}
?>

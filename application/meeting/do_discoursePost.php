<?php /*
    Copyright 2017-2020 Nino Treyssat-Vincent, Cédric Levieux, Parti Pirate

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

if (!isset($api)) exit();

include_once("config/discourse.config.php");
include_once("config/discourse.structure.php");
require_once("engine/discourse/DiscourseAPI.php");
require_once("engine/utils/DiscourseUtils.php");

$discourseApi = new pnoeric\DiscourseAPI($config["discourse"]["url"], $config["discourse"]["api_key"], $config["discourse"]["protocol"]);

require_once("engine/utils/LogUtils.php");
addLog($_SERVER, $_SESSION, null, $_POST);

$connection = openConnection();

$meetingBo = MeetingBo::newInstance($connection, $config);

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

$discourseCategoryId = $_REQUEST["discourse_category"];
$discourseTitle = $_REQUEST["discourse_title"];

if (!isset($categories[$discourseCategoryId]['id']) OR ($categories[$discourseCategoryId]['id'] != $discourseCategoryId)) {
	echo "<div id='discourse-result' class='alert alert-danger' role='alert'>" . lang("export_permission_description") . " ($discourseCategoryId)</div>";
	exit("Unauthorized discourse category ($discourseCategoryId)");
}

$report = $_REQUEST["report"];

//$response = $discourseApi->createTopic($discourseTitle, $report , $discourseCategoryId, $config["discourse"]["user"], 0);

$topicId = createDiscourseTopic($discourseApi, $discourseTitle, $report , $discourseCategoryId, $config["discourse"]["user"]);

/*
echo "POST<br>";
print_r($_REQUEST);
echo "<br>POST<br>";

echo "<br>";
print_r($response);
echo "<br>";
*/

/*
if () {
	
}
$http_code_topic = $discourseApi->getTopic($topicId)->http_code;
*/

if ($topicId) {
	$topicUrl = $config["discourse"]["base"] . "/t/" . $topicId . "?u=congressus";
	echo "<div id='discourse-result' class='alert alert-success' role='alert'>" . lang("export_discourse_success") . " <a target='_blank' href='$topicUrl'>$topicUrl</a></div>";
}
else {
	echo "<div id='discourse-result' class='alert alert-danger' role='alert'>" . lang("export_discourse_fail") . "</div>";
}
?>

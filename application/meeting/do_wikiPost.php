<?php /*
	Copyright 2017 Cédric Levieux, Parti Pirate

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

require_once("config/mediawiki.php");

// Log in to a wiki
$services = openWikiSession();

if (!isset($api)) exit();

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

// Create a new page
$wikiTitle = $_REQUEST["wiki_title"];
$wikiReport = $_REQUEST["report"];

if (isset($_REQUEST["wiki_categories"]) && count($_REQUEST["wiki_categories"])) {
	foreach($_REQUEST["wiki_categories"] as $category) {
		$wikiReport .= "\n$category";	
	}
}

$title = new \Mediawiki\DataModel\Title($wikiTitle);
$newContent = new \Mediawiki\DataModel\Content($wikiReport);

$identifier = new \Mediawiki\DataModel\PageIdentifier($title );
$revision = new \Mediawiki\DataModel\Revision($newContent, $identifier);
$result = $services->newRevisionSaver()->save($revision);

//$result = $services->newPageGetter()->getFromTitle($title);

//$topicId = $new_topic->apiresult->topic_id;

//$http_code_topic = $discourseApi->getTopic($topicId)->http_code;
//$rootCatIdent = new \Mediawiki\DataModel\PageIdentifier( new \Mediawiki\DataModel\Title('Category:Categories'));
//$rootCat = $services->newPageGetter()->getFromPageIdentifier($rootCatIdent);
//$categories = $services->newCategoryTraverser()->descend($rootCat);

if ($result) {
	$topic_url = $config["mediawiki"]["base"] . "/" . str_replace(" ", "_", $wikiTitle);
	echo "<div id='wiki-result' class='alert alert-success' role='alert'>" . lang("export_wiki_success") . " <a target='_blank' href='$topic_url'>$topic_url</a></div>";
}
else {
	echo "<div id='wiki-result' class='alert alert-danger' role='alert'>" . lang("export_wiki_fail") . " (code http $http_code_topic)</div>";
}
?>

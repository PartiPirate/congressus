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

include_once("config/config.php");
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

$meeting[$_REQUEST["property"]] = $_REQUEST["text"];


$category = $discourseApi->getCategory("sandbox"); // TODO: Choose the right category/sub-category
$categoryId = $category->apiresult->topic_list->topics[0]->category_id;

$report = file_get_contents($config["server"]["base"]. "meeting/do_export.php?template=markdown&id=" . $_REQUEST["meetingId"]);

$new_topic = $discourseApi->createTopic($meeting["mee_label"] . " - " . $meeting["mee_start_time"], $report , $categoryId, $config["discourse"]["user"], $replyToId = 0);

?>

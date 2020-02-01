<?php /*
    Copyright 2015-2019 Cédric Levieux, Parti Pirate

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

include_once("config/database.php");
include_once("config/memcache.php");
require_once("engine/utils/SessionUtils.php");
require_once("engine/bo/AgendaBo.php");
require_once("engine/bo/ChatBo.php");
require_once("engine/bo/ChatAdviceBo.php");
require_once("engine/bo/ConclusionBo.php");
require_once("engine/bo/MeetingBo.php");
require_once("engine/bo/MotionBo.php");
require_once("engine/bo/TaskBo.php");
require_once("engine/bo/VoteBo.php");
require_once("engine/utils/EventStackUtils.php");
require_once("engine/utils/MeetingAPI.php");

$connection = openConnection();

$meetingId = $_REQUEST["id"];
$pointId = $_REQUEST["pointId"];
$requestId = $_REQUEST["requestId"];
$userId = SessionUtils::getUserId($_SESSION);

// error_log(print_r($_REQUEST, true));

$api = new MeetingAPI($connection, $config);

$response = $api->getAgendaPoint($meetingId, $pointId, $userId, $requestId);

//print_r($data);

echo json_encode($response, JSON_NUMERIC_CHECK);
?>

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
require_once("engine/bo/MeetingBo.php");
require_once("engine/bo/PingBo.php");
require_once("engine/utils/EventStackUtils.php");
require_once("engine/utils/DateTimeUtils.php");
require_once("engine/utils/MeetingAPI.php");

require_once("engine/utils/LogUtils.php");
addLog($_SERVER, $_SESSION, null, $_POST);

$connection = openConnection();

$api = new MeetingAPI($connection, $config);

$response = $api->ping($_REQUEST["id"] ?? null, SessionUtils::getUserId($_SESSION), $_SESSION["guestId"] ?? null, $_SESSION["guestNickname"] ?? null);

echo json_encode($response, JSON_NUMERIC_CHECK);
?>
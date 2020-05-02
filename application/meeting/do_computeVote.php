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

/*
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
*/

session_start();

$path = "../";
set_include_path(get_include_path() . PATH_SEPARATOR . $path);

require_once("config/database.php");
require_once("config/memcache.php");
require_once("engine/utils/SessionUtils.php");
require_once("engine/bo/GaletteBo.php");
require_once("engine/bo/MeetingBo.php");
require_once("engine/bo/MotionBo.php");
require_once("engine/bo/AgendaBo.php");
require_once("engine/bo/NoticeBo.php");
require_once("engine/bo/VoteBo.php");
require_once("engine/bo/TagBo.php");
require_once("engine/bo/ChatBo.php");
require_once("language/language.php");

require_once("engine/utils/PersonaeClient.php");
require_once("engine/utils/MeetingAPI.php");

require_once("engine/utils/LogUtils.php");
addLog($_SERVER, $_SESSION, null, $_POST);

$connection = openConnection();

$api = new MeetingAPI($connection, $config);

$motionId = intval($_REQUEST["motionId"]);
$save = (isset($_REQUEST["save"]) && $_REQUEST["save"] == "true");

$data = $api->computeVote($motionId, $save);

echo json_encode($data, JSON_NUMERIC_CHECK);
?>

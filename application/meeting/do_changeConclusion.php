<?php /*
	Copyright 2014 Cédric Levieux, Jérémy Collot, ArmagNet

	This file is part of OpenTweetBar.

    OpenTweetBar is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    OpenTweetBar is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with OpenTweetBar.  If not, see <http://www.gnu.org/licenses/>.
*/
session_start();

$path = "../";
set_include_path(get_include_path() . PATH_SEPARATOR . $path);

include_once("config/database.php");
require_once("engine/utils/SessionUtils.php");
require_once("engine/bo/AgendaBo.php");
require_once("engine/bo/ConclusionBo.php");
require_once("engine/bo/MeetingBo.php");

$connection = openConnection();

//$agendaBo = AgendaBo::newInstance($connection);
$conclusionBo = ConclusionBo::newInstance($connection, $config);
//$meetingBo = MeetingBo::newInstance($connection);

$conclusionId = $_REQUEST["conclusionId"];

$conclusion = $conclusionBo->getById($conclusionId);

if ($conclusion) {
	$conclusion = array($conclusionBo->ID_FIELD => $conclusionId);
	$conclusion["con_text"] = $_REQUEST["text"];

	$conclusionBo->save($conclusion);
}

$data = array("ok" => "ok");
echo json_encode($data, JSON_NUMERIC_CHECK);
?>
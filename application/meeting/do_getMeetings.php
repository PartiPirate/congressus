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
    along with Congressus.  If age, see <http://www.gnu.org/licenses/>.
*/

if (!isset($api)) exit();

include_once("config/database.php");
include_once("config/memcache.php");
require_once("engine/utils/SessionUtils.php");
require_once("engine/bo/MeetingBo.php");
//require_once("engine/bo/MeetingRightBo.php");
//require_once("engine/bo/AgendaBo.php");

$connection = openConnection();
$meetingBo = MeetingBo::newInstance($connection, $config);

$filters = array();
$filters["with_status"] = array("open", "closed");
$meetings = $meetingBo->getByFilters($filters);

$data = array();
$data["ok"] = "ok";
$data["meetings"] = $meetings;

echo json_encode($data, JSON_NUMERIC_CHECK);
?>
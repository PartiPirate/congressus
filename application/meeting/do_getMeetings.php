<?php /*
	Copyright 2015-2018 Cédric Levieux, Parti Pirate

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
include_once("config/memcache.php");
//require_once("engine/bo/MeetingRightBo.php");
//require_once("engine/bo/AgendaBo.php");

$connection = openConnection();
$memcache = openMemcacheConnection();

$meetingBo = MeetingBo::newInstance($connection, $config);

$filters = array();

if (isset($_REQUEST["status"]) && is_array($_REQUEST["status"])) {
    $filters["with_status"] = $_REQUEST["status"];
}
else {
    $filters["with_status"] = array("open", "closed");
}

$memcacheKey = json_encode(array("with_status" => $filters["with_status"], "from" => (isset($_REQUEST["from"]) ? $_REQUEST["from"] : "0")));
$json = $memcache->get($memcacheKey);

if (!$json) {
    $meetings = $meetingBo->getByFilters($filters);
    
    $data = array();
    $data["ok"] = "ok";
    $data["meetings"] = $meetings;
    
    if (isset($_REQUEST["from"])) {
        foreach($data["meetings"] as $index => $meeting) {
            if ($meeting["mee_status"] != "open" && $meeting["mee_datetime"] < $_REQUEST["from"]) {
                unset($data["meetings"][$index]);
            }
        }
        
        sort($data["meetings"]);
    }

	$json = json_encode($data, JSON_NUMERIC_CHECK);

	if (!$memcache->replace($memcacheKey, $json, MEMCACHE_COMPRESSED, 60)) {
		$memcache->set($memcacheKey, $json, MEMCACHE_COMPRESSED, 60);
	}
}
else {
	$data = json_decode($json, true);
	$data["cached"] = true;
}


echo json_encode($data, JSON_NUMERIC_CHECK);
?>
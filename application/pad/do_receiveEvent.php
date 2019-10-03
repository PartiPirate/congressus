<?php /*
    Copyright 2019 Cédric Levieux, Parti Pirate

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

$memcache = openMemcacheConnection();

$lastTimeEvent = $arguments["lastTimeEvent"];
$padId = $arguments["padId"];

$memcacheKey = "pad_$padId";
$json = $memcache->get($memcacheKey);
if ($json) {
    $events = json_decode($json, true);
}
else {
    $events = array();
}

$toSendEvents = array();

if (count($events)) {
    foreach($events as $event) {
        if ($event["time"] <= $lastTimeEvent) continue;
        
        $toSendEvents[] = $event;
    }
}

echo json_encode($toSendEvents);
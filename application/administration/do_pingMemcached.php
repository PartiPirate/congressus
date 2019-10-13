<?php /*
    Copyright 2018-2019 Cédric Levieux, Parti Pirate

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

$data = array();

$host = $arguments["memcached_host_input"];
$port = $arguments["memcached_port_input"];

try {
	$memcache = new Memcache();
	$connected = @$memcache->connect($host, $port);

	if ($connected) {
		$data["ok"] = "ok";
	}
	else {
		$data["ko"] = "ko";
		$data["error"] =  "no_host";
	}
}
catch(Exception $e){
	$data["ko"] = "ko";
	$data["error"] =  $e->getMessage();
}

echo json_encode($data, JSON_NUMERIC_CHECK);
?>
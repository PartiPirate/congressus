<?php /*
	Copyright 2015 Cédric Levieux, Parti Pirate

	This file is part of PPMoney.

    PPMoney is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    PPMoney is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with PPMoney.  If not, see <http://www.gnu.org/licenses/>.
*/
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include("config/database.php");
require_once("engine/utils/LogUtils.php");

session_start();
addLog($_SERVER, $_SESSION);
session_destroy();

/*
$referer = $_SERVER["HTTP_REFERER"];

if ($referer) {
	header("Location: $referer");
}
else {
	header("Location: index.php");
}
*/

$data["ok"] = "ok";

echo json_encode($data, JSON_NUMERIC_CHECK);

?>
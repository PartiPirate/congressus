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
    along with Congressus.  If not, see <https://www.gnu.org/licenses/>.
*/
include_once("config/database.php");
require_once("engine/utils/FormUtils.php");
require_once("engine/bo/CandidateBo.php");

// We sanitize the request fields
xssCleanArray($_REQUEST);

$connection = openConnection();

session_start();

if (isset($_SESSION["memberId"])) {
	$sessionUserId = $_SESSION["memberId"];
}
else {
	echo json_encode(array("error" => "error_not_connected"));
}

$candidateBo = CandidateBo::newInstance($connection, $config);

$candidate = array();
$candidate["can_member_id"] = $sessionUserId;
$candidate["can_theme_id"] = $_REQUEST["can_theme_id"];

// TODO Test eligibility

// Retrieve previous candidate
$candidates = $candidateBo->getCandidates($candidate);
if (count($candidates)) {
	$candidate = $candidates[0];
}

$candidate["can_status"] = isset($_REQUEST["can_status"]) ? $_REQUEST["can_status"] : "neutral";
$candidate["can_text"] = $_REQUEST["can_text"];

// Save it
$candidateBo->save($candidate);

// TODO Create candidate event
echo json_encode(array("ok" => "ok"));
?>
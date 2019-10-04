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
    along with Congressus.  If not, see <https://www.gnu.org/licenses/>.
*/

if (!isset($api)) exit();

include_once("config/database.php");
include_once("config/memcache.php");
require_once("engine/utils/SessionUtils.php");
require_once("engine/bo/CoAuthorBo.php");
require_once("engine/bo/MotionBo.php");

require_once("engine/utils/LogUtils.php");
addLog($_SERVER, $_SESSION, null, $_POST);

$userId = SessionUtils::getUserId($_SESSION);
if (!$userId) {
	echo json_encode(array("ko" => "ko", "message" => "logged_method"));
	exit();
}

$memcache = openMemcacheConnection();

$connection = openConnection();

$coAuthorBo = CoAuthorBo::newInstance($connection, $config);

$data = array();

$coAuthor = $coAuthorBo->getById($_REQUEST["coAuthorId"]);

if (!$coAuthor) {
	$data["error"] = "error";
	echo json_encode($data, JSON_NUMERIC_CHECK);
	exit();
}

$authorId = null;

if ($coAuthor["cau_object_type"] == "motion") {

    // Get motion
    $motionBo   = MotionBo::newInstance($connection, $config);
    $motion = $motionBo->getById($coAuthor["cau_object_id"]);

    // Retrieve motion author
    $authorId = $motion["mot_author_id"];
    
    // Update the motion with the co-author user id
    $toUpdateMotion = array();
    $toUpdateMotion["mot_author_id"] = $coAuthor["cau_user_id"];
    $toUpdateMotion["mot_id"] = $motion["mot_id"];

    $motionBo->save($toUpdateMotion);
}

// TODO add security check on behalf of the object

// Update the co-author user id
$toUpdateCoAuthor = array();
$toUpdateCoAuthor["cau_id"] = $coAuthor["cau_id"];
$toUpdateCoAuthor["cau_user_id"] = $authorId;

$coAuthorBo->save($toUpdateCoAuthor);

$data["ok"] = "ok";

echo json_encode($data, JSON_NUMERIC_CHECK);
?>
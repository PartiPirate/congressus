<?php 
require_once("engine/utils/SessionUtils.php");

session_start();

$data = array("ok" => "ok","timestamp" => time());

if (SessionUtils::getUserId($_SESSION)) {
	$sessionUser = SessionUtils::getUser($_SESSION);
	$sessionUserId = SessionUtils::getUserId($_SESSION);
	
	$data["user"] = $sessionUser;
}

echo json_encode($data);

?>
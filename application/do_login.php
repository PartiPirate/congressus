<?php /*
    Copyright 2015-2017 Cédric Levieux, Parti Pirate

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
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include_once("config/database.php");
require_once("engine/utils/FormUtils.php");
require_once("engine/utils/LogUtils.php");
require_once("engine/bo/GaletteBo.php");
require_once("engine/utils/SessionUtils.php");

session_start();

// We sanitize the request fields
xssCleanArray($_REQUEST);

$connection = openConnection();

$authenticator = AuthenticatorFactory::getInstance($connection, $config, $config["modules"]["authenticator"]);

$login = $_REQUEST["login"];
$password = $_REQUEST["password"];
//$ajax = isset("")

$data = array();

if ($login == $config["administrator"]["login"] && $password == $config["administrator"]["password"]) {
	$_SESSION["administrator"] = true;
	$data["ok"] = "ok";

	addLog($_SERVER, $_SESSION, null, array("result" => "administrator"));

	header('Location: administration.php');
	exit();
}

$member = $authenticator->authenticate($login, $password);
if ($member) {
	$data["ok"] = "ok";
	$connectedMember = array();
	$connectedMember["pseudo_adh"] = GaletteBo::showIdentity($member);
	$connectedMember["id_adh"] = $member["id_adh"];

	SessionUtils::setLanguage($member["pref_lang"], $_SESSION);

	$sessionUserId = $member["id_adh"];

	$_SESSION["member"] = json_encode($connectedMember);
	$_SESSION["memberId"] = $sessionUserId;
	
	require_once("engine/bo/GroupBo.php");

	$groupFilters = array();
	
	$groupBo = GroupBo::newInstance($connection, $config);
	$groups = $groupBo->getGroups(array("for_user_only" => (isset($_GET["limit"]) ? $sessionUserId : 0)));
	
	foreach($groups as $index => $group) {
		$groups[$index]["gro_is_admin"] = $groupBo->isMemberAdmin($group, $sessionUserId);
	}

	$myVotingGroups = $groupBo->getMyGroups(array("userId" => $sessionUserId, "state" => "voting"));
	$myEligibleGroups = $groupBo->getMyGroups(array("userId" => $sessionUserId, "state" => "eligible"));
	
	function isInMyGroup($theme, $mygroups) {
		foreach($mygroups as $group) {
			foreach($group["gro_themes"] as $myTheme) {
				if ($myTheme["the_id"] == $theme["the_id"]) return true;
			}
		}
	
		return false;
	}
	
	foreach($groups as $groupId => $group) {
		
	//	echo "#" . @$group["gro_label"] . "#" . "<br>";
		
		$groups[$groupId]["memberInGroup"] = false;

		foreach($groups[$groupId]["gro_themes"] as $themeId => $theme) {
			$groups[$groupId]["gro_themes"][$themeId]["memberInTheme"] = false;

			foreach($groups[$groupId]["gro_themes"][$themeId]["fixation"]["members"] as $memberId => $member) {
				if ($memberId == $sessionUserId) {
					$groups[$groupId]["memberInGroup"] = true;
					$groups[$groupId]["gro_themes"][$themeId]["memberInTheme"] = true;
				}
			}

			unset($groups[$groupId]["gro_themes"][$themeId]["fixation"]["members"]);
		}
	}

//	print_r($groups);

	require_once("engine/bo/UserPropertyBo.php");
	
	function getUserProperty($property) {
		global $userProperties;
		
		foreach($userProperties as $userProperty) {
			if ($userProperty["upr_property"] == $property) {
				return $userProperty;
			}
		}
		
		return array("upr_id" => 0, "upr_user_id" => 0, "upr_property" => $property);
	}

    $userPropertyBo = UserPropertyBo::newInstance($connection, $config);

    $userProperties = $userPropertyBo->getByFilters(array("upr_user_id" => $sessionUserId));
    $property = getUserProperty("viewable_groups");

	if (isset($property["upr_value"]) && $property["upr_value"]) {
		$_SESSION["viewable_groups"] = json_decode($property["upr_value"], true);
	}

	$_SESSION["groups"] = $groups;

	addLog($_SERVER, $_SESSION, null, array("result" => "ok"));
}
else {
	$data["ko"] = "ko";
	$data["message"] = "error_login_bad";
	addLog($_SERVER, $_SESSION, null, array("result" => "ko"));
}

session_write_close();

$referer = isset($_POST["referer"]) ? $_POST["referer"] : null;
if ($referer && substr($referer, strrpos($referer, "/") + 1) == "activate.php") $referer = "index.php";

if (isset($data["ok"]) && $referer) {
	header('Location: ' . $referer);
}
else if (!isset($data["ok"]) && $referer) {
	header('Location: connect.php?error=' . $data["message"] . "&referer=" . urlencode($referer));
}
else {
	echo json_encode($data);
}
?>

<?php /*
    Copyright 2020 Cédric Levieux, Parti Pirate

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
require_once("config/database.php");
require_once("engine/utils/FormUtils.php");
require_once("engine/bo/CandidateBo.php");
require_once("engine/bo/DelegationConditionBo.php");
require_once("language/language.php");

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

$delegationConditionBo = DelegationConditionBo::newInstance($connection, $config);

$delegationCondition = array();

$delegationCondition["dco_label"] = lang("conditional_conditional_label_no_delegation_on_vote");
$delegationCondition["dco_theme_id"] = $_REQUEST["del_theme_id"];
$delegationCondition["dco_theme_type"] = $_REQUEST["del_theme_type"];
$delegationCondition["dco_member_from"] = $sessionUserId;
$delegationCondition["dco_order"] = -1;
$delegationCondition["dco_end_of_delegation"] = true;
$delegationCondition["dco_conditions"] = "[{\"interaction\":\"if\",\"field\":\"voter_me\",\"operator\":\"do_vote\",\"value\":\"\"}]";

$conditions = $delegationConditionBo->getConditions($delegationCondition);

if (isset($_REQUEST["del_no_delegation_on_vote"])) {
	// retrieve and save
	if (!count($conditions)) $delegationConditionBo->save($delegationCondition);
}
else {
	// retrieve and delete
	if (count($conditions)) $delegationConditionBo->deleteByUniqueKey($conditions[0]);
}

$data = array("ok" => "ok");

echo json_encode($data);
?>
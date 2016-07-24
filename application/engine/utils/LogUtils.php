<?php

@include("engine/bo/LogBo.php");

function addLog($server, $session, $action = null, $data = null) {
	
	$pdo = openConnection();
	$logBo = LogBo::newInstance($pdo);
	
	// if no action is given, the page name is used
	if (!$action) {
		$action = $server["SCRIPT_NAME"];
		if (strrpos($action, "/") !== false) {
			$action = substr($action, strrpos($action, "/") + 1);
		}
		$action = str_replace(".php", "", $action);
	}

	if (!$data) {
		$data = array();
		$data["q"] = 	$server['QUERY_STRING'];
	}
	
	$log = array();
	$log["log_action"] = $action; 
	$log["log_data"] = json_encode($data);
	$log["log_ip"] = $server["HTTP_X_REAL_IP"];
	
	if (isset($session["memberId"])) {
		$log["log_user_id"] = $session["memberId"];
	}
	else if (isset($session["guestId"])) {
		$log["log_user_id"] = "G" . $session["guestId"];
	}
	
	// TODO pb timezone
	$now = new DateTime();
	$now->add(new DateInterval('PT2H'));
	
	$log["log_datetime"] = $now->format("Y-m-d H:i:s");

	$logBo->save($log);
}

?>
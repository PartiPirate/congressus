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

session_start();

$path = "../";
set_include_path(get_include_path() . PATH_SEPARATOR . $path);

include_once("config/database.php");
include_once("config/memcache.php");
require_once("engine/utils/SessionUtils.php");
require_once("engine/bo/GaletteBo.php");
require_once("engine/bo/MeetingBo.php");
require_once("engine/bo/MotionBo.php");
require_once("engine/bo/AgendaBo.php");
require_once("engine/bo/NoticeBo.php");
require_once("engine/bo/VoteBo.php");

require_once("engine/utils/PersonaeClient.php");

require_once("engine/utils/LogUtils.php");
addLog($_SERVER, $_SESSION, null, $_POST);

$memcache = openMemcacheConnection();
$connection = openConnection();

$personaeClient = PersonaeClient::newInstance($config["personae"]["api"]);

$meetingBo = MeetingBo::newInstance($connection, $config);
$motionBo  = MotionBo::newInstance($connection, $config);
$agendaBo  = AgendaBo::newInstance($connection, $config);
$noticeBo  = NoticeBo::newInstance($connection, $config);
$voteBo    = VoteBo::newInstance($connection, $config);

$motionId = intval($_REQUEST["motionId"]);

$memcacheKey = "do_getComputeVote_$motionId";
$json = $memcache->get($memcacheKey);

if (!$json || (isset($_REQUEST["save"]) && $_REQUEST["save"] == "true")) {
    
    $data = array();
    
    $propositions = $motionBo->getByFilters(array($motionBo->ID_FIELD => $motionId));
    
    $motion = $propositions[0];
    $agenda = $agendaBo->getById($motion["mot_agenda_id"]);
    $meeting = $meetingBo->getById($agenda["age_meeting_id"]);
    $voters = array();
    $nbVoters = 0;
    
    foreach($motion as $key => $value) {
        if (strpos($key, "mot_") === false) {
            unset($motion[$key]);
        }
    }
    
    foreach($propositions as $index => $proposition) {
        foreach($proposition as $key => $value) {
            if (strpos($key, "mot_") !== false) {
                unset($propositions[$index][$key]);
            }
        }
    }
    
//    $data["propositionsAtStart"] = $propositions;
    
    $votes = array();
    
    foreach($propositions as $index => $proposition) {
        $propositionVotes = $voteBo->getByFilters(array("vot_motion_proposition_id" => $proposition["mpr_id"]));
    
        // clean data
        foreach($propositionVotes as $jndex => $vote) {
            foreach($vote as $key => $value) {
                if (strpos($key, "vot_") === false && $key != "id_adh" && $key != "pseudo_adh" && $key != "nom_adh" && $key != "prenom_adh") {
                    unset($propositionVotes[$jndex][$key]);
                }
                else if ($key == "pseudo_adh" || $key == "nom_adh" || $key == "prenom_adh") {
                    $propositionVotes[$jndex][$key] = utf8_encode($value);
                }
            }
        }

        $votes = array_merge($votes, $propositionVotes);
        
        $propositions[$index]["votes"] = $propositionVotes;
    }

    /*
    // clean data
    foreach($votes as $index => $vote) {
        foreach($vote as $key => $value) {
            if (strpos($key, "vot_") === false) {
                unset($votes[$index][$key]);
            }
        }
    }
    */
    
    //print_r($votes);
    
    $notices = $noticeBo->getByFilters(array("not_meeting_id" => $meeting["mee_id"]));
    
    foreach($notices as $notice) {
        if (!$notice["not_voting"]) continue;
        if ($notice["not_target_type"] == "dlp_themes") {
            $delegations = $personaeClient->getNoticePowers($notice["not_target_id"], $motion, $votes);
    
    //        $data["response"] = $delegations; // TODO compute in a better way
        }
    }
    
    /*
    echo "#";
    print_r($delegations);
    echo "#";
    */
    
    /*
    $results = json_decode($_REQUEST["explanations"], true);
    
    foreach($results as $key => $explanation) {
    	$id = str_replace("proposition_", "", $key);
    
    	foreach ($propositions as $proposition) {
    		if ($proposition[$motionBo->ID_FIELD_PROPOSITION] == $id) {
    			$updatedProposition = array($motionBo->ID_FIELD_PROPOSITION => $id);
    			$updatedProposition["mpr_explanation"] = json_encode($explanation);
    			$updatedProposition["mpr_winning"] = $explanation["winning"];
    
    			$motionBo->updateProposition($updatedProposition);
    		}
    	}
    }
    */
    
    if ($motion["mot_win_limit"] >= 0 || $motion["mot_win_limit"] == -1) {
        $totalPower = 0;
        foreach($propositions as $index => $proposition) {
            $propositions[$index]["total_power"] = 0;
            $propositions[$index]["mpr_winning"] = 0;
            $propositions[$index]["mpr_explanation"] = array("winning" => 0, "votes" => array(), "power" => 0);
    
            foreach($proposition["votes"] as $jndex => $vote) {
                if (!isset($delegations["powers"][$vote["vot_member_id"]])) continue; // Voter without any known power even 0

                if (!isset($voters[$vote["vot_member_id"]])) {
                    $voters[$vote["vot_member_id"]] = $vote["vot_member_id"];
                    $nbVoters++;
                }

                if ($vote["vot_power"] < 0) $vote["vot_power"] = 0;
                
                if ($motion["mot_win_limit"] >= 0) { // Not in borda
                    if ($vote["vot_power"] > $delegations["theme"]["the_voting_power"]) $vote["vot_power"] = $delegations["theme"]["the_voting_power"]; // TODO a better patch
                }

                $propositions[$index]["votes"][$jndex]["vot_real_power"] = $vote["vot_power"] * $delegations["powers"][$vote["vot_member_id"]]["power"] / $delegations["theme"]["the_voting_power"];

                $propositions[$index]["total_power"] += $propositions[$index]["votes"][$jndex]["vot_real_power"];
                $totalPower += $propositions[$index]["votes"][$jndex]["vot_real_power"] * (1 - $propositions[$index]["mpr_neutral"]);
    
                $explainedVote = array("neutral" => $propositions[$index]["mpr_neutral"], "power" => $propositions[$index]["votes"][$jndex]["vot_real_power"], "memberLabel" => GaletteBo::showIdentity($vote), "memberId" => $vote["vot_member_id"], "votePower" => $delegations["powers"][$vote["vot_member_id"]]["power"], "ballotPower" => $vote["vot_power"]);
    
                $propositions[$index]["mpr_explanation"]["votes"][] = $explainedVote;
            }
    
            $propositions[$index]["mpr_explanation"]["power"] = $propositions[$index]["total_power"];
        }
        
        foreach($propositions as $index => $proposition) {
            $propositions[$index]["proportion_power"] = (($totalPower != 0) ? $propositions[$index]["total_power"] * (1 - $propositions[$index]["mpr_neutral"]) / $totalPower : 0);
        //    unset($propositions[$index]["votes"]);
        }
    }
    else if ($motion["mot_win_limit"] == -2) { // JM
        $defaultJmArray = array(0);
        for($index = 0; $index < count($config["congressus"]["ballot_majority_judgment"]); $index++) {
            $defaultJmArray[] = 0;
        }
    
        foreach($propositions as $index => $proposition) {
            $propositions[$index]["mpr_explanation"] = array("winning" => 0, "votes" => array(), "power" => 0);
            $propositions[$index]["total_power"] = 0;
            $propositions[$index]["mpr_winning"] = 0;
            $propositions[$index]["jm_powers"] = $defaultJmArray;
            $propositions[$index]["jm_proportion_powers"] = $defaultJmArray;
            $propositions[$index]["jm_sum_proportion_powers"] = $defaultJmArray;
            $propositions[$index]["jm_median_power"] = 0;
    
            // Compute for each proposition the repartition of votes
            foreach($proposition["votes"] as $jndex => $vote) {
                if (!isset($delegations["powers"][$vote["vot_member_id"]])) continue; // Voter without any known power even 0
    
                if (!isset($voters[$vote["vot_member_id"]])) {
                    $voters[$vote["vot_member_id"]] = $vote["vot_member_id"];
                    $nbVoters++;
                }

                $propositions[$index]["votes"][$jndex]["vot_real_power"] = $defaultJmArray;
    
                $jmVoteValue = $vote["vot_power"];
                $votePower = $delegations["powers"][$vote["vot_member_id"]]["power"] / $delegations["theme"]["the_voting_power"];
    
                $propositions[$index]["votes"][$jndex]["vot_real_power"][$jmVoteValue] = $votePower;
                $propositions[$index]["jm_powers"][$jmVoteValue] += $votePower;
                $propositions[$index]["jm_powers"][0] += $votePower;
    
                $explainedVote = array("neutral" => $propositions[$index]["mpr_neutral"], "power" => $jmVoteValue, "memberLabel" => GaletteBo::showIdentity($vote), "memberId" => $vote["vot_member_id"], "votePower" => $delegations["powers"][$vote["vot_member_id"]]["power"], "jmPower" => $jmVoteValue);
    
                $propositions[$index]["mpr_explanation"]["votes"][] = $explainedVote;
            }
    
            for($jndex = count($propositions[$index]["jm_powers"]) - 1; $jndex > 0; $jndex--) {
                $propositions[$index]["jm_proportion_powers"][$jndex] = $propositions[$index]["jm_powers"][$jndex] / $propositions[$index]["jm_powers"][0];
                $propositions[$index]["jm_sum_proportion_powers"][$jndex] = $propositions[$index]["jm_proportion_powers"][$jndex];
                
                if (isset($propositions[$index]["jm_sum_proportion_powers"][$jndex + 1])) {
                    $propositions[$index]["jm_sum_proportion_powers"][$jndex] += $propositions[$index]["jm_sum_proportion_powers"][$jndex + 1];
                }
    
                if (!$propositions[$index]["jm_median_power"] && $propositions[$index]["jm_sum_proportion_powers"][$jndex] >= 0.5) {
                    $propositions[$index]["jm_median_power"] = $jndex;
                    $propositions[$index]["mpr_explanation"]["jm_winning"] = $jndex;
                    $propositions[$index]["mpr_explanation"]["jm_percent"] = $propositions[$index]["jm_sum_proportion_powers"][$jndex] * 100;
                }
            }
        }
    }
    
    /*
    print_r($propositions);
    echo "----\n";
    print_r($motion);
    echo "----\n";
    */
    
    function sortPropositionsOnProportionPower($p1, $p2) {
        return intval(100 * ($p2["proportion_power"] - $p1["proportion_power"]));
    }
    
    function sortPropositionsOnJMPower($p1, $p2) {
        if ($p2["jm_median_power"] == $p1["jm_median_power"]) {
            return intval(100 * ($p2["jm_sum_proportion_powers"][$p2["jm_median_power"]] - $p1["jm_sum_proportion_powers"][$p1["jm_median_power"]]));
        }
    
        return intval(100 * ($p2["jm_median_power"] - $p1["jm_median_power"]));
    }
    
    switch($motion["mot_win_limit"]) {
        case 0:
    //        echo "La meilleure\n";
            usort($propositions, "sortPropositionsOnProportionPower");
            $propositions[0]["mpr_winning"] = 1;
            $propositions[0]["mpr_explanation"]["winning"] = 1;
            break;
        case -1: // Borda, TODO can have more than one winner
    //        echo "Borda\n";
            usort($propositions, "sortPropositionsOnProportionPower");
            $propositions[0]["mpr_winning"] = 1;
            $propositions[0]["mpr_explanation"]["winning"] = 1;
            break;
        case -2: // JM, TODO can have more than one winner
    //        echo "Jugement majoritaire\n"; 
            usort($propositions, "sortPropositionsOnJMPower");
            $propositions[0]["mpr_winning"] = 1;
            $propositions[0]["mpr_explanation"]["winning"] = 1;
            break;
        default:
    //        echo "Majorité qualifiée : " . $motion["mot_win_limit"] . "%\n";
            usort($propositions, "sortPropositionsOnProportionPower");
            if ($motion["mot_win_limit"] < ($propositions[0]["proportion_power"] * 100)) {
                $propositions[0]["mpr_winning"] = 1;
                $propositions[0]["mpr_explanation"]["winning"] = 1;
            }
            break;
    }

    // Clean
    foreach($propositions as $index => $proposition) {
        $propositions[$index]["mpr_explanation"] = json_encode($propositions[$index]["mpr_explanation"]);
        $saveProposition = array();

        foreach($proposition as $key => $value) {
            if (strpos($key, "mpr_") === false && strpos($key, "jm_") === false && $key != "total_power" && $key != "proportion_power") {
                unset($propositions[$index][$key]);
            }
            else if (strpos($key, "mpr_") !== false) {
                $saveProposition[$key] = $value;
                if ($key == "mpr_explanation") {
                    $saveProposition[$key] = json_encode($value);
                }
            }
        }

        // save if needed
        if (isset($_REQUEST["save"]) && $_REQUEST["save"] == "true") {
            $motionBo->saveProposition($saveProposition);
        }
    }
    
    //$data["meeting"] = $meeting;
    //$data["agenda"] = $agenda;
    $motion["mot_number_of_voters"] = $nbVoters;
    
    $data["motion"] = $motion;
    $data["propositions"] = $propositions;
    $data["delegations"] = $delegations;

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
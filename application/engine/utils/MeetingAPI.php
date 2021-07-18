<?php /*
    Copyright 2019-2021 Cédric Levieux, Parti Pirate

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

defined('CONNECTED_TIME') or define("CONNECTED_TIME", 60);
defined('DISCONNECTED_TIME') or define("DISCONNECTED_TIME", 65);

if (!function_exists("sortPropositionsOnProportionPower")) {
	function sortPropositionsOnProportionPower($p1, $p2) {
	    return intval(100 * ($p2["proportion_power"] - $p1["proportion_power"]));
	}
}

if (!function_exists("sortPropositionsOnJMPower")) {
	function sortPropositionsOnJMPower($p1, $p2) {
	    if ($p2["jm_median_power"] == $p1["jm_median_power"]) {
	        return intval(10000 * ($p2["jm_sum_proportion_powers"][$p2["jm_median_power"]] - $p1["jm_sum_proportion_powers"][$p1["jm_median_power"]]));
	    }
	
	    return intval(10000 * ($p2["jm_median_power"] - $p1["jm_median_power"]));
	}
}

if (!function_exists("sortPropositionsOnLabel")) {
	function sortPropositionsOnLabel($a, $b) {
		if (strtolower(($a["mpr_label"])) == "oui" || strtolower(($a["mpr_label"])) == "pour") return -1;
		if (strtolower(($a["mpr_label"])) == lang("vote_abstain")) return 1;
	
		if (strtolower(($b["mpr_label"])) == "oui" || strtolower(($b["mpr_label"])) == "pour") return 1;
		if (strtolower(($b["mpr_label"])) == lang("vote_abstain")) return -1;
	
		return 0;
	}
}

if (!function_exists("createGameEvent")) {
	function createGameEvent($userId, $eventId) {
	    global $config;
	
	    $event = array("user_uuid" => computeGameUserId($userId), "event_uuid" => $eventId, "service_uuid" => $config["gamifier"]["service_uuid"], "service_secret" => $config["gamifier"]["service_secret"]);
	    
	    return $event;
	}
}

if (!function_exists("computeGameUserId")) {
	function computeGameUserId($userId) {
	    global $config;
	
	    return sha1($config["gamifier"]["user_secret"] . $userId);
	}
}

if (!function_exists("pingSpeakingRequestCompare")) {
	function pingSpeakingRequestCompare($pingA, $pingB) {
		if ($pingA == $pingB) {
			return 0;
		}
		return ($pingA["pin_speaking_request"] < $pingB["pin_speaking_request"]) ? -1 : 1;
	}
}

require_once("engine/utils/WinningMotionHook.php");
require_once("engine/utils/TaskHook.php");
require_once("engine/utils/DateTimeUtils.php");

include_once("config/discourse.config.php");
require_once("engine/discourse/DiscourseAPI.php");

include_once("config/mediawiki.config.php");
include_once("config/mediawiki.php");

class MeetingAPI {
	var $pdo = null;
	var $config = null;

	function __construct($pdo, $config) {
		$this->config = $config;
		$this->pdo = $pdo;
	}

    private function getGamifierClient() {
        $gamifierClient = null;
        if (isset($this->config["gamifier"]["url"])) {
            $gamifierClient = GamifierClient::newInstance($this->config["gamifier"]["url"]);
        }

        return $gamifierClient;
    }

	function computeVote($motionId, $save) {
		require_once("engine/utils/PersonaeClient.php");

		$memcache = openMemcacheConnection();

		$personaeClient = PersonaeClient::newInstance($this->config["personae"]["api"]);

		$meetingBo = MeetingBo::newInstance($this->pdo, $this->config);
		$motionBo  = MotionBo::newInstance($this->pdo, $this->config);
		$agendaBo  = AgendaBo::newInstance($this->pdo, $this->config);
		$noticeBo  = NoticeBo::newInstance($this->pdo, $this->config);
		$voteBo    = VoteBo::newInstance($this->pdo, $this->config);
		$tagBo     = TagBo::newInstance($this->pdo, $this->config);

		$motionId = intval($motionId);

		$memcacheKey = "do_getComputeVote_$motionId";
		$json = $memcache->get($memcacheKey);

		if (!$json || $save) {

		    $data = array();

		    $propositions = $motionBo->getByFilters(array($motionBo->ID_FIELD => $motionId));

		    $motion = $propositions[0];
		    $agenda = $agendaBo->getById($motion["mot_agenda_id"]);
		    $meeting = $meetingBo->getById($agenda["age_meeting_id"]);
		
			$end = getDateTime($meeting["mee_datetime"]);
			$duration = new DateInterval("PT" . ($meeting["mee_expected_duration"] ? $meeting["mee_expected_duration"] : 60) . "M");
			$end = $end->add($duration);
		
		    $motion["mot_date"] = $end->format("Y-m-d");
		
			$motion["mot_tag_ids"] = json_decode($motion["mot_tag_ids"]);
			$motion["mot_tags"] = array();
			
			if (count($motion["mot_tag_ids"])) {
				$tags = $tagBo->getByFilters(array("tag_ids" => $motion["mot_tag_ids"]));
				$motion["mot_tags"] = $tags;
			}
		
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
		    
//		    print_r($notices);

		    
		    foreach($notices as $notice) {
		        if (!$notice["not_voting"]) continue;
		        if ($notice["not_target_type"] == "dlp_themes") {
		
		            $delegations = $personaeClient->getNoticePowers($notice["not_target_id"], $motion, $votes);

//					return $data;

//		            echo "<!--\n";
//		            print_r($delegations);
//		            echo "\n-->";

//					return $data;
		//            $data["response"] = $delegations; // TODO compute in a better way
		        }
		        else if ($notice["not_target_type"] == "galette_adherents") {
		            $delegations = array("theme" => array());
		            $delegations["theme"]["the_voting_power"] = 1;
		            $delegations["votes"] = $votes;
		            $delegations["powers"] = array();
		
		            $groupSource = GroupSourceFactory::getInstance("galetteallmembersgroups");
		            $pings = array();
		            $groupSource->updateNotice($meeting, $notice, $pings, $pings);
		
		//            $delegations["notice"] = $notice;
		            
		            foreach($notice["not_people"] as $people) {
		                $delegations["powers"][$people["mem_id"]] = array();
		                $delegations["powers"][$people["mem_id"]]["id_adh"] = $people["mem_id"];
		                $delegations["powers"][$people["mem_id"]]["nickname"] = $people["mem_nickname"];
		                $delegations["powers"][$people["mem_id"]]["power"] = 1;
		                $delegations["powers"][$people["mem_id"]]["max_power"] = 1;
		                $delegations["powers"][$people["mem_id"]]["delegation_level"] = 1;
		            }
		        }
		        else if ($notice["not_target_type"] == "galette_groups") {
		
		            $delegations = array("theme" => array());
		            $delegations["theme"]["the_voting_power"] = 1;
		            $delegations["votes"] = $votes;
		            $delegations["powers"] = array();
		
		            $groupSource = GroupSourceFactory::getInstance("galettegroups");
		            $pings = array();
		            $groupSource->updateNotice($meeting, $notice, $pings, $pings);
		            
		            foreach($notice["not_people"] as $people) {
		                $delegations["powers"][$people["mem_id"]] = array();
		                $delegations["powers"][$people["mem_id"]]["id_adh"] = $people["mem_id"];
		                $delegations["powers"][$people["mem_id"]]["nickname"] = $people["mem_nickname"];
		                $delegations["powers"][$people["mem_id"]]["power"] = 1;
		                $delegations["powers"][$people["mem_id"]]["max_power"] = 1;
		                $delegations["powers"][$people["mem_id"]]["delegation_level"] = 1;
		            }
		        }
		    }

//			return $data;

		//    $data["notices"] = $notices;
		    
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
		    else if ($motion["mot_win_limit"] == -2 || $motion["mot_win_limit"] == -3 || $motion["mot_win_limit"] == -4) { // JM or Approval
		        $defaultJmArray = array(0);
		        for($index = 0; $index < count($this->config["congressus"]["ballot_majority_judgment"]); $index++) {
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
		            	if ($propositions[$index]["jm_powers"][0]) {
			                $propositions[$index]["jm_proportion_powers"][$jndex] = $propositions[$index]["jm_powers"][$jndex] / $propositions[$index]["jm_powers"][0];
		            	}
		            	else {
		            		$propositions[$index]["jm_proportion_powers"][$jndex] = 0;
		            	}

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

		    if ($motion["mot_anonymous"] && $motion["mot_status"] != "resolved") {
		        usort($propositions, "sortPropositionsOnLabel");
		    }
		    else {
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
		            case -4: // Approval-3 / Maybe, TODO can have more than one winner
		            case -3: // Approval, TODO can have more than one winner
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

		        foreach($propositions as $index => $proposition) {
		            $propositions[$index]["mpr_position"] = $index;
		        }
		    }

		    // Clean
		    foreach($propositions as $index => $proposition) {
		        $propositions[$index]["mpr_explanation"] = json_encode($propositions[$index]["mpr_explanation"]);
		        $saveProposition = array();
		
		        foreach($proposition as $key => $value) {
		            if (strpos($key, "mpr_") === false && strpos($key, "jm_") === false && $key != "total_power" && $key != "proportion_power") {
		                unset($propositions[$index][$key]);
		            }
		            else if (strpos($key, "mpr_") !== false && $key != "mpr_position") {
		                $saveProposition[$key] = $value;
		                if ($key == "mpr_explanation") {
		                    $saveProposition[$key] = json_encode($value);
		                    
		//                    print($saveProposition[$key]);
		                }
		            }
		        }

		        // save if needed
		        if ($save || false) {
		            $motionBo->saveProposition($saveProposition);

					$chatBo = ChatBo::newInstance($this->pdo, $this->config);

					$chats = $chatBo->getByFilters(array("cha_agenda_id" => $agenda[$agendaBo->ID_FIELD]));

					// if the proposition is the winning one, then call the hooks
					if ($saveProposition["mpr_winning"]) {
						
						// find the hooks, add them, call them with the motion and the proposition

						$directoryHandler = dir("../motion_hooks/");
						while(($fileEntry = $directoryHandler->read()) !== false) {
							if($fileEntry != '.' && $fileEntry != '..' && strpos($fileEntry, ".php")) {
								require_once("motion_hooks/" . $fileEntry);
							}
						}
						$directoryHandler->close();

						global $hooks;
						
						if ($hooks) {
							foreach($hooks as $hookKey => $hook) {
								$data[$hookKey] = $hook->doHook($agenda, $motion, $proposition, $chats);
							}
						}
					}
		        }
		    }

		    //$data["meeting"] = $meeting;
		    //$data["agenda"] = $agenda;
		    $motion["mot_number_of_voters"] = $nbVoters;

		    $data["motion"] = $motion;
		    $data["propositions"] = $propositions;
		    $data["delegations"] = $delegations;

		    if ($motion["mot_anonymous"] && $motion["mot_status"] != "resolved") {
		        $data["delegations"] = array();

		        foreach($data["propositions"] as $propositionIndex => $proposition) {
		            $data["propositions"][$propositionIndex]["mpr_explanation"] = json_encode(array("winning" => 0, "votes" => array(), "power" => 0));
		            $data["propositions"][$propositionIndex]["mpr_winning"] = 0;
		            $data["propositions"][$propositionIndex]["proportion_power"] = 0;
		            $data["propositions"][$propositionIndex]["total_power"] = 0;
		            $data["propositions"][$propositionIndex]["jm_median_power"] = 0;
		            $data["propositions"][$propositionIndex]["jm_sum_proportion_powers"] = 0;
		        }
		    }

			$json = json_encode($data, JSON_NUMERIC_CHECK);

			if (!$memcache->replace($memcacheKey, $json, 3600)) {
				$memcache->set($memcacheKey, $json, 3600);
			}
		}
		else {
			$data = json_decode($json, true);
			$data["cached"] = true;
		}

		return $data;
	}

	function vote($motionId, $propositionId, $userId, $votePower) {
		$memcache = openMemcacheConnection();
		
		$motionBo = MotionBo::newInstance($this->pdo, $this->config);
		$voteBo = VoteBo::newInstance($this->pdo, $this->config);
		
		$motion = $motionBo->getById($motionId);
		$motionId = $motion[$motionBo->ID_FIELD];
		
		$proposition = array("mpr_id" => $propositionId);
		
		$data = array();
		
		if (!$userId) {
			return array("ko" => "ko", "message" => "vote_not_accessible");
		}
		
		$vote = array();
		$vote["vot_member_id"] = $userId;
		$vote["vot_motion_proposition_id"] = $proposition["mpr_id"];

		$votes = $voteBo->getByFilters($vote);
		if (count($votes)) {
			$vote[$voteBo->ID_FIELD] = $votes[0][$voteBo->ID_FIELD];
		}

		$vote["vot_power"] = $votePower;

		$voteBo->save($vote);
		$vote = $voteBo->getById($vote[$voteBo->ID_FIELD]);

		$vote["mem_id"] = $vote["id_adh"] ? $vote["id_adh"] : "G" . $vote["chat_guest_id"];
		$vote["mem_nickname"] = htmlspecialchars(utf8_encode($vote["pseudo_adh"] ? $vote["pseudo_adh"] : (isset($vote["pin_nickname"]) ? $vote["pin_nickname"] : "")));

		$data["ok"] = "ok";

		foreach ($vote as $key => $value) {
		    if (strpos($key, "_adh")) unset($vote[$key]);
		    if ($key == "id_statut") unset($vote[$key]);
		    if ($key == "bool_display_info") unset($vote[$key]);
		    if ($key == "date_echeance") unset($vote[$key]);
		    if ($key == "pref_lang") unset($vote[$key]);
		    if ($key == "lieu_naissance") unset($vote[$key]);
		    if ($key == "gpgid") unset($vote[$key]);
		    if ($key == "fingerprint") unset($vote[$key]);
		    if ($key == "parent_id") unset($vote[$key]);
		}
		$data["vote"] = $vote;

		$gamifierClient = $this->getGamifierClient();
		if ($gamifierClient) {
		    $events = array();
		    $events[] = createGameEvent($userId, GameEvents::HAS_VOTED);
		    
		    $addEventsResult = $gamifierClient->addEvents($events);
		
		    $data["gamifiedUser"] = $addEventsResult;
		}

		$pointId = $motion["mot_agenda_id"];
		$memcacheKey = "do_getAgendaPoint_$pointId";
		$memcache->delete($memcacheKey);
		$memcacheKey = "do_getAgendaPoint_-1";
		$memcache->delete($memcacheKey);
		$memcacheKey = "do_getComputeVote_$motionId";
		$memcache->delete($memcacheKey);

		return $data;
	}

	function ping($meetingId, $userId = null, $guestId = null, $guestName = null) {
		$meetingBo = MeetingBo::newInstance($this->pdo, $this->config);
		$pingBo = PingBo::newInstance($this->pdo, $this->config);

		$meeting = $meetingBo->getById($meetingId);

		if (!$meeting) {
			return array("ko" => "ko", "message" => "meeting_does_not_exist");
		}

		// TODO Compute the key // Verify the key

		if (false) {
			return array("ko" => "ko", "message" => "meeting_not_accessible");
		}

		$ping = array("pin_meeting_id" => $meeting[$meetingBo->ID_FIELD]);

		if (!$userId) {
			$ping["pin_guest_id"] = $guestId;
//			$ping["pin_nickname"] = $guestName;
		}
		else {
			$ping["pin_member_id"] = $userId;
		}

		$now = getNow();

		$ping["pin_datetime"] = $now->format("Y-m-d H:i:s");

		$previousPings = $pingBo->getByFilters($ping);

		//error_log("Number of pings : " . count($previousPings));

		//print_r($previousPings);

		if (count($previousPings)) {
			$previousPing = $previousPings[0];
			$ping[$pingBo->ID_FIELD] = $previousPing[$pingBo->ID_FIELD];
			
			$now = getNow();
			$lastPing = getDateTime($previousPing["pin_datetime"]);
			
			$diff = $now->getTimestamp() -  $lastPing->getTimestamp();

			if ($diff > 60) {
				addEvent($meetingId, EVENT_JOIN, "", array("userId" => $userId ? $userId : "G" . $guestId));
			}

			if (isset($ping["pin_guest_id"]) && !$previousPing["pin_nickname"]) {
				$ping["pin_nickname"] = $nickname = "Guest " . substr(md5($guestId), 0, 6);
			}
		}
		else {
			// first time
			if (isset($ping["pin_guest_id"])) {
				$ping["pin_nickname"] = $nickname = "Guest " . substr(md5($guestId), 0, 6);
			}
			addEvent($meetingId, EVENT_JOIN, "", array("userId" => $userId ? $userId : "G" . $guestId));
		}

		$pingBo->save($ping);

		$data["ping"] = $ping;
		$data["ok"] = "ok";

		return $data;
	}

	function getAgenda($meetingId) {
		$memcacheKey = "do_getAgenda_$meetingId";
		
		$memcache = openMemcacheConnection();
		$json = $memcache->get($memcacheKey);
		
		if (!$json) {
			$meetingBo = MeetingBo::newInstance($this->pdo, $this->config);
			$meetingRightBo = MeetingRightBo::newInstance($this->pdo, $this->config);
			$agendaBo = AgendaBo::newInstance($this->pdo, $this->config);
		
			$meeting = $meetingBo->getById($meetingId, true);
		
			if (!$meeting) {
				return array("ko" => "ko", "message" => "meeting_does_not_exist");
			}
		
			// TODO Compute the key // Verify the key
		
			if (false) {
				return array("ko" => "ko", "message" => "meeting_not_accessible");
			}
		
		//	print_r($meeting);
		
			$data = array();
		
			$agendas = $agendaBo->getByFilters(array("age_meeting_id" => $meeting[$meetingBo->ID_FIELD], "with_count_motions" => true));
		
			$end = getDateTime($meeting["mee_datetime"]);
			$duration = new DateInterval("PT" . ($meeting["mee_expected_duration"] ? $meeting["mee_expected_duration"] : 60) . "M");
			$meeting["mee_end_datetime"] = $end->add($duration);
			$meeting["mee_end_datetime"] = $meeting["mee_end_datetime"]->format("Y-m-d H:i:s");
		
			$meeting["mee_rights"] = array();
			
		//	error_log("Meeting id : $meetingId");
		//	error_log("User : $meetingId");
		
		//	error_log("Meeting : " . print_r($meeting, true));
			
			$rights = $meetingRightBo->getByFilters(array("mri_meeting_id" => $meeting[$meetingBo->ID_FIELD]));
			foreach($rights as $right) {
				$meeting["mee_rights"][] = $right["mri_right"];
			}
		
			if ($meeting["loc_type"] == "discord") {
//				include("config/discord.structure.php");

				$discord_vocal_channels = array();
				$discord_text_channels = array();

				require_once("engine/bo/DiscordChannelBo.php");

				$discordChannelBo = new DiscordChannelBo($this->pdo, $this->config);
				
				$channels = $discordChannelBo->getByFilters(array());
				foreach($channels as $channel) {
				    if ($channel["dch_type"] == "text") {
				        $discord_text_channels[$channel["dch_name"]] = $channel["dch_url"];
				    }
				    else if ($channel["dch_type"] == "voice") {
				        $discord_vocal_channels[$channel["dch_name"]] = $channel["dch_url"];
				    }
				}
		
				list($discord_text_channel, $discord_vocal_channel) = explode(",", $meeting["loc_channel"]);
		
				$discord_text_link = @$discord_text_channels[$discord_text_channel];
				$discord_vocal_link = @$discord_vocal_channels[$discord_vocal_channel];
		
				$meeting["loc_discord_text_channel"]	= $discord_text_channel;
				$meeting["loc_discord_vocal_channel"]	= $discord_vocal_channel;
				$meeting["loc_discord_text_link"]		= $discord_text_link;
				$meeting["loc_discord_vocal_link"]		= $discord_vocal_link;
			}
		
			$data["meeting"] = $meeting;
			$data["agendas"] = $agendas;
		
			$data["ok"] = "ok";
		
			$json = json_encode($data, JSON_NUMERIC_CHECK);
		
			if (!$memcache->replace($memcacheKey, $json, 60)) {
				$memcache->set($memcacheKey, $json, 60);
			}
		}
		else {
			$data = json_decode($json, true);
			$data["cached"] = true;
		}

		return $data;
	}

	function getAgendaPoint($meetingId, $pointId, $userId, $requestId) {

		$agendaBo = AgendaBo::newInstance($this->pdo, $this->config);
		$chatBo = ChatBo::newInstance($this->pdo, $this->config);
		$chatAdviceBo = ChatAdviceBo::newInstance($this->pdo, $this->config);
		$conclusionBo = ConclusionBo::newInstance($this->pdo, $this->config);
		$meetingBo = MeetingBo::newInstance($this->pdo, $this->config);
		$motionBo = MotionBo::newInstance($this->pdo, $this->config);
		$taskBo = TaskBo::newInstance($this->pdo, $this->config);
		$voteBo = VoteBo::newInstance($this->pdo, $this->config);

		$memcache = openMemcacheConnection();

		$meeting = $meetingBo->getById($meetingId);
		
		if (!$meeting) {
			return array("ko" => "ko", "message" => "meeting_does_not_exist");
		}

		if ($userId && $userId == $meeting["mee_secretary_member_id"]) {
			if ($meeting["mee_secretary_agenda_id"] != $pointId) {
				$meeting["mee_secretary_agenda_id"] = $pointId;
				$meetingBo->save($meeting);
				
				$memcacheKey = "do_getAgenda_$meetingId";
				$memcache->delete($memcacheKey);
				
				addEvent($meetingId, EVENT_SECRETARY_READS_ANOTHER_POINT, "Le secrétaire de séance vient de changer de point");
			}
		}
		if ($userId) {
			addEvent($meetingId, EVENT_USER_ON_AGENDA_POINT, "", array("agendaPointId" => $pointId, "userId" => $userId));	
		}
		
		// TODO Compute the key // Verify the key
		
		if (false) {
			return array("ko" => "ko", "message" => "meeting_not_accessible");
		}
		
		$memcacheKey = "do_getAgendaPoint_$pointId";

		if ($requestId == -1) {
			$memcache->delete($memcacheKey);
		}
		
		$json = $memcache->get($memcacheKey);
		
		if (!$json) {
			if ($pointId == -1) {
				$agendas = $agendaBo->getByFilters(array("age_meeting_id" => $meetingId));
				$agenda = array("age_meeting_id" => $meetingId, "age_id" => -1);
		
				$motions = array();
		
				foreach($agendas as $currentAgenda) {
					$ageObjects = json_decode($currentAgenda["age_objects"], true);
					foreach($ageObjects as $ageObject) {
						if (isset($ageObject["motionId"])) {
							$motions[] = $ageObject;
						}
					}
				}
		
				$agenda["age_objects"] = json_encode($motions);
			}
			else {
				$agenda = $agendaBo->getById($pointId);
			}
			$now = getNow();
		
			if (!$agenda || $agenda["age_meeting_id"] != $meeting[$meetingBo->ID_FIELD]) {
				echo json_encode(array("ko" => "ko", "message" => "agenda_point_not_accessible"));
				exit();
			}
			
			$agenda["age_objects"] = json_decode($agenda["age_objects"]);
			
			$data = array();
			
			$data["agenda"] = $agenda;
		
		//	error_log($_REQUEST["pointId"]);
		//	error_log(print_r($agenda, true));

			if ($pointId == -1) {
				$motionFilters = array("with_meeting" => true, "mee_id" => $meetingId);
			}
			else {
				$motionFilters = array("mot_agenda_id" => $agenda[$agendaBo->ID_FIELD]);
			}
		
			$motions = $motionBo->getByFilters($motionFilters);
		
			// The appearing of the proposition is based on pro first, no change otherwise
			usort($motions, "sortPropositionsOnLabel");
		
			$data["motions"] = $motions; 
			foreach($data["motions"] as $index => $motion) {
				$data["motions"][$index]["mot_tag_ids"] = json_decode($data["motions"][$index]["mot_tag_ids"]);
		
				foreach($motion as $key => $value) {
					if (strpos($key, "age_") !== false) unset($data["motions"][$index][$key]);
					if (strpos($key, "mee_") !== false) unset($data["motions"][$index][$key]);
				}
		
				if ($motion["mot_deadline"]) {
					$date = getDateTime($motion["mot_deadline"]);
					$dateFormat = $date->format(lang("date_format", false));
		
					$data["motions"][$index]["mot_deadline_string"] = str_replace("{date}", $dateFormat, str_replace("{time}", $date->format(lang("time_format", false)), lang("datetime_format", false)));
		
					$interval = $date->diff($now);
		
					$hours = $interval->format("%a") * 24 + $interval->format("%H");
		
					$data["motions"][$index]["mot_deadline_diff"] = $interval->format("%r". ($hours < 10 ? "0" : "") . $hours.":%I:%S");
				}
			}
		
			$data["chats"] = $chatBo->getByFilters(array("cha_agenda_id" => $agenda[$agendaBo->ID_FIELD]));
		//	$data["chats"] = array();
		
			$chatAdvices = $chatAdviceBo->getByFilters(array("cad_agenda_id" => $agenda[$agendaBo->ID_FIELD]));
		
			foreach($data["chats"] as $index => $chat) {
				$data["chats"][$index]["mem_id"] = $chat["id_adh"] ? $chat["id_adh"] : "G" . $chat["cha_guest_id"];
			//	$data["chats"][$index]["mem_nickname"] = $chat["pin_nickname"] ? $chat["pin_nickname"] : $chat["pseudo_adh"];
				$data["chats"][$index]["mem_nickname"] = htmlspecialchars(utf8_encode($chat["pin_nickname"] ? $chat["pin_nickname"] : ($chat["pseudo_adh"] ? $chat["pseudo_adh"] : $chat["nom_adh"] . ' ' . $chat["prenom_adh"])), ENT_SUBSTITUTE);
			
		 		foreach($chat as $key => $value) {
		 			if (substr($key, 0, 4) != "cha_" && substr($key, 0, 4) != "mem_") {
		 				unset($data["chats"][$index][$key]);
		 			}
					
		 			$data["chats"][$index]["advices"] = array();
		 			foreach($chatAdvices as $advice) {
		 				if ($advice["cad_chat_id"] != $chat["cha_id"]) continue;
		
		 				$data["chats"][$index]["advices"][] = $advice;
		 			}
		 		
		 		}
			}

			$data["votes"] = array();

			if (isset($agendas)) {
				foreach($agendas as $currentAgenda) {
					$votes = $voteBo->getByFilters(array("mot_agenda_id" => $currentAgenda[$agendaBo->ID_FIELD]));
					$data["votes"] = array_merge($data["votes"], $votes);
				}
			}
			else {
				$data["votes"] = $voteBo->getByFilters(array("mot_agenda_id" => $agenda[$agendaBo->ID_FIELD]));
			}

			foreach($data["votes"] as $index => $vote) {
				$data["votes"][$index]["mem_id"] = $vote["id_adh"];
				$data["votes"][$index]["mem_nickname"] = htmlspecialchars(utf8_encode($vote["pseudo_adh"] ? $vote["pseudo_adh"] : $vote["nom_adh"] . ' ' . $vote["prenom_adh"]), ENT_SUBSTITUTE);
			
			
				foreach($vote as $key => $value) {
					if (substr($key, 0, 4) != "vot_" && substr($key, 0, 4) != "mpt_" && substr($key, 0, 4) != "mot_" && substr($key, 0, 4) != "mem_") {
						unset($data["votes"][$index][$key]);
					}
				}
			}

			$data["tasks"] = $taskBo->getByFilters(array("tas_agenda_id" => $agenda[$agendaBo->ID_FIELD]));

			$data["conclusions"] = $conclusionBo->getByFilters(array("con_agenda_id" => $agenda[$agendaBo->ID_FIELD]));

			$data["ok"] = "ok";

			$json = json_encode($data, JSON_NUMERIC_CHECK);

			if (!$memcache->replace($memcacheKey, $json, 60)) {
				$memcache->set($memcacheKey, $json, 60);
			}
		}
		else {
			$data = json_decode($json, true);
			$data["cached"] = true;
		}

		$data["requestId"] = $requestId;

		return $data;
	}

	function getPeople($meetingId, $connectUserId) {
		$memcacheKey = "do_getPeople_$meetingId";

		$memcache = openMemcacheConnection();
		$json = $memcache->get($memcacheKey);

		if (!$json) {
			global $connection;

			if (!$connection) {
				$connection = $this->pdo;
			}

			$meetingBo = MeetingBo::newInstance($this->pdo, $this->config);
			$noticeBo = NoticeBo::newInstance($this->pdo, $this->config);
			$pingBo = PingBo::newInstance($this->pdo, $this->config);
		
			$meeting = $meetingBo->getById($meetingId);
		
			if (!$meeting) {
				return array("ko" => "ko", "message" => "meeting_does_not_exist");
			}
		
			// TODO Compute the key // Verify the key
		
			if (false) {
				return array("ko" => "ko", "message" => "meeting_not_accessible");
			}

			$pings = $pingBo->getByFilters(array("pin_meeting_id" => $meeting[$meetingBo->ID_FIELD]));
			usort($pings, "pingSpeakingRequestCompare");
			
			$order = 1;
		
			//print_r($pings);
			global $now;
			$now = getNow();
		
			$numberOfConnected = 0;
			$numberOfPresents = 0;
		
			$numberOfVoters = 0;
			$numberOfVoters = $meetingBo->getNumberOfVoters($meeting[$meetingBo->ID_FIELD]);
		
			foreach($pings as $index => $ping) {
				if ($ping["pin_speaking"] == 1) {
				    $startSpeaking = getDateTime($ping["pin_speaking_start"]);
				    $speakingTime = $now->getTimestamp() -  $startSpeaking->getTimestamp();
					$pings[$index]["pin_current_speaking_time"] = $speakingTime;
				}
				else {
					$pings[$index]["pin_current_speaking_time"] = 0;
				}
		
				$lastPing = getDateTime($ping["pin_datetime"]);
		
				$diff = $now->getTimestamp() -  $lastPing->getTimestamp();
		
				if ($diff >= CONNECTED_TIME) {
					if ($diff <= DISCONNECTED_TIME) {
						addEvent($meetingId, EVENT_LEFT, "", array("userId" => $ping["pin_member_id"] ? $ping["pin_member_id"] : "G" . $ping["pin_guest_id"]));
					}
					
					if ($ping["pin_guest_id"])
					{
						continue;
					}
				}
				else {
					$numberOfConnected++;
				}
				
				if ($ping["pin_first_presence_datetime"] && $ping["pin_noticed"] == 1) $numberOfPresents++;
		
				if (!$ping["pin_speaking_request"]) continue;
				
				$pings[$index]["pin_speaking_request"] = $order;
				$order++;
			}
		
			//print_r($pings);
		
			$notices = $noticeBo->getByFilters(array("not_meeting_id" => $meeting[$meetingBo->ID_FIELD]));
		
			$data = array();
			$data["numberOfConnected"] = $numberOfConnected;
			$data["numberOfPresents"] = $numberOfPresents;
			$data["numberOfVoters"] = $numberOfVoters;
			$data["notices"] = array();
		
			$usedPings = array();
		
			$numberOfNoticed = 0;
			$numberOfPowers = 0;

			$groupSources = isset($this->config["modules"]["availablegroupsources"]) ? $this->config["modules"]["availablegroupsources"] : $this->config["modules"]["groupsources"];

			foreach($notices as $notice) {
				if (!isset($notice["not_people"])) {
					$notice["not_people"] = array();
				}

				foreach($groupSources as $groupSourceKey) {
					$groupSource = GroupSourceFactory::getInstance($groupSourceKey);
		        	$groupKeyLabel = $groupSource->getGroupKeyLabel();
		
		        	if ($groupKeyLabel["key"] != $notice["not_target_type"]) continue;

//					print_r($notice);

		        	$groupSource->updateNotice($meeting, $notice, $pings, $usedPings);
				}
		
				$data["notices"][] = $notice;
				
				if (isset($notice["not_people"])) $numberOfNoticed += count($notice["not_people"]);
				foreach($notice["not_people"] as $childPeople) {
					$numberOfPowers += $childPeople["mem_power"];
				}
		
				if (isset($notice["no_children"])) {
					foreach($notice["no_children"] as $childNotice) {
						if (isset($childNotice["not_people"])) $numberOfNoticed += count($childNotice["not_people"]);
						$numberOfPowers += $childNotice["not_power"];
					}
				}
			}
		
			$data["numberOfNoticed"] = $numberOfNoticed;
			$data["numberOfPowers"] = $numberOfPowers;
			$data["mee_quorum"] = $meeting["mee_quorum"];
			$data["mee_computed_quorum"] = ceil(eval("return " . $meeting["mee_quorum"] . ";"));
		
			$nowString = $now->format("Y-m-d H:i:s");
		
			if (
					$meeting["mee_start_time"] && // we have a start date
					$meeting["mee_start_time"] != "0000-00-00 00:00:00" && // and it's not an empty date
					$meeting["mee_start_time"] < $nowString && // and we are now behind it (obviously the case)
					$meeting["mee_status"] != "closed") { // and the meeting is still not closed
				foreach($usedPings as $ping) {
					// If the noticed information is not set, set it, the used pings are noticed people
					if (!$ping["pin_noticed"]) {
						$noticedPing = array($pingBo->ID_FIELD => $ping[$pingBo->ID_FIELD]);
						$noticedPing["pin_noticed"] = 1;
						$pingBo->save($noticedPing);
					}
		
					if (!$ping["pin_first_presence_datetime"] || $ping["pin_first_presence_datetime"] == "0000-00-00 00:00:00") {
						$lastPing = getDateTime($ping["pin_datetime"]);
		
						$diff = $now->getTimestamp() -  $lastPing->getTimestamp();
		
						if ($diff < CONNECTED_TIME) {
							$presencePing = array($pingBo->ID_FIELD => $ping[$pingBo->ID_FIELD]);
							$presencePing["pin_first_presence_datetime"] = $nowString;
							$pingBo->save($presencePing);
						}
					}
				}
		
				foreach($pings as $ping) {
					if (!$ping["pin_first_presence_datetime"] || $ping["pin_first_presence_datetime"] == "0000-00-00 00:00:00") {
						$lastPing = getDateTime($ping["pin_datetime"]);
		
						$diff = $now->getTimestamp() -  $lastPing->getTimestamp();
		
						if ($diff < CONNECTED_TIME) {
							$presencePing = array($pingBo->ID_FIELD => $ping[$pingBo->ID_FIELD]);
							$presencePing["pin_first_presence_datetime"] = $nowString;
							$pingBo->save($presencePing);
						}
					}
				}
			}
		
			$data["visitors"] = array();
		
			foreach($pings as $ping) {
				$people = array("mem_id" => $ping["id_adh"] ? $ping["id_adh"] : "G" . $ping["pin_guest_id"]);
				$people["mem_nickname"] = htmlspecialchars(utf8_encode($ping["id_adh"] ? $ping["pseudo_adh"] : $ping["pin_nickname"]));
				$people["mem_meeting_president"] = ($people["mem_id"] == $meeting["mee_president_member_id"]) ? 1 : 0;
				$people["mem_meeting_secretary"] = ($people["mem_id"] == $meeting["mee_secretary_member_id"]) ? 1 : 0;
		
				$lastPing = getDateTime($ping["pin_datetime"]);
		
				$diff = $now->getTimestamp() -  $lastPing->getTimestamp();
		
				if ($diff < CONNECTED_TIME) {
					$people["mem_connected"] = true;
				}
				else if (!$ping["id_adh"]) {
					continue;
				}
		
				$people["mem_speaking"] = $ping["pin_speaking"];
				$people["mem_speaking_time"] = $ping["pin_speaking_time"];
				$people["mem_current_speaking_time"] = $ping["pin_current_speaking_time"];
				$people["mem_current_speaking_time_string"] = getDurationString($ping["pin_current_speaking_time"]);
				$people["mem_speaking_request"] = $ping["pin_speaking_request"];
		
				$data["visitors"][] = $people;
			}
		
			$data["ok"] = "ok";
		
			$json = json_encode($data, JSON_NUMERIC_CHECK);
		
			if (!$memcache->replace($memcacheKey, $json, 60)) {
				$memcache->set($memcacheKey, $json, 60);
			}
		}
		else {
			$data = json_decode($json, true);
			$data["cached"] = true;
		}
		
		if (!$connectUserId) {
			$data["notices"] = array();
		}
		
		$data["mee_quorum"] = phpToLanguageQuorum($data["mee_quorum"]);

		return $data;
	}

	function getEvents($meetingId) {
		$data = array();
		$data["ok"] = "ok";
		$data["timestamp"] = time();
		$data["events"] = getEvents($meetingId);
		//$data["events"] = array();
		
		return $data;
	}

	function getTaskHooks() {
	
		$directoryHandler = dir("../task_hooks/");

		while(($fileEntry = $directoryHandler->read()) !== false) {
			if($fileEntry != '.' && $fileEntry != '..' && strpos($fileEntry, ".php")) {
				require_once("task_hooks/" . $fileEntry);
			}
		}
		$directoryHandler->close();
		
		global $taskHooks;
		
		return $taskHooks;
/*						
						if ($hooks) {
							foreach($hooks as $hookKey => $hook) {
								$data[$hookKey] = $hook->doHook($agenda, $motion, $proposition, $chats);
							}
						}
*/						
	}
	
	function moveObject($from, $to, $object, $user) {
		$data = array();

		$chatBo = ChatBo::newInstance($this->pdo, $this->config);
		$agendaBo = AgendaBo::newInstance($this->pdo, $this->config);
		$meetingBo = MeetingBo::newInstance($this->pdo, $this->config);

		$fullObject = null;

		switch($object["type"]) {
			case "chat":
				$objectBo  = ChatBo::newInstance($this->pdo, $this->config);
				$fullObject = $objectBo->getById(intval($object["id"]));
				$agendaFieldLabel = "cha_agenda_id";

				foreach($fullObject as $key => $value) {
					if (substr($key, 0, 4) != "cha_") {
						unset($fullObject[$key]);
					}
				}

				$objectLink = array("chatId" => $object["id"]);
				$objectLinkLabel = "chatId";

				break;
			case "motion":
				$objectBo  = MotionBo::newInstance($this->pdo, $this->config);
				$fullObject = $objectBo->getById(intval($object["id"]));
				$agendaFieldLabel = "mot_agenda_id";

				foreach($fullObject as $key => $value) {
					if (substr($key, 0, 4) != "mot_") {
						unset($fullObject[$key]);
					}
				}

				$objectLink = array("motionId" => $object["id"]);
				$objectLinkLabel = "motionId";

				$amendmentAgendaFilters = array("age_label" => "amendments-" . $object["id"], "age_parent_id" => $from["agendaId"]);
				$amendmentAgendas = $agendaBo->getByFilters($amendmentAgendaFilters);

				if (count($amendmentAgendas)) {
					foreach($amendmentAgendas as $amendmentAgenda) {
						$amendmentAgenda["age_parent_id"] = $to["agendaId"];
						$amendmentAgenda["age_meeting_id"] = $to["meetingId"];

						$agendaBo->save($amendmentAgenda);
					}
				}

				$chatFilters = array("cha_motion_id" => $object["id"]);
				$motionChats = $chatBo->getByFilters($chatFilters);

				foreach($motionChats as $motionChat) {
					$this->moveobject($from, $to, array("type" => "chat", "id" => $motionChat["cha_id"]), $user);
				}

				// we take the motion out of the trash
				$fullObject["mot_trashed"] = 0;

				break;
		}

		if (!$fullObject) {
			$data["ko"] = "no object";

			return $data;
		}
		else if ($fullObject[$agendaFieldLabel] != $from["agendaId"]) {
			$data["ko"] = "bad agenda id";

			return $data;
		}

		$fullObject[$agendaFieldLabel] = $to["agendaId"];
		$data["object"] = $fullObject;

		$objectBo->save($fullObject);

		$fromAgenda = $agendaBo->getById($from["agendaId"]);
		$toAgenda = $agendaBo->getById($to["agendaId"]);

		$fromAgenda["age_objects"] = json_decode($fromAgenda["age_objects"], true);
		$toAgenda["age_objects"] = json_decode($toAgenda["age_objects"], true);

		$data["from"] = $fromAgenda;
		$data["to"] = $toAgenda;

		$newFromAgendaObjects = array();

		foreach($fromAgenda["age_objects"] as $index => $agendaObject) {
			if (isset($agendaObject[$objectLinkLabel]) && $agendaObject[$objectLinkLabel] == $objectLink[$objectLinkLabel]) {
//				unset($fromAgenda["age_objects"][$index]);
//				break;
			}
			else {
				$newFromAgendaObjects[] = $agendaObject;
			}
		}

		$fromAgenda["age_objects"] = $newFromAgendaObjects;
		$toAgenda["age_objects"][] = $objectLink;

		$data["from_after"] = $fromAgenda;
		$data["to_after"] = $toAgenda;

		$fromAgenda["age_objects"] = json_encode($fromAgenda["age_objects"]);
		$toAgenda["age_objects"] = json_encode($toAgenda["age_objects"]);

		$agendaBo->save($fromAgenda);
		$agendaBo->save($toAgenda);

		$data["ok"] = "ok";

		return $data;
	}
}
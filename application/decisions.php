<?php /*
    Copyright 2015-2020 Cédric Levieux, Parti Pirate

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
include_once("header.php");

require_once("engine/bo/TagBo.php");
require_once("engine/bo/GuestBo.php");
require_once("engine/bo/SearchBo.php");

function meetingCompare($meetingA, $meetingB) {
    if ($meetingA["mee_datetime"] == $meetingB["mee_datetime"]) {
        return 0;
    }
    return ($meetingA["mee_datetime"] > $meetingB["mee_datetime"]) ? -1 : 1;
}

$skeleton = true;
$groupId = null;

if (isset($_REQUEST["id"])) {
    $skeleton = false;
    $groupId = $_REQUEST["id"];
}

$tagBo = TagBo::newInstance($connection, $config);
$searchBo = SearchBo::newInstance($connection, $config);

$conclusions = $searchBo->conclusionSearch(array("query" => ""));
$propositions = $searchBo->propositionSearch(array("query" => "", "mot_status" => "resolved", "mee_type" => "meeting"));

$userId = SessionUtils::getUserId($_SESSION);

$objects = array();

foreach($conclusions as $conclusion) {
    if (!$conclusion["not_target_type"]) continue;
    if (!$conclusion["con_text"]) continue;

//echo "<pre>";
//vprint_r($conclusion);
//echo "</pre>";

    $voter = array("not_target_type" => $conclusion["not_target_type"], "not_target_id" => $conclusion["not_target_id"]);
    $voterIndex = json_encode($voter);

    if (!isset($objects[$voterIndex])) {
        $objects[$voterIndex] = array("voter" => $voter, "meetings" => array());
    }
    
    if (!isset($objects[$voterIndex]["meetings"][$conclusion["mee_id"]])) {
        $objects[$voterIndex]["meetings"][$conclusion["mee_id"]] = array();

        $objects[$voterIndex]["meetings"][$conclusion["mee_id"]]["mee_id"] = $conclusion["mee_id"];
        $objects[$voterIndex]["meetings"][$conclusion["mee_id"]]["mee_label"] = $conclusion["mee_label"];
        $objects[$voterIndex]["meetings"][$conclusion["mee_id"]]["mee_datetime"] = $conclusion["mee_datetime"];
        $objects[$voterIndex]["meetings"][$conclusion["mee_id"]]["agendas"] = array();
    }
    
    if (!isset($objects[$voterIndex]["meetings"][$conclusion["mee_id"]]["agendas"][$conclusion["age_id"]])) {
        $objects[$voterIndex]["meetings"][$conclusion["mee_id"]]["agendas"][$conclusion["age_id"]] = array();

        $objects[$voterIndex]["meetings"][$conclusion["mee_id"]]["agendas"][$conclusion["age_id"]]["age_id"] = $conclusion["age_id"];
        $objects[$voterIndex]["meetings"][$conclusion["mee_id"]]["agendas"][$conclusion["age_id"]]["age_label"] = $conclusion["age_label"];
        $objects[$voterIndex]["meetings"][$conclusion["mee_id"]]["agendas"][$conclusion["age_id"]]["age_objects"] = $conclusion["age_objects"];
        $objects[$voterIndex]["meetings"][$conclusion["mee_id"]]["agendas"][$conclusion["age_id"]]["objects"] = array();
    }
    
    $objectIndex = json_encode($conclusion["object"]);
    
    $objects[$voterIndex]["meetings"][$conclusion["mee_id"]]["agendas"][$conclusion["age_id"]]["objects"][$objectIndex] = $conclusion;
}

foreach($propositions as $proposition) {
    if (!$proposition["not_target_type"]) continue;
//    if (!$proposition["con_text"]) continue;

//echo "<pre>";
//vprint_r($conclusion);
//echo "</pre>";

    $voter = array("not_target_type" => $proposition["not_target_type"], "not_target_id" => $proposition["not_target_id"]);
    $voterIndex = json_encode($voter);

    if (!isset($objects[$voterIndex])) {
        $objects[$voterIndex] = array("voter" => $voter, "meetings" => array());
    }
    
    if (!isset($objects[$voterIndex]["meetings"][$proposition["mee_id"]])) {
        $objects[$voterIndex]["meetings"][$proposition["mee_id"]] = array();

        $objects[$voterIndex]["meetings"][$proposition["mee_id"]]["mee_id"] = $proposition["mee_id"];
        $objects[$voterIndex]["meetings"][$proposition["mee_id"]]["mee_label"] = $proposition["mee_label"];
        $objects[$voterIndex]["meetings"][$proposition["mee_id"]]["mee_datetime"] = $proposition["mee_datetime"];
        $objects[$voterIndex]["meetings"][$proposition["mee_id"]]["agendas"] = array();
    }
    
    if (!isset($objects[$voterIndex]["meetings"][$proposition["mee_id"]]["agendas"][$proposition["age_id"]])) {
        $objects[$voterIndex]["meetings"][$proposition["mee_id"]]["agendas"][$proposition["age_id"]] = array();

        $objects[$voterIndex]["meetings"][$proposition["mee_id"]]["agendas"][$proposition["age_id"]]["age_id"] = $proposition["age_id"];
        $objects[$voterIndex]["meetings"][$proposition["mee_id"]]["agendas"][$proposition["age_id"]]["age_label"] = $proposition["age_label"];
        $objects[$voterIndex]["meetings"][$proposition["mee_id"]]["agendas"][$proposition["age_id"]]["age_objects"] = $proposition["age_objects"];
        $objects[$voterIndex]["meetings"][$proposition["mee_id"]]["agendas"][$proposition["age_id"]]["objects"] = array();
    }

    $motionIndex = array(array("type" => "meeting", "id" => $proposition["mee_id"]), array("type" => "agenda", "id" => $proposition["age_id"]), array("type" => "motion", "id" => $proposition["mot_id"]));
    $motionIndex = json_encode($motionIndex);

    if (!isset($objects[$voterIndex]["meetings"][$proposition["mee_id"]]["agendas"][$proposition["age_id"]]["objects"][$motionIndex])) {
        $objects[$voterIndex]["meetings"][$proposition["mee_id"]]["agendas"][$proposition["age_id"]]["objects"][$motionIndex] = array();
        $objects[$voterIndex]["meetings"][$proposition["mee_id"]]["agendas"][$proposition["age_id"]]["objects"][$motionIndex]["mot_id"] = $proposition["mot_id"];
        $objects[$voterIndex]["meetings"][$proposition["mee_id"]]["agendas"][$proposition["age_id"]]["objects"][$motionIndex]["mot_title"] = $proposition["mot_title"];
        $objects[$voterIndex]["meetings"][$proposition["mee_id"]]["agendas"][$proposition["age_id"]]["objects"][$motionIndex]["mot_description"] = $proposition["mot_description"];
        $objects[$voterIndex]["meetings"][$proposition["mee_id"]]["agendas"][$proposition["age_id"]]["objects"][$motionIndex]["mot_win_limit"] = $proposition["mot_win_limit"];

        $objects[$voterIndex]["meetings"][$proposition["mee_id"]]["agendas"][$proposition["age_id"]]["objects"][$motionIndex]["propositions"] = array();

    	$proposition["mot_tag_ids"] = json_decode($proposition["mot_tag_ids"]);
    	$objects[$voterIndex]["meetings"][$proposition["mee_id"]]["agendas"][$proposition["age_id"]]["objects"][$motionIndex]["mot_tags"] = array();
    	
    	if (count($proposition["mot_tag_ids"])) {
    		$tags = $tagBo->getByFilters(array("tag_ids" => $proposition["mot_tag_ids"]));
    		$objects[$voterIndex]["meetings"][$proposition["mee_id"]]["agendas"][$proposition["age_id"]]["objects"][$motionIndex]["mot_tags"] = $tags;
    	}

    }

//    echo json_encode($proposition["object"]);

    $objectIndex = json_encode($proposition["object"]);
    
    $objects[$voterIndex]["meetings"][$proposition["mee_id"]]["agendas"][$proposition["age_id"]]["objects"][$motionIndex]["propositions"][$objectIndex] = $proposition;
}

//echo "<pre>";
//print_r($objects);
//echo "</pre>";

?>

<style>
    
.nav-tabs>li>a {
    border-radius: 4px;
    margin-left: 10px;
}

.nav-tabs>li>a:hover {
    margin-left: 5px;
}

.nav-tabs>li.active>a, .nav-tabs>li.active>a:focus, .nav-tabs>li.active>a:hover {
    color: #555;
    cursor: default;
    background-color: #fff;
    border: 1px solid #ddd;
    margin-left: 5px;
}

.nav-tabs>li.li-header>a, .nav-tabs>li.li-header>a:focus, .nav-tabs>li.li-header>a:hover {
    color: #555;
    cursor: default;
    background-color: #ccc;
    border: 1px solid #aaa;
    margin-left: 0px;
}

</style>

<div class=" theme-showcase meeting" role="main"
	style="margin-left: 32px; margin-right: 32px; "
    role="main">
	<ol class="breadcrumb">
		<li><a href="index.php"><?php echo lang("breadcrumb_index"); ?></a></li>
		<li class="active"><?php echo lang("breadcrumb_decisions"); ?></li>
	</ol>

<?php if ($skeleton) {

$personalGroups = array();

foreach($config["modules"]["groupsources"] as $groupSourceId) {
    $groupSource = GroupSourceFactory::getInstance($groupSourceId);

    if (method_exists($groupSource, "getGroups")) {
        $groups = $groupSource->getGroups($sessionUserId);
        $personalGroups = array_merge($personalGroups, $groups);
    }
}

?>

	<div class="row">
		<div class="col-md-3">

        	<!-- Nav tabs -->
        	<ul class="nav nav-tabs" role="tablist">
<?php 	
//        $active = "active";
        $active = "";

		foreach($objects as $voterIndex => $voter) {
		    $id = $voter["voter"]["not_target_type"] . "_" .  $voter["voter"]["not_target_id"];

            $voterLabel = $id;

            foreach($config["modules"]["groupsources"] as $groupSourceKey) {
            	$groupSource = GroupSourceFactory::getInstance($groupSourceKey);
            	$groupKeyLabel = $groupSource->getGroupKeyLabel();

            	if ($groupKeyLabel["key"] != $voter["voter"]["not_target_type"]) continue;

            	$label = $groupSource->getGroupLabel($voter["voter"]["not_target_id"]);
            	
            	if ($label != null) {
            	    $voterLabel = $label;
            	}

            	break;
            }
            
            $objects[$voterIndex]["label"] = $voterLabel;
        }

        function compareVoterByLabel($voterA, $voterB) {
            if ($voterA["label"] == $voterB["label"]) return 0;
            
            return $voterA["label"] < $voterB["label"] ? -1 : 1;
        }

        uasort($objects, "compareVoterByLabel");

        //  Only active
?>        
		<li role="presentation" class="li-header" style="float: none;">
			<a href="#" disabled=disabled><?php echo lang("decisions_ownactivegroups"); ?></a>
		</li>
<?php        
        foreach($personalGroups as $personalGroup) {
            if (!$personalGroup["active"]) continue;
            $currentId = $personalGroup["type"] . "_" .  $personalGroup["id"];

    		foreach($objects as $voterIndex => $voter) {
    		    $id = $voter["voter"]["not_target_type"] . "_" .  $voter["voter"]["not_target_id"];
    		    
    		    if ($currentId != $id) continue;
    ?>
            		<li role="presentation" class="<?php echo $active; ?>" style="float: none;">
            			<a href="#<?php echo $id; ?>" role="tab" data-toggle="tab"><?php echo $objects[$voterIndex]["label"]; ?></a>
            		</li>
    <?php 	    $active = "";
                break;
    		}
    	}

?><li style="float: none;">&nbsp;</li><?php

        // Not yours
?>        
		<li role="presentation" class="li-header" style="float: none;">
			<a href="#" disabled=disabled><?php echo lang("decisions_othergroups"); ?></a>
		</li>
<?php        
		foreach($objects as $voterIndex => $voter) {
		    $id = $voter["voter"]["not_target_type"] . "_" .  $voter["voter"]["not_target_id"];

            $found = false;

            foreach($personalGroups as $personalGroup) {
                $currentId = $personalGroup["type"] . "_" .  $personalGroup["id"];
    		    if ($currentId == $id) {
    		        $found = true;
    		        break;
    		    }
            }
		    
		    if ($found) continue;
?>
        		<li role="presentation" class="<?php echo $active; ?>" style="float: none;">
        			<a href="#<?php echo $id; ?>" role="tab" data-toggle="tab"><?php echo $objects[$voterIndex]["label"]; ?></a>
        		</li>
<?php 	    $active = "";
		}

?><li style="float: none;">&nbsp;</li><?php

        //  Only unactive
?>        
		<li role="presentation" class="li-header" style="float: none;">
			<a href="#" disabled=disabled><?php echo lang("decisions_owninactivegroups"); ?></a>
		</li>
<?php        
        foreach($personalGroups as $personalGroup) {
            if ($personalGroup["active"]) continue;
            $currentId = $personalGroup["type"] . "_" .  $personalGroup["id"];

    		foreach($objects as $voterIndex => $voter) {
    		    $id = $voter["voter"]["not_target_type"] . "_" .  $voter["voter"]["not_target_id"];
    		    
    		    if ($currentId != $id) continue;
    ?>
            		<li role="presentation" class="<?php echo $active; ?>" style="float: none;">
            			<a href="#<?php echo $id; ?>" role="tab" data-toggle="tab"><?php echo $objects[$voterIndex]["label"]; ?></a>
            		</li>
    <?php 	    $active = "";
                break;
    		}
    	}
?>
        	</ul>
    	</div>
		<div class="col-md-9">

	<!-- Tab panes -->
        	<div class="tab-content">
<?php 	$active = "active";
		foreach($objects as $voterIndex => $voter) {
		    $id = $voter["voter"]["not_target_type"] . "_" . $voter["voter"]["not_target_id"];
?>
        		<div role="tabpanel" class="tab-pane <?php echo $active; ?>" id="<?php echo $id; ?>">
        
        		</div>
<?php 		$active = "";
		}?>
        	</div>

    	</div>
	</div>

<?php } ?>

<?php   if (!$skeleton) {?>
<?php
        $page = isset($_REQUEST["page"]) ? $_REQUEST["page"] : 0;
        $numberOfObjectsPerPage = isset($_REQUEST["numberOfObjectsPerPage"]) ? $_REQUEST["numberOfObjectsPerPage"] : 10;
        $currentObjectIndex = 0;
        $numberOfObjects = 0;

        // Beginning of pagination

		foreach($objects as $voterIndex => $voter) {
		    $id = $voter["voter"]["not_target_type"] . "_" . $voter["voter"]["not_target_id"];

		    if ($groupId != $id) continue; 

            $meetings = $voter["meetings"];

            uasort($meetings, 'meetingCompare');

            foreach($meetings as $meeting) { 
                foreach($meeting["agendas"] as $agenda) { 

                    $agendaObjects = json_decode($agenda["age_objects"], true);
                    $agendaObjects = array_reverse($agendaObjects);
    
                    foreach($agendaObjects as $agendaObject) {
                        foreach($agenda["objects"] as $object) { 
                            if (isset($agendaObject["motionId"]) && isset($object["mot_id"]) && $object["mot_id"] == $agendaObject["motionId"]) {
                            }
                            else if (isset($agendaObject["conclusionId"]) && isset($object["con_id"]) && $object["con_id"] == $agendaObject["conclusionId"]) {
                            }
                            else {
                                // neither a conclusion neither a motion, skip it
                                continue;
                            }

                            $numberOfObjects++;
    
                            if ($numberOfObjects < ($page * $numberOfObjectsPerPage + 1)) {
                                // this object has no impact
                            } 
                            else if ($numberOfObjects > (($page + 1) * $numberOfObjectsPerPage)) {
                                // this object has no impact
                            }
                            else {
                                // we must show the whole object
                                $objects[$voterIndex]["meetings"][$meeting["mee_id"]]["mee_to_be_shown"] = true;
                                $objects[$voterIndex]["meetings"][$meeting["mee_id"]]["agendas"][$agenda["age_id"]]["age_to_be_shown"] = true;
                            }
                        }
                    }
                }
            }
		}
		
		$numberOfPages = ceil($numberOfObjects / $numberOfObjectsPerPage);

        // End of pagination

		foreach($objects as $voterIndex => $voter) {
		    $id = $voter["voter"]["not_target_type"] . "_" . $voter["voter"]["not_target_id"];
		    
		    if ($groupId != $id) continue;

?>
    		<div role="tabpanel" class="tab-pane" id="<?php echo $id; ?>">
    		    <div class="text-center pagination-container">
<?php   if ($page > 0) { ?>
    		        <a class="pagination-link btn btn-info" href="?page=0" data-page="0" title="La page premiere"><i class="glyphicon glyphicon-fast-backward"></i></a>
    		        <a class="pagination-link btn btn-info" href="?page=<?=$page - 1?>" data-page="<?=$page - 1?>" title="La page précédente"><i class="glyphicon glyphicon-backward"></i></a>
<?php   } ?>

<?php   if ($page > 1) { ?>
                    <a class="pagination-link btn btn-info"                 href="?page=<?=$page - 2?>" data-page="<?=$page - 2?>"><?=$page - 1?></a>
<?php   } ?>
<?php   if ($page > 0) { ?>
                    <a class="pagination-link btn btn-info"                 href="?page=<?=$page - 1?>" data-page="<?=$page - 1?>"><?=$page - 0?></a>
<?php   } ?>
                    <!-- page en cours -->
                    <a class="pagination-link btn btn-primary active-page"  href="?page=<?=$page    ?>" data-page="<?=$page    ?>"><?=$page + 1?></a>

<?php   if ($page < $numberOfPages - 1) { ?>
                    <a class="pagination-link btn btn-info"                 href="?page=<?=$page + 1?>" data-page="<?=$page + 1?>"><?=$page + 2?></a>
<?php   } ?>
<?php   if ($page < $numberOfPages - 2) { ?>
                    <a class="pagination-link btn btn-info"                 href="?page=<?=$page + 2?>" data-page="<?=$page + 2?>"><?=$page + 3?></a>
<?php   } ?>

<?php   if ($page < $numberOfPages - 1) { ?>
    		        <a class="pagination-link btn btn-info" href="?page=<?=$page + 1?>" data-page="<?=$page + 1?>" title="La page suivante"><i class="glyphicon glyphicon-forward"></i></a>
    		        <a class="pagination-link btn btn-info" href="?page=<?=$numberOfPages - 1?>" data-page="<?=$numberOfPages - 1?>" title="La page dernière"><i class="glyphicon glyphicon-fast-forward"></i></a>
<?php   } ?>
    		    </div>
<?php

            $meetings = $voter["meetings"];

            uasort($meetings, 'meetingCompare');

            foreach($meetings as $meeting) { 
                $date = new DateTime($meeting["mee_datetime"]);
                $date = $date->format(lang("date_format"));
            ?>
             <div style="display: <?=isset($meeting["mee_to_be_shown"]) ? "block" : "none" ?>">
                 <h2><?php echo $meeting["mee_label"]; ?> du <?php echo $date; ?></h2>
                 
<?php
                foreach($meeting["agendas"] as $agenda) { 
                
                    $agendaObjects = json_decode($agenda["age_objects"], true);
                    $agendaObjects = array_reverse($agendaObjects);
                
                ?>
                 <div style="display: <?=isset($agenda["age_to_be_shown"]) ? "block" : "none" ?>">
                     <h3><?php echo $agenda["age_label"]; ?> <a href="meeting.php?id=<?php echo $meeting["mee_id"]; ?>#agenda-<?php echo $agenda["age_id"]; ?>|" target="_blank"><span class="glyphicon glyphicon-new-window"></span></a></h3>

<?php
                    foreach($agendaObjects as $agendaObject) {
                    foreach($agenda["objects"] as $object) { 

                        if (isset($agendaObject["motionId"]) && isset($object["mot_id"]) && $object["mot_id"] == $agendaObject["motionId"]) {
                        }
                        else if (isset($agendaObject["conclusionId"]) && isset($object["con_id"]) && $object["con_id"] == $agendaObject["conclusionId"]) {
                        }
                        else {
                            // neither a conclusion neither a motion, skip it
                            continue;
                        }

                        $currentObjectIndex++;
                        
                        if ($currentObjectIndex < ($page * $numberOfObjectsPerPage + 1)) continue;
                        if ($currentObjectIndex > (($page + 1) * $numberOfObjectsPerPage)) continue;
                    ?>
                     <div>

                        <ul class="list-group objects">

<?php                       if (isset($object["con_id"])) { ?>

                    		<li id="conclusion-<?php echo $object["con_id"]; ?>"
                    				class="list-group-item conclusion" data-id="<?php echo $object["con_id"]; ?>">
                    			<span class="fa fa-lightbulb-o"></span>
                    			<span class="conclusion-text"><?php echo $object["con_text"]; ?></span>
                    		</li>

<?php                       }
                            else if (isset($object["mot_id"])) { ?>


                    		<li id="motion-<?php echo $object["mot_id"]; ?>" data-id="<?php echo $object["mot_id"]; ?>" class="list-group-item motion">
                    			<h4>
                    				<span class="fa fa-archive"></span>
                    				<span class="motion-title"><?php echo $object["mot_title"]; ?></span>
                    			</h4>
                    
                    			<div class="motion-description">
                    				<div class="motion-description-text"><?php echo $object["mot_description"]; ?></div>
                    			</div>
                    
                    			<div class="motion-propositions">
                    			    
<?php                           foreach($object["propositions"] as $proposition) {

//                                    print_r($proposition);

                                    $propositionClass = "text-danger";
                                    if ($proposition["mpr_winning"] == "1") {
                                        $propositionClass = "text-success";
                                    }
?>
	<div id="proposition-<?php echo $proposition["mpr_id"]; ?>"
			class="row proposition <?php echo $propositionClass; ?>" data-id="<?php echo $proposition["mpr_id"]; ?>"
			style="margin: 2px; min-height:22px; display: block;">
		<span class="pull-left fa fa-cube"></span>
		<span class="pull-left proposition-label"><?php echo $proposition["mpr_label"]; ?></span>
		<span class="pull-left powers"></span>
		<span class="pull-left"> : </span>
		
		<ul class="pull-left vote-container">
<?php   
//		        echo $proposition["mpr_explanation"];
		        $explanation = json_decode($proposition["mpr_explanation"], true);
//		        print_r($explanation);
		        if (isset($explanation["votes"])) {
		            foreach($explanation["votes"] as $vote) { 
		                if (!$vote["power"]) continue;
?>
				<li class="vote">
        			<span class="nickname"><?php echo $vote["memberLabel"]; ?></span>
        			<span
        				title="Pouvoir du vote"
        				data-toggle="tooltip" data-placement="bottom" 
        				class="badge power">
        			    <?php    if ($object["mot_win_limit"] == -2) { 
        			                 echo lang("motion_majorityJudgment_" . $vote["power"]);
        			                 echo " / ";
        				             echo $vote["votePower"];
        			             }
        			             else if ($vote["votePower"]) { ?>
        			    <?php        echo $vote["power"]; ?>
        			    
        			    <?php        if ($vote["votePower"] != $vote["power"]) { ?>
        				/ <?php          echo $vote["votePower"]; ?>
        				<?php        } ?>
        				<?php    } else echo "0"; ?>
        				
        				</span>
        		</li>
		<?php       }
		        }?>
		</ul>
	</div>
<?php                           } ?>
                    			    
                    			    
                    			</div>
                    			<?php   if (count($object["mot_tags"])) { ?>
                    			<div class="motion-tags">
                    			    <?php   foreach($object["mot_tags"] as $tag) { ?>
                    			        <span class="badge"><?php echo $tag["tag_label"]; ?></span>
                    			    <?php   } ?>
                    			</div>
                    			<?php   } ?>
                    		</li>


<?php                       }
                            else { ?>
                            
                                <?php print_r($object); ?>
                            
<?php                       }?>

                        </ul>


                     </div>           
<?php
                    }
                    }
?>
                     
                     
                </div>           
<?php
            }
?>

            </div>           
<?php
            }
?>

    		    <div class="text-center pagination-container">
<?php   if ($page > 0) { ?>
    		        <a class="pagination-link btn btn-info" href="?page=0" data-page="0" title="La page premiere"><i class="glyphicon glyphicon-fast-backward"></i></a>
    		        <a class="pagination-link btn btn-info" href="?page=<?=$page - 1?>" data-page="<?=$page - 1?>" title="La page précédente"><i class="glyphicon glyphicon-backward"></i></a>
<?php   } ?>

<?php   if ($page > 1) { ?>
                    <a class="pagination-link btn btn-info"                 href="?page=<?=$page - 2?>" data-page="<?=$page - 2?>"><?=$page - 1?></a>
<?php   } ?>
<?php   if ($page > 0) { ?>
                    <a class="pagination-link btn btn-info"                 href="?page=<?=$page - 1?>" data-page="<?=$page - 1?>"><?=$page - 0?></a>
<?php   } ?>
                    <!-- page en cours -->
                    <a class="pagination-link btn btn-primary active-page"  href="?page=<?=$page    ?>" data-page="<?=$page    ?>"><?=$page + 1?></a>

<?php   if ($page < $numberOfPages - 1) { ?>
                    <a class="pagination-link btn btn-info"                 href="?page=<?=$page + 1?>" data-page="<?=$page + 1?>"><?=$page + 2?></a>
<?php   } ?>
<?php   if ($page < $numberOfPages - 2) { ?>
                    <a class="pagination-link btn btn-info"                 href="?page=<?=$page + 2?>" data-page="<?=$page + 2?>"><?=$page + 3?></a>
<?php   } ?>

<?php   if ($page < $numberOfPages - 1) { ?>
    		        <a class="pagination-link btn btn-info" href="?page=<?=$page + 1?>" data-page="<?=$page + 1?>" title="La page suivante"><i class="glyphicon glyphicon-forward"></i></a>
    		        <a class="pagination-link btn btn-info" href="?page=<?=$numberOfPages - 1?>" data-page="<?=$numberOfPages - 1?>" title="La page dernière"><i class="glyphicon glyphicon-fast-forward"></i></a>
<?php   } ?>
    		    </div>



    		</div>



<?php
            
        }
?>

<?php } ?>





<?php include("connect_button.php"); ?>
</div>

<div class="container otbHidden">
</div>

<div class="lastDiv"></div>

<script>
</script>
<?php include("footer.php");?>

</body>
</html>
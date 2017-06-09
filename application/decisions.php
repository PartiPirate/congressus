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
    along with Congressus.  If not, see <http://www.gnu.org/licenses/>.
*/
include_once("header.php");

require_once("engine/bo/MeetingBo.php");
require_once("engine/bo/GuestBo.php");
require_once("engine/bo/SearchBo.php");
require_once("engine/bo/GaletteBo.php");
require_once("engine/bo/GroupBo.php");
require_once("engine/bo/ThemeBo.php");

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

$themeBo = ThemeBo::newInstance($connection, $config);
$groupBo = GroupBo::newInstance($connection, $config);
$galetteBo = GaletteBo::newInstance($connection, $config["galette"]["db"]);

$themes = $themeBo->getThemes(array("with_group_information" => true));
$groups = $groupBo->getGroups();
$galetteGroups = $galetteBo->getGroups();

$searchBo = SearchBo::newInstance($connection, $config);
$conclusions = $searchBo->conclusionSearch(array("query" => ""));
$propositions = $searchBo->propositionSearch(array("query" => ""));

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
        
        $objects[$voterIndex]["meetings"][$proposition["mee_id"]]["agendas"][$proposition["age_id"]]["objects"][$motionIndex]["propositions"] = array();
    }

//    echo json_encode($proposition["object"]);

    $objectIndex = json_encode($proposition["object"]);
    
    $objects[$voterIndex]["meetings"][$proposition["mee_id"]]["agendas"][$proposition["age_id"]]["objects"][$motionIndex]["propositions"][$objectIndex] = $proposition;
}

//echo "<pre>";
//print_r($objects);
//echo "</pre>";

?>

<div class="container theme-showcase meeting" role="main">
	<ol class="breadcrumb">
		<li><a href="index.php"><?php echo lang("breadcrumb_index"); ?></a></li>
		<li class="active"><?php echo lang("breadcrumb_decisions"); ?></li>
	</ol>

<?php if ($skeleton) {?>

	<!-- Nav tabs -->
	<ul class="nav nav-tabs" role="tablist">
<?php 	$active = "active";
		foreach($objects as $voterIndex => $voter) {
		    $id = $voter["voter"]["not_target_type"] . "_" .  $voter["voter"]["not_target_id"];

            $voterLabel = $id;
            
            switch($voter["voter"]["not_target_type"]) {
                case "dlp_groups":
                    $voterLabel = $groups[$voter["voter"]["not_target_id"]]["gro_label"];
                    break;
                case "dlp_themes":
                    foreach($themes as $theme) {
                        if ($theme["the_id"] == $voter["voter"]["not_target_id"]) {
                            $voterLabel = $theme["the_label"];
                            break;
                        }
                    }
                    break;
                case "galette_groups":
                    foreach($galetteGroups as $galetteGroup) {
                        if ($galetteGroup["id_group"] == $voter["voter"]["not_target_id"]) {
                            $voterLabel = utf8_encode($galetteGroup["group_name"]);
                            break;
                        }
                    }
                    break;
            }

?>
		<li role="presentation" class="<?php echo $active; ?>">
			<a href="#<?php echo $id; ?>" role="tab" data-toggle="tab"><?php echo $voterLabel; ?></a>
		</li>
<?php 	$active = "";
		}?>
	</ul>

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

<?php } ?>

<?php   if (!$skeleton) {?>
<?php
		foreach($objects as $voterIndex => $voter) {
		    $id = $voter["voter"]["not_target_type"] . "_" . $voter["voter"]["not_target_id"];
		    
		    if ($groupId != $id) continue; 

?>
    		<div role="tabpanel" class="tab-pane" id="<?php echo $id; ?>">
<?php

            $meetings = $voter["meetings"];

            uasort($meetings, 'meetingCompare');

            foreach($meetings as $meeting) { 
                $date = new DateTime($meeting["mee_datetime"]);
                $date = $date->format(lang("date_format"));
            ?>
             <div>
                 <h2><?php echo $meeting["mee_label"]; ?> du <?php echo $date; ?></h2>
                 
<?php
                foreach($meeting["agendas"] as $agenda) { 
                
                    $agendaObjects = json_decode($agenda["age_objects"], true);
                    $agendaObjects = array_reverse($agendaObjects);
                
                ?>
                 <div>
                     <h3><?php echo $agenda["age_label"]; ?> <a href="meeting.php?id=<?php echo $meeting["mee_id"]; ?>#agenda-<?php echo $agenda["age_id"]; ?>|" target="_blank"><span class="glyphicon glyphicon-new-window"></span></a></h3>

<?php
                    foreach($agendaObjects as $agendaObject) {
                    foreach($agenda["objects"] as $object) { 
                    
                        if (isset($agendaObject["motionId"]) && isset($object["mot_id"]) && $object["mot_id"] == $agendaObject["motionId"]) {
                        }
                        else if (isset($agendaObject["conclusionId"]) && isset($object["con_id"]) && $object["con_id"] == $agendaObject["conclusionId"]) {
                        }
                        else {
                            continue;
                        }
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
		<?php   $explanation = json_decode($proposition["mpr_explanation"], true);
		        if (isset($explanation["votes"])) {
		            foreach($explanation["votes"] as $vote) { ?>
				<li class="vote">
        			<span class="nickname"><?php echo $vote["memberLabel"]; ?></span>
        			<span
        				title="Pouvoir du vote"
        				class="badge power"><?php echo $vote["votePower"]; ?></span>
        		</li>
		<?php       }
		        }?>
		</ul>
	</div>
<?php                           } ?>
                    			    
                    			    
                    			</div>
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
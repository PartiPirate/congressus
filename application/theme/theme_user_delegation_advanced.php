<?php /*
	Copyright 2015-2019 Cédric Levieux, Parti Pirate

	This file is part of Personae.

    Personae is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    Personae is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with Personae.  If not, see <http://www.gnu.org/licenses/>.
*/

if (isset($config["tags"]["url"])) {
    $tags = json_decode(file_get_contents($config["tags"]["url"]), true);
}
else {
    require_once("engine/bo/TagBo.php");
    $tagBo = TagBo::newInstance($connection, $config);

    $tags = $tagBo->getTagsForCombobox();
    if (!count($tags)) unset($tags);
}

function orderByIdentity($eligibleA, $eligibleB) {
    $compare = strcasecmp(GaletteBo::showIdentity($eligibleA), GaletteBo::showIdentity($eligibleB));

    return $compare;
}

?>

<!-- DELEGATION ADVANCED part -->

<?php   if (!$isVoting || $theme["the_voting_method"] != "demliq") return; ?>

<!-- DEV -->
<?php   //if ($sessionUserId != 12 && $sessionUserId != 1 && $sessionUserId != 649 && $sessionUserId != 338 && $sessionUserId != 887 && $sessionUserId != 731 && $sessionUserId != 928) return; ?>

<?php   
        $statusEligibles = array("candidate" => array(), "anti" => array(), "voting" => array());

        foreach($eligibles as $eligible) {
		    if ($eligible["id_adh"] == $sessionUserId) continue;
		    if (!$eligible["id_type_cotis"]) continue; // TODO view to change that
		    
		    switch($eligible["can_status"]) {
    			case "candidate":
    			    $statusEligibles["candidate"][] = $eligible;
    				break;
    			case "anti":
    			    $statusEligibles["anti"][] = $eligible;
    				break;
    			case "neutral":
    			case "voting":
    			    $statusEligibles["voting"][] = $eligible;
    				break;
		    }
	    }

        $candidates = $statusEligibles["candidate"];
        usort($candidates, "orderByIdentity");

        $votings = $statusEligibles["voting"];
        usort($votings, "orderByIdentity");

        $antis = $statusEligibles["anti"];
        usort($antis, "orderByIdentity");

?>

<?php 

// print_r($delegations); 

$conditionalDelegations = array();
$hasConditionDelegations = false;

foreach($delegations as $delegation) {
    if (!$delegation["dco_id"]) {
        $delegation["dco_id"] = 0;
    }
    else {
        $hasConditionDelegations = true;
    }

    if (! isset($conditionalDelegations[$delegation["dco_id"]])) {
        $conditionalDelegations[$delegation["dco_id"]] = $delegation;
        $conditionalDelegations[$delegation["dco_id"]]["delegations"] = array();
        
        if (!$delegation["dco_conditions"]) {
            $delegation["dco_conditions"] = "[]";
        }

        $conditionalDelegations[$delegation["dco_id"]]["conditions"] = json_decode($delegation["dco_conditions"], true);
    }

    $conditionalDelegations[$delegation["dco_id"]]["delegations"][] = $delegation;
}

// print_r($conditionalDelegations);

?>

<div id="conditional-delegetation-container">

<?php

if (!$hasConditionDelegations) {
    $conditionalDelegations[-1] = array();
    $conditionalDelegations[-1]["delegations"] = array();
    $conditionalDelegations[-1]["conditions"] = array();
    $conditionalDelegations[-1]["dco_end_of_delegation"] = false;
}

?>

<?php
    foreach($conditionalDelegations as $conditionalDelegationId => $conditionalDelegation) {
        if ($conditionalDelegationId == 0) continue;
?>        

<div class="panel panel-default conditional-delegation">
	<div class="panel-heading">
        <button type="button" class="btn btn-danger remove-conditional-delegation-btn pull-right" title="<?php echo lang("conditional_remove_conditional") ;?>"><i class="fa fa-minus" aria-hidden="true"></i></button>
		<input class="conditional-delegation-label-input form-control" placeholder="<?php echo lang("conditional_conditional_label") ;?>" style="width: calc(100% - 42px); display: inline-block;">
	</div>
	<div class="panel-body">

        <div class="form-group form-headers">
            <label class="col-md-2 control-label" ></label>
            <label class="col-md-3 control-label" ><?php echo lang("conditional_condition_headers_field"); ?></label>
            <label class="col-md-3 control-label" ><?php echo lang("conditional_condition_headers_operator"); ?></label>
            <label class="col-md-2 control-label" ><?php echo lang("conditional_condition_headers_value"); ?></label>
            <label class="col-md-2 control-label" ></label>
        </div>
        <div class="clearfix"></div>

        <div class="condition-container">
            
<?php
            $currentCondtions = $conditionalDelegation["conditions"];
            if (!count($currentCondtions)) {
                $currentCondtions[] = array();
            }

            foreach($currentCondtions as $condition) {
?>
            
            <div class="form-group condition clearfix">
                <div class="col-md-2">
                    <select name="condition-interaction-select" class="form-control">
                        <option value="if"      <?php if (@$condition["interaction"] == "if")     echo 'selected="selected"' ?>><?php echo lang("conditional_condition_operator_if"); ?></option>
                        <option value="and"     <?php if (@$condition["interaction"] == "and")    echo 'selected="selected"' ?>><?php echo lang("conditional_condition_operator_and"); ?></option>
                        <option value="or"      <?php if (@$condition["interaction"] == "or")     echo 'selected="selected"' ?>><?php echo lang("conditional_condition_operator_or"); ?></option>
                        <option value="andif"   <?php if (@$condition["interaction"] == "andif")  echo 'selected="selected"' ?>><?php echo lang("conditional_condition_operator_andif"); ?></option>
                        <option value="orif"    <?php if (@$condition["interaction"] == "orif")   echo 'selected="selected"' ?>><?php echo lang("conditional_condition_operator_orif"); ?></option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="field-select" class="form-control">
                        <option value=""></option>
                        <optgroup label="<?php echo lang("conditional_condition_field_motion"); ?>">
                            <option value="motion_title"       <?php if (@$condition["field"] == "motion_title")       echo 'selected="selected"' ?> data-type="string"><?php echo lang("conditional_condition_field_motion_title"); ?></option>
                            <option value="motion_description" <?php if (@$condition["field"] == "motion_description") echo 'selected="selected"' ?> data-type="string"><?php echo lang("conditional_condition_field_motion_description"); ?></option>
                            <option value="motion_tags"        <?php if (@$condition["field"] == "motion_tags")        echo 'selected="selected"' ?> data-type="<?php   if (isset($tags)) { echo "tag"; } else { echo "string"; } ?>"><?php echo lang("conditional_condition_field_motion_tags"); ?></option>
                            <option value="motion_date"        <?php if (@$condition["field"] == "motion_date")        echo 'selected="selected"' ?> data-type="date"><?php echo lang("conditional_condition_field_motion_date"); ?></option>
                        </optgroup>
                        <optgroup label="<?php echo lang("conditional_condition_field_motion_voters"); ?>">
                            <option value="voter_me"           <?php if (@$condition["field"] == "voter_me")           echo 'selected="selected"' ?> data-type="me"><?php echo lang("conditional_condition_field_motion_me"); ?></option>
                        </optgroup>
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="operator-select" class="form-control">
                        <option value="" data-need-value="false"></option>
                        <optgroup label="<?php echo lang("conditional_condition_operator_string"); ?>" data-type="string">
                            <option value="contains"        <?php if (@$condition["operator"] == "contains")        echo 'selected="selected"' ?> data-need-value="true"><?php echo lang("conditional_condition_operator_string_contains"); ?></option>
                            <option value="do_not_contain"  <?php if (@$condition["operator"] == "do_not_contain")  echo 'selected="selected"' ?> data-need-value="true"><?php echo lang("conditional_condition_operator_string_does_not_contain"); ?></option>
                            <option value="equals"          <?php if (@$condition["operator"] == "equals")          echo 'selected="selected"' ?> data-need-value="true"><?php echo lang("conditional_condition_operator_string_equal"); ?></option>
                        </optgroup>
                        <optgroup label="<?php echo lang("conditional_condition_operator_me"); ?>" data-type="me">
                            <option value="do_vote"         <?php if (@$condition["operator"] == "do_vote")         echo 'selected="selected"' ?> data-need-value="false"><?php echo lang("conditional_condition_operator_me_voted"); ?></option>
                        </optgroup>
                        <optgroup label="<?php echo lang("conditional_condition_operator_date"); ?>" data-type="date">
                            <option value="is_before"       <?php if (@$condition["operator"] == "is_before")       echo 'selected="selected"' ?> data-need-value="true"><?php echo lang("conditional_condition_operator_date_before"); ?></option>
                            <option value="is_after"        <?php if (@$condition["operator"] == "is_after")        echo 'selected="selected"' ?> data-need-value="true"><?php echo lang("conditional_condition_operator_date_after"); ?></option>
                        </optgroup>
                        <optgroup label="<?php echo lang("conditional_condition_operator_tag"); ?>" data-type="tag">
                            <option value="equals"          <?php if (@$condition["operator"] == "equals")          echo 'selected="selected"' ?> data-need-value="true"><?php echo lang("conditional_condition_operator_tag_equal"); ?></option>
                        </optgroup>
                    </select>
                </div>
                <div class="col-md-2">
                    <input name="value-input"       type="text" placeholder="" value="<?php echo @$condition["value"]; ?>" class="form-control input-md">
                    <input name="value-date-input"  type="date" placeholder="" value="<?php echo @$condition["value"]; ?>" class="form-control input-md" style="height: 38px;">
                    <?php   if (isset($tags)) { ?>
                    <select name="value-tag-input" class="form-control input-md">
                    <?php   foreach($tags as $tag) { ?>
                        <option value="<?=$tag["label"]?>" <?php     if (@$condition["value"] && $condition["value"] == $tag["label"]) { echo "selected='selected'"; } ?> ><?=$tag["label"]?></option>
                    <?php   } ?>
                    </select>
                    <?php   } ?>
                </div>
                <div class="col-md-2">
                    <button type="button" class="btn btn-primary add-condition-btn"   title="<?php echo lang("conditional_add_condition");    ?>"><i class="fa fa-plus"  aria-hidden="true"></i></button>
                    <button type="button" class="btn btn-danger remove-condition-btn" title="<?php echo lang("conditional_remove_condition"); ?>"><i class="fa fa-minus" aria-hidden="true"></i></button>
                </div>
            </div>

<?php
            }
?>


        </div>

        <hr>

        <div class="form-group form-headers">
            <label class="col-md-2 control-label" ></label>
            <label class="col-md-4 control-label" ><?php echo lang("conditional_delegation_delegate"); ?></label>
            <label class="col-md-4 control-label" ><?php echo lang("conditional_delegation_power");    ?></label>
            <label class="col-md-2 control-label" ></label>
        </div>
        <div class="clearfix"></div>

        <div class="delegation-container">

<?php

/*
            $currentDelegations = array(array());
            if (isset($conditionalDelegations[0])) {
                $currentDelegations = $conditionalDelegations[0]["delegations"];
            }
*/

            $currentDelegations = $conditionalDelegation["delegations"];
            if (!count($currentDelegations)) {
                $currentDelegations[] = array();
            }

            foreach($currentDelegations as $delegation) {

?>

            <div class="form-group delegation clearfix">
                <div class="col-md-2" style="padding-top:  10px; text-align: right; ">
                    <?php echo lang("conditional_delegation_give_to"); ?>
                </div>
                <div class="col-md-4">
                    <select name="person-select" class="form-control">
                        <option value=""></option>
                        <optgroup label="<?php echo lang("conditional_delegation_delegate_candidate"); ?>">
                            <?php   foreach($candidates as $eligible) { ?>
                                <option value="<?php echo $eligible["id_adh"]; ?>" <?php if(@$delegation["del_member_to"] == $eligible["id_adh"]) echo 'selected="selected"'; ?>><?php echo GaletteBo::showIdentity($eligible); ?></option>
                            <?php   } ?>
                        </optgroup>
                        <optgroup label="<?php echo lang("conditional_delegation_delegate_eligible"); ?>">
                            <?php   foreach($votings as $eligible) { ?>
                                <option value="<?php echo $eligible["id_adh"]; ?>" <?php if(@$delegation["del_member_to"] == $eligible["id_adh"]) echo 'selected="selected"'; ?>><?php echo GaletteBo::showIdentity($eligible); ?></option>
                            <?php   } ?>
                        </optgroup>
                        <optgroup label="<?php echo lang("conditional_delegation_delegate_dont"); ?>">
                            <?php   foreach($antis as $eligible) { ?>
                                <option value="<?php echo $eligible["id_adh"]; ?>" <?php if(@$delegation["del_member_to"] == $eligible["id_adh"]) echo 'selected="selected"'; ?>><?php echo GaletteBo::showIdentity($eligible); ?></option>
                            <?php   } ?>
                        </optgroup>
                    </select>
                </div>
                <div class="col-md-4">
                    <input name="value-input" type="number" min="0" max="<?php echo $theme["the_voting_power"]; ?>" value="<?php echo (@$delegation["del_member_to"] != @$delegation["del_member_from"] ? @$delegation["del_power"] : ""); ?>" placeholder="" class="form-control input-md">
                </div>
                <div class="col-md-2">
                    <button type="button" class="btn btn-primary add-delegation-btn"   title="<?php echo lang("conditional_add_delegation");    ?>"><i class="fa fa-plus"  aria-hidden="true"></i></button>
                    <button type="button" class="btn btn-danger remove-delegation-btn" title="<?php echo lang("conditional_remove_delegation"); ?>"><i class="fa fa-minus" aria-hidden="true"></i></button>
                </div>
            </div>

<?php       } ?>

        </div>            

        <div class="form-group">
            <div class="col-md-2 text-right">
                <label class="checkbox-inline">
                    <input type="checkbox" name="end-of-delegation" <?php if(@$conditionalDelegation["dco_end_of_delegation"]) echo 'checked="checked"'; ?> value="1">
                </label>
            </div>
            <label class="col-md-10 control-label" style="padding-top:  10px; "><?php echo lang("conditional_end_of_delegations"); ?></label>
        </div>

    </div>
    
</div>

<?php
    }
?>


</div>

<button id="add-conditional-delegation-btn" type="button" class="btn btn-primary"><i class="fa fa-plus" aria-hidden="true"></i> <?php echo lang("conditional_add_conditional"); ?></button>

<br>
<br>

<div class="panel panel-default" id="default-delegation">
	<div class="panel-heading">
        <?php echo lang("conditional_default_delegations"); ?>
	</div>
	<div class="panel-body">

        <div class="form-group form-headers">
            <label class="col-md-2 control-label" ></label>
            <label class="col-md-4 control-label" ><?php echo lang("conditional_delegation_delegate"); ?></label>
            <label class="col-md-4 control-label" ><?php echo lang("conditional_delegation_power");    ?></label>
            <label class="col-md-2 control-label" ></label>
        </div>
        <div class="clearfix"></div>

        <div class="delegation-container">

<?php

            $currentDelegations = array(array());
            if (isset($conditionalDelegations[0])) {
                $currentDelegations = $conditionalDelegations[0]["delegations"];
            }

            foreach($currentDelegations as $delegation) {

?>

            <div class="form-group delegation clearfix">
                <div class="col-md-2" style="padding-top:  10px; text-align: right; ">
                    <?php echo lang("conditional_delegation_give_to"); ?>
                </div>
                <div class="col-md-4">
                    <select name="person-select" class="form-control">
                        <option value=""></option>
                        <optgroup label="<?php echo lang("conditional_delegation_delegate_candidate"); ?>">
                            <?php   foreach($candidates as $eligible) { ?>
                                <option value="<?php echo $eligible["id_adh"]; ?>" <?php if(@$delegation["del_member_to"] == $eligible["id_adh"]) echo 'selected="selected"'; ?>><?php echo GaletteBo::showIdentity($eligible); ?></option>
                            <?php   } ?>
                        </optgroup>
                        <optgroup label="<?php echo lang("conditional_delegation_delegate_eligible"); ?>">
                            <?php   foreach($votings as $eligible) { ?>
                                <option value="<?php echo $eligible["id_adh"]; ?>" <?php if(@$delegation["del_member_to"] == $eligible["id_adh"]) echo 'selected="selected"'; ?>><?php echo GaletteBo::showIdentity($eligible); ?></option>
                            <?php   } ?>
                        </optgroup>
                        <optgroup label="<?php echo lang("conditional_delegation_delegate_dont"); ?>">
                            <?php   foreach($antis as $eligible) { ?>
                                <option value="<?php echo $eligible["id_adh"]; ?>" <?php if(@$delegation["del_member_to"] == $eligible["id_adh"]) echo 'selected="selected"'; ?>><?php echo GaletteBo::showIdentity($eligible); ?></option>
                            <?php   } ?>
                        </optgroup>
                    </select>
                </div>
                <div class="col-md-4">
                    <input name="value-input" type="number" min="0" max="<?php echo $theme["the_voting_power"]; ?>" value="<?php echo @$delegation["del_power"]; ?>" placeholder="" class="form-control input-md">
                </div>
                <div class="col-md-2">
                    <button type="button" class="btn btn-primary add-delegation-btn"   title="<?php echo lang("conditional_add_delegation");    ?>"><i class="fa fa-plus"  aria-hidden="true"></i></button>
                    <button type="button" class="btn btn-danger remove-delegation-btn" title="<?php echo lang("conditional_remove_delegation"); ?>"><i class="fa fa-minus" aria-hidden="true"></i></button>
                </div>
            </div>

<?php       } ?>

        </div>            

    </div>
</div>

<button type="button" id="save-delegations-btn" class="btn btn-success" title="<?php echo lang("conditional_save_delegations"); ?>" <?php	if ($theme["the_delegation_closed"]) { echo "disabled=disabled"; } ?> ><i class="fa fa-save" aria-hidden="true"></i> <?php echo lang("conditional_save_delegations"); ?></button>

<br><br>

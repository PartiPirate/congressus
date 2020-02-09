<?php

/*
 Copyright 2018-2019 Cédric Levieux, Parti Pirate

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

class ConditionalFactory {

    static function getConditionInstance($condition) {
        if (strtolower($condition["field"]) == "motion_date") return new MotionDateCondition();
        if (strtolower($condition["field"]) == "motion_title") return new MotionTitleCondition();
        if (strtolower($condition["field"]) == "motion_description") return new MotionDescriptionCondition();
        if (strtolower($condition["field"]) == "motion_tags") return new MotionTagsCondition();
        if (strtolower($condition["field"]) == "voter_me") return new VoterMeCondition();

        return null;
    }

    static function getOperatorInstance($condition) {
        if (strtolower($condition["operator"]) == "is_after") return new IsAfterOperator();
        if (strtolower($condition["operator"]) == "is_before") return new IsBeforeOperator();
        if (strtolower($condition["operator"]) == "contains") return new ContainsOperator();
        if (strtolower($condition["operator"]) == "equals") return new EqualsOperator();
        if (strtolower($condition["operator"]) == "do_not_contain") return new DoNotContainOperator();
        if (strtolower($condition["operator"]) == "do_vote") return new DoVoteOperator();

        return null;
    }

    static function testConditions(&$conditions, $context) {

        $conditionalGroups = array();
        $currentIndex = -1;

        foreach($conditions as $index => &$condition) {
        	$conditionResolver = ConditionalFactory::getConditionInstance($condition);
//        	echo "<br>\n";
//        	print_r($conditionResolver);
        	
        	$condition["result"] = $conditionResolver->evaluateCondition($condition, $context);

        	if ($condition["interaction"] == "if" || $condition["interaction"] == "andif" || $condition["interaction"] == "orif") {
        	    $currentIndex++;
        	    $conditionalGroups[$currentIndex] = array("interaction" => $condition["interaction"], "conditions" => array(), "result" => true);
        	}

        	$conditionalGroups[$currentIndex]["conditions"][] = $condition;
        }

//        echo "<br>\n<pre>";
//        print_r($conditionalGroups);
//        echo "</pre><br>\n";

        $result = true;

        foreach($conditionalGroups as &$conditionalGroup) {

            foreach($conditionalGroup["conditions"] as $cindex => $condition) {
                if ($cindex == 0) {
                    $conditionalGroup["result"] = $condition["result"];
                }
                else if ($condition["interaction"] == "or") {
                    $conditionalGroup["result"] |= $condition["result"];
                }
                else if ($condition["interaction"] == "and") {
                    $conditionalGroup["result"] &= $condition["result"];
                }
            }

            if ($conditionalGroup["interaction"] == "if" || $conditionalGroup["interaction"] == "andif") {
                $result &= $conditionalGroup["result"];
            }
            else if ($conditionalGroup["interaction"] == "orif") {
                $result |= $conditionalGroup["result"];
            }

        }

//        echo "<br>\n<pre>";
//        print_r($conditionalGroups);
//        echo "</pre><br>\n";

        return $result;
    }
}

?>
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

/* global $ */
/* global themePower */

function checkInteractions() {
    $(".condition-container").each(function() {
        var interactions = $(this).find("select[name=condition-interaction-select]");

        interactions.each(function(index) {
            var select = $(this);
            if (index == 0) {
                select.find("option[value=if]").removeAttr("disabled");
                select.val("if");
                select.find("option[value!=if]").attr("disabled", "disabled");
            }
            else {
                select.find("option[value!=if]").removeAttr("disabled");
                if (select.val() == "if") {
                    select.val("and");
                }
                select.find("option[value=if]").attr("disabled", "disabled");
            }
        });
    });
}

function checkRemoveButtons() {
    $(".condition-container").each(function() {
       var conditions = $(this).find(".remove-condition-btn");
       if (conditions.length == 1) {
           conditions.hide();
       }
       else {
           conditions.show();
       }
    });
    $(".delegation-container").each(function() {
       var delegations = $(this).find(".remove-delegation-btn");
       if (delegations.length == 1) {
           delegations.hide();
       }
       else {
           delegations.show();
       }
    });
    $("#conditional-delegetation-container").each(function() {
       var delegations = $(this).find(".remove-conditional-delegation-btn");
       if (delegations.length == 1) {
           delegations.hide();
       }
       else {
           delegations.show();
       }
    });
}

function addConditionHandler() {
    $("body").on("click", ".condition-container .add-condition-btn", function() {
        var condition = $(this).parents(".condition");
        var conditionContainer = $(this).parents(".condition-container");
        
        var clonedCondition = condition.clone(true, true);
        condition.after(clonedCondition);

    	checkRemoveButtons();
        checkInteractions();
    });
}

function addDelegationHandler() {
    $("body").on("click", ".delegation-container .add-delegation-btn", function() {
        var delegation = $(this).parents(".delegation");
        var delegationContainer = $(this).parents(".delegation-container");
        
        var clonedDelegation = delegation.clone(true, true);
        delegation.after(clonedDelegation);

    	checkRemoveButtons();
    });
}

function removeConditionHandler() {
    $("body").on("click", ".condition-container .remove-condition-btn", function() {
        var condition = $(this).parents(".condition");
        var conditionContainer = $(this).parents(".condition-container");

        if (conditionContainer.find(".condition").length > 1) {
            condition.remove();
        	checkRemoveButtons();
            checkInteractions();
        }
    });
}

function removeDelegationHandler() {
    $("body").on("click", ".delegation-container .remove-delegation-btn", function() {
        var delegation = $(this).parents(".delegation");
        var delegationContainer = $(this).parents(".delegation-container");

        if (delegationContainer.find(".delegation").length > 1) {
            delegation.remove();
        	checkRemoveButtons();
        }
    });
}

function addConditionDelegationHandler() {
    $("body").on("click", "#add-conditional-delegation-btn", function() {
        var delegationContainer = $("#conditional-delegetation-container");
        var delegations = delegationContainer.find(".conditional-delegation")
        var delegation = delegations.eq(delegations.length - 1);
        
        var clonedDelegation = delegation.clone(true, true);
        delegation.after(clonedDelegation);

    	checkRemoveButtons();
    });
}

function removeConditionDelegationHandler() {
    $("body").on("click", "#conditional-delegetation-container .remove-conditional-delegation-btn", function() {
        var conditionalDelegation = $(this).parents(".conditional-delegation");
        var conditionalDelegationContainer = $(this).parents("#conditional-delegetation-container");

        if (conditionalDelegationContainer.find(".conditional-delegation").length > 1) {
            conditionalDelegation.remove();
        	checkRemoveButtons();
        }
    });
}

function changeOperatorHandler() {
    $("body").on("change", "select[name=operator-select]", function() {
        var option = $(this).find("option:selected");
        var needValue = (("" + option.data("need-value")) == "true");

        var condition = $(this).parents(".condition");

        var typeOption = condition.find("select[name=field-select] option:selected");
        var type = typeOption.data("type");

        if (needValue && type == "string") {
            condition.find("input[name=value-input]").show();
            condition.find("input[name=value-date-input]").hide();
            condition.find("select[name=value-tag-input]").hide();
        }
        else if (needValue && type == "date") {
            condition.find("input[name=value-input]").hide();
            condition.find("input[name=value-date-input]").show();
            condition.find("select[name=value-tag-input]").hide();
        }
        else if (needValue && type == "tag") {
            condition.find("input[name=value-input]").hide();
            condition.find("input[name=value-date-input]").hide();
            condition.find("select[name=value-tag-input]").show();
        }
        else {
            condition.find("input[name=value-input]").hide();
            condition.find("input[name=value-date-input]").hide();
            condition.find("select[name=value-tag-input]").hide();
        }
    });
}

function changeFieldHandler() {
    $("body").on("change", "select[name=field-select]", function() {
        var option = $(this).find("option:selected");
        var type = option.data("type");

        var condition = $(this).parents(".condition");

        condition.find("select[name=operator-select] optgroup").hide();
        condition.find("select[name=operator-select] optgroup[data-type="+type+"]").show();

        var selectedOption = condition.find("select[name=operator-select] option:selected");
        if (selectedOption.parent("optgroup").css("display") == "none") {
            condition.find("select[name=operator-select]").val("");
            condition.find("select[name=operator-select]").change();
        }
    });
}

function addSaveDelegationHandler() {
    $("body").on("click", "#save-delegations-btn", function() {
        var delegation = {"conditionals" : []};

        var dumpDelegations = function(delegationContainer) {
            var delegations = [];

            delegationContainer.find(".delegation").each(function() {
               var currentDelegation = {};

               currentDelegation["id"] = $(this).find("select[name=person-select]").val();
               currentDelegation["power"] = $(this).find("input[name=value-input]").val();

               if (currentDelegation["id"] && currentDelegation["power"]) {
                   delegations.push(currentDelegation);
               }
            });

            return delegations;
        }

        var dumpConditions = function(conditionContainer) {
            var conditions = [];

            conditionContainer.find(".condition").each(function() {
                var currentCondition = {};

                currentCondition["interaction"] = $(this).find("select[name=condition-interaction-select]").val();
                currentCondition["field"] = $(this).find("select[name=field-select]").val();
                currentCondition["operator"] = $(this).find("select[name=operator-select]").val();
               
                var typeOption = $(this).find("select[name=field-select] option:selected");
                var type = typeOption.data("type");

                switch(type) {
                    case "date":
                        currentCondition["value"] = $(this).find("input[name=value-date-input]").val();
                        break;
                    case "tag":
                        currentCondition["value"] = $(this).find("select[name=value-tag-input]").val();
                        break
                    default:
                        currentCondition["value"] = $(this).find("input[name=value-input]").val();
                        break;
                }

                if (currentCondition["field"] && currentCondition["operator"]) {
                    conditions.push(currentCondition);
                }
            });

            return conditions;
        }

        $("#conditional-delegetation-container .conditional-delegation").each(function() {
            var conditionalDelegation = {};
            conditionalDelegation["conditions"] = dumpConditions($(this).find(".condition-container"));
            conditionalDelegation["delegations"] = dumpDelegations($(this).find(".delegation-container"));

            conditionalDelegation["endOfDelegation"] = ($(this).find("input[name=end-of-delegation]:checked").length > 0);

            if (conditionalDelegation.delegations.length > 0 || conditionalDelegation["endOfDelegation"]) {
                delegation.conditionals.push(conditionalDelegation);
            }
        });

        delegation["default"] = dumpDelegations($("#default-delegation").find(".delegation-container"));

//        console.log(delegation);

        var form = {};
        form.del_theme_id = $("#votingForm input[name=del_theme_id]").val();
        form.del_theme_type = $("#votingForm input[name=del_theme_type]").val();
        form.delegation = JSON.stringify(delegation);

        $.post("do_save_advanced_delegations.php", form, function(data) {
            if (data.ok) {
                $("#success_theme_votingAlert").parents(".container").show();
    			$("#success_theme_votingAlert").show().delay(2000).fadeOut(1000, function() {
    				$(this).parents(".container").hide();
    			});
    		}
    		else {
    			$("#" + data.error + "Alert").parents(".container").show();
    			$("#" + data.error + "Alert").show().delay(2000).fadeOut(1000, function() {
    				$(this).parents(".container").hide();
    			});
    		}
        }, "json");
    });
}

$(function() {
    addSaveDelegationHandler();

    addConditionDelegationHandler();
	removeConditionDelegationHandler();

	addConditionHandler();
	removeConditionHandler();

	addDelegationHandler();
	removeDelegationHandler();

	changeOperatorHandler();
	changeFieldHandler();

	checkRemoveButtons();
	checkInteractions();

    $("#conditional-delegetation-container").sortable({"distance": 5});
    $("select[name=field-select]").change();
	$("select[name=operator-select]").change();
});

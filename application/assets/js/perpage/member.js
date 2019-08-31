function endorseSkillHandler() {
	var dialog = $("#dialog-endorse-skill");
	var form = dialog.find("form");

	dialog.find(".btn-ok").addClass("disabled").prop("disabled", true);

	$.post("skill_api.php?method=do_endorseUserSkill", form.serialize(), function(data) {

		var susId = dialog.find("input[name=sus_id]").val();

		var button = $(".data[data-id="+susId+"] .btn-endorse-skill");
		var parent = button.parent();
		button.remove();

		parent.text("Vous avez approuvé");

		dialog.find(".btn-ok").removeClass("disabled").prop("disabled", false);
		dialog.modal("hide");
	}, "json");
}

function showEndorseSkillModalHandler() {
	var dialog = $("#dialog-endorse-skill");

	dialog.find(".skill-label").text($(this).parents(".data").data("skill-label"));
	dialog.find(".skill-user-identity").text($(this).parents(".data").data("identity"));
	dialog.find("input[name=sus_id]").val($(this).parents(".data").data("id"));
	
	dialog.modal("show");
}

$(function() {
	$("#panel-endorsments").on("click", ".btn-endorse-skill", showEndorseSkillModalHandler);
	$("#dialog-endorse-skill").on("click", ".btn-ok", endorseSkillHandler);
});
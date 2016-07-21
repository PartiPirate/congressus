
$(function() {
	checkMailSecure = function() {
		$(".secure-message").hide();
		$(".secure-value-" + $("#smtp_secure_input").val()).show();
	}
	
	submitAdministrationForm = function() {
		$.post("do_updateAdministration.php", $("#administration-form").serialize(), function(data) {
			$("#administration_save_successAlert").show().delay(2000).fadeOut(1000);
		}, "json");
	}
	
	$("#smtp_secure_input").change(checkMailSecure);
	
	$("#administration-form").submit(function(event) {
		event.preventDefault();
		submitAdministrationForm();
	})
	
	$("#btn-administration-save").click(function(event) {
		event.preventDefault();
		submitAdministrationForm();
	});
	
	checkMailSecure();
	
	$("#btn-administration-save").prop("disabled", false);
})
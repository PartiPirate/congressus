
$(function() {
	checkMailSecure = function() {
		$(".secure-message").hide();
		$(".secure-value-" + $("#smtp_secure_input").val()).show();
	}
	
	submitAdministrationForm = function() {
		$.post("do_updateAdministration.php", $("#administration-form").serialize(), function(data) {
			
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
})
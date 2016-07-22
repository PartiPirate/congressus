
$(function() {
	pingDatabase = function() {
		$("#btn-ping-database").prop("disabled", true);
		
		$.post("administration_api.php?method=do_pingDatabase", $("#administration-form").serialize(), function(data) {
//			$("#administration_save_successAlert").show().delay(2000).fadeOut(1000);

			if (data.ok) {
				$("#administration_ping_successAlert").show().delay(2000).fadeOut(1000);
			}
			else {
				$("#administration_ping_" + data.error + "Alert").show().delay(2000).fadeOut(1000);
			}

//			alert(data);
			$("#btn-ping-database").prop("disabled", false);
			
		}, "json");
	}
	
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

	$("#btn-ping-database").click(function(event) {
		event.preventDefault();
		pingDatabase();
	});

	$("#btn-administration-save").click(function(event) {
		event.preventDefault();
		submitAdministrationForm();
	});
	
	checkMailSecure();
	
	$("#btn-ping-database").prop("disabled", false);
	$("#btn-administration-save").prop("disabled", false);
})
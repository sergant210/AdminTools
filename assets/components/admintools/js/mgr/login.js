$('#send-email-btn').on('click',function(e){
	$('#errormsg').text('');
	$(this).attr('disabled', 'disabled');
	$.post(document.location.href, {action: "login", userdata: $('#userdata').val()}, function(res) {
		if (res.success) {
			$('.panel-body').text(res.message);
		} else {
			$('#errormsg').text(res.message);
		}
	}, 'json');
	$(this).removeAttr("disabled");
});
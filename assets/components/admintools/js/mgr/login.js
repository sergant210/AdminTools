var  btn = document.getElementById('send-email-btn');
btn.addEventListener('click', function(e){
	document.getElementById('errormsg').innerText='';
	btn.disabled = true;

	var request = new XMLHttpRequest();
	request.open('GET', location.href+'?'+'action=login&userdata='+document.getElementById('userdata').value);
	request.onload = function(){
		if (request.status == 200){
			var response = {};
			if (request.responseText) response = JSON.parse(request.responseText);
			if (response.success) {
				document.getElementsByClassName('panel-body')[0].innerText =  response.message;
			} else {
				document.getElementById('errormsg').innerText = response.message;
			}
		} else {
			document.getElementById('errormsg').innerText = 'Error: ' + (request.status ? request.statusText : 'request is failed');
		}
		btn.disabled = false;
	};
	request.setRequestHeader("X-Requested-With", "XMLHttpRequest");
	request.send();
});
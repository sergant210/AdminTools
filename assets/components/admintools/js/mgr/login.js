function sendAjaxRequest() {
	document.getElementById('errormsg').innerText='';
	let oldText = btn.innerText;
	btn.disabled = true;
	btn.innerText = btn.dataset.sending || 'Sending...';

	let request = new XMLHttpRequest();
	request.open('GET', location.href + '?' + 'action=login&userdata=' + document.getElementById('userdata').value);
	request.onload = function(){
		if (request.status == 200){
			let response = {};
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
		btn.innerText = oldText;
	};
	request.setRequestHeader("X-Requested-With", "XMLHttpRequest");
	request.send();
}
let btn = document.getElementById('send-email-btn'),
	form = document.getElementById('manager-login-form');

btn.addEventListener('click', () => {
	sendAjaxRequest();
});

form.addEventListener('submit', () => {
	sendAjaxRequest();
})
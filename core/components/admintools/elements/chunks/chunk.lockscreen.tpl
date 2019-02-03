<!DOCTYPE html>
<html lang="[[+lang]]">
<head>
	<meta charset="UTF-8">
	<title>[[+title]]</title>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<style>
		.lockscreen {
			background-image: url([[+assets_url]]components/admintools/images/lockscreen.jpg);
		}
		@media only screen and (max-width : 767px) {
			.lockscreen {
				background-image: url([[+assets_url]]components/admintools/images/lockscreen800.jpg);
			}
		}
		@media only screen and (min-width : 768px) and (max-width: 1223px) {
			.lockscreen {
				background-image: url([[+assets_url]]components/admintools/images/lockscreen1280.jpg);
			}
		}
		.lockscreen .container {
			background-color: rgba(255,255,255,0.1);
			margin: 5% auto 0;
			padding: 20px;
			text-align: center;
			width: 250px;
		}
		.lockscreen .form-element {
			box-sizing: border-box;
			font-size: 14px;
			margin: 5px 0;
			padding: 6px 12px;
			width: 220px;
		}
		.lockscreen .username {
			font-size: 20px;
			color: #fff;
			padding: 10px;
		}
		.lockscreen input {
			border: 1px solid transparent;
		}
		.lockscreen input:focus {
			border-color: #45a2ec;
			outline: 0;
		}
		.lockscreen .btn {
			display: inline-block;
			color: #fff;
			background-color: #3697CD;
			font-weight: bold;
			line-height: 1.6;
			text-align: center;
			vertical-align: middle;
			cursor: pointer;
			border: 1px solid #3CA4DE;
		}
		.btn:hover {
			color: #fff;
			background-color: #286090;
			border-color: #1e4c74;
		}
	</style>
</head>
<body class="lockscreen">
<div class="container">
	<div class="photo"><img src="[[+photo]]" alt="avatar" onsubmit="return false;"></div>
	<div class="username">[[+username]]</div>
	<form name="unlockform" action="[[+form_action]]" method="POST">
		<div>
			<input id="unlock_code" class="form-element" type="password" name="unlock_code" placeholder="[[+input_placeholder]]" required>
		</div>
		<div>
			<button class="form-element btn submit-btn">[[%admintools_unlock]]</button>
		</div>
	</form>
</div>
<script>
	document.forms[0].addEventListener("submit", function(event) {
		event.preventDefault();
		let body = "admintools_action=unlock&action=mgr/system/unlock&unlock_code=" + encodeURIComponent(unlock_code.value) + "&HTTP_MODAUTH=[[+auth]]";
		let xhr = new XMLHttpRequest();
		xhr.open(this.getAttribute('method'), this.getAttribute('action'), true);
		xhr.setRequestHeader("X-Requested-With", "XMLHttpRequest");
		xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
		xhr.onreadystatechange = function() {
			if (this.readyState == 4 && this.status == 200) {
				document.location.reload();
			}
		};

		xhr.send(body);
	});
</script>
</body>
</html>
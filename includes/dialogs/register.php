<?php
	include dirname(dirname(__FILE__)) . "/session.php";
	include dirname(dirname(__FILE__)) . "/settings.php";
?>

<span class="header">Register for <?php echo $prog_title; ?></span>
<form action="includes/register.php" method="POST" id="register-info">
	<input name="username" type="text" placeholder="Username"/><br/>
	<input name="email" type="text" placeholder="Email"/><br/>
	<input name="password" type="password" placeholder="Password"/><br/>
</form>
<div class="dialog-buttons">
	<div class="button" onClick="submitRegister();">Register</div>
	<div class="button" id="closeDialog">Cancel</div>
</div>

<script src="js/dialog.js"></script>
<script>
function submitRegister() {
	document.getElementById("login-info").submit();
}
</script>
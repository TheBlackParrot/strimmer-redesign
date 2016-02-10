<?php

include_once dirname(dirname(__FILE__)) . "/settings.php";
include_once dirname(dirname(__FILE__)) . "/session.php";

$invalid_cred = FALSE;

if(isset($_SESSION['login']))
	if($_SESSION['login']) {
		http_response_code(409);
		die("Already logged in. {$_SESSION['username']}");
	}
}

?>

<div class="dialog-wrapper">
	<div class="dialog">
		<span class="header">Login to <?php echo $prog_title; ?></span>
		<form action="includes/login.php" method="post" name="login-dialog" id="login-info">
			Username<br/>
			<div><input type="text" name="username" style="width: 100%;" placeholder="Username" required></div><br/>
			
			Password<br/>
			<div><input type="password" name="password" style="width: 100%;" placeholder="Password" required></div><br/><br/>
			No account? <a href="#" onClick="loadRegisterDialog();">Click here to register.</a>
		</form>
		<div class="dialog-buttons">
			<div class="button" onClick="submitLogin();">Login</div>
			<div class="button" id="closeDialog">Cancel</div>
		</div>
	</div>
</div>

<?php if ($invalid_cred) { ?>
	<span style="color: rgb(255,0,0); font-weight: bold;">Invalid username or password.</span>
<?php } ?>

<script src="js/dialog.js"></script>

<script>
function submitLogin() {
	document.getElementById("login-info").submit();
}
</script>
<?php

include_once dirname(dirname(__FILE__)) . "/settings.php";
include_once dirname(dirname(__FILE__)) . "/session.php";

if(!isset($_SESSION['login'])) {
	exit;
}
if(!$_SESSION['login']) {
	exit;
}

$query = 'SELECT API_KEY1,API_KEY2 FROM user_db WHERE USERNAME="' . $_SESSION['username'] . '"';
$result = $mysqli->query($query);
if($result->num_rows != 1) {
	die("Error in request.");
}
$row = $result->fetch_assoc();

?>

<div class="dialog-wrapper">
	<div class="dialog">
		<span class="header">API Keys</span>
		Hover over the lines below to see your API keys.<br/><br/>

		API Key 1<br/>
		<span class="dialog-hidden"><?php echo $row['API_KEY1']; ?></span><br/><br/>

		API Key 2<br/>
		<span class="dialog-hidden"><?php echo $row['API_KEY2']; ?></span><br/><br/>

		<span class="dialog-caption">*A feature to request new API keys will be added later on.</span>

		<div class="dialog-buttons">
			<div class="button" id="closeDialog">Back</div>
		</div>
	</div>
</div>

<script src="js/dialog.js"></script>
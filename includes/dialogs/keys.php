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
		<div class="keys-content">
			Hover over the lines below to see your API keys.<br/><br/>

			API Key 1<br/>
			<span class="dialog-hidden"><?php echo $row['API_KEY1']; ?></span><br/><br/>

			API Key 2<br/>
			<span class="dialog-hidden"><?php echo $row['API_KEY2']; ?></span><br/><br/>
		</div>
		<div class="dialog-buttons">
			<div class="button" id="resetKeys">Request New Keys</div>
			<div class="button" id="closeDialog">Back</div>
		</div>
	</div>
</div>

<script src="js/dialog.js"></script>
<script>
var strimmer_host = 'https://strimmer2.theblackparrot.us/api/1.0/';

function requestNewKeys(callback) {
	var url = strimmer_host + 'users/reset_keys.php';
	$.ajax({
		type: 'GET',
		url: url,
		contentType: 'text/plain',
		dataType: 'text',
		xhrFields: {
			withCredentials: false
		},
		success: function(data) {
			if(typeof callback === "function") {
				callback(data);
			}
		},
		error: function() {
			console.log("error");
		}
	});
}

$("#resetKeys").off("click").on("click",function(){
	requestNewKeys(function(data){
		$(".keys-content").text(data);
		$("#closeDialog").text("Close");
		$("#resetKeys").remove();
	});
});
</script>
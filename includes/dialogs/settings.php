<?php

include_once dirname(dirname(__FILE__)) . "/settings.php";
include_once dirname(dirname(__FILE__)) . "/session.php";


?>
<div class="dialog-wrapper">
	<div class="dialog">
		<span class="header"><?php echo $prog_title; ?> Settings</span>
		Theme
		<select onchange="changeTheme(this.value);" selected="getCookie('theme');">
			<option value="main">Light</option>
			<option value="main-dark">Dark</option>
		</select><br/>
		<?php echo $prog_title; ?>'s look and feel.<br/>

		<hr/>

		<input id="enbCSP" type="checkbox" onchange="updateCSP();"> Enable client-sided playback<br/><br/>

		SoundCloud API Key
		<input id="SCAPIinput" type="text" onchange="updateSCAPI(this.value);"/><br/>
		Used for client-sided playback of SoundCloud tracks.<br/><br/>

		Jamendo API Key
		<input id="JMAPIinput" type="text" onchange="updateJMAPI(this.value);"/><br/>
		Used for client-sided playback of Jamendo tracks.

		<div class="dialog-buttons">
			<div class="button" id="closeDialog">Cancel</div>
		</div>
	</div>
</div>

<script src="js/dialog.js"></script>

<script>
$("#SCAPIinput").val(getCookie("SCAPIKey"));
$("#JMAPIinput").val(getCookie("JMAPIKey"));
$("#enbCSP").prop("checked",getCookie("enbCSP"));


function updateSCAPI(value) {
	setCookie("SCAPIKey",value,365);
}
function updateJMAPI(value) {
	setCookie("JMAPIKey",value,365);
}
function updateCSP() {
	var checked = $("#enbCSP").prop("checked");
	if(checked == true) {
		setCookie("enbCSP",1,365);
	} else {
		setCookie("enbCSP",0,365);
		$("#audioCSP").remove();
	}
}

function changeTheme(value) {
	var old_theme = getCookie("theme");
	if(value != old_theme || old_theme == "") {
		setCookie("theme",value,365);
		document.getElementById('themecss').href = "css/" + value + ".css";
	}
}
</script>
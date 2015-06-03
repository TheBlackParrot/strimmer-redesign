<?php

include_once dirname(dirname(__FILE__)) . "/settings.php";
include_once dirname(dirname(__FILE__)) . "/session.php";


?>
<div class="dialog-wrapper">
	<div class="dialog">
		<span class="header"><?php echo $prog_title; ?> Settings</span>
		Theme
		<select onchange="changeTheme(this.value);">
			<option value="main">Light</option>
			<option value="main-dark">Dark</option>
		</select>
		<div class="dialog-buttons">
			<div class="button" id="closeDialog">Cancel</div>
		</div>
	</div>
</div>

<script src="js/dialog.js"></script>

<script>
function changeTheme(value) {
	var old_theme = getCookie("theme");
	if(value != old_theme || typeof old_theme == "undefined") {
		setCookie("theme",value,365);
		document.getElementById('themecss').href = "css/" + value + ".css";
	}
}
</script>
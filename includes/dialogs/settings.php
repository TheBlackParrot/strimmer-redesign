<?php

include_once dirname(dirname(__FILE__)) . "/settings.php";
include_once dirname(dirname(__FILE__)) . "/session.php";


?>
<div class="dialog-wrapper">
	<div class="dialog">
		<span class="header"><?php echo $prog_title; ?> Settings</span>
		<strong>Theme</strong><br/>
		<select onchange="changeTheme(this.value);" selected="getCookie('theme');">
			<option value="main">Light</option>
			<option value="main-dark">Dark</option>
		</select><br/>
		<span class="dialog-caption"><?php echo $prog_title; ?>'s look and feel.</span><br/><br/>

		<strong>Color Scheme</strong><br/>
		<div class="color-boxes">
			<div class="color-box standard"></div>
			<div class="color-box bright"></div>
			<div class="color-box dark"></div>
			<div class="color-box darker"></div>
		</div>
		<div class="color-inputs" style="text-align: center;">
			<input style="width: 72px;" id="color-input" type="text" placeholder="#3F51B5" onchange="sendColor();"/>
			<input style="width: 48px;" id="perc-bright" type="text" placeholder="15%" onchange="sendColor();"/>
			<input style="width: 48px;" id="perc-dark" type="text" placeholder="-15%" onchange="sendColor();"/>
			<input style="width: 48px;" id="perc-darker" type="text" placeholder="-30%" onchange="sendColor();"/>
		</div><br/>

		<strong>Font</strong>
		<input id="font_setting" type="text" onchange="updateFont(this.value);" placeholder="Roboto"/><br/>

		<input id="enb_pb_smooth" type="checkbox" onchange="updateSmoothPB();"> Enable progress bar smoothing<br/>

		<hr/>

		<input id="enbCSP" type="checkbox" onchange="updateCSP();"> Enable client-sided playback<br/><br/>

		SoundCloud API Key<br/>
		<input id="SCAPIinput" type="text" onchange="updateSCAPI(this.value);"/><br/>
		<span class="dialog-caption">Used for client-sided playback of SoundCloud tracks.</span><br/><br/>

		Jamendo API Key<br/>
		<input id="JMAPIinput" type="text" onchange="updateJMAPI(this.value);"/><br/>
		<span class="dialog-caption">Used for client-sided playback of Jamendo tracks. (not <em>currently</em> used)</span>

		<div class="dialog-buttons">
			<div class="button" id="closeDialog">Cancel</div>
		</div>
	</div>
</div>

<script src="js/dialog.js"></script>

<script>
$("#color-input").val(getCookie("color-main"));
$("#perc-bright").val(getCookie("perc-bright"));
$("#perc-dark").val(getCookie("perc-dark"))
$("#perc-darker").val(getCookie("perc-darker"))
sendColor();

$("#SCAPIinput").val(getCookie("SCAPIKey"));
$("#JMAPIinput").val(getCookie("JMAPIKey"));
if(getCookie("enbCSP") != "") {
	if(getCookie("enbCSP") == "1") {
		$("#enbCSP").prop("checked",true);
	} else {
		$("#enbCSP").prop("checked",false);
	}
} else {
	$("#enbCSP").prop("checked",false);
}

if(getCookie("smooth_pb") != "") {
	if(getCookie("smooth_pb") == "1") {
		$("#enb_pb_smooth").prop("checked",true);
	} else {
		$("#enb_pb_smooth").prop("checked",false);
	}
} else {
	$("#enb_pb_smooth").prop("checked",true);
}


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
function updateSmoothPB() {
	var checked = $("#enb_pb_smooth").prop("checked");
	if(checked == true) {
		setCookie("smooth_pb",1,365);

	} else {
		setCookie("smooth_pb",0,365);
		$("#audioCSP").remove();
	}
	$(".progress-bar-filled").css("transition",getCookie("smooth_pb") + "s");
}

function changeTheme(value) {
	var old_theme = getCookie("theme");
	if(value != old_theme || old_theme == "") {
		setCookie("theme",value,365);
		document.getElementById('themecss').href = "css/" + value + ".css";
		sendColor();
	}
}

function updateFont(value) {
	$("body").css("font-family",value);
	setCookie("font",value,365);
}
</script>
<?php
	include dirname(dirname(__FILE__)) . "/settings.php";
?>

<div class="dialog-wrapper">
	<div class="dialog">
		<span class="header">Listen</span>
		<div class="dialog-buttons">
			<form action="http://<?php echo "{$icecast['public_url']}:{$icecast['port']}/{$icecast['mount']}.m3u"; ?>" method="GET" id="mount">
				<span class="button" onClick="document.getElementById('mount').submit();">MP3 Q6 (~115kbps)</span>
			</form>
			<br/>
			<form action="http://<?php echo "{$icecast['public_url']}:{$icecast['port']}/{$icecast['mountlq']}.m3u"; ?>" method="GET" id="mount">
				<span class="button" onClick="document.getElementById('mount').submit();">MP3 Q9 (~65kbps)</span>
			</form>
			<br/>
			<form action="http://<?php echo "{$icecast['public_url']}:{$icecast['port']}/{$icecast['mount_opus']}.m3u"; ?>" method="GET" id="mount">
				<span class="button" onClick="document.getElementById('mount').submit();">Opus Audio (48kbps)</span>
			</form>
			<br/>
			<form action="http://<?php echo "{$icecast['public_url']}:{$icecast['port']}/{$icecast['mountlq_opus']}.m3u"; ?>" method="GET" id="mount">
				<span class="button" onClick="document.getElementById('mount').submit();">Opus Audio (24kbps)</span>
			</form>
			<br/>
			<form action="http://<?php echo "{$icecast['public_url']}:{$icecast['port']}/stream_experimental.mp3.m3u"; ?>" method="GET" id="mount">
				<span class="button" onClick="document.getElementById('mount').submit();">Experimental (MP3 Q6)</span>
			</form>
			<br/>
			The experimental stream uses dynamic range compression via an ffmpeg filter defined as:<br/>
			<span style="font-family: monospace;">-filter "compand=0|0:0.2|0.2:-90/-900|-70/-70|-30/-9|0/-3:2:2.9:0:0"</span><br/>
			This is an attempt at getting music much closer to the same volume level and to reduce quality loss artifacts.
			<br/>
			<br/>
			AAC streams may be available in the future.<br/><br/>
			<div class="button" id="closeDialog">Cancel</div>
		</div>
	</div>
</div>

<script src="js/dialog.js"></script>
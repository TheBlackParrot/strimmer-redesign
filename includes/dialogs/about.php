<?php
	include dirname(dirname(__FILE__)) . "/settings.php";

	$query = "SELECT COUNT(USERNAME) FROM user_db";
	$result = $mysqli->query($query);
	$reg = $result->fetch_array(MYSQLI_NUM);
?>

<div class="dialog-wrapper">
	<div class="dialog">
		<span class="header">Strimmer</span>
		Strimmer is an open-source system for streaming radio that uses cloud services for content.<br/>
		The source code is available on the <a href="https://github.com/TheBlackParrot/strimmer-redesign">GitHub repository</a>.<br/><br/>

		<strong>System Info</strong><br/>
		<table style="width: 200px;">
			<tr>
				<td>Memory Usage</td>
				<td><?php echo round(memory_get_usage()/1024, 0) . " KiB"; ?></td>
			</tr>
			<tr>
				<td>Registered Users</td>
				<td><?php echo "{$reg[0]} users"; ?></td>
			<tr>
				<td>Library Size</td>
				<td id="libsize"></td>
			</tr>
		</table><br/>

		<span class="dialog-caption" style="color: #999;">v<?php echo $strimmerVersion; ?></span><br/>
		<span class="dialog-caption" style="color: #999;">
			Strimmer is Copyright © 2015 <a href="https://github.com/theblackparrot">TheBlackParrot</a> and licensed under the <a href="license.txt">MIT License.</a>
		</span>
		<div class="dialog-buttons">
			<div class="button" id="closeDialog">Close</div>
		</div>
	</div>
</div>

<script src="js/dialog.js"></script>
<script>
$(document).ready(function(){
	$("#libsize").text(library_data.RETURN_DATA.length.toLocaleString() + " songs");
});
</script>
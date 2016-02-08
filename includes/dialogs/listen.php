<?php
	include dirname(dirname(__FILE__)) . "/settings.php";
?>

<div class="dialog-wrapper">
	<div class="dialog">
		<span class="header">Listen</span>
		<div class="dialog-buttons">
			<?php
				foreach ($stream['names'] as $mount => $name) {
					?>
					<form action="http://<?php echo "{$icecast['public_url']}:{$icecast['port']}/$mount.m3u"; ?>" method="GET" id="mount">
						<span class="button" onClick="document.getElementById('mount').submit();"><?php echo $name; ?></span>
					</form>
					<br/>
					<?php
				}
			?>

			<br/>
			<br/>
			AAC streams may be available in the future.<br/><br/>
			<div class="button" id="closeDialog">Cancel</div>
		</div>
	</div>
</div>

<script src="js/dialog.js"></script>
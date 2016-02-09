<?php
	header("Content-Type: text/plain");
	// CORS requests
	header("Access-Control-Allow-Origin: *");

	$root = dirname(dirname(dirname(dirname(__FILE__))));

	include "$root/includes/settings.php";

	$RETURN_DATA = [];
	foreach ($stream['names'] as $mount => $name) {
		$RETURN_DATA[] = array(
			"mount" => $mount,
			"name" => $name,
			"listen_url" => "http://{$icecast['public_url']}:{$icecast['port']}/$mount"
		);
	}

	echo json_encode($RETURN_DATA);
?>
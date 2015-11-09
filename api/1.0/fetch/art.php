<?php
	// CORS requests
	header("Access-Control-Allow-Origin: *");

	$root = dirname(dirname(dirname(dirname(__FILE__))));

	include "$root/config.php";

	if(!isset($_GET['ID'])) {
		header("Content-Type: text/plain");
		http_response_code(400);
		die("400: Bad request - no track ID");
	}

	$valid = array('-', '_');
	$test_str = str_replace($valid, '', $_GET['ID']);
	if(!ctype_alnum($test_str)) {
		header("Content-Type: text/plain");
		http_response_code(400);
		die("400: Bad request - track ID must be alphanumeric");
	}

	if(file_exists("$root/cache/{$_GET['ID']}.jpg")) {
		header("Content-Type: image/jpeg");
		echo file_get_contents("$root/cache/{$_GET['ID']}.jpg");
	} else {
		header("Content-Type: text/plain");
		http_response_code(400);
		die("400: Bad request - track ID does not exist");
	}
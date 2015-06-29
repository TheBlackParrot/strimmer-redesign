<?php
	$root = dirname(dirname(dirname(dirname(__FILE__))));

	include "$root/includes/settings.php";
	include "$root/includes/session.php";
	include "$root/includes/functions.php";

	header("Content-Type: text/plain");

	if(!isAllowedUse()) {
		http_response_code(401);
		die("401: Unauthorized");
	}

	$user = getStrimmerUser();
	if($user == -1) {
		http_response_code(500);
		die("500: Internal Server Error - user does not exist");
	}

	if($user['RANK'] < 1) {
		http_response_code(401);
		die("401: Unauthorized");
	}

	$username = addslashes($user['USERNAME']);
	$file = "$root/locdata/images/avatars/" . basename($username) . '.jpg';

	if(!is_file($file) || stripos($username,"./") !== FALSE) {
		$file = "$root/images/av-placeholder.jpg";
	}

	$type = 'image/jpeg';
	header('Content-Type:'.$type);
	readfile($file);
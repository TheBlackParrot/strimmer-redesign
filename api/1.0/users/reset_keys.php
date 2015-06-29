<?php
	$root = dirname(dirname(dirname(dirname(__FILE__))));

	include "$root/includes/settings.php";
	include "$root/includes/session.php";
	include "$root/includes/functions.php";

	header("Content-Type: text/plain");

	if(!isset($_SESSION['login']) || $_SESSION['login'] == FALSE) {
		http_response_code(401);
		die("401: Unauthorized - This API function must be used within the main $prog_title interface.");
	}

	$options = ['cost' => 4];
	$api[1] = hash("sha256",password_hash(uniqid('',true), PASSWORD_DEFAULT, $options));
	$api[2] = hash("gost",password_hash(uniqid('',true), PASSWORD_DEFAULT, $options));

	$query = 'UPDATE user_db SET API_KEY1="' . $api[1] . '", API_KEY2="' . $api[2] . '" WHERE USERNAME="' . $_SESSION['username'] . '"';
	$mysqli->real_query($query);
	
	echo "You have changed your API keys. Reopen this dialog to see your new keys.";
	exit;
?>
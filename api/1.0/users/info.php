<?php
	header("Content-Type: text/plain");
	// CORS requests
	header("Access-Control-Allow-Origin: *");

	$root = dirname(dirname(dirname(dirname(__FILE__))));

	include "$root/includes/settings.php";
	include "$root/includes/session.php";
	include "$root/includes/functions.php";

	$user = getStrimmerUser();
	if(!isset($_GET['user'])) {
		if($user != -1) {
			$selection = $user;
			unset($selection['API_KEY1']);
			unset($selection['API_KEY2']);
		}
	} else {
		$username = htmlspecialchars($_GET['user']);
		$query = 'SELECT LASTACTIVE,FAVORITES,RANK FROM user_db WHERE USERNAME="' . $username . '"';
		if($result = $mysqli->query($query)) {
			if(!$result->num_rows) {
				if($username == $prog_title) {
					$selection['LASTACTIVE'] = 0;
					$selection['FAVORITES'] = "";
					$selection['RANK'] = 0;
				} else {
					http_response_code(400);
					die(json_encode("400: Bad request"));
				}
			} else {
				$selection = $result->fetch_assoc();
			}
		} else {
			http_response_code(400);
			die(json_encode("400: Bad request"));
		}
	}
		
	$fixed_type = "json";
	if(isset($_GET['type'])) {
		$type = htmlspecialchars($_GET['type']);
		if(in_array($type,array("none","json"))) {
			$fixed_type = $type;
		}
	}

	switch($fixed_type) {
		case 'json':
			echo json_encode($selection);
			break;
		
		case 'none':
			foreach ($selection as $value) {
				if($value != "\r\n" && $value != "") {
					echo "$value\r\n";
				}
			}
			break;
	}
?>
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

	if($user['RANK'] < 2) {
		http_response_code(401);
		die("401: Unauthorized");
	}

	if(!isset($_GET['ID'])) {
		http_response_code(400);
		die("400: Bad request, no track ID specified");
	}

	$id = $mysqli->real_escape_string($_GET['ID']);
	$query = 'SELECT TRACKID FROM db_cache WHERE TRACKID="' . $id . '"';
	if($user['RANK'] == 2) {
		$query .= ' AND ADDED_BY="' . $user['USERNAME'] . '"';
	}
	$result = $mysqli->query($query);
	if($result->num_rows < 1) {
		http_response_code(400);
		die("400: Bad request, invalid ID or track not owned by user");
	}

	$query = 'DELETE FROM db_cache WHERE TRACKID="' . $id . '"';
	$mysqli->real_query($query);

	$query = 'DELETE FROM play_queue WHERE TRACKID="' . $id . '"';
	$mysqli->real_query($query);

	$query = 'SELECT FAVORITES,ID FROM user_db';
	$result = $mysqli->query($query);
	while($row = $result->fetch_assoc()) {
		$faves_str = $row['FAVORITES'];
		$faves_arr = explode(";",$faves_str);

		if(in_array($id,$faves_arr)) {
			$faves_str = str_replace($id . ";","",$faves_str);
			$query = 'UPDATE user_db SET FAVORITES="' . $faves_str . '" WHERE ID=' . $row['ID'];
			$mysqli->real_query($query);
		}
	}

	echo 1;
	exit;
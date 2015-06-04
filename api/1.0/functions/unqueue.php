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

	if(!isset($_GET['ID'])) {
		http_response_code(400);
		die("400: Bad request - no track ID");
	}
	$track_id = $mysqli->real_escape_string($_GET['ID']);

	if(!canUserUnqueue($user,$track_id)) {
		$return_val = 0;
		echo $return_val;
		exit;
	}

	$return_val = 0;

	$query = 'SELECT * FROM play_queue WHERE TRACKID="' . $track_id . '" AND ADDED_BY="' . $user['USERNAME'] . '"';
	$result = $mysqli->query($query);
	if($result->num_rows) {
		$query = 'DELETE FROM play_queue WHERE TRACKID="' . $track_id . '" AND !ISNULL(play_queue.ADDED_BY) LIMIT 1';
		if(!$mysqli->real_query($query)) {
			http_response_code(500);
			die("500: Internal Server Error - track is not queued by a user");
		}
		$query = 'UPDATE user_db SET LAST_QUEUED=NULL WHERE USERNAME="' . $user['USERNAME'] . '"';
		$mysqli->real_query($query);
		$return_val = 1;
	}

	echo $return_val;
	exit;
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

	if(!canUserQueue($user,$track_id)) {
		$return_val = 0;
		echo $return_val;
		exit;
	}

	$return_val = 0;

	$query = 'SELECT * FROM play_queue WHERE TRACKID="' . $track_id . '" AND ADDED_BY="' . $user['USERNAME'] . '"';
	$result = $mysqli->query($query);
	if(!$result->num_rows) {
		$query = 'SELECT * FROM db_cache WHERE TRACKID="' . $track_id . '" LIMIT 1';
		$storage = $mysqli->query($query);
		if($storage->num_rows) {
			$row = $storage->fetch_assoc();
			$query = 'INSERT INTO play_queue ( TRACKID, SERVICE, ADDED_ON, ADDED_BY ) VALUES ( "' . $row['TRACKID'] . '", "' . $row['SERVICE'] . '", ' . time() . ', "' . $user['USERNAME'] . '")';
			$mysqli->real_query($query);
			if($user['RANK'] == 1) {
				$query = 'UPDATE user_db SET LAST_QUEUED=' . time() . ' WHERE USERNAME="' . $user['USERNAME'] . '"';
				$mysqli->real_query($query);
			}
			$return_val = 1;
		} else {
			http_response_code(500);
			die("500: Internal Server Error - track does not exist");
		}
	}

	echo $return_val;
	exit;
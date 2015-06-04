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

	if(isset($_GET['ID'])) {
		$query = "SELECT * FROM db_cache WHERE TRACKID='" . $track_id . "'";
		$result = $mysqli->query($query);
		if($result->num_rows) {
			$row = $result->fetch_assoc();

			$faves_str = $user['FAVORITES'];
			$faves_arr = explode(";",$faves_str);

			if(in_array($row['TRACKID'],$faves_arr)) {
				$faves_str = str_replace($row['TRACKID'] . ";","",$faves_str);
				$query = 'UPDATE user_db SET FAVORITES="' . $faves_str . '" WHERE ID=' . $user['ID'];
				$return_val = 0;
			} else {
				$faves_str .= $row['TRACKID'] . ";";
				$query = 'UPDATE user_db SET FAVORITES="' . $faves_str . '" WHERE ID=' . $user['ID'];
				$return_val = 1;
			}

			$mysqli->real_query($query);
			echo $return_val;
			exit;
		} else {
			http_response_code(400);
			die("400: Bad request - couldn't find track");
		}
	}
?>
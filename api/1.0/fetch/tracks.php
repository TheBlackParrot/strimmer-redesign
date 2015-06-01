<?php
	header("Content-Type: text/plain");
	// CORS requests
	header("Access-Control-Allow-Origin: *");

	$root = dirname(dirname(dirname(dirname(__FILE__))));

	include "$root/config.php";

	$query = "SELECT COUNT(TRACKID) FROM db_cache";
	$result = $mysqli->query($query);
	$val = $result->fetch_array();
	$tracks = $val[0];

	if(isset($_GET['amount'])) {
		$amount = htmlspecialchars($_GET['amount']);
		if($amount <= 0) {
			$amount = $tracks;
		}
		if($amount > $tracks) {
			$amount = $tracks;
		}
	} else {
		$amount = $tracks;
	}

	$fixed_sort = "ADDED_ON";
	if(isset($_GET['sort'])) {
		$sort = htmlspecialchars($_GET['sort']);
		if(in_array($sort, array("title","artist","added","plays"))) {
			switch($sort) {
				case "title":
					$fixed_sort = "RETURN_ARG2";
					break;
				case "artist":
					$fixed_sort = "RETURN_ARG3";
					break;
				case "added":
					$fixed_sort = "ADDED_ON";
					break;
				case "plays":
					$fixed_sort = "PLAY_COUNT";
					break;
			}
		}
	}

	$fixed_order = "DESC";
	if(isset($_GET['order'])) {
		$order = htmlspecialchars($_GET['order']);
		if(in_array($order,array("ASC","DESC"))) {
			$fixed_order = htmlspecialchars($_GET['order']);
		}
	} else {
		$fixed_order = "DESC";
	}

	$fixed_offset = 0;
	if(isset($_GET['offset'])) {
		$offset = htmlspecialchars($_GET['offset']);
		if($offset >= $tracks) {
			http_response_code(400);
			die(json_encode("400: Bad request"));
		}
		$fixed_offset = $offset;
	}

	$query = "SELECT * FROM db_cache ORDER BY $fixed_sort $fixed_order LIMIT $fixed_offset,$amount";
	$result = $mysqli->query($query);
	while($tmp = $result->fetch_assoc()) {
		$cur_array['STRIMMER_ID'] = $tmp['TRACKID'];
		$cur_array['SERVICE'] = $tmp['SERVICE'];
		$cur_array['SERVICE_ID'] = $tmp['RETURN_ARG1'];
		$cur_array['TITLE'] = $tmp['RETURN_ARG2'];
		$cur_array['ARTIST'] = $tmp['RETURN_ARG3'];
		$cur_array['ARTIST_PERMALINK'] = $tmp['RETURN_ARG4'];
		$cur_array['API_STREAM'] = $tmp['RETURN_ARG5'];
		$cur_array['TRACK_PERMALINK'] = $tmp['RETURN_ARG6'];
		$cur_array['CACHED_ART'] = $prog_internal_url . "/cache/" . $tmp['TRACKID'] . ".jpg";
		$cur_array['ART_PERMALINK'] = $tmp['RETURN_ARG7'];
		$cur_array['ADDED_BY'] = $tmp['ADDED_BY'];
		$cur_array['ADDED_ON'] = $tmp['ADDED_ON'];
		$cur_array['IS_PLAYING'] = $tmp['PLAYING'];
		$cur_array['PLAY_COUNT'] = $tmp['PLAY_COUNT'];
		$cur_array['LAST_API_RESPONSE_CODE'] = $tmp['ERRORCODE'];
		$data['RETURN_DATA'][] = $cur_array;
		unset($cur_array);
		unset($tmp);
	}
	$data['METADATA']['COUNT'] = count($data['RETURN_DATA']);
	$data['METADATA']['TIMESTAMP'] = time();

	echo json_encode($data);
?>
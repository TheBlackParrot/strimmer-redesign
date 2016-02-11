<?php
	header("Content-Type: text/plain");
	// CORS requests
	header("Access-Control-Allow-Origin: *");

	$root = dirname(dirname(dirname(dirname(__FILE__))));

	include "$root/config.php";

	if(!isset($_GET['time'])) {
		http_response_code(400);
		die("400: Bad request - no timestamp provided");
	}
	$time = $mysqli->real_escape_string($_GET['time']);

	if(isset($_GET['count_only'])) {
		$query = "SELECT COUNT(TRACKID) FROM db_cache WHERE ADDED_ON >= $time";
		$result = $mysqli->query($query);
		$val = $result->fetch_array();
		die($val[0]);
	}

	$fixed_order = "DESC";
	if(isset($_GET['order'])) {
		$order = htmlspecialchars($_GET['order']);
		if(in_array($order,array("asc","desc"))) {
			$fixed_order = htmlspecialchars($_GET['order']);
		}
	}

	$query = "SELECT * FROM db_cache WHERE ADDED_ON >= $time ORDER BY ADDED_ON $fixed_order";
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

		if($tmp['SERVICE'] == "URL") {
			$cur_array['API_STREAM'] = "N/A";
		}

		$data['RETURN_DATA'][] = $cur_array;
		unset($cur_array);
		unset($tmp);
	}

	$data['METADATA']['COUNT'] = count($data['RETURN_DATA']);
	$data['METADATA']['TIMESTAMP'] = time();

	echo json_encode($data);
?>
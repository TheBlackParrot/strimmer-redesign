<?php
	header("Content-Type: text/plain");
	// CORS requests
	header("Access-Control-Allow-Origin: *");

	$root = dirname(dirname(dirname(dirname(__FILE__))));

	include "$root/config.php";

	$fixed_verbosity = "TRACKID";
	if(isset($_GET['verbosity'])) {
		$verbosity = htmlspecialchars($_GET['verbosity']);
		if(in_array($verbosity,array("low","med","high"))) {
			switch ($verbosity) {
				case 'low': $fixed_verbosity = "TRACKID"; break;
				case 'med': $fixed_verbosity = "TRACKID,RETURN_ARG2,RETURN_ARG3,RETURN_ARG4,RETURN_ARG6"; break;
				case 'high': $fixed_verbosity = "*"; break;
			}
		} else {
			http_response_code(400);
			die(json_encode("400: Bad request"));
		}
	}

	$fixed_type = "none";
	if(isset($_GET['type'])) {
		$type = htmlspecialchars($_GET['type']);
		if(in_array($type,array("none","json"))) {
			$fixed_type = $type;
		}
	}

	$query = "SELECT $fixed_verbosity FROM db_cache WHERE PLAYING=1";
	$result = $mysqli->query($query);
	$tmp = $result->fetch_assoc();

	switch($verbosity) {
		case 'low':
			$cur_array['STRIMMER_ID'] = $tmp['TRACKID'];
			break;
		case 'title':
			$cur_array['TITLE'] = $tmp['RETURN_ARG2'];
			break;
		case 'med':
			$cur_array['STRIMMER_ID'] = $tmp['TRACKID'];
			$cur_array['SERVICE'] = $tmp['SERVICE'];
			$cur_array['TITLE'] = $tmp['RETURN_ARG2'];
			$cur_array['ARTIST'] = $tmp['RETURN_ARG3'];
			$cur_array['ARTIST_PERMALINK'] = $tmp['RETURN_ARG4'];
			$cur_array['TRACK_PERMALINK'] = $tmp['RETURN_ARG6'];
			$cur_array['CACHED_ART'] = $prog_internal_url . "/cache/" . $tmp['TRACKID'] . ".jpg";
			break;
		case 'high':
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
			break;
	}
	if($verbosity == "high") {
		if($tmp['SERVICE'] == "URL") {
			$cur_array['API_STREAM'] = "N/A";
		}
	}
	$data['RETURN_DATA'][] = $cur_array;
	unset($cur_array);
	unset($tmp);

	if($fixed_type == "json") {
		$data['METADATA']['COUNT'] = count($data['RETURN_DATA']);
		$data['METADATA']['TIMESTAMP'] = time();

		echo json_encode($data);
	} else {
		foreach ($data['RETURN_DATA'][0] as $value) {
			if($value != "\r\n" && $value != "") {
				echo "$value";
				if(count($data['RETURN_DATA'][0]) > 1) {
					echo "\r\n";
				}
			}
		}
	}
?>
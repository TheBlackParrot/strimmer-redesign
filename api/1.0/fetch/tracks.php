<?php
	header("Content-Type: text/plain");
	// CORS requests
	header("Access-Control-Allow-Origin: *");

	$root = dirname(dirname(dirname(dirname(__FILE__))));

	include "$root/config.php";

	$fixed_where = "db_cache";
	if(isset($_GET['where'])) {
		$where = htmlspecialchars($_GET['where']);
		switch($where) {
			case "library": $fixed_where = "db_cache"; break;
			case "queue": $fixed_where = "play_queue"; break;
			case "history": $fixed_where = "play_history"; break;
		}
	}

	$query = "SELECT COUNT(TRACKID) FROM $fixed_where";
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

	if($fixed_where == "play_history") {
		$fixed_sort = "PLAYED_ON";
	} else {
		$fixed_sort = "ADDED_ON";
	}
	if(isset($_GET['sort'])) {
		$sort = htmlspecialchars($_GET['sort']);
		if(in_array($sort, array("title","artist","added","plays"))) {
			switch($sort) {
				case "title": $fixed_sort = "RETURN_ARG2"; break;
				case "artist": $fixed_sort = "RETURN_ARG3"; break;
				case "plays": $fixed_sort = "PLAY_COUNT"; break;

				case "added":
					if($fixed_where == "play_history") {
						$fixed_sort = "PLAYED_ON";
					} else {
						$fixed_sort = "ADDED_ON";
					}
					break;
			}
		}
	}

	$fixed_order = "DESC";
	if(isset($_GET['order'])) {
		$order = htmlspecialchars($_GET['order']);
		if(in_array($order,array("asc","desc"))) {
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

	if($fixed_where != "db_cache") {
		if($fixed_where == "play_queue") {
			$query = "SELECT * FROM play_queue ORDER BY ISNULL(play_queue.ADDED_BY) ASC LIMIT $fixed_offset,$amount";
		} else {
			$query = "SELECT * FROM $fixed_where ORDER BY $fixed_sort $fixed_order LIMIT $fixed_offset,$amount";
		}
		$result = $mysqli->query($query);
		while($selection = $result->fetch_assoc()) {
			$query2 = 'SELECT * FROM db_cache WHERE TRACKID="' . $selection['TRACKID'] . '"';
			$result2 = $mysqli->query($query2);
			while($tmp = $result2->fetch_assoc()) {
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
				if(isset($selection['ADDED_BY'])) {
					$cur_array['ADDED_BY'] = $selection['ADDED_BY'];
				} else {
					$cur_array['ADDED_BY'] = $prog_title;
				}
				if(isset($selection['PLAYED_ON'])) {
					$cur_array['ADDED_ON'] = $selection['PLAYED_ON'];
				} else {
					$cur_array['ADDED_ON'] = $selection['ADDED_ON'];
				}
				$cur_array['IS_PLAYING'] = $tmp['PLAYING'];
				$cur_array['PLAY_COUNT'] = $tmp['PLAY_COUNT'];
				$cur_array['LAST_API_RESPONSE_CODE'] = $tmp['ERRORCODE'];
				$data['RETURN_DATA'][] = $cur_array;
				unset($cur_array);
				unset($tmp);
			}
		}
	} else {
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
	}
	$data['METADATA']['COUNT'] = count($data['RETURN_DATA']);
	$data['METADATA']['TIMESTAMP'] = time();

	echo json_encode($data);
?>
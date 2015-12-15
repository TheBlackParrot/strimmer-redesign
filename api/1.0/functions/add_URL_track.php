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

	if($user['RANK'] < 3) {
		http_response_code(401);
		die("401: Unauthorized");
	}

	if(!isset($_GET['url'])) {
		http_response_code(400);
		die("400: Bad request - no track URL");
	}

	$url = urldecode($_GET['url']);
	$url = $mysqli->real_escape_string($url);

	$parsedUrl = parse_url($url);

	$allowedSchemes = array("http", "https");
	if(!in_array($parsedUrl['scheme'], $allowedSchemes)) {
		http_response_code(400);
		die("400: Bad request - invalid URL");
	}

	$time = time();
	$goodCodes = array(302,200,201,203);

	$curl = curl_init();
	curl_setopt($curl, CURLOPT_URL, $url);
	curl_setopt($curl, CURLOPT_FOLLOWLOCATION, false);
	curl_setopt($curl, CURLOPT_HEADER, true);  
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl, CURLOPT_USERAGENT, "Mozilla/5.0 (X11; Linux x86_64; rv:21.0) Gecko/20100101 Firefox/21.0");
	$output = curl_exec($curl);

	$httpCode_track = curl_getinfo($curl, CURLINFO_HTTP_CODE);
	if(!in_array($httpCode_track,$goodCodes)) {
		echo "Error with API request on $url.<br/><strong>Error code</strong>: $httpCode_track";
		curl_close($curl);
		die();
	}
	curl_close($curl);

	$query = 'SELECT RETURN_ARG5 FROM db_cache WHERE RETURN_ARG5="' . $url . '" LIMIT 1';
	$result = $mysqli->query($query);
	
	if($result->num_rows) {
		die("This track already exists in the library.");
	}

	$file_data = json_decode(shell_exec('ffprobe -v quiet -print_format json -show_format -show_streams -i ' . escapeshellarg($url)), 1);

	$valid_formats = array("mp3", "ogg", "opus", "m4a", "aac", "flac");
	$codec = strtolower($file_data['format']['format_name']);

	if(!in_array($codec, $valid_formats)) {
		if(!in_array($file_data['streams'][0]['codec_name'], $valid_formats)) {
			http_response_code(400);
			die("400: Bad request - invalid codec");
		}
	}

	if($codec == "m4a" || $codec == "aac") {
		http_response_code(400);
		die("400: M4A/AAC support is currently being looked at, file headers are not guaranteed to be at the beginning of a file. This is needed for realtime decoding in FFMPEG/LibAV.");
	}

	if(isset($file_data['format']['tags'])) {
		$tags = $file_data['format']['tags'];
	} else {
		$tags = $file_data['streams'][0]['tags'];
	}
	$track_info = [];

	$wanted_tags = array("title", "TITLE", "artist", "ARTIST");
	foreach($wanted_tags as $i) {
		if(isset($tags[$i])) {
			$track_info[strtolower($i)] = $tags[$i];
		}
	}
	if(!isset($track_info['title'])) {
		http_response_code(400);
		die("400: Bad request - track must have title metadata");
	}
	if(!isset($track_info['artist'])) {
		http_response_code(400);
		die("400: Bad request - track must have artist metadata");
	}

	$trk_id = strtoupper(base_convert($time, 10, 36) . base_convert(mt_rand(50000, 1500000), 10, 36));

	// track id, title, owner account, stream url, permalink id
	/* 
		track id	RETURN_ARG1
		title		RETURN_ARG2
		owner acc.	RETURN_ARG3
		owner link	RETURN_ARG4
		stream url	RETURN_ARG5
		permalink	RETURN_ARG6
		art link	RETURN_ARG7
	*/

	if(isset($_GET['artwork_url'])) {
		$artwork_url = urldecode($_GET['artwork_url']);
		$artwork_url = $mysqli->real_escape_string($artwork_url);

		$parsedArtUrl = parse_url($artwork_url);

		if(!in_array($parsedArtUrl['scheme'], $allowedSchemes)) {
			http_response_code(400);
			die("400: Bad request - invalid art URL");
		}
	}

	if(isset($artwork_url)) {
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $artwork_url);
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, false);
		curl_setopt($curl, CURLOPT_HEADER, true);  
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_USERAGENT, "Mozilla/5.0 (X11; Linux x86_64; rv:21.0) Gecko/20100101 Firefox/21.0");
		$output = curl_exec($curl);

		$httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		if(in_array($httpCode,$goodCodes)) {
			$filename = "$root/cache/URL$trk_id.jpg";
			$image = new Imagick();
			$image->readImage($artwork_url);
			if($image->valid()) {
				$image->setFormat("jpg");
				$image->setImageCompression(Imagick::COMPRESSION_JPEG);
				$image->setImageCompressionQuality(97);
				$image->thumbnailImage(100,100);
				$image->writeImage($filename);
				$image->clear();
			} else {
				$avvy_loc = "$root/locdata/images/avatars/";
				if(!file_exists($avvy_loc . $user['USERNAME'] . ".jpg")) {
					$valid_art_url = $avvy_loc . $user['USERNAME'] . ".jpg";
				} else {
					$valid_art_url = "$root/images/bg-placeholder.jpg";
				}
				copy($valid_art_url,$filename);
			}
		} else {
			$avvy_loc = "$root/locdata/images/avatars/";
			if(!file_exists($avvy_loc . $user['USERNAME'] . ".jpg")) {
				$valid_art_url = $avvy_loc . $user['USERNAME'] . ".jpg";
			} else {
				$valid_art_url = "$root/images/bg-placeholder.jpg";
			}
			copy($valid_art_url,$filename);	
		}

		curl_close($curl);
	} else {
		$artwork_url = "";
		$avvy_loc = "$root/locdata/images/avatars/";
		if(!file_exists($avvy_loc . $user['USERNAME'] . ".jpg")) {
			$valid_art_url = $avvy_loc . $user['USERNAME'] . ".jpg";
		} else {
			$valid_art_url = "$root/images/bg-placeholder.jpg";
		}
		copy($valid_art_url,$filename);
	}

	$query = 'INSERT INTO db_cache ( TRACKID,SERVICE,RETURN_ARG1,RETURN_ARG2,RETURN_ARG3,RETURN_ARG4,RETURN_ARG5,RETURN_ARG6,RETURN_ARG7,ADDED_BY,ADDED_ON ) VALUES (
		"URL' . $trk_id . '",
		"URL",
		"' . $trk_id . '",
		"' . $track_info['title'] . '",
		"' . $track_info['artist'] . '",
		"\#",
		"' . $url . '",
		"\#",
		"' . $artwork_url . '",
		"' . $user['USERNAME'] . '",
		' . $time . '
		)';
	//$cur_array['debug'] = $query;
	$mysqli->real_query($query);

	$cur_array['STRIMMER_ID'] = "URL$trk_id";
	$cur_array['SERVICE'] = "URL";
	$cur_array['SERVICE_ID'] = $trk_id;
	$cur_array['TITLE'] = $track_info['title'];
	$cur_array['ARTIST'] = $track_info['artist'];
	$cur_array['ARTIST_PERMALINK'] = "#";
	$cur_array['API_STREAM'] = "N/A";
	$cur_array['TRACK_PERMALINK'] = "#";
	$cur_array['CACHED_ART'] = "$prog_internal_url/cache/URL$trk_id.jpg";
	$cur_array['ART_PERMALINK'] = $artwork_url;
	$cur_array['ADDED_BY'] = $user['USERNAME'];
	$cur_array['ADDED_ON'] = $time;
	$cur_array['IS_PLAYING'] = 0;
	$cur_array['PLAY_COUNT'] = 0;
	$cur_array['LAST_API_RESPONSE_CODE'] = $httpCode_track;
	$data['RETURN_DATA'][] = $cur_array;

	$data['METADATA']['COUNT'] = count($data['RETURN_DATA']);
	$data['METADATA']['TIMESTAMP'] = $time;

	echo json_encode($data);
	die();
?>
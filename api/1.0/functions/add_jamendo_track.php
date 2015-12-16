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


	function jamendo_resolveFromID($track_id) {
		$root = dirname(dirname(dirname(dirname(__FILE__))));
		include "$root/includes/settings.php";

		if(isset($track_id)) {
			$url = "http://api.jamendo.com/v3.0/tracks/?client_id=" . $jm_api_key . "&format=json&limit=1&id=" . $track_id . "&audioformat=mp32&audiodlformat=flac";
			
			$curl = curl_init();
			curl_setopt($curl, CURLOPT_URL, $url);
			curl_setopt($curl, CURLOPT_HEADER, 0);  
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);   
			
			$output = curl_exec($curl);
			
			curl_close($curl);

			return $output;
		}
	}

	function getIDFromURL($url) {
		$path = parse_url($url, PHP_URL_PATH);
		
		$pathFragments = explode('/', $path);
		foreach ($pathFragments as $fragment) {
			if(ctype_digit($fragment)) {
				return $fragment;
			}
		}

		return "error";
	}

	$url = urldecode($_GET['url']);
	$url = $mysqli->real_escape_string($url);

	$allowedHosts = array("jamen.do", "jamendo.com", "www.jamen.do", "www.jamendo.com");
	$parsedUrl = parse_url($url);
	if(!in_array($parsedUrl['host'], $allowedHosts)) {
		http_response_code(400);
		die("400: Bad request - invalid URL");
	}

	$parsed_id = getIDFromURL($_GET['url']);
	if(!ctype_digit($parsed_id)) {
		http_response_code(400);
		die("400: invalid ID from URL");
	}

	$time = time();
	$resolved_vars = json_decode(jamendo_resolveFromID($parsed_id), true);

	if($resolved_vars['headers']['results_count'] < 1) {
		http_response_code(400);
		die("400: Bad request - Jamendo track with specified ID could not be found");
	}

	$stream_vars = $resolved_vars['results'][0];
	if(!isset($stream_vars['audio'])) {
		http_response_code(400);
		die("400: Bad request - Jamendo did not give a stream link");
	}
	// jamendo pls remove by default thx
	$stream_vars['audio'] = str_replace("&from=app-$jm_api_key", "", $stream_vars['audio']);

	$goodCodes = array(302,200,201,203);

	$curl = curl_init();
	curl_setopt($curl, CURLOPT_URL, $stream_vars['audio'] . "&client_id=" . $jm_api_key);
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

	$jm_trk_id = $stream_vars['id'];

	$query = 'SELECT TRACKID FROM db_cache WHERE TRACKID="JMND' . $jm_trk_id . '" LIMIT 1';
	$result = $mysqli->query($query);
	
	if($result->num_rows) {
		die("This track already exists in the library.");
	}

	if(!isset($stream_vars['album_image'])) {
		$artwork_url = $stream_vars['image'];
	} else {
		$artwork_url = $stream_vars['album_image'];
	}

	$curl = curl_init();
	curl_setopt($curl, CURLOPT_URL, $artwork_url);
	curl_setopt($curl, CURLOPT_FOLLOWLOCATION, false);
	curl_setopt($curl, CURLOPT_HEADER, true);  
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl, CURLOPT_USERAGENT, "Mozilla/5.0 (X11; Linux x86_64; rv:21.0) Gecko/20100101 Firefox/21.0");
	$output = curl_exec($curl);

	$httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
	if(!in_array($httpCode,$goodCodes)) {
		$artwork_url = "";
	}

	curl_close($curl);

	$filename = "$root/cache/JMND$jm_trk_id.jpg";
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

	$query = 'INSERT INTO db_cache ( TRACKID,SERVICE,RETURN_ARG1,RETURN_ARG2,RETURN_ARG3,RETURN_ARG4,RETURN_ARG5,RETURN_ARG6,RETURN_ARG7,ADDED_BY,ADDED_ON ) VALUES (
		"JMND' . $jm_trk_id . '",
		"JMND",
		' . $jm_trk_id . ',
		"' . $stream_vars['name'] . '",
		"' . $stream_vars['artist_name'] . '",
		"http://jamen.do/a/' . $stream_vars['artist_id'] . '",
		"' . $stream_vars['audio'] . '",
		"' . $stream_vars['shorturl'] . '",
		"' . $artwork_url . '",
		"' . $user['USERNAME'] . '",
		' . $time . '
		)';
	$mysqli->real_query($query);

	$cur_array['STRIMMER_ID'] = "JMND$jm_trk_id";
	$cur_array['SERVICE'] = "JMND";
	$cur_array['SERVICE_ID'] = $jm_trk_id;
	$cur_array['TITLE'] = $stream_vars['name'];
	$cur_array['ARTIST'] = $stream_vars['artist_name'];
	$cur_array['ARTIST_PERMALINK'] = "http://jamen.do/a/{$stream_vars['artist_id']}";
	$cur_array['API_STREAM'] = $stream_vars['audio'];
	$cur_array['TRACK_PERMALINK'] = $stream_vars['shorturl'];
	$cur_array['CACHED_ART'] = "$prog_internal_url/cache/JMND$jm_trk_id.jpg";
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
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
		die("400: Bad request - no track ID");
	}

	function soundcloud_resolveFromURL($track_url) {
		if(isset($track_url)) {
			$root = dirname(dirname(dirname(dirname(__FILE__))));
			include "$root/includes/settings.php";

			$url = "http://api.soundcloud.com/resolve.json?url=" . $track_url . "&client_id=" . $sc_api_key;

			$curl = curl_init();
			curl_setopt($curl, CURLOPT_URL, $url);
			curl_setopt($curl, CURLOPT_HEADER, 0);  
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);   
			$output = curl_exec($curl);
			curl_close($curl);
			
			return $output;
		}
	}
	function soundcloud_getStreamVars($location) {
		if(isset($location)) {
			$curl = curl_init();
			curl_setopt($curl, CURLOPT_URL, $location);
			curl_setopt($curl, CURLOPT_HEADER, 0);  
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);   
			$output = curl_exec($curl);
			curl_close($curl);
			
			return $output;
		}
	}
	function soundcloud_getDirectStream($location) {
		if(isset($location)) {
			$root = dirname(dirname(dirname(dirname(__FILE__))));
			include "$root/includes/settings.php";
			
			$curl = curl_init();
			curl_setopt($curl, CURLOPT_URL, $location . "?client_id=" . $sc_api_key);
			curl_setopt($curl, CURLOPT_FOLLOWLOCATION, false);
			curl_setopt($curl, CURLOPT_HEADER, true);  
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($curl, CURLOPT_USERAGENT, "Mozilla/5.0 (X11; Linux x86_64; rv:21.0) Gecko/20100101 Firefox/21.0");
			$output = curl_exec($curl);
			preg_match_all('/^Location:(.*)$/mi', $output, $matches);
			curl_close($curl);

			return $matches;
		}
	}

	$url = urldecode($_GET['url']);
	$url = $mysqli->real_escape_string($_GET['url']);

	$parsedUrl = parse_url($url);

	if($parsedUrl['host'] != "www.soundcloud.com") {
		if($parsedUrl['host'] != "soundcloud.com") {
			http_response_code(400);
			die("400: Bad request - invalid URL");
		}
	}

	$time = time();
	$resolved_vars = json_decode(soundcloud_resolveFromURL($url),true);
	$stream_vars = json_decode(soundcloud_getStreamVars($resolved_vars['location']),true);
	$goodCodes = array(302,200,201,203);

	$curl = curl_init();
	curl_setopt($curl, CURLOPT_URL, $stream_vars['stream_url'] . "?client_id=" . $sc_api_key);
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

	$sc_trk_id = $stream_vars['id'];

	$query = 'SELECT TRACKID FROM db_cache WHERE TRACKID="SDCL' . $sc_trk_id . '" LIMIT 1';
	$result = $mysqli->query($query);
	
	if($result->num_rows) {
		die("This track already exists in the library.");
	}

	$user_vars = $stream_vars['user'];
	if(!isset($stream_vars['stream_url'])) {
		die("No stream URL could be obtained from $url");
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
	if(!isset($stream_vars['artwork_url'])) {
		$artwork_url = $user_vars['avatar_url'];
	} else {
		$artwork_url = $stream_vars['artwork_url'];
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
		$artwork_url = $user_vars['avatar_url'];
	}

	curl_close($curl);

	$filename = "$root/cache/SDCL$sc_trk_id.jpg";
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

	$query = 'INSERT INTO db_cache ( TRACKID,SERVICE,RETURN_ARG1,RETURN_ARG2,RETURN_ARG3,RETURN_ARG4,RETURN_ARG5,RETURN_ARG6,RETURN_ARG7,ADDED_BY,ADDED_ON ) VALUES (
		"SDCL' . $sc_trk_id . '",
		"SDCL",
		' . $sc_trk_id . ',
		"' . $stream_vars['title'] . '",
		"' . $user_vars['username'] . '",
		"' . $user_vars['permalink_url'] . '",
		"' . $stream_vars['stream_url'] . '",
		"' . $stream_vars['permalink_url'] . '",
		"' . $artwork_url . '",
		"' . $user['USERNAME'] . '",
		' . $time . '
		)';
	$mysqli->real_query($query);

	$cur_array['STRIMMER_ID'] = "SDCL$sc_trk_id";
	$cur_array['SERVICE'] = "SDCL";
	$cur_array['SERVICE_ID'] = $sc_trk_id;
	$cur_array['TITLE'] = $stream_vars['title'];
	$cur_array['ARTIST'] = $user_vars['username'];
	$cur_array['ARTIST_PERMALINK'] = $user_vars['permalink_url'];
	$cur_array['API_STREAM'] = $stream_vars['stream_url'];
	$cur_array['TRACK_PERMALINK'] = $stream_vars['permalink_url'];
	$cur_array['CACHED_ART'] = "$prog_internal_url/cache/SDCL$sc_trk_id.jpg";
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
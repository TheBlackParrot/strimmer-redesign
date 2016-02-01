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

	if(!isset($ma_api_key)) {
		http_response_code(500);
		die("500: Internal Server Error - no API key for ModArchive defined");
	} else {
		if($ma_api_key == "") {
			http_response_code(500);
			die("500: Internal Server Error - API key for ModArchive is blank");
		}
	}

	// http://modarchive.org/index.php?request=view_by_moduleid&query=60395
	function getIDFromURL($url) {
		$query = parse_url($url, PHP_URL_QUERY);

		if(ctype_digit($query)) {
			return $query;
		}
		
		$queryFragments = explode('&', $query);
		foreach ($queryFragments as $fragment) {
			$fragmentParts = explode('=', $fragment);
			if($fragmentParts[0] == "query") {
				return $fragmentParts[1];
			}
		}

		return "error";
	}


	function ModArchive_resolveFromID($track_id) {
		$root = dirname(dirname(dirname(dirname(__FILE__))));
		include "$root/includes/settings.php";

		if(isset($track_id)) {
			$url = "http://api.modarchive.org/xml-tools.php?key=" . $ma_api_key . "&request=view_by_moduleid&query=" . $track_id;
			
			$curl = curl_init();
			curl_setopt($curl, CURLOPT_URL, $url);
			curl_setopt($curl, CURLOPT_HEADER, 0);  
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);   
			
			$output = curl_exec($curl);
			
			curl_close($curl);

			return $output;
		}
	}

	$url = urldecode($_GET['url']);
	$url = $mysqli->real_escape_string($url);

	$allowedHosts = array("modarchive.org", "www.modarchive.org");
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

	$xmlObj = simplexml_load_string(ModArchive_resolveFromID($parsed_id), null, LIBXML_NOCDATA);
	if(!$xmlObj) {
		http_response_code(500);
		die("500: Internal Server Error - Error with XML parser");
	}

	$why_can_we_not_just_convert_to_an_actual_array = json_encode($xmlObj);
	$resolved_vars = json_decode($why_can_we_not_just_convert_to_an_actual_array, TRUE);

	if($resolved_vars['results'] < 1) {
		http_response_code(400);
		die("400: Bad request - ModArchive track could not be found");
	}

	$format = strtolower($resolved_vars['module']['format']);
	$allowedFormats = array('s3m', 'it', 'mod', 'xm');
	if(!in_array($format, $allowedFormats)) {
		http_response_code(400);
		die("400: Bad request - Only IT, MOD, S3M, and XM are supported");
	}

	$stream_vars = [];

	$stream_vars['trackID'] = $parsed_id;
	$stream_vars['audio'] = "http://api.modarchive.org/downloads.php?moduleid=$parsed_id";
	$stream_vars['permalink'] = $url;
	$stream_vars['art'] = $mysqli->real_escape_string("#");

	if(isset($resolved_vars['module']['songtitle'])) {
		$stream_vars['title'] = $resolved_vars['module']['songtitle'];
	} else {
		$stream_vars['title'] = pathinfo($resolved_vars['module']['filename'], PATHINFO_FILENAME);
	}

	$stream_vars['artist'] = "ModArchive";

	$artistCount = $resolved_vars['module']['artist_info']['artists'];
	if($artistCount < 1) {
		$guessedArtistCount = $resolved_vars['module']['artist_info']['guessed_artists'];

		if($guessedArtistCount > 0) {
			$stream_vars['artist'] = join($resolved_vars['module']['artist_info']['guessed_artist'], ", ");
		}
	}
	if($artistCount == 1) {
		$stream_vars['artist'] = $resolved_vars['module']['artist_info']['artist']['alias'];
	}
	if($artistCount > 1) {
		$allArtists = [];
		foreach($resolved_vars['module']['artist_info']['artist'] as $artist) {
			$allArtists[] = $artist['alias'];
		}

		$stream_vars['artist'] = join($allArtists, ", ");
	}

	// i can really only support 1 here
	$stream_vars['artistPermalink'] = "#";
	if($artistCount == 1) {
		$stream_vars['artistPermalink'] = "http://modarchive.org/member.php?{$resolved_vars['module']['artist_info']['artist']}";
	}
	$stream_vars['artistPermalink'] = $mysqli->real_escape_string($stream_vars['artistPermalink']);


	$goodCodes = array(302,200,201,203);

	$curl = curl_init();
	curl_setopt($curl, CURLOPT_URL, $stream_vars['audio']);
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

	$query = 'SELECT TRACKID FROM db_cache WHERE TRACKID="MODA' . $parsed_id . '" LIMIT 1';
	$result = $mysqli->query($query);
	
	if($result->num_rows) {
		die("This track already exists in the library.");
	}

	$filename = "$root/cache/MODA$parsed_id.jpg";
	$image = new Imagick();
	$image->readImage("$root/images/modarchive.png");
	if($image->valid()) {
		$image->setFormat("jpg");
		$image->setImageCompression(Imagick::COMPRESSION_JPEG);
		$image->setImageCompressionQuality(97);
		$image->thumbnailImage(100,100);
		$image->writeImage($filename);
		$image->clear();
	} else {
		// should never happen but You Never Know
		copy("$root/images/bg-placeholder.jpg", $filename);
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
		"MODA' . $parsed_id . '",
		"MODA",
		' . $parsed_id . ',
		"' . $stream_vars['title'] . '",
		"' . $stream_vars['artist'] . '",
		"' . $stream_vars['artistPermalink'] . '",
		"' . $stream_vars['audio'] . '",
		"' . $stream_vars['permalink'] . '",
		"' . $stream_vars['art'] . '",
		"' . $user['USERNAME'] . '",
		' . $time . '
		)';
	$mysqli->real_query($query);

	$cur_array['STRIMMER_ID'] = "MODA$parsed_id";
	$cur_array['SERVICE'] = "MODA";
	$cur_array['SERVICE_ID'] = $parsed_id;
	$cur_array['TITLE'] = $stream_vars['title'];
	$cur_array['ARTIST'] = $stream_vars['artist'];
	$cur_array['ARTIST_PERMALINK'] = $stream_vars['artistPermalink'];
	$cur_array['API_STREAM'] = $stream_vars['audio'];
	$cur_array['TRACK_PERMALINK'] = $stream_vars['permalink'];
	$cur_array['CACHED_ART'] = "$prog_internal_url/cache/MODA$parsed_id.jpg";
	$cur_array['ART_PERMALINK'] = "#";
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
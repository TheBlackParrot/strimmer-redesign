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

	if($parsedUrl['host'] != "www.youtube.com") {
		if($parsedUrl['host'] != "youtube.com") {
			if($parsedUrl['host'] != "youtu.be") {
				http_response_code(400);
				die("400: Bad request - invalid URL");
			}
		}
	}

	$time = time();
	$result_vars = json_decode(getYouTubeData(escapeshellarg($url),"JSON"),true);

	if(empty($result_vars)) {
		die("No stream URL could be obtained from $url");
	}

	$query = 'SELECT TRACKID FROM db_cache WHERE TRACKID="YTUB' . $result_vars['id'] . '" LIMIT 1';
	$result = $mysqli->query($query);
	
	if($result->num_rows) {
		die("This track already exists in the library.");
	}

	$goodCodes = array(302,200,201,203);
	$stream_link = getYouTubeData("https://youtube.com/watch?v=" . $result_vars['id'],"StreamLink");
	if($stream_link == "" || !$stream_link) {
		die("youtube-dl returned a null stream link.");
	}

	$curl = curl_init();
	curl_setopt($curl, CURLOPT_URL, $stream_link);
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

	$filename = "$root/cache/YTUB" . $result_vars['id'] . ".jpg";
	$image = new Imagick();
	$image->readImage($result_vars['thumbnail']);
	if($image->valid()) {
		$image->setFormat("jpg");
		$image->setImageCompression(Imagick::COMPRESSION_JPEG);
		$image->setImageCompressionQuality(97);
		$image->cropThumbnailImage(100,100);
		$image->writeImage($filename);
		$image->clear();
	} else {
		die("Invalid thumbnail image.");
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
		"YTUB' . $result_vars['id'] . '",
		"YTUB",
		"' . $result_vars['id'] . '",
		"' . $result_vars['title'] . '",
		"' . $result_vars['uploader'] . '",
		"' . "https://youtube.com/user/" . $result_vars['uploader_id'] . '",
		"' . "https://youtube.com/watch?v=" . $result_vars['id'] . '",
		"' . "https://youtube.com/watch?v=" . $result_vars['id'] . '",
		"' . $result_vars['thumbnail'] . '",
		"' . $user['USERNAME'] . '",
		' . $time . '
		)';
	$mysqli->real_query($query);

	$cur_array['STRIMMER_ID'] = 'YTUB' . $result_vars['id'];
	$cur_array['SERVICE'] = "YTUB";
	$cur_array['SERVICE_ID'] = $result_vars['id'];
	$cur_array['TITLE'] = $result_vars['title'];
	$cur_array['ARTIST'] = $result_vars['uploader'];
	$cur_array['ARTIST_PERMALINK'] = "https://youtube.com/user/" . $result_vars['uploader_id'];
	$cur_array['API_STREAM'] = "https://youtube.com/watch?v=" . $result_vars['id'];
	$cur_array['TRACK_PERMALINK'] = "https://youtube.com/watch?v=" . $result_vars['id'];
	$cur_array['CACHED_ART'] = "$prog_internal_url/cache/YTUB" . $result_vars['id'] . ".jpg";
	$cur_array['ART_PERMALINK'] = $result_vars['thumbnail'];
	$cur_array['ADDED_BY'] = $user['USERNAME'];
	$cur_array['ADDED_ON'] = $time;
	$cur_array['IS_PLAYING'] = 0;
	$cur_array['PLAY_COUNT'] = 0;
	$cur_array['LAST_API_RESPONSE_CODE'] = $httpCode_track;
	$data['RETURN_DATA'][] = $cur_array;

	$data['METADATA']['COUNT'] = count($data['RETURN_DATA']);
	$data['METADATA']['TIMESTAMP'] = $time;

	echo json_encode($data);
	exit;
?>
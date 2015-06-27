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

	if($user['RANK'] < 4) {
		http_response_code(401);
		die("401: Unauthorized");
	}

	if(!isset($_GET['ID'])) {
		http_response_code(400);
		die("400: Bad request - no track ID");
	}
	$track_id = $mysqli->real_escape_string($_GET['ID']);

	// SERVICE -- SDCL,YTUB,etc.
	// RETURN_ARG1 -- service ID
	// RETURN_ARG5 -- API stream link

	$query = 'SELECT TRACKID,SERVICE,RETURN_ARG1,RETURN_ARG5 FROM db_cache WHERE TRACKID="' . $track_id . '"';
	$result = $mysqli->query($query);
	if($result->num_rows < 1) {
		http_response_code(400);
		die("400: Bad request, invalid ID");
	}

	$row = $result->fetch_assoc();

	switch($row['SERVICE']) {
		case 'SDCL':
			$stream_link = $row['RETURN_ARG5'] . "?client_id=" . $sc_api_key;
			break;

		case 'WYZL':
		case 'JMND':
		case 'UNDF':
		case 'HYPE':
			$stream_link = $row['RETURN_ARG5'];
			break;

		case 'YTUB':
			$stream_link = getYouTubeData($row['RETURN_ARG5'],"StreamLink");
			break;

		default:
			$stream_link = $row['RETURN_ARG5'];
			break;
	}

	$curl = curl_init();
	curl_setopt($curl, CURLOPT_URL, $stream_link);
	curl_setopt($curl, CURLOPT_FOLLOWLOCATION, false);
	curl_setopt($curl, CURLOPT_HEADER, true);  
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	//curl_setopt($curl, CURLOPT_CONNECT_ONLY, true);
	curl_setopt($curl, CURLOPT_USERAGENT, "Mozilla/5.0 (X11; Linux x86_64; rv:21.0) Gecko/20100101 Firefox/21.0");
	$output = curl_exec($curl);

	$httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
	
	$query = 'UPDATE db_cache SET ERRORCODE=' . $httpCode . ' WHERE TRACKID="' . $row['TRACKID'] . '"';
	$mysqli->real_query($query);

	echo $httpCode;
	exit;
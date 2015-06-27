<?php
	header("Content-Type: text/plain");
	// CORS requests
	header("Access-Control-Allow-Origin: *");

	$root = dirname(dirname(dirname(dirname(__FILE__))));

	include "$root/config.php";

	// http://stackoverflow.com/questions/11441517/ffmpeg-progress-bar-encoding-percentage-in-php

	// $filename = "$root/ffmpeg_info.txt";
	//$filename = "/srv/http/test/mpd/includes/ffmpeg_info.txt";
	$filename = "/srv/http/strimmer-data/strimmer_ffmpeg_info.txt";
	// having to use Strimmer 1.0 here for testing
	if(!is_file($filename)) {
	    die("Not a file");
	}

	$content = @file_get_contents($filename);

	if($content){
	    //get duration of source
	    preg_match("/Duration: (.*?), start:/", $content, $matches);

	    $rawDuration = $matches[1];

	    //rawDuration is in 00:00:00.00 format. This converts it to seconds.
	    $ar = array_reverse(explode(":", $rawDuration));
	    $duration = floatval($ar[0]);
	    if (!empty($ar[1])) $duration += intval($ar[1]) * 60;
	    if (!empty($ar[2])) $duration += intval($ar[2]) * 60 * 60;

	    //get the time in the file that is already encoded
	    preg_match_all("/time=(.*?) bitrate/", $content, $matches);

	    $rawTime = array_pop($matches);

	    //this is needed if there is more than one match
	    if (is_array($rawTime)){$rawTime = array_pop($rawTime);}

	    //rawTime is in 00:00:00.00 format. This converts it to seconds.
	    $ar = array_reverse(explode(":", $rawTime));
	    $time = floatval($ar[0]);
	    if (!empty($ar[1])) $time += intval($ar[1]) * 60;
	    if (!empty($ar[2])) $time += intval($ar[2]) * 60 * 60;

	    //calculate the progress
	    //$progress = round(($time/$duration) * 100);
	    $progress = ($time/$duration)*100;

	    //$temp = $time/$duration;

	    $readable_time = floor($time/60) . ":" . sprintf("%02d", floor($time) % 60);
	    $readable_duration = floor($duration/60) . ":" . sprintf("%02d", floor($duration) % 60);
	} else {
		die(0);
	}

	$fixed_type = "percent";
	if(isset($_GET['type'])) {
		$type = htmlspecialchars($_GET['type']);
		if(in_array($type,array("percent","time","all"))) {
			$fixed_type = $type;
		} else {
			http_response_code(400);
			die("400: Bad request");
		}
	}

	switch($fixed_type) {
		case 'percent':
			echo $progress;
			break;

		case 'time':
			echo "$readable_time\r\n$readable_duration";
			break;

		case 'all':
			echo "$progress\r\n$readable_time\r\n$readable_duration";
			break;
	}
?>
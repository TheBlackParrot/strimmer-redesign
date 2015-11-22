<?php
	if (php_sapi_name() != "cli") {
		http_response_code(401);
		die("401: Unauthorized, must be run in CLI");
	}

	$root = dirname(dirname(__FILE__));
	$init_time = time();

	include "$root/includes/settings.php";
	include "$root/includes/functions.php";

	// used for old logs
	// http://stackoverflow.com/a/22754032
	function gzCompressFile($source, $level = 9){ 
		$dest = $source . '.gz'; 
		$mode = 'wb' . $level; 
		$error = false; 
		if ($fp_out = gzopen($dest, $mode)) { 
			if ($fp_in = fopen($source,'rb')) { 
				while (!feof($fp_in)) 
					gzwrite($fp_out, fread($fp_in, 1024 * 512)); 
				fclose($fp_in); 
			} else {
				$error = true; 
			}
			gzclose($fp_out); 
		} else {
			$error = true; 
		}
		if($error) {
			return false;
		} else {
			if(file_exists($source)) {
				unlink($source);
			}
			return $dest;
		} 
	}
	if($logging['compress_old_logs']) {
		$dirIter = new DirectoryIterator("$root/" . $logging['dir']);
		foreach($dirIter as $fileinfo) {
			if(!$fileinfo->isDot()) {
				if($fileinfo->getExtension() == "log") {
					gzCompressFile($fileinfo->getPathname());
				}
			}
		}
	}

	function strimmerLog($line) {
		global $root, $init_time;
		include "$root/includes/settings.php";

		if(!$logging['enabled']) {
			return;
		}

		$logdir = "$root/" . $logging['dir'];
		$logfile = "$logdir/strimmer-$init_time.log";
		if(!file_exists($logdir)) {
			mkdir($logdir,0755,true);
		}

		$data = '[' . date('m/d/y H:i:s') . '] ' . $line . "\r\n";
		echo $data;
		file_put_contents($logfile,$data,FILE_APPEND | LOCK_EX);
	}

	$time = 0;
	$previous_song = "";
	$good_track_found = 0;
	$goodCodes = array(302,200,201,203);
	$serviceCodes = array(500,502,503,504);

	while(true) {
		if(time() - $time <= 15) {
			sleep(15);
		}

		// get the row count in the main cache
		$query = "SELECT COUNT(*) FROM db_cache";
		$result_init1 = $mysqli->query($query);
		$temp = $result_init1->fetch_array();
		$rand_max = $temp[0] - 1;

		// make sure we don't cause a forever while loop, lol
		$max_queued = 20;
		if($temp[0] < 20) {
			$max_queued = $temp[0];
		}

		// get a track
		$query = 'SELECT * FROM play_queue ORDER BY ISNULL(play_queue.ADDED_BY) LIMIT 1 OFFSET 0';
		$result = $mysqli->query($query);
		// if one isn't obtained, assume the queue is empty
		if($result->num_rows < 1) {
			// initiate the play queue, basically
			// use this as a fallback in case of an emergency, /actually/ initiate this in the setup eventually

			// go ahead and add tracks to it
			$used_offsets = array();
			for ($i=0;$i<$max_queued;$i++) { 
				$rand = mt_rand(0,$rand_max);

				// prevent duplicate tracks beforehand
				while(in_array($rand,$used_offsets)) {
					$rand = mt_rand(0,$rand_max);
				}

				$query = "SELECT * FROM db_cache LIMIT 1 OFFSET $rand";
				$result_init2 = $mysqli->query($query);

				// if something really is wrong, quit entirely
				if($result_init2->num_rows < 1) {
					echo "NO SQL RESULT GIVEN (QUEUE INIT FALLBACK)";
					die();
				}

				// get a random track
				$row = $result_init2->fetch_assoc();

				// see if it's already in the queue
				// (thinking this might not be needed? we're already checking duplicates)
				/*
				$query = 'SELECT TRACKID FROM play_queue WHERE TRACKID="' . $row['TRACKID'] . '"';
				$result_checkdups = $mysqli->query($query);
				while($result_checkdups->num_rows > 0) {
					$query = 'SELECT TRACKID FROM play_queue WHERE TRACKID="' . $row['TRACKID'] . '"';
					$result_checkdups = $mysqli->query($query);
				}
				*/

				// add it
				$query = 'INSERT INTO play_queue ( TRACKID, SERVICE, ADDED_ON ) VALUES ( "' . $row['TRACKID'] . '", "' . $row['SERVICE'] . '", ' . time() . ')';
				$mysqli->real_query($query);
				strimmerLog("Queued track " . $row['TRACKID']);
			}
			// restart the loop
			$time = 0;
			continue;
		} else {
			// if one is obtained, grab it
			$selection = $result->fetch_assoc();

			// find the track in the db
			$query = 'SELECT * FROM db_cache WHERE TRACKID="' . $selection['TRACKID'] . '"';
			$result = $mysqli->query($query);
			// selected track's info, THIS IS USED IN THE REST OF THE SCRIPT
			$row = $result->fetch_assoc();
			strimmerLog("Attempting to play track " . $row['TRACKID']);

			// delete it from the queue
			$query = 'DELETE FROM play_queue ORDER BY ISNULL(play_queue.ADDED_BY) LIMIT 1';
			$mysqli->real_query($query);

			// if it WAS NOT ADDED MANUALLY
			if(!isset($selection['ADDED_BY'])) {
				// select a new random track to add
				$query = "SELECT * FROM db_cache LIMIT 1 OFFSET " . mt_rand(0,$rand_max);
				$result = $mysqli->query($query);
				$temp_row = $result->fetch_assoc();

				// check for already queued tracks
				// (well, i can use it here)
				if($max_queued > 20) {
					$query = 'SELECT TRACKID FROM play_queue WHERE TRACKID="' . $temp_row['TRACKID'] . '"';
					$result_checkdups = $mysqli->query($query);
					while($result_checkdups->num_rows > 0) {
						$query = "SELECT * FROM db_cache LIMIT 1 OFFSET " . mt_rand(0,$rand_max);
						$result = $mysqli->query($query);
						$temp_row = $result->fetch_assoc();

						$query = 'SELECT TRACKID FROM play_queue WHERE TRACKID="' . $temp_row['TRACKID'] . '"';
						$result_checkdups = $mysqli->query($query);
					}
				}

				// make sure the track hasn't been detected as faulty before queueing it
				while(!$good_track_found) {
					if(isset($temp_row['ERRORCODE'])) {
						if(!in_array($temp_row['ERRORCODE'],$goodCodes) && !in_array($temp_row['ERRORCODE'],$serviceCodes)) {
							$query = "SELECT * FROM db_cache LIMIT 1 OFFSET " . mt_rand(0,$rand_max);
							$result = $mysqli->query($query);
							$temp_row = $result->fetch_assoc();
						} else {
							$good_track_found = 1;
						}
					} else {
						$good_track_found = 1;
					}
				}
				$good_track_found = 0;

				$query = 'INSERT INTO play_queue ( TRACKID, SERVICE, ADDED_ON ) VALUES ( "' . $temp_row['TRACKID'] . '", "' . $temp_row['SERVICE'] . '", ' . time() . ')';
				$mysqli->real_query($query);
				strimmerLog("Queued track " . $temp_row['TRACKID']);
			}
		}

		switch ($row['SERVICE']) {
			case 'SDCL':
				$stream_link = $row['RETURN_ARG5'] . "?client_id=$sc_api_key";
				break;

			case 'WYZL':
			case 'JMND':
			case 'UNDF':
			case 'URL':
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
		curl_setopt($curl, CURLOPT_USERAGENT, "Mozilla/5.0 (X11; Linux x86_64; rv:21.0) Gecko/20100101 Firefox/21.0");
		$output = curl_exec($curl);

		$httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		
		$query = 'UPDATE db_cache SET ERRORCODE=' . $httpCode . ' WHERE TRACKID="' . $row['TRACKID'] . '"';
		$mysqli->real_query($query);
		strimmerLog("Got API response code of $httpCode");

		if(!in_array($httpCode,$goodCodes)) {
			if($email['alerts_enabled']) {
				$message =  "This is an automated message from Strimmer. If you do not wish to see these messages, please disable them in your configuration file.\r\n";
				$message .= "\r\n";
				$message .= "The following track, " . $row['RETURN_ARG2'] . " by " . $row['RETURN_ARG3'] . " on " . $row['SERVICE'] . " [" . $row['TRACKID'] . "] has returned an error code of " . $httpCode . ".\r\n";

				if(in_array($httpCode,$serviceCodes) && !in_array($httpCode,$goodCodes)) {
					$subject = '[Strimmer] Service interruption detected (' . $row['TRACKID'] . ')';
					$message .= "This appears to be a service interruption. The track has been flagged with a warning corresponding with the error code. Strimmer will continue to play the track and will resolve the warning the next time the track is queued.";
				}
				if(!in_array($httpCode,$goodCodes) && !in_array($httpCode,$serviceCodes)) {
					$subject = '[Strimmer] Attempted to play a faulty track (' . $row['TRACKID'] . ')';
					$message .= "The track was skipped and has been tagged with the error code. It will no longer be played by Strimmer until the issue is resolved.";
					$message .= "\r\n<b>Attempted URL:</b> " . $stream_link;
				}

				strimmerLog("Sent out email pertaining to track " . $row['TRACKID']);

				$headers   = array();
				$headers[] = "MIME-Version: 1.0";
				$headers[] = "Content-type: text/plain; charset=iso-8859-1";
				$headers[] = "From: {$email['from']}";
				$headers[] = "Reply-To: {$email['to']}";
				$headers[] = "Subject: {$subject}";
				$headers[] = "X-Mailer: PHP/".phpversion();

				mail($email['to'], $subject, $message, implode("\r\n", $headers));
			}

			$time = 0;
			curl_close($curl);
			continue;
		}
		curl_close($curl);

		$url_str = $row['RETURN_ARG3'] . " - " . $row['RETURN_ARG2'];

		putenv("ICHOST=" . $icecast['host']);
		putenv("ICPORT=" . $icecast['port']);
		putenv("ICMOUNT=" . $icecast['mount']);
		putenv("ICMOUNT_LQ=" . $icecast['mountlq']);
		putenv("ICMOUNT_OPUS=" . $icecast['mount_opus']);
		putenv("ICMOUNT_LQ_OPUS=" . $icecast['mountlq_opus']);
		putenv("ICADMIN_USER=" . $icecast['admin_user']);
		putenv("ICADMIN_PASS=" . $icecast['admin_pass']);

		$icecast['mount_exp'] = "stream_experimental.mp3";
		putenv("ICMOUNT_EXP=" . $icecast['mount_exp']);

		// anything i'm trying to do involving escaping flat out fails, so i caved and i'm doing this -.-
		$original_chars = array('\\','$','"');
		$escaped_chars = array('\\\\','\$','\"');
		$cmd_str = str_replace($original_chars, $escaped_chars, $url_str);
		exec('./metadata_upd "' . $cmd_str . '" > /dev/null 2>&1 &');

		strimmerLog("Updated stream metadata: $cmd_str");

		$time = time();

		$query = 'SELECT TRACKID FROM play_history';
		$result = $mysqli->query($query);
		//$records = $result->num_rows;
		if($result->num_rows > 5000) {
			$query = 'DELETE FROM play_history LIMIT 1';
			$mysqli->real_query($query);
		}

		if(isset($selection['ADDED_BY'])) {
			$query = 'INSERT INTO play_history ( TRACKID, SERVICE, PLAYED_ON, ADDED_BY ) VALUES ( "' . $row['TRACKID'] . '", "' . $row['SERVICE'] . '", ' . time() . ', "' . $selection['ADDED_BY'] . '")';
		} else {
			$query = 'INSERT INTO play_history ( TRACKID, SERVICE, PLAYED_ON ) VALUES ( "' . $row['TRACKID'] . '", "' . $row['SERVICE'] . '", ' . time() . ')';
		}
		$mysqli->real_query($query);

		$query = 'UPDATE db_cache SET PLAYING=0 WHERE PLAYING=1';
		$mysqli->real_query($query);

		$playcount = $row['PLAY_COUNT'] + 1;
		$query = 'UPDATE db_cache SET PLAYING=1,PLAY_COUNT=' . $playcount . ' WHERE TRACKID="' . $row['TRACKID'] . '"';
		$mysqli->real_query($query);

		strimmerLog("Playing track " . $row['TRACKID']);
		exec($icecast['ffmpeg'] . ' -hide_banner -re -i \'' . $stream_link . '\' -codec:a libmp3lame -codec:v none -strict -2 -q ' . $icecast['qual'] . ' -content_type "audio/mpeg3" "icecast://source:' . $icecast['pass'] . '@' . $icecast['host'] . ':' . $icecast['port'] . '/' . $icecast['mount'] . '" -codec:a libmp3lame -codec:v none -strict -2 -q ' . $icecast['quallq'] . ' -content_type "audio/mpeg3" "icecast://source:' . $icecast['pass'] . '@' . $icecast['host'] . ':' . $icecast['port'] . '/' . $icecast['mountlq'] . '" -codec:a libopus -codec:v none -strict -2 -vbr on -compression_level 0 -frame_duration 40 -packet_loss 5 -b:a ' . $icecast['qual_opus'] . ' -content_type "audio/ogg" "icecast://source:' . $icecast['pass'] . '@' . $icecast['host'] . ':' . $icecast['port'] . '/' . $icecast['mount_opus'] . '" -codec:a libopus -codec:v none -strict -2 -vbr on -compression_level 0 -frame_duration 40 -packet_loss 5 -b:a ' . $icecast['quallq_opus'] . ' -content_type "audio/ogg" "icecast://source:' . $icecast['pass'] . '@' . $icecast['host'] . ':' . $icecast['port'] . '/' . $icecast['mountlq_opus'] . '" -codec:a libmp3lame -codec:v none -strict -2 -q ' . $icecast['qual'] . ' -content_type "audio/mpeg3" -filter "compand=0|0:0.2|0.2:-90/-900|-70/-70|-30/-9|0/-3:2:2.9:0:0" "icecast://source:' . $icecast['pass'] . '@' . $icecast['host'] . ':' . $icecast['port'] . '/' . $icecast['mount_exp'] . '" 1> /srv/http/strimmer-data/strimmer_ffmpeg_info.txt 2>&1');
		// needed to start logging commands as of the YouTube update
		//file_put_contents(dirname(__FILE__) . "/ffmpeg_log.txt",$icecast['ffmpeg'] . ' -hide_banner -re -i \'' . $stream_link . '\' -codec:a libmp3lame -codec:v none -strict -2 -q ' . $icecast['qual'] . ' -content_type "audio/mpeg3" "icecast://source:' . $icecast['pass'] . '@' . $icecast['host'] . ':' . $icecast['port'] . '/' . $icecast['mount'] . '" -codec:a libmp3lame -codec:v none -strict -2 -q ' . $icecast['quallq'] . ' -content_type "audio/mpeg3" "icecast://source:' . $icecast['pass'] . '@' . $icecast['host'] . ':' . $icecast['port'] . '/' . $icecast['mountlq'] . '" 1> ../includes/ffmpeg_info.txt 2>&1');
	}
?>
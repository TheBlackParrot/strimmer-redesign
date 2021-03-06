<?php
	// nginx appears to not send this on my server
	// API scripts use this script, so including it here should suffice
	header("Access-Control-Allow-Origin: *");

	function isAllowedUse() {
		// plz
		include dirname(__FILE__) . "/settings.php";
		include dirname(__FILE__) . "/session.php";

		if(isset($_SESSION['login']) && $_SESSION['login'] == FALSE) {
			$query = 'SELECT API_KEY1,API_KEY2 FROM user_db';
			$result = $mysqli->query($query);
			while($row = $result->fetch_assoc()) {
				if(isset($_GET[$row['API_KEY1']])) {
					if($_GET[$row['API_KEY1']] == $row['API_KEY2']) {
						return 1;
					}
				}
			}
		} else {
			if($_SESSION['login'] == TRUE) {
				return 1;
			}
		}
		return 0;
	}

	function getStrimmerUser() {
		// PLZ
		include dirname(__FILE__) . "/settings.php";
		include dirname(__FILE__) . "/session.php";

		if(!isset($_SESSION['login']) || $_SESSION['login'] == FALSE) {
			$query = 'SELECT API_KEY1,API_KEY2,USERNAME,RANK,FAVORITES,DATE_REGISTERED,ID,LAST_QUEUED FROM user_db';
			$result = $mysqli->query($query);
			while($row = $result->fetch_assoc()) {
				if(isset($_GET[$row['API_KEY1']])) {
					if($_GET[$row['API_KEY1']] == $row['API_KEY2']) {
						return $row;
					}
				}
			}
		} else {
			if($_SESSION['login'] == TRUE) {
				$query = 'SELECT USERNAME,RANK,FAVORITES,DATE_REGISTERED,ID,LAST_QUEUED FROM user_db WHERE USERNAME="' . $_SESSION['username'] . '"';
				$result = $mysqli->query($query);
				$row = $result->fetch_assoc();
				return $row;
			}
		}
		return -1;
	}

	function canUserUnqueue($user,$trackid) {
		include dirname(__FILE__) . "/settings.php";
		include dirname(__FILE__) . "/session.php";

		if(!isset($trackid)) {
			http_response_code(400);
			die("400: Bad request - no track ID");
		}
		switch($user['RANK']) {
			case 4:
				return 1;
				break;

			case 2:
			case 3:
				$query = 'SELECT * FROM play_queue WHERE TRACKID="' . $trackid . '" AND ADDED_BY="' . $user['USERNAME'] . '"';
				$result = $mysqli->query($query);
				if($result->num_rows) {
					return 1;
				} else {
					return 0;
				}
				break;

			case 1:
				$query = 'SELECT * FROM play_queue WHERE TRACKID="' . $trackid . '" AND ADDED_BY="' . $user['USERNAME'] . '"';
				$result = $mysqli->query($query);
				if($result->num_rows) {
					$query = 'UPDATE user_db SET LAST_QUEUED=NULL WHERE USERNAME="' . $user['USERNAME'] . '"';
					return 1;
				} else {
					return 0;
				}
				break;

			default:
				return 0;
				break;
		}
	}

	function canUserQueue($user,$trackid) {
		include dirname(__FILE__) . "/settings.php";
		include dirname(__FILE__) . "/session.php";
		
		if(!isset($trackid)) {
			http_response_code(400);
			die("400: Bad request - no track ID");
		}
		switch($user['RANK']) {
			case 4:
				return 1;
				break;

			case 3:
				$query = 'SELECT * FROM play_queue WHERE TRACKID="' . $trackid . '" AND !ISNULL(play_queue.ADDED_BY)';
				$result = $mysqli->query($query);
				if(!$result->num_rows) {
					return 1;
				} else {
					return 0;
				}
				break;

			case 2:
				$query = 'SELECT * FROM play_queue WHERE ADDED_BY="' . $user['USERNAME'] . '"';
				$result = $mysqli->query($query);
				if($result->num_rows > 5) {
					return 0;
				} else {
					$query = 'SELECT * FROM play_queue WHERE TRACKID="' . $trackid . '" AND !ISNULL(play_queue.ADDED_BY)';
					$result = $mysqli->query($query);
					if(!$result->num_rows) {
						return 1;
					} else {
						return 0;
					}
				}
				break;

			case 1:
				$query = 'SELECT * FROM play_queue WHERE ADDED_BY="' . $user['USERNAME'] . '"';
				$result = $mysqli->query($query);
				if($result->num_rows > 1) {
					return 0;
				} else {
					if(time() - $user['LAST_QUEUED'] < 3600) {
						return 0;
					} else {
						$query = 'SELECT * FROM play_queue WHERE TRACKID="' . $trackid . '" AND !ISNULL(play_queue.ADDED_BY)';
						$result = $mysqli->query($query);
						if(!$result->num_rows) {
							return 1;
						} else {
							return 0;
						}
					}
				}
				break;

			default:
				return 0;
				break;
		}
	}

	function getYouTubeData($url,$what) {
		switch($what) {
			case 'VideoID':
				$data = exec('youtube-dl --restrict-filenames --get-id \'' . $url . '\'');
				break;
			case 'StreamLink':
				$data = exec('youtube-dl --youtube-skip-dash-manifest --no-cache-dir -g -f mp3/aac/m4a \'' . $url . '\'');
				break;
			case 'JSON':
				$data = exec('youtube-dl --restrict-filenames -j \'' . $url . '\'');
				break;
			
			default:
				return -1;
				break;
		}
		$data = str_replace("\r","",$data);
		$data = str_replace("\n","",$data);
		return $data;
	}
?>
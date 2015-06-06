<?php
	function isAllowedUse() {
		// plz
		include dirname(__FILE__) . "/settings.php";
		include dirname(__FILE__) . "/session.php";

		if(!isset($_SESSION['login']) || $_SESSION['login'] == FALSE) {
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
?>
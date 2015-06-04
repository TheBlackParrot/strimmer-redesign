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
			$query = 'SELECT API_KEY1,API_KEY2,USERNAME,RANK,FAVORITES,DATE_REGISTERED,ID FROM user_db';
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
				$query = 'SELECT USERNAME,RANK,FAVORITES,DATE_REGISTERED,ID FROM user_db WHERE USERNAME="' . $_SESSION['username'] . '"';
				$result = $mysqli->query($query);
				$row = $result->fetch_assoc();
				return $row;
			}
		}
		return -1;
	}
?>
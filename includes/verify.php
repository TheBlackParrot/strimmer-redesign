<?php
	include_once dirname(__FILE__) . "/settings.php";
	include_once dirname(__FILE__) . "/session.php";

	if($_SESSION['login']) {
		header("Location: $prog_internal_url");
		exit;
	}

	if(!isset($_GET['verify'])) {
		die("No verify code specified.<br/><a href=\"$prog_internal_url\">Go back</a>");
	}

	$token = $mysqli->real_escape_string($_GET['verify']);
	$username = $mysqli->real_escape_string(urldecode($_GET['user']));

	$query = 'SELECT DATE_REGISTERED,VERIFY FROM user_db WHERE USERNAME="' . $username . '"';
	$result = $mysqli->query($query);

	if($result->num_rows > 0) {
		$row = $result->fetch_assoc();
		if($row['VERIFY'] == null) {
			die("User already verified.<br/><a href=\"$prog_internal_url\">Go back</a>");
		}

		if(time() - $row['DATE_REGISTERED'] < 1800) {
			$query = 'UPDATE user_db SET VERIFY=NULL WHERE USERNAME="' . $username . '"';
			$mysqli->real_query($query);

			die("Your account has been verified; you may now login!<br/><a href=\"$prog_internal_url\">Go back</a>");
		} else {
			$query = 'DELETE FROM user_db WHERE USERNAME="' . $username . '"';
			$mysqli->real_query($query);

			die("You must verify within 30 minutes of registering. Please re-register<br/><a href=\"$prog_internal_url\">Go back</a>");
		}
	} else {
		die("Invalid username<br/><a href=\"$prog_internal_url\">Go back</a>");
	}
?>
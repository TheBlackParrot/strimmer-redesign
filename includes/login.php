<?php

include_once dirname(__FILE__) . "/settings.php";
include_once dirname(__FILE__) . "/session.php";

$invalid_cred = FALSE;

//old strimmer code
$here = 'https://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];

if ((stripos(($here), 'index.php') !== FALSE)) {
	$here = 'http://'.dirname($_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']).'/';
}

if (((!isset($_SESSION['loginrefer'])) && ((stripos($_SERVER['HTTP_REFERER'], 'login') !== FALSE )))
		|| (empty($_SERVER['HTTP_REFERER']))) {
	$_SESSION['loginrefer'] = dirname($here);
} elseif (!isset($_SESSION['loginrefer'])) {
	$_SESSION['loginrefer'] = $_SERVER['HTTP_REFERER'];
}

if(isset($_SESSION['login']) && $_SESSION['login']) {
	die();
}

function checkUsername($username) {
	$areValid = array('-','_');
	if(!ctype_alnum(str_replace($areValid, '', $username))) {
		die("Your username can only contain alphanumeric characters, dashes, and underscores.");
	}
}

if(!empty($_POST)) {
	checkUsername($_POST['username']);
	
	$username = $mysqli->real_escape_string($_POST['username']);
	$password = $mysqli->real_escape_string($_POST['password']);

	if(strlen($username) >= 4 && strlen($username) < 64) {
		$query = 'SELECT * FROM user_db WHERE USERNAME="' . $username . '"';
		$result = $mysqli->query($query);
		if($result->num_rows == 1) {
			$row = $result->fetch_assoc();

			if(password_verify($password,$row['PASSWORD_HASH'])) {
				$query = 'SELECT VERIFY,DATE_REGISTERED FROM user_db WHERE USERNAME="' . $username . '"';
				$result = $mysqli->query($query);
				$code_checks = $result->fetch_assoc();

				if($code_checks['VERIFY'] != null && $register['require_verification']) {
					if(time() - $code_checks['DATE_REGISTERED'] < 1800) {
						$query = 'DELETE FROM user_db WHERE USERNAME="' . $username . '"';
						$mysqli->real_query($query);

						die("You must verify within 30 minutes of registering. Please re-register.");
					} else {
						die("You must verify your account before you can use $prog_title. Please check your email and your spam folders for a verification message.");
					}
				}
				session_start();
				$_SESSION['login'] = TRUE;
				$_SESSION['username'] = $username;
				$_SESSION['user_id'] = $row['ID'];
				header("Location: ". $prog_internal_url);
				exit;
			} else {
				$invalid_cred = TRUE;
			}
		} else {
			$invalid_cred = TRUE;
		}
	} else {
		$invalid_cred = TRUE;
	}

	if(!$invalid_cred) {
		echo "Invalid username or password.";
	} else {
		header("Location: " . $_SERVER['HTTP_REFERER']);
	}
}

?>
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

if(!empty($_POST)) {
	$username = mysqli_real_escape_string($mysqli,stripslashes(htmlspecialchars($_POST['username'])));
	$password = mysqli_real_escape_string($mysqli,stripslashes(htmlspecialchars($_POST['password'])));

	if(strlen($username) >= 4 && strlen($username) < 64) {
		$query = 'SELECT * FROM user_db WHERE USERNAME="' . $username . '"';
		$result = mysqli_query($mysqli,$query);
		if(mysqli_num_rows($result) == 1) {
			$row = mysqli_fetch_array($result);
			if($row['PASSWORD'] == hash("sha512",$password . "-:-" . $username)) {
				session_start();
				$_SESSION['login'] = TRUE;
				$_SESSION['username'] = $username;
				$_SESSION['user_id'] = $row['ID'];
				header("Location: ". $_SESSION['loginrefer']);
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
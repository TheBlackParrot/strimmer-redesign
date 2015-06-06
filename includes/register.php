<?php
	include dirname(__FILE__) . "/settings.php";
	include dirname(__FILE__) . "/session.php";

	// taken from Zend_Mail
	function filterEmail($email) {
		$rule = array("\r" => '',
					  "\n" => '',
					  "\t" => '',
					  '"'  => '',
					  ','  => '',
					  '<'  => '',
					  '>'  => '',
					  '$'  => '',
		);

		return strtr($email, $rule);
	}

	if($_SESSION['login']) {
		header("Location: $prog_internal_url");
		exit;
	}

	if(!isset($_POST['username'])) {
		die("No username specified.");
	}

	function checkUsername($username) {
		$areValid = array('-','_');
		if(!ctype_alnum(str_replace($areValid, '', $username))) {
			die("Your username can only contain alphanumeric characters, dashes, and underscores.");
		}
	}
	checkUsername($username);

	$username = $mysqli->real_escape_string($_POST['username']);
	$query = 'SELECT USERNAME FROM user_db WHERE USERNAME="' . $username . '"';
	$result = $mysqli->query($query);
	if($result->num_rows > 0) {
		die("User already exists.");
	}

	if(!isset($_POST['password'])) {
		die("No password specified.");
	}
	if(!isset($_POST['email'])) {
		die("No email specified.");
	}

	$email_p = $mysqli->real_escape_string(filterEmail($_POST['email']));
	if(stripos($email_p,"@") == -1) {
		die("Invalid email.");
	}
	$password = $mysqli->real_escape_string($_POST['password']);
	$time = time();

	$options = ['cost' => 4];
	$api[1] = hash("sha256",password_hash(uniqid('',true), PASSWORD_DEFAULT, $options));
	$api[2] = hash("gost",password_hash(uniqid('',true), PASSWORD_DEFAULT, $options));

	$options = ['cost' => 10];
	$hash = password_hash($password, PASSWORD_DEFAULT, $options);

	if(!password_verify($password,$hash)) {
		die("Something went wrong, please try again.");
	}

	$verify = "";
	if($register['require_verification']) {
		$options = ['cost' => 4];
		$verify = hash("md5",password_hash(uniqid('',true), PASSWORD_DEFAULT, $options));

		$subject = "Enable your $prog_title account";

		$message =  "Welcome to $prog_title!\r\n";
		$message .= "\r\n";
		$message .= "$prog_title requires new accounts to be verified before they are activated.\r\n";
		$message .= "Click the link below to verify your account.\r\n";
		$message .= "\r\n";
		$message .= "$prog_internal_url/includes/verify.php?user=" . urlencode($username) . "&verify=$verify";
		$message .= "\r\n";
		$message .= "New accounts are automatically set to the rank of Listener. $prog_title administrators can increase your rank if need be.\r\n";
		$message .= "See the About panel for more information on ranks.";

		$headers   = array();
		$headers[] = "MIME-Version: 1.0";
		$headers[] = "Content-type: text/plain; charset=iso-8859-1";
		$headers[] = "From: " . $email['from'];
		$headers[] = "Reply-To: " . filterEmail($_POST['email']);
		$headers[] = "Subject:" . $subject;
		$headers[] = "X-Mailer: PHP/".phpversion();

		mail(filterEmail($_POST['email']), $subject, $message, implode("\r\n", $headers));
	}

	$query =	"INSERT INTO user_db (USERNAME,EMAIL,DATE_REGISTERED,PASSWORD_HASH,API_KEY1,API_KEY2,VERIFY,RANK)
				VALUES (\"$username\",\"$email_p\"," . time() . ",\"$hash\",\"$api[1]\",\"$api[2]\",\"$verify\",1)";
	$mysqli->query($query);

	if($register['require_verification']) {
		echo "Registration successful, please check your email for a verification link.";
	} else {
		echo "Registration successful. You may now login.";
	}
?>
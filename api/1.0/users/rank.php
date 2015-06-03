<?php
	header("Content-Type: text/plain");
	// CORS requests
	header("Access-Control-Allow-Origin: *");

	$root = dirname(dirname(dirname(dirname(__FILE__))));

	include "$root/config.php";

	if(isset($_GET['user'])) {
		$username = htmlspecialchars($_GET['user']);
		$query = 'SELECT RANK FROM user_db WHERE USERNAME="' . $username . '"';
		if($result = $mysqli->query($query)) {
			if(!$result->num_rows) {
				$return = 0;
			} else {
				$selection = $result->fetch_array();
				$return = $selection[0];
			}
		}
		
		$fixed_type = "none";
		if(isset($_GET['type'])) {
			$type = htmlspecialchars($_GET['type']);
			if(in_array($type,array("none","json"))) {
				$fixed_type = $type;
			}
		}

		switch($fixed_type) {
			case 'json':
				echo json_encode($return);
				break;
			
			case 'none':
				echo $return;
				break;
		}
	} else {
		http_response_code(400);
		die(json_encode("400: Bad request"));
	}
?>
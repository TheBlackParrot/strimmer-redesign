<?php
	header("Content-Type: text/plain");
	// CORS requests
	header("Access-Control-Allow-Origin: *");

	$root = dirname(dirname(dirname(dirname(__FILE__))));

	include "$root/config.php";

	$query = 'SELECT USERNAME,LASTACTIVE,RANK FROM user_db';
	$result = $mysqli->query($query);
	while($row = $result->fetch_assoc()) {
		$cur_array['USER'] = $row['USERNAME'];
		$cur_array['LAST_ACTIVE'] = $row['LASTACTIVE'];
		$cur_array['RANK'] = $row['RANK'];
		$data['RETURN_DATA'][] = $cur_array;
		unset($cur_array);
		unset($row);
	}

	$cur_array['USER'] = $prog_title;
	$cur_array['LAST_ACTIVE'] = 0;
	$cur_array['RANK'] = 0;
	$data['RETURN_DATA'][] = $cur_array;
	unset($cur_array);

	$data['METADATA']['COUNT'] = count($data['RETURN_DATA']);
	$data['METADATA']['TIMESTAMP'] = time();

	echo json_encode($data);
?>
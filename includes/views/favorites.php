<?php
	include dirname(dirname(__FILE__)) . "/session.php";

	if($_SESSION['login'] == FALSE || !isset($_SESSION['login'])) {
		http_response_code(401);
		die("401: Unauthorized");
	}
?>

<tr class="table-header">
	<td>#</td>
	<td></td>
	<td><i class="fa fa-music"></i>&nbsp; Title</td>
	<td><i class="fa fa-microphone"></i>&nbsp; Artist</td>
	<td><i class="fa fa-user"></i>&nbsp; Added by</td>
	<td><i class="fa fa-clock-o"></i>&nbsp; Added on</td>
</tr>
<script src="js/list-view.js"></script>
<script>
	$(".header-wrapper h1").text("Favorites");
	strimmer_data = {};
	strimmer_data.RETURN_DATA = [];

	for (var i in library_data.RETURN_DATA) {
		var trackid = library_data.RETURN_DATA[i].STRIMMER_ID;

		found = favorite_data.RETURN_DATA.some(function(val){
			return val == trackid;
		});

		if(found) {
			strimmer_data.RETURN_DATA.push(library_data.RETURN_DATA[i]);
		}
	}

	var max = 50;
	if(strimmer_data.RETURN_DATA.length < 50) {
		max = strimmer_data.RETURN_DATA.length;
	}

	for(i=0;i<max;i++) {
		addStrimmerRow(strimmer_data.RETURN_DATA[i]);
	}

</script>

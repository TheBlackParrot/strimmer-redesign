<?php
	include dirname(dirname(__FILE__)) . "/includes/session.php";
	header("Content-Type: application/javascript");

	if($_SESSION['login'] == FALSE || !isset($_SESSION['login'])) {
		http_response_code(401);
		die("401: Unauthorized");
	}
?>

var strimmer_host = 'https://strimmer2.theblackparrot.us/api/1.0/';

console.log("verification script loaded");

function verifyStrimmerTrack(trackid,callback) {
	var url = strimmer_host + 'functions/verify.php?ID=' + encodeURI(trackid);
	$.ajax({
		type: 'GET',
		url: url,
		contentType: 'text/plain',
		dataType: 'text',
		xhrFields: {
			withCredentials: false
		},
		success: function(data) {
			if(typeof callback === "function") {
				callback(data);
			}
		},
		error: function() {
			console.log("error");
		}
	});
}

$("#verifyTrack").on("click",function(){
	var trackid = $(".info-area").attr("loaded_track");
	var element = $(this);

	console.log("Verifying API response for " + trackid + "...");

	verifyStrimmerTrack(trackid,function(data){
		var index = null;
		library_data.RETURN_DATA.some(function(row,i){
			if(row.STRIMMER_ID == trackid) {
				index = i;
				return true;
			}
		});
		if(index != null) {
			if(library_data.RETURN_DATA[index].LAST_API_RESPONSE_CODE != data) {
				library_data.RETURN_DATA[index].LAST_API_RESPONSE_CODE = data;
				console.log("API response code for " + trackid + " revised to " + data);

				//if($(".song_row[trackid='" + trackid + "' > td > .errorCode").length == 1) {
					//add showing/hiding/modifying of the error code in the list later, seems like it'll be a pain
				//}
			} else {
				console.log("API response code for " + trackid + " remains " + data);
			}
		}
	});
});
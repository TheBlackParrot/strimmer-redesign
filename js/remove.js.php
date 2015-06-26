<?php
	include dirname(dirname(__FILE__)) . "/includes/session.php";
	header("Content-Type: application/javascript");

	if($_SESSION['login'] == FALSE || !isset($_SESSION['login'])) {
		http_response_code(401);
		die("401: Unauthorized");
	}
?>

var strimmer_host = 'https://strimmer2.theblackparrot.us/api/1.0/';

console.log("track removal script loaded");

function removeStrimmerTrack(trackid,callback) {
	var url = strimmer_host + 'functions/remove.php?ID=' + encodeURI(trackid);
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

$("#removeTrack").on("click",function(){
	var trackid = $(".info-area").attr("loaded_track");
	var element = $(this);

	removeStrimmerTrack(trackid,function(data){
		if(data == "1") {
			//todo: add animations
			$(".song_row[trackid='" + trackid + "'").remove();
			
			//var row = library_data.RETURN_DATA.filter(function(obj){
			//	return obj.STRIMMER_ID == tmpid;
			//});

			var index = null;
			library_data.RETURN_DATA.some(function(row,i){
				if(row.STRIMMER_ID == trackid) {
					index = i;
					return true;
				}
			});
			if(index != null) {
				console.log("Removing " + library_data.RETURN_DATA[index].STRIMMER_ID + " from the library");
				library_data.RETURN_DATA.splice(index,1);
			}

			index = null;
			favorite_data.RETURN_DATA.some(function(row,i){
				if(row[0] == trackid) {
					index = i;
					return true;
				}
			});
			if(index != null) {
				console.log("Removing " + favorite_data.RETURN_DATA[index] + " from the favorites array");
				favorite_data.RETURN_DATA.splice(index,1);
			}
		} else {
			console.log("Error in request");
		}
	});
});
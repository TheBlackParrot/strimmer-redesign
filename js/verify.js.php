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

	element.addClass("info-buttons-disabled");
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
			var song_row = $(".song_row[trackid='" + trackid + "']");
			var goodResponseCodes = ["302", "200", "201", "203"];
			var serviceResponseCodes = ["500", "502", "503", "504"];

			if(library_data.RETURN_DATA[index].LAST_API_RESPONSE_CODE != data) {
				library_data.RETURN_DATA[index].LAST_API_RESPONSE_CODE = data;
				console.log("API response code for " + trackid + " revised to " + data);

				//if($(".song_row[trackid='" + trackid + "'] > td > .errorCode").length == 1) {
					//add showing/hiding/modifying of the error code in the list later, seems like it'll be a pain
				//}
			} else {
				console.log("API response code for " + trackid + " remains " + data);
			}

			var art_td = $(".song_row[trackid='" + trackid + "'] td:nth-child(2)");
			var title_td = $(".song_row[trackid='" + trackid + "'] td:nth-child(3)");
			var temp_color = art_td.css("background-color");

			if(goodResponseCodes.indexOf(data) != -1) {
				song_row.children("td").css("background-color","#4CAF50");
				temp_color = null;

				if(art_td.children(".errorCode").length > 0) {
					art_td.children(".errorCode").remove();
					art_td.children("img").css("opacity","1");
					title_td.children(".fa").remove();
					title_td.html(title_td.html().replace(/&nbsp;/gi,''));
				}
			}
			if(serviceResponseCodes.indexOf(data) != -1) {
				song_row.children("td").css("background-color","#2196F3");
				temp_color = "#2196F3";
				
				if(art_td.children(".errorCode").length > 0) {
					art_td.children(".errorCode").text(data);
				} else {
					art_td.append('<span class="errorCode">' + data + '</span>');
					art_td.children("img").css("opacity","0.33");
				}

				title_td.children(".fa").remove();
				
				// i'm going to assume someone might also run into this issue
				// http://stackoverflow.com/a/6452789
				// there's your answer
				title_td.html(title_td.html().replace(/&nbsp;/gi,''));
				
				title_td.prepend("<i style=\"color: #2196F3;\" class=\"fa fa-question-circle error-symbol\"></i>&nbsp;");
			}
			if(serviceResponseCodes.indexOf(data) == -1 && goodResponseCodes.indexOf(data) == -1) {
				song_row.children("td").css("background-color","#F44336");
				temp_color = "#F44336";
				
				if(art_td.children(".errorCode").length > 0) {
					art_td.children(".errorCode").text(data);
				} else {
					art_td.append('<span class="errorCode">' + data + '</span>');
					art_td.children("img").css("opacity","0.33");
				}

				title_td.children(".fa").remove();
				title_td.html(title_td.html().replace(/&nbsp;/gi,''));
				title_td.prepend("<i style=\"color: #F44336;\" class=\"fa fa-exclamation-triangle error-symbol\"></i>&nbsp;");
			}	
			setTimeout(function(){
				song_row.children("td").css("background-color","");
				art_td.css("background-color",temp_color);
				element.removeClass("info-buttons-disabled");
			},500);
		}
	});
});
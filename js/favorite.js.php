<?php
	include dirname(dirname(__FILE__)) . "/includes/session.php";
	header("Content-Type: application/javascript");

	if($_SESSION['login'] == FALSE || !isset($_SESSION['login'])) {
		http_response_code(401);
		die("401: Unauthorized");
	}
?>

var strimmer_host = 'https://strimmer2.theblackparrot.us/api/1.0/';

console.log("favorites script loaded");

function favoriteStrimmerTrack(trackid,callback) {
	var url = strimmer_host + 'functions/favorite.php?ID=' + encodeURI(trackid);
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

function getUserFavorites(callback) {
	var url = strimmer_host + 'users/favorites.php';
	$.ajax({
		type: 'GET',
		url: url,
		contentType: 'text/plain',
		dataType: 'json',
		xhrFields: {
			withCredentials: false
		},
		success: function(data) {
			favorite_data = data;

			if(typeof callback === "function") {
				callback(data);
			}
		},
		error: function() {
			console.log("error");
		}
	});
}

$("#favoriteTrack").on("click",function(){
	var trackid = $(".info-area").attr("loaded_track");
	var element = $(this);

	favoriteStrimmerTrack(trackid,function(data){
		if(data == "1") {
			element.addClass("is-favorite");
			element.attr("title","Unfavorite Track");
			favorite_data.RETURN_DATA.push(trackid);
		} else {
			element.removeClass("is-favorite");
			element.attr("title","Favorite Track");
			favorite_data.RETURN_DATA.pop(trackid);
		}
	});
});

function checkIfFavorite(trackid,callback) {
	var found = favorite_data.RETURN_DATA.some(function(val){
		return val == trackid;
	});

	if(typeof callback === "function") {
		callback(found);
	}
}
<?php
	include dirname(dirname(__FILE__)) . "/includes/session.php";
	header("Content-Type: application/javascript");

	if($_SESSION['login'] == FALSE || !isset($_SESSION['login'])) {
		http_response_code(401);
		die("401: Unauthorized");
	}
?>

var strimmer_host = 'https://strimmer2.theblackparrot.us/api/1.0/';

console.log("queue script loaded");

function queueStrimmerTrack(action,trackid,callback) {
	if(action == "unqueue") {
		var url = strimmer_host + 'functions/unqueue.php?ID=' + encodeURI(trackid);
	} else {
		var url = strimmer_host + 'functions/queue.php?ID=' + encodeURI(trackid);
	}
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

function checkIfQueued(trackid,callback) {
	var url = strimmer_host + 'fetch/queued.php?ID=' + encodeURI(trackid);
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

function canUserQueue(action,trackid,callback) {
	if(action == "unqueue") {
		var url = strimmer_host + 'users/can_unqueue.php?ID=' + encodeURI(trackid);
	} else {
		var url = strimmer_host + 'users/can_queue.php?ID=' + encodeURI(trackid);
	}
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

$("#queueTrack").on("click",function(){
	var trackid = $(".info-area").attr("loaded_track");
	var element = $(this);
	console.log("queue button clicked");

	if(!element.hasClass("info-buttons-disabled")) {
		if(element.hasClass("is-queued")) {
			queueStrimmerTrack("unqueue",trackid,function(){
				element.attr("title","Queue Track");
				element.removeClass("is-queued");
			});
		} else {
			queueStrimmerTrack("queue",trackid,function(){
				element.attr("title","Unqueue Track");
				element.addClass("is-queued");
			});
		}
	}
});
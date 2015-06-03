var strimmer_host = 'https://strimmer2.theblackparrot.us/api/1.0/';
var old_result;
var dominant_color = {r: 63, g: 81, b: 181};
var playing = 0;

function getStrimmerNowPlaying(verbosity,type,callback) {
	var url = strimmer_host + 'fetch/playing.php?verbosity=' + encodeURI(verbosity) + '&type=' + encodeURI(type);
	//console.log(url);

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

function getStrimmerProgress(type,callback) {
	var url = strimmer_host + 'fetch/progress.php?type=' + encodeURI(type);
	//console.log(url);

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


function updateNowPlaying() {
	getStrimmerNowPlaying("low","none", function(current_result){
		if(!library_data) {
			console.log("library_data not found, doing nothing.");
		} else {
			if(old_result != current_result) {
				old_result = current_result;
				library_data.RETURN_DATA.map(function (search) {
					if(search.STRIMMER_ID == current_result) {
						index = library_data.RETURN_DATA.indexOf(search);
						console.log(index);
					}
				});
				var row = library_data.RETURN_DATA[index];

				updateDominantColor(row.CACHED_ART);
				
				$(".progress-bar-filled").css("background-color","rgb(" + dominant_color.toString() + ")");

				$(".bg_img img").removeClass("standard-fadein");
				$(".bg_img img").addClass("standard-fadeout");
				$(".bg_img img").one("animationend webkitAnimationEnd oAnimationEnd MSAnimationEnd", function(){
					$(".bg_img img").attr("src",row.CACHED_ART);
					$(".bg_img img").removeClass("standard-fadeout");
					$(".bg_img img").addClass("standard-fadein");
				});

				$(".playing-info").removeClass("info-stats-fadein");
				$(".playing-info").addClass("info-stats-fadeout");
				$(".playing-info").one("animationend webkitAnimationEnd oAnimationEnd MSAnimationEnd", function(){
					$(".playing-info .title").html('<a href="' + row.TRACK_PERMALINK + '">' + row.TITLE + '</a>');
					$(".playing-info .artist").html('<a href="' + row.ARTIST_PERMALINK + '">' + row.ARTIST + '</a>');
					$(".playing-info .info").html("Added by " + row.ADDED_BY + " from " + row.SERVICE);
					$(".playing-album-art img").attr("src",row.CACHED_ART);
					$(".playing-info").removeClass("info-stats-fadeout");
					$(".playing-info").addClass("info-stats-fadein");
					$(".playing-drawer").attr("loaded_track",row.STRIMMER_ID);
				});

				$(".song_row").each(function(){
					if($(this).attr("trackid") != row.STRIMMER_ID) {
						$(this).removeClass("song_row_playing");
					} else {
						$(this).addClass("song_row_playing");
					}
				});

				playing = 0;
				$("#audioCSP").remove();
			}
		}
	});
}

function updateDominantColor(url) {
	var img = new Image();
	img.crossOrigin = "Anonymous";
	img.src = url;
	img.onload = function() {
		var colorThief = new ColorThief();
		dominant_color = colorThief.getColor(img);
		$(".progress-bar-filled").css("background-color","rgb(" + dominant_color.toString() + ")");
	}
}

setInterval(function(){
	updateNowPlaying();
}, 5000);

/*
			<div class="playing-stats">
				<div class="elapsed-time">0:34</div>
				<div class="progress-bar-wrapper" style="width: calc(100% - 116px);">
					<div class="progress-bar-filled" style="width: calc(100% - 86%);"></div>
					<div class="progress-bar-unfilled" style="width: calc(100%);"></div>
				</div>
				<div class="total-time">3:51</div>
			</div>
*/
setInterval(function(){
	getStrimmerProgress("all", function(data){
		var lines = data.split("\r\n");
		$(".elapsed-time").html(lines[1]);
		$(".progress-bar-filled").css("width",lines[0] + "%");
		$(".total-time").html(lines[2]);
		if(lines[0] > 0 && playing == 0) {
			console.log("change detected");
			if(getCookie("enbCSP") == 1) {
				playing = 1;
				getStrimmerNowPlaying("low","none", function(current_result){
					library_data.RETURN_DATA.map(function (search) {
						if(search.STRIMMER_ID == current_result) {
							index = library_data.RETURN_DATA.indexOf(search);
							console.log(index);
						}
					});
					var row = library_data.RETURN_DATA[index];

					var error = 0;
					if(getCookie("SCAPIKey") == "" && row.SERVICE == "SDCL") {
						console.log("No SoundCloud API key detected, please fix this in your settings.");
						error = 1;
					}
					if(getCookie("JMAPIKey") == "" && row.SERVICE == "JMND") {
						console.log("No Jamendo API key detected, please fix this in your settings.");
						error = 1;	
					}

					if(error == 0) {
						var hms = $(".elapsed-time").text();
						var a = hms.split(':');
						var offset = (+a[0]) * 60 + (+a[1]);

						switch(row.SERVICE) {
							case "SDCL":
								$("body").append('<audio id="audioCSP" src="' + row.API_STREAM + '?client_id=' + getCookie("SCAPIKey") + '" preload/>');
								break;
							case "JMND":
								$("body").append('<audio id="audioCSP" src="' + row.API_STREAM + '" preload/>');
								break;
							case "YTUB":
								$("body").append('<iframe id="audioCSP" type="text/html" style="display: none;" src="https://www.youtube.com/embed/' + row.SERVICE_ID + '?autoplay=1&start=' + offset + '"/>');
								break;
							default:
								$("body").append('<audio id="audioCSP" src="' + row.API_STREAM + '" preload="auto"/>');
								break;
						}

						var audio = document.getElementById('audioCSP');
						if(audio.nodeName == "AUDIO") {
							audio.currentTime = offset;
							audio.play();
						}
					}
				});
			}
		}
	});
}, 1000);
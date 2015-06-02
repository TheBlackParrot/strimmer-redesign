var strimmer_host = 'http://strimmer2.theblackparrot.us/api/1.0/';
var old_result;
var dominant_color = {r: 63, g: 81, b: 181};

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
/*
		<div class="bg_img"><img src="images/test/flower.jpg"/></div>
		<div class="playing-drawer">
			<div class="playing-stats">
				<div class="elapsed-time">0:34</div>
				<div class="progress-bar-wrapper" style="width: calc(100% - 116px);">
					<div class="progress-bar-filled" style="width: calc(100% - 86%);"></div>
					<div class="progress-bar-unfilled" style="width: calc(100%);"></div>
				</div>
				<div class="total-time">3:51</div>
			</div>
			<div class="playing-wrapper">
				<div class="playing-album-art">
					<img src="images/test/flower.jpg"/>
				</div>
				<div class="playing-info">
					<span class="title">Sample Song</span><br/>
					<span class="artist">Someone</span><br/>
					<span class="info">Added by TheBlackParrot from SoundCloud</span>
				</div>
			</div>
		</div>
*/

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
					$(".playing-info .title").html(row.TITLE);
					$(".playing-info .artist").html(row.ARTIST);
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
	});
}, 1000);
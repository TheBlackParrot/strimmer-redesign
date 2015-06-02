var strimmer_host = 'http://strimmer2.theblackparrot.us/api/1.0/';
var old_result;

function getStrimmerNowPlaying(verbosity,type,callback) {
	var url = strimmer_host + 'fetch/playing.php?verbosity=' + encodeURI(verbosity) + '&type=' + encodeURI(type);
	console.log(url);

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
			$(".table-loader-wrapper").remove();
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
setInterval(function(){
	getStrimmerNowPlaying("low","none", function(current_result){
		if(!strimmer_data) {
			console.log("strimmer_data not found, doing nothing.");
		} else {
			if(old_result != current_result) {
				old_result = current_result;
				strimmer_data.RETURN_DATA.map(function (search) {
					if(search.STRIMMER_ID == current_result) {
						index = strimmer_data.RETURN_DATA.indexOf(search);
						console.log(index);
					}
				});
				var row = strimmer_data.RETURN_DATA[index];

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
			}
		}
	});
}, 5000)
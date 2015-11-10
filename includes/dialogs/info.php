<div class="dialog-wrapper">
	<div class="dialog" style="width: 700px;">
		<div class="bg_img_info_dg">
			<img src="images/bg-placeholder.jpg">
		</div>
		<div class="info-album-art" style="position: relative; z-index: 100;">
			<img src="images/bg-placeholder.jpg">
		</div>
		<div class="info-dg-header" style="position: relative; z-index: 100;">
			<span class="title"></span><br/>
			<span class="artist"></span><br/>
		</div>

		<table class="info-table">
			<tr>
				<td>Title</td>
				<td id="inf0"></td>
			</tr>
			<tr>
				<td>Artist</td>
				<td id="inf1"></td>
			</tr>
			<tr>
				<td>Artwork</td>
				<td id="inf2"></td>
			</tr>
			<tr>
				<td>Last API Response</td>
				<td id="inf3"></td>
			</tr>
			<tr>
				<td>Play Count</td>
				<td id="inf4"></td>
			</tr>
			<tr>
				<td>Added By</td>
				<td id="inf5"></td>
			</tr>
			<tr>
				<td>Added On</td>
				<td id="inf6"></td>
			</tr>
			<tr>
				<td>Service</td>
				<td id="inf7"></td>
			</tr>
			<tr>
				<td>Service ID</td>
				<td id="inf8"></td>
			</tr>
			<tr>
				<td>Strimmer ID</td>
				<td id="inf9"></td>
			</tr>
		</table>

		<div class="dialog-buttons">
			<div class="button" id="closeDialog">Close</div>
		</div>
	</div>
</div>

<script src="js/dialog.js"></script>
<script>
$(document).ready(function(){
	var row = library_data.RETURN_DATA.filter(function(obj){
		return obj.STRIMMER_ID === $(".info-area").attr("loaded_track");
	})[0];

	$("#inf0").html('<a href="' + row.TRACK_PERMALINK + '">' + row.TITLE + '</a>');
	$("#inf1").html('<a href="' + row.ARTIST_PERMALINK + '">' + row.ARTIST + '</a>');
	$("#inf2").html('<a href="' + row.ART_PERMALINK + '">' + row.ART_PERMALINK + '</a>');
	$("#inf3").text((row.LAST_API_RESPONSE_CODE == null) ? "N/A" : row.LAST_API_RESPONSE_CODE);
	$("#inf4").text(row.PLAY_COUNT + " plays");
	$("#inf6").text(getFormattedDate(row.ADDED_ON));
	$("#inf7").text(getLongService(row.SERVICE));
	$("#inf8").text(row.SERVICE_ID);
	$("#inf9").text(row.STRIMMER_ID);

	getUserData(row.ADDED_BY,function(data){
		if(typeof data === "undefined") {
			data = {};
			data.RANK = "0";
		}
		getUserColor(data.RANK, function(color){
			$("#inf5").html('<div class="user-rank-list" style="background-color: ' + color + ';"></div>' + row.ADDED_BY);
		});
	});

	$(".info-dg-header .title").html('<a title="' + row.TITLE + '" href="' + row.TRACK_PERMALINK + '">' + row.TITLE + '</a>');
	$(".info-dg-header .artist").html('<a title="' + row.ARTIST + '" href="' + row.ARTIST_PERMALINK + '">' + row.ARTIST + '</a>');
	$(".info-album-art img").attr("src",row.CACHED_ART);
	$(".bg_img_info_dg img").attr("src",row.CACHED_ART);
	$(".info-dg-header").addClass("info-stats-fadein");

	var img = new Image();
	img.crossOrigin = "Anonymous";
	img.src = row.CACHED_ART;
	
	img.onload = function() {
		var colorThief = new ColorThief();
		var colors = colorThief.getColor(img);

		var hex = "";
		var newc = "";
		
		for(i in colors) {
			newc = colors[i].toString(16);
			if(newc.length == 1) {
				newc = "0" + newc;
			}
			hex += newc;
		}

		var YIQ = getContrastYIQ(hex);
		$(".dialog").css("background-color", "rgb(" + colorThief.getColor(img).toString() + ")");
		//console.log(hex + " " + YIQ);
		$(".dialog").css("color", YIQ);
		$(".dialog-buttons .button").css("background-color", YIQ);
		if(YIQ == "white") {
			$(".dialog-buttons .button").css("color", "black");
		} else {
			$(".dialog-buttons .button").css("color", "white");
		}
	}
});
</script>
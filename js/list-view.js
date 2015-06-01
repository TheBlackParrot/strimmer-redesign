var strimmer_host = 'http://strimmer2.theblackparrot.us/api/1.0/';
var loading;
var last_track;

// http://stackoverflow.com/questions/3187790/convert-unix-time-to-mm-dd-yy-hhmm-24-hour-in-javascript/3189792#3189792
String.prototype.padLeft = function (length, character) { 
	return new Array(length - this.length + 1).join(character || ' ') + this; 
};

getFormattedDate = function(timestamp) {
	var date = new Date(timestamp*1000);
	var monthName = ["January","February","March","April","May","June","July","August","September","October","November","December"];

	return monthName[date.getMonth()] + " " + String(date.getDate()).padLeft(2, '0') + ", " + date.getFullYear() + ", " + String(date.getHours()).padLeft(2, '0') + ":" + String(date.getMinutes()).padLeft(2, '0');
};

function testLoad() {
	$(".main-area").append("<div class=\"table-loader-wrapper\"><i class=\"fa fa-circle-o-notch fa-spin table-loader\">&nbsp;</i></div>");
}

// use this function for loading all list views

function getStrimmerListJSON(offset,amount,sort,order,callback) {
	$(".main-area").append("<div class=\"table-loader-wrapper\"><i class=\"fa fa-circle-o-notch fa-spin table-loader\">&nbsp;</i></div>");
	var url = strimmer_host + 'fetch/tracks.php?offset=' + encodeURI(offset) + '&amount=' + encodeURI(amount) + '&sort=' + encodeURI(sort) + '&order=' + encodeURI(order);
	console.log(url);

	$.ajax({
		type: 'GET',
		url: url,
		contentType: 'text/plain',
		dataType: 'json',
		xhrFields: {
			withCredentials: false
		},
		success: function(data) {
			strimmer_data = data;
			if(typeof callback === "function") {
				callback();
			}
			$(".table-loader-wrapper").remove();
		},
		error: function() {
			console.log("error");
			$(".table-loader-wrapper").remove();
		}
	});
}

function addStrimmerRow(index) {
	if(index > strimmer_data.RETURN_DATA.length) {
		return;
	}
	var row = strimmer_data.RETURN_DATA[index];

	var joined_str = "<tr class=\"song_row\">";

	var position = index + 1;

	joined_str += "<td>" + position + "</td>";
	joined_str += "<td><img src=\"" + row.CACHED_ART + "\"/></td>";
	joined_str += "<td>" + row.TITLE + "</td>";
	joined_str += "<td>" + row.ARTIST + "</td>";
	joined_str += "<td>" + row.ADDED_BY + "</td>";
	joined_str += "<td>" + getFormattedDate(row.ADDED_ON) + "</td>";

	joined_str += "</tr>";
	$('.main-table tr:last').after(joined_str);
	$('.main-table tr:last').attr("trackid",row.STRIMMER_ID);
	$('.main-table tr:last').attr("list_pos",position);

	last_track = index+1;
}

$(".main-area").scroll(function() {
	var diff = $(".content-wrapper").height() - $(".main-area").height();
	var diff2 = $(".main-area").scrollTop() - diff;

	if($(".main-area").scrollTop() - diff >= 112) {
		if(!loading) {
			loading = 1;
			var lastload = 50+last_track;
			for(i=last_track;i<lastload;i++) {
				addStrimmerRow(i);
			}
			loading = 0;
		}
	}
});

/* 				<div class="info-stats" id="toggleInfo">
					<div class="info-album-art">
						<img src="images/test/flower.jpg"/>
					</div>
					<span class="title">Sample Song</span><br/>
					<span class="artist">Someone</span><br/>
					<span class="info">Added by TheBlackParrot from SoundCloud</span>
					<i class="fa fa-caret-down"></i>&nbsp;
				</div>
				<div class="info-buttons">
					<i class="fa fa-heart"></i>&nbsp;
					<i class="fa fa-plus-circle"></i>&nbsp;
					<i class="fa fa-pencil"></i>&nbsp;
					<i class="fa fa-trash"></i>&nbsp;
				</div>
*/

$(".main-table").on("click", "tr", function(e){
	var trackid = $(this).attr("trackid");
	var row = strimmer_data.RETURN_DATA[$(this).attr("list_pos")-1];

	$(".info-area").attr("loaded_track",trackid);

	$(".info-album-art img").attr("src",row.CACHED_ART);
	$(".bg_img_info img").attr("src",row.CACHED_ART);

	$(".info-stats .title").text(row.TITLE);
	$(".info-stats .artist").text(row.ARTIST);
	$(".info-stats .info").text("Added by " + row.ADDED_BY + " from " + row.SERVICE);

	if($(".info-area").attr("visible") != 1) {
		toggleInfoPanel();
	}

	e.preventDefault();
});
var strimmer_host = 'https://strimmer2.theblackparrot.us/api/1.0/';
var loading;
var last_track;
var last_row;
var last_playing_row;
var position = 0;

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
function getStrimmerListJSON(offset,amount,sort,order,where,callback) {
	// override to just use library_data, since it's there already
	$(".main-area").append("<div class=\"table-loader-wrapper\"><i class=\"fa fa-circle-o-notch fa-spin table-loader\">&nbsp;</i></div>");
	if(where == "library") {
		strimmer_data = library_data;
		if(typeof callback === "function") {
			callback(library_data);
		}
		$(".table-loader-wrapper").remove();
	} else {
		var url = strimmer_host + 'fetch/tracks.php?offset=' + encodeURI(offset) + '&amount=' + encodeURI(amount) + '&sort=' + encodeURI(sort) + '&order=' + encodeURI(order) + '&where=' + encodeURI(where);
		//console.log(url);

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
				console.log(url);
				if(typeof callback === "function") {
					callback(data);
				}
				$(".table-loader-wrapper").remove();
			},
			error: function() {
				console.log("error");
				$(".table-loader-wrapper").remove();
			}
		});
	}
}

function addStrimmerRow(row,top) {
	if(!row) {
		return;
	}

	var joined_str = "<tr class=\"song_row\">";

	position += 1;

	if(!top) {
		joined_str += "<td>" + position + "</td>";
	} else {
		$('.main-table .song_row td:nth-child(1)').each(function(){
			var old_pos = parseInt($(this).text());
			var new_pos = old_pos + 1;
			$(this).text(new_pos);
			$(this).parent().attr("list_pos",new_pos);
		});
		joined_str += "<td>1</td>";
	}

	var goodResponseCodes = ["302", "200", "201", "203"];
	var serviceCodes = ["500", "502", "503", "504"];
	var isValid = 2;
	if(row.LAST_API_RESPONSE_CODE != null) {
		if(typeof row.LAST_API_RESPONSE_CODE === "number") {
			row.LAST_API_RESPONSE_CODE = row.LAST_API_RESPONSE_CODE.toString();
		}
		if(goodResponseCodes.indexOf(row.LAST_API_RESPONSE_CODE) == -1) {
			if(serviceCodes.indexOf(row.LAST_API_RESPONSE_CODE) != -1) {
				isValid = 1;
			} else {
				isValid = 0;
			}
		}
	}

	if(isValid != 2) {
		switch(isValid) {
			case 0:
				joined_str += "<td style=\"background-color: #F44336;\"><span class=\"errorCode\">" + row.LAST_API_RESPONSE_CODE + "</span><img style=\"opacity: 0.33;\" src=\"" + row.CACHED_ART + "\"/></td>";
				joined_str += "<td><i style=\"color: #F44336;\" class=\"fa fa-exclamation-triangle error-symbol\"></i>&nbsp;" + row.TITLE + "</td>";
				break;
			case 1:
				joined_str += "<td style=\"background-color: #2196F3;\"><span class=\"errorCode\">" + row.LAST_API_RESPONSE_CODE + "</span><img style=\"opacity: 0.33;\" src=\"" + row.CACHED_ART + "\"/></td>";
				joined_str += "<td><i style=\"color: #2196F3;\" class=\"fa fa-question-circle error-symbol\"></i>&nbsp;" + row.TITLE + "</td>";
				break;
			default:
				console.log("This shouldn't be happening.");
				joined_str += "<td><img src=\"" + row.CACHED_ART + "\"/></td>";
				joined_str += "<td>" + row.TITLE + "</td>";
				break;
		}
	} else {
		joined_str += "<td><img src=\"" + row.CACHED_ART + "\"/></td>";
		joined_str += "<td>" + row.TITLE + "</td>";
	}
	
	joined_str += "<td>" + row.ARTIST + "</td>";

	joined_str += "<td>"
	getUserData(row.ADDED_BY,function(data){
		if(typeof data === "undefined") {
			data = {};
			data.RANK = "0";
		}
		getUserColor(data.RANK, function(color){
			joined_str += "<div class=\"user-rank-list\" style=\"background-color: " + color + ";\"></div>";
		});
	});
	joined_str += row.ADDED_BY;
	joined_str += "</td>";

	joined_str += "<td>" + getFormattedDate(row.ADDED_ON) + "</td>";

	joined_str += "</tr>";
	if(!top) {
		$('.main-table tr:last').after(joined_str);
		$('.main-table tr:last').attr("trackid",row.STRIMMER_ID);
		$('.main-table tr:last').attr("list_pos",position);
	} else {
		$('.main-table tr:nth-child(2)').before(joined_str);
		$('.main-table tr:nth-child(2)').attr("trackid",row.STRIMMER_ID);
		$('.main-table tr:nth-child(2)').attr("list_pos",1);
	}
	last_track = position+1;

	if(row.STRIMMER_ID == $(".playing-drawer").attr("loaded_track")) {
		$('.main-table tr:last').addClass("song_row_playing");
	}
}

$(".main-area").scroll(function() {
	var diff = $(".content-wrapper").height() - $(".main-area").height();
	var diff2 = $(".main-area").scrollTop() - diff;

	if($(".main-area").scrollTop() - diff >= 0) {
		if(!loading) {
			loading = 1;
			var lastload = 50+position;
			for(i=position;i<lastload;i++) {
				addStrimmerRow(strimmer_data.RETURN_DATA[i]);
			}
			loading = 0;
		}
	}
});

$(".main-table").off("click").on("click", "tr", function(e){
	if($(this).hasClass("table-header")) {
		return;
	}
	var trackid = $(this).attr("trackid");
	if(!trackid) {
		return;
	}

	library_data.RETURN_DATA.map(function (search) {
		if(search.STRIMMER_ID == trackid) {
			index = library_data.RETURN_DATA.indexOf(search);
			console.log(index);
		}
	});
	var row = library_data.RETURN_DATA[index];

	if(typeof checkIfFavorite === "function") {
		checkIfFavorite(trackid,function(result){
			if(result == 1) {
				$("#favoriteTrack").addClass("is-favorite");
				$("#favoriteTrack").attr("title","Unfavorite Track");
			} else {
				$("#favoriteTrack").removeClass("is-favorite");
				$("#favoriteTrack").attr("title","Favorite Track");
			}
		});
	}

	if(typeof checkIfQueued === "function") {
		checkIfQueued(trackid,function(result){
			var element = $("#queueTrack");
			console.log("checking if " + trackid + " is queued...")
			if(result == 1) {
				console.log("it is");
				canUserQueue("unqueue",trackid,function(data){
					element.attr("title","Unqueue Track");
					if(data == 1) {
						console.log("user can unqueue");
						element.removeClass("info-buttons-disabled");
						element.addClass("is-queued");
					} else {
						console.log("user cannot unqueue");
						element.removeClass("is-queued");
						element.addClass("info-buttons-disabled");
					}
				});
			} else {
				console.log("it is not");
				canUserQueue("queue",trackid,function(data){
					element.attr("title","Queue Track");
					if(data == 1) {
						console.log("user can queue");
						element.removeClass("info-buttons-disabled");
						element.removeClass("is-queued");
					} else {
						console.log("user cannot queue");
						element.removeClass("is-queued");
						element.addClass("info-buttons-disabled");
					}
				});
			}
		});
	}

	if(typeof username !== "undefined") {
		var user_data = getUserData(username);
		var rank = user_data.RANK;

		var element = $("#removeTrack");
		if(rank == 1) {
			element.addClass("info-buttons-disabled");
		}
		if(rank == 2) {
			if(row.ADDED_BY != username) {
				element.addClass("info-buttons-disabled");
			} else {
				element.removeClass("info-buttons-disabled");
			}
		}
		if(rank > 2) {
			element.removeClass("info-buttons-disabled");
		}


		element = $("#verifyTrack");
		if(rank < 4) {
			element.addClass("info-buttons-disabled");
		}
	}

	$(".bg_img_info img").removeClass("standard-fadein");
	$(".bg_img_info img").addClass("standard-fadeout");
	$(".bg_img_info img").one("animationend webkitAnimationEnd oAnimationEnd MSAnimationEnd", function(){
		$(".bg_img_info img").attr("src",row.CACHED_ART);
		$(".bg_img_info img").removeClass("standard-fadeout");
		$(".bg_img_info img").addClass("standard-fadein");
	});

	$(".info-content").removeClass("info-stats-fadein");
	$(".info-content").addClass("info-stats-fadeout");
	$(".info-content").one("animationend webkitAnimationEnd oAnimationEnd MSAnimationEnd", function(){
		$(".info-content .title").html('<a href="' + row.TRACK_PERMALINK + '">' + row.TITLE + '</a>');
		$(".info-content .artist").html('<a href="' + row.ARTIST_PERMALINK + '">' + row.ARTIST + '</a>');
		$(".info-content .info").html("Added by " + row.ADDED_BY + " from " + row.SERVICE);
		$(".info-album-art img").attr("src",row.CACHED_ART);
		$(".info-content").removeClass("info-stats-fadeout");
		$(".info-content").addClass("info-stats-fadein");
		$(".info-area").attr("loaded_track",trackid);
	});

	if(last_row) {
		last_row.removeClass("song_row_toggled");
	}
	$(this).addClass("song_row_toggled");
	last_row = $(this);

	if($(".info-area").attr("visible") != 1) {
		toggleInfoPanel();
	}

	e.preventDefault();
});

$("#testPlaying").off("click").on("click", function(){
	var random = Math.floor(Math.random()*last_track);

	if(last_playing_row) {
		last_playing_row.removeClass("song_row_playing");
	}
	var element = $(".main-table .song_row").eq(random);
	element.addClass("song_row_playing");
	last_playing_row = element;
})
var strimmer_host = 'http://strimmer2.theblackparrot.us/api/1.0/';
var loading;
var last_track;

function getFullMonth(month) {
	switch(month+1) {
		case 1: return "January";
		case 2: return "February";
		case 3: return "March";
		case 4: return "April";
		case 5: return "May";
		case 6: return "June";
		case 7: return "July";
		case 8: return "August";
		case 9: return "September";
		case 10: return "October";
		case 11: return "November";
		case 12: return "December";
	}
}

// use this function for loading all list views

function getStrimmerJSON(offset,amount,sort,order,callback) {
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
		},
		error: function() {
			console.log("error");
		}
	});
}

function addStrimmerRow(index) {
	if(index > strimmer_data.RETURN_DATA.length) {
		return;
	}
	row = strimmer_data.RETURN_DATA[index];

	var joined_str = "<tr>";

	var date = new Date(row.ADDED_ON*1000);
	var position = index + 1;

	joined_str += "<td>" + position + "</td>";
	joined_str += "<td><img src=\"" + row.CACHED_ART + "\"/></td>";
	joined_str += "<td>" + row.TITLE + "</td>";
	joined_str += "<td>" + row.ARTIST + "</td>";
	joined_str += "<td>" + row.ADDED_BY + "</td>";
	joined_str += "<td>" + getFullMonth(date.getMonth()) + " " + date.getDate() + ", " + date.getFullYear() + ", " + date.getHours() + ":" + date.getMinutes() + "</td>";

	joined_str += "</tr>";
	$('.main-table tr:last').after(joined_str);

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
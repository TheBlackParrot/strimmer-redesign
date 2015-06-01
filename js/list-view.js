var strimmer_host = 'http://strimmer2.theblackparrot.us/api/1.0/';

function getFullMonth(month) {
	switch(month) {
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

function getStrimmerJSON(offset,amount,sort,order) {
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
			var strimmer_data = data;
			//console.log(strimmer_data.RETURN_DATA[0].TITLE);
			for (var i = strimmer_data.RETURN_DATA.length - 1;i>=0;i--) {
				//console.log(strimmer_data.RETURN_DATA[i]);
				var joined_str = "<tr>";

				var position = (strimmer_data.RETURN_DATA.length - i);
				var date = new Date(strimmer_data.RETURN_DATA[i].ADDED_ON*1000);

				joined_str += "<td>" + position + "</td>";
				joined_str += "<td><img src=\"" + strimmer_data.RETURN_DATA[i].CACHED_ART + "\"/></td>";
				joined_str += "<td>" + strimmer_data.RETURN_DATA[i].TITLE + "</td>";
				joined_str += "<td>" + strimmer_data.RETURN_DATA[i].ARTIST + "</td>";
				joined_str += "<td>" + strimmer_data.RETURN_DATA[i].ADDED_BY + "</td>";
				joined_str += "<td>" + getFullMonth(date.getMonth()) + " " + date.getDate() + ", " + date.getFullYear() + ", " + date.getHours() + ":" + date.getMinutes() + "</td>";

				joined_str += "</tr>";
				$('.main-table tr:last').after(joined_str);
			};
		},
		error: function() {
			console.log("error");
		}
	});
}
getStrimmerJSON(0,21,"added","asc");
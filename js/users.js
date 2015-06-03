var strimmer_host = 'https://strimmer2.theblackparrot.us/api/1.0/';

function getStrimmerUsers(callback) {
	var url = strimmer_host + 'users/all.php';
	$.ajax({
		type: 'GET',
		url: url,
		contentType: 'text/plain',
		dataType: 'json',
		xhrFields: {
			withCredentials: false
		},
		success: function(data) {
			user_data = data;
			if(typeof callback === "function") {
				callback(data);
			}
		},
		error: function() {
			console.log("error");
		}
	});
}

function getUserData(user, callback) {
	var index;

	user_data.RETURN_DATA.map(function(search) {
		if(search.USER == user) {
			index = user_data.RETURN_DATA.indexOf(search);
		}
	});

	var row = user_data.RETURN_DATA[index];

	if(typeof callback === "function") {
		callback(row);
	} else {
		return row;
	}
}

function getUserColor(user, callback) {
	getUserData(username,function(data){
		var rank = data.RANK;
		var color;
		switch(rank) {
			case "1": color = "#2196F3"; break;
			case "2": color = "#009688"; break;
			case "3": color = "#8BC34A"; break;
			case "4": color = "#FFC107"; break;
			default: color = "#000000"; break;
		}
		if(typeof callback === "function") {
			callback(color);
		} else {
			return color;
		}
	});
}
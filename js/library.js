var strimmer_host = 'http://strimmer2.theblackparrot.us/api/1.0/';

function getStrimmerLibrary(callback) {
	var url = strimmer_host + 'fetch/tracks.php';
	$.ajax({
		type: 'GET',
		url: url,
		contentType: 'text/plain',
		dataType: 'json',
		xhrFields: {
			withCredentials: false
		},
		success: function(data) {
			library_data = data;
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
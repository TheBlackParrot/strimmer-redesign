<?php
	include dirname(dirname(__FILE__)) . "/session.php";
	include dirname(dirname(__FILE__)) . "/settings.php";
	include dirname(dirname(__FILE__)) . "/functions.php";

	if(!isAllowedUse()) {
		header('HTTP/1.0 401 Unauthorized');
		http_response_code(401);
		die();
	}

	$user = getStrimmerUser();
	if($user['RANK'] < 3) {
		header('HTTP/1.0 401 Unauthorized');
		http_response_code(401);
		die("Not allowed.");
	}

	if(!$sc_api_key) {
		die("No SoundCloud API key is defined for the server, please check your configuration file.");
	}
?>

<div class="dialog-wrapper">
	<div class="dialog" style="width: 500px;">
		<span class="header">Add a Track</span>
		<div class="add-track-tabs">
			<div class="add-track-tab" style="background-color: #f60;" load="soundcloud">
				<img src="images/assets/soundcloud.png"/>
			</div>
			<div class="add-track-tab tt-disabled" style="background-color: #b31217;" load="youtube">
				<img src="images/assets/youtube.png"/>
			</div>
			<div class="add-track-tab tt-disabled" style="background-color: #702f6f;" load="jamendo">
				<img src="images/assets/jamendo.png"/>
			</div>
			<div class="add-track-tab tt-disabled" style="background-color: #999;" load="URL">
				<img src="images/assets/url.png"/>
			</div>
		</div>

		<div class="add-track-content" tab="soundcloud">
			<form id="soundcloud_form" action="api/1.0/functions/add_soundcloud_track.php" method="GET">
				Track URL<br/>
				<input class="url-input" type="text" name="url" placeholder="https://soundcloud.com/account/some-neat-song-title"/>
			</form>
		</div>
		<div class="add-track-content" tab="youtube">
			<form id="youtube_form" action="api/1.0/functions/add_youtube_track.php" method="GET">
				Video URL<br/>
				<input class="url-input" type="text" name="url" placeholder="https://youtube.com/watch?v=dQw4w9WgXcQ"/>
			</form>
		</div>
		<div class="add-track-content" tab="jamendo">
			<form id="jamendo_form" action="api/1.0/functions/add_jamendo_track.php" method="GET">
				Track URL<br/>
				<input class="url-input" type="text" name="url" placeholder="https://www.jamendo.com/en/track/1234567/some-track"/>
			</form>
		</div>
		<div class="add-track-content" tab="URL">
			<form id="URL_form" action="api/1.0/functions/add_URL_track.php" method="GET">
				Track URL<br/>
				<input class="url-input" type="text" name="url" placeholder="http://example.com/track.mp3?dl=1"/>
				Artwork URL<br/>
				<input class="url-input" type="text" name="artwork_url" placeholder="http://i.imgur.com/1a2B3cD.png"/>
			</form>
		</div>
		<span class="add-track-status"></span>

		<div class="dialog-buttons">
			<div class="button" id="closeDialog">Cancel</div>
			<div class="button" id="submitTrack">Add Track</div>
		</div>
	</div>
</div>

<script src="js/dialog.js"></script>
<script>
//background-image: linear-gradient(transparent 50%, rgba(0,0,0,0.33) 200%);
	var bg_color;
	var current_tab = "";
	$(".add-track-tabs").off("mouseenter").on("mouseenter", ".add-track-tab", function(e){
		bg_color = $(this).css("background-color");
	});
	$(".add-track-tabs").off("click").on("click", ".add-track-tab", function(e){
		var load_tab = $(this).attr("load");
		current_tab = load_tab;

		$(".add-track-content[tab=" + load_tab + "]").show();
		$(".add-track-content[tab!=" + load_tab + "]").hide();

		$(this).css("opacity","1");
		$(".add-track-tab[load!=" + load_tab + "]").css("opacity","0.5");

		$(this).parent().css("border-bottom","5px solid " + bg_color);

		$(".add-track-status").each(function(){
			$(this).text("");
			$(this).css("color","transparent");
		})
	});

	$("#submitTrack").off("click").on("click",function(){
		if(current_tab == "jamendo") {
			alert("Currently WIP.");
			return;
		}

		if(current_tab != "") {
			var form = $("#" + current_tab + "_form");
			var url_data = form.serialize();
			var url = strimmer_host + "functions/add_" + current_tab + "_track.php?" + url_data;

			$(".add-track-status").text("Attempting to add your track...");
			$(".add-track-status").css("color","#000");

			console.log(url);

			// will return a json object that can just be pushed into library_data
			$.ajax({
				type: 'GET',
				url: url,
				contentType: 'text/plain',
				dataType: 'json',
				xhrFields: {
					withCredentials: false
				},
				success: function(data) {
					if(typeof data == "object") {
						var new_data = data.RETURN_DATA[0];
						library_data.RETURN_DATA.unshift(new_data);
						if($(".header-wrapper h1").text() == "Library") {
							addStrimmerRow(new_data,true);
						}
						$(".add-track-status").each(function(){
							$(this).text("Track successfully added!");
							$(this).css("color","#4CAF50");
						});
						$(".url-input").val("");
					} else {
						$(this).html("There was an error with adding your track.<br/>" + data.responseText);
						$(this).css("color","#F44336");
					}
				},
				error: function(data) {
					$(".add-track-status").each(function(){
						$(this).html("There was an error with adding your track. See the developer console for more information.<br/>" + data.responseText);
						$(this).css("color","#F44336");
					});
					console.log("error with adding track");
				}
			});
		}
	})
</script>
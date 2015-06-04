function toggleInfoPanel() {
	if($(".info-area").attr("visible") == 1) {
		console.log("Info area is marked as visible.");
		$(".info-area").removeClass("main-frame_slideUp");
		$(".info-area").addClass("main-frame_slideDown");
		$(".bg_img_info").removeClass("main-frame_slideUp");
		$(".bg_img_info").addClass("main-frame_slideDown");
		$(".song_row_toggled").removeClass("song_row_toggled");
		$(".info-area").one("animationend webkitAnimationEnd oAnimationEnd MSAnimationEnd", function(){
			$(".info-area").hide();
			$(".info-area").attr("visible","0");
			$(".bg_img_info").hide();
			$(".bg_img_info").attr("visible","0");
		});
	} else {
		console.log("Info area is marked as invisible.");
		$(".info-area").show();
		$(".info-area").attr("visible","1");
		$(".info-area").removeClass("main-frame_slideDown");
		$(".info-area").addClass("main-frame_slideUp");
		$(".bg_img_info").show();
		$(".bg_img_info").attr("visible","1");
		$(".bg_img_info").removeClass("main-frame_slideDown");
		$(".bg_img_info").addClass("main-frame_slideUp");
	}
}

$(document).ready(function(){
	console.log("Ready.");
	$("#toggleInfo, #toggleMain").on("click",function(){
		console.log("Info toggle clicked.");
		toggleInfoPanel();
	});

	$(".user-header, #toggleUser").on("click",function(){
		if(!$(".user-header").hasClass("override")) {
			console.log("Account info toggle clicked.");
			if($(".user-wrapper").attr("visible") == 1) {
				console.log("User account area is marked as visible.");
				$(".user-wrapper").removeClass("user_slideUp");
				$(".user-wrapper").addClass("user_slideDown");
				$(".user-caret").removeClass("rotatef");
				$(".user-caret").addClass("rotateb");
				$(".user-wrapper").one("animationend webkitAnimationEnd oAnimationEnd MSAnimationEnd", function(){
					$(".user-wrapper").attr("visible","0");
				});
			} else {
				console.log("User account area is marked as invisible.");
				$(".user-wrapper").attr("visible","1");
				$(".user-wrapper").removeClass("user_slideDown");
				$(".user-wrapper").addClass("user_slideUp");
				$(".user-caret").removeClass("rotateb");
				$(".user-caret").addClass("rotatef");
			}
		}
	});

	// menu items
	$(".menu-item, .user-header, .user-menu-item").on("click",function(){
		if($(this).attr("page")) {
			var element = $(".main-table");
			element.empty();
			element.load("includes/views/" + $(this).attr("page") + ".php");
			$(this).parent().children().removeClass("menu-item-toggled");
			$(this).addClass("menu-item-toggled");
		}
		if($(this).attr("dialog")) {
			var loadSpot = $(".dialog-load-spot");
			loadSpot.empty();
			loadSpot.load("includes/dialogs/" + $(this).attr("dialog"), function(){
				$(".dialog").addClass("dialog-open");
				$(".dialog-wrapper").addClass("standard-fadein");
			});
		}
	});
});

// shoutouts to w3schools
function getCookie(cname) {
	var name = cname + "=";
	var ca = document.cookie.split(';');
	for(var i=0; i<ca.length; i++) {
		var c = ca[i];
		while (c.charAt(0)==' ') c = c.substring(1);
		if (c.indexOf(name) == 0) return c.substring(name.length,c.length);
	}
	return "";
}
function setCookie(cname, cvalue, exdays) {
	var d = new Date();
	d.setTime(d.getTime() + (exdays*24*60*60*1000));
	var expires = "expires="+d.toUTCString();
	document.cookie = cname + "=" + cvalue + "; " + expires;
}

function loadRegisterDialog() {
	$(".dialog").removeClass("dialog-open");
	$(".dialog").addClass("dialog-close");
	$(".dialog").one("animationend webkitAnimationEnd oAnimationEnd MSAnimationEnd", function(){
		$(".dialog").empty();
		$(".dialog").load("includes/dialogs/register.php", function(){
			$(".dialog").removeClass("dialog-close");
			$(".dialog").addClass("dialog-open");
		});
	});
}
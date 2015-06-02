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
	});

	// menu items
	$(".menu-item").on("click",function(){
		if($(this).attr("page")) {
			var element = $(".main-table");
			element.empty();
			element.load("includes/views/" + $(this).attr("page") + ".php");
			$(this).parent().children().removeClass("menu-item-toggled");
			$(this).addClass("menu-item-toggled");
		}
	});
});
$(document).ready(function(){
	$("#openDialog").off("click").on("click",function(){
		var loadSpot = $(".dialog-load-spot");
		loadSpot.empty();
		loadSpot.load("includes/test-dialog.html", function(){
			$(".dialog").addClass("dialog-open");
			$(".dialog-wrapper").addClass("standard-fadein");
		});
	})
	$("#closeDialog").off("click").on("click",function(event){
		closeDialog($(this).parent().parent());
	});
	$(".dialog-wrapper").off("click").on("click",function(event){
		var clicked_element = event.target;
		if($(clicked_element).hasClass("dialog-wrapper")) {
			closeDialog($(this).children(".dialog"));
		}
	});
});

function closeDialog(dialog) {
	dialog.removeClass("dialog-open");
	dialog.addClass("dialog-close");

	var wrapper = dialog.parent();
	wrapper.removeClass("standard-fadein");
	wrapper.addClass("standard-fadeout");

	dialog.one("animationend webkitAnimationEnd oAnimationEnd MSAnimationEnd", function(){
		wrapper.remove();
	});
}
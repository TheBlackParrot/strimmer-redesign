<?php
	include "includes/session.php";
?>

<html>

<head>
	<link rel="stylesheet" href="css/fonts.css"/>
	<link rel="stylesheet" href="css/reset.css"/>
	<link rel="stylesheet" href="css/animations.css"/>
	<link rel="stylesheet" href="css/font-awesome.css"/>
	<link rel="stylesheet" id="themecss"/>
	<script src="js/jquery.js"></script>
	<script src="js/library.js"></script>
	<script src="js/users.js"></script>
	<script src="js/ui-interaction.js"></script>
	<script src="js/dialog.js"></script>
	<script src="js/now-playing.js"></script>
	<script src="js/color-thief.js"></script>
	<!--
	 * Color Thief v2.0
	 * by Lokesh Dhakar - http://www.lokeshdhakar.com
	 *
	 * License
	 * -------
	 * Creative Commons Attribution 2.5 License:
	 * http://creativecommons.org/licenses/by/2.5/
	-->
	<script>
		var theme_cookie = getCookie("theme");
		if(theme_cookie != "") {
			document.getElementById('themecss').href = "css/" + theme_cookie + ".css";
		} else {
			document.getElementById('themecss').href = "css/main.css";
		}
		var logged_in = 0;
		<?php if($_SESSION['login'] == TRUE) { ?>
			logged_in = 1;
			var username = <?php echo '"' . $_SESSION['username'] . '";'; ?>;
		<?php } ?>
		$(".main-area").append("<div class=\"table-loader-wrapper\"><i class=\"fa fa-circle-o-notch fa-spin table-loader\">&nbsp;</i></div>");
		getStrimmerLibrary(function(){
			getStrimmerUsers(function(){
				$(".main-table").load("includes/views/library.php");
				$(".table-loader-wrapper").remove();
				var dominant_color = {r: 63, g: 81, b: 181};
				updateNowPlaying();
				if(logged_in == 1) {
					console.log("user is logged in");
					getUserData(username,function(data){
						getUserColor(data.RANK, function(color){
							$(".user-av").css("border","3px solid " + color);
						});
					});
				}
			});
		});
	</script>
</head>

<body>
	<div class="wrapper">
		<div class="bg_img_info"><img src="images/bg-placeholder.jpg"/></div>
		<div class="info-area" visible="0">
			<div class="info-wrapper">
				<div class="info-stats" id="toggleInfo">
					<div class="info-content">
						<div class="info-album-art">
							<img src="images/bg-placeholder.jpg"/>
						</div>
						<span class="title"></span><br/>
						<span class="artist"></span><br/>
						<span class="info"></span>
					</div>
					<i class="fa fa-caret-down"></i>&nbsp;
				</div>
				<div class="info-buttons">
					<i class="fa fa-heart"></i>&nbsp;
					<i class="fa fa-plus-circle"></i>&nbsp;
					<i class="fa fa-pencil"></i>&nbsp;
					<i class="fa fa-trash"></i>&nbsp;
				</div>
			</div>
		</div>
		<div class="main-area">
			<div class="content-wrapper">
				<div class="header-wrapper">
					<h1>Library</h1>
				</div>
				<div class="main-table-wrapper">
					<table class="main-table">
						<tr>
							<td>#</td>
							<td></td>
							<td><i class="fa fa-music"></i>&nbsp; Title</td>
							<td><i class="fa fa-microphone"></i>&nbsp; Artist</td>
							<td><i class="fa fa-user"></i>&nbsp; Added by</td>
							<td><i class="fa fa-clock-o"></i>&nbsp; Added on</td>
						</tr>
					</table>
				</div>
			</div>
		</div>
		<div class="menu-drawer">
			<div class="menu-wrapper">
				<div class="menu-item menu-item-disabled">
					<span class="menu-item-wrapper">
						<i class="fa fa-play-circle fa-fw"></i>&nbsp; Listen
					</span>
				</div>
				<?php if($_SESSION['login'] == TRUE) { ?>
					<hr/>
					<div class="menu-item menu-item-disabled">
						<span class="menu-item-wrapper">
							<i class="fa fa-user fa-fw"></i>&nbsp; My Music
						</span>
					</div>
					<div class="menu-item menu-item-disabled">
						<span class="menu-item-wrapper">
							<i class="fa fa-heart fa-fw"></i>&nbsp; Favorites
						</span>
					</div>
				<?php } ?>
				<hr/>
				<div class="menu-item" page="library">
					<span class="menu-item-wrapper">
						<i class="fa fa-music fa-fw"></i>&nbsp; Music Library
					</span>
				</div>
				<div class="menu-item" page="queue">
					<span class="menu-item-wrapper">
						<i class="fa fa-list fa-fw"></i>&nbsp; Play Queue
					</span>
				</div>
				<div class="menu-item" page="history">
					<span class="menu-item-wrapper">
						<i class="fa fa-history fa-fw"></i>&nbsp; Play History
					</span>
				</div>
				<div class="menu-item" dialog="export.php">
					<span class="menu-item-wrapper">
						<i class="fa fa-download fa-fw"></i>&nbsp; Export Library
					</span>
				</div>
				<?php if($_SESSION['login'] == TRUE) { ?>
					<hr/>
					<div class="menu-item menu-item-disabled">
						<span class="menu-item-wrapper">
							<i class="fa fa-plus-circle fa-fw"></i>&nbsp; Add a Track
						</span>
					</div>
				<?php } ?>
				<hr/>
				<div class="menu-item menu-item-disabled">
					<span class="menu-item-wrapper">
						<i class="fa fa-info-circle fa-fw"></i>&nbsp; About
					</span>
				</div>
				<div class="menu-item menu-item-disabled">
					<span class="menu-item-wrapper">
						<i class="fa fa-exclamation-triangle fa-fw"></i>&nbsp; DMCA Information
					</span>
				</div>
				<hr/>
				<div class="menu-item" id="openDialog">
					<span class="menu-item-wrapper">
						<i class="fa fa-cog fa-fw"></i>&nbsp; Open a dialog
					</span>
				</div>
				<div class="menu-item" id="toggleInfo">
					<span class="menu-item-wrapper">
						<i class="fa fa-cog fa-fw"></i>&nbsp; Toggle the info panel
					</span>
				</div>
				<div class="menu-item" id="toggleUser">
					<span class="menu-item-wrapper">
						<i class="fa fa-cog fa-fw"></i>&nbsp; Toggle the account panel
					</span>
				</div>
				<div class="menu-item" id="testPlaying">
					<span class="menu-item-wrapper">
						<i class="fa fa-cog fa-fw"></i>&nbsp; Mark a row as playing
					</span>
				</div>
			</div>
			<div class="user-wrapper" visible="0">
				<?php if($_SESSION['login'] == TRUE) { ?>
					<div class="user-header">
						<img src="locdata/images/avatars/<?php echo $_SESSION['username']; ?>.jpg" class="user-av"/> <?php echo $_SESSION['username']; ?>
						<span class="user-caret">
							<i class="fa fa-caret-up"></i>&nbsp;
						</span>
					</div>
					<div class="user-menu-wrapper">
						<div class="user-menu-item" dialog="settings.php">
							<span class="user-menu-item-wrapper">
								<i class="fa fa-cog fa-fw"></i>&nbsp; Settings
							</span>
						</div>
						<div class="user-menu-item" onClick="location.href='/includes/logout.php'">
							<span class="user-menu-item-wrapper">
								<i class="fa fa-sign-out fa-fw"></i>&nbsp; Logout</a>
							</span>
						</div>
					</div>
				<?php } else { ?>
					<div class="user-header override" dialog="login.php">
						Login
					</div>
				<?php } ?>
			</div>
		</div>
		<div class="bg_img"><img src="images/bg-placeholder.jpg"/></div>
		<div class="playing-drawer">
			<div class="playing-stats">
				<div class="elapsed-time">-:--</div>
				<div class="progress-bar-wrapper" style="width: calc(100% - 116px);">
					<div class="progress-bar-filled"></div>
					<div class="progress-bar-unfilled" style="width: calc(100%);"></div>
				</div>
				<div class="total-time">-:--</div>
			</div>
			<div class="playing-wrapper">
				<div class="playing-album-art">
					<img src="images/bg-placeholder.jpg"/>
				</div>
				<div class="playing-info">
					<span class="title"></span><br/>
					<span class="artist"></span><br/>
					<span class="info"></span>
				</div>
			</div>
		</div>
		<div class="dialog-load-spot"></div>
	</div>
</body>

</html>
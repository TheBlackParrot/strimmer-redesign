<html>

<head>
	<link rel="stylesheet" href="css/reset.css"/>
	<link rel="stylesheet" href="css/main.css"/>
	<link rel="stylesheet" href="css/animations.css"/>
	<link rel="stylesheet" href="css/font-awesome.css"/>
	<script src="js/jquery.js"></script>
	<script src="js/ui-interaction.js"></script>
	<script src="js/dialog.js"></script>
	<script src="js/now-playing.js"></script>
</head>

<body>
	<div class="wrapper">
		<div class="bg_img_info"><img src="images/test/flower.jpg"/></div>
		<div class="info-area" visible="0">
			<div class="info-wrapper">
				<div class="info-stats" id="toggleInfo">
					<div class="info-content">
						<div class="info-album-art">
							<img src="images/test/flower.jpg"/>
						</div>
						<span class="title">Sample Song</span><br/>
						<span class="artist">Someone</span><br/>
						<span class="info">Added by TheBlackParrot from SoundCloud</span>
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
				<div class="menu-item">
					<span class="menu-item-wrapper">
						<i class="fa fa-play-circle fa-fw"></i>&nbsp; Listen
					</span>
				</div>
				<hr/>
				<div class="menu-item">
					<span class="menu-item-wrapper">
						<i class="fa fa-user fa-fw"></i>&nbsp; My Music
					</span>
				</div>
				<div class="menu-item">
					<span class="menu-item-wrapper">
						<i class="fa fa-heart fa-fw"></i>&nbsp; Favorites
					</span>
				</div>
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
				<div class="menu-item">
					<span class="menu-item-wrapper">
						<i class="fa fa-download fa-fw"></i>&nbsp; Export Library
					</span>
				</div>
				<hr/>
				<div class="menu-item">
					<span class="menu-item-wrapper">
						<i class="fa fa-plus-circle fa-fw"></i>&nbsp; Add a Track
					</span>
				</div>
				<hr/>
				<div class="menu-item">
					<span class="menu-item-wrapper">
						<i class="fa fa-info-circle fa-fw"></i>&nbsp; About
					</span>
				</div>
				<div class="menu-item">
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
				<div class="user-header">
					<img src="images/test/flower.jpg" class="user-av"/> TheBlackParrot
					<span class="user-caret">
						<i class="fa fa-caret-up"></i>&nbsp;
					</span>
				</div>
				<div class="user-menu-wrapper">
					<div class="user-menu-item">
						<span class="user-menu-item-wrapper">
							<i class="fa fa-cog fa-fw"></i>&nbsp; Settings
						</span>
					</div>
					<div class="user-menu-item">
						<span class="user-menu-item-wrapper">
							<i class="fa fa-sign-out fa-fw"></i>&nbsp; Logout
						</span>
					</div>
				</div>
			</div>
		</div>
		<div class="bg_img"><img src="images/test/flower.jpg"/></div>
		<div class="playing-drawer">
			<div class="playing-stats">
				<div class="elapsed-time">0:34</div>
				<div class="progress-bar-wrapper" style="width: calc(100% - 116px);">
					<div class="progress-bar-filled" style="width: calc(100% - 86%);"></div>
					<div class="progress-bar-unfilled" style="width: calc(100%);"></div>
				</div>
				<div class="total-time">3:51</div>
			</div>
			<div class="playing-wrapper">
				<div class="playing-album-art">
					<img src="images/test/flower.jpg"/>
				</div>
				<div class="playing-info">
					<span class="title">Sample Song</span><br/>
					<span class="artist">Someone</span><br/>
					<span class="info">Added by TheBlackParrot from SoundCloud</span>
				</div>
			</div>
		</div>
		<div class="dialog-load-spot"></div>
	</div>
</body>

</html>
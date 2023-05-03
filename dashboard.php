<!doctype html>
<?php
include_once("db_connect.php");
include("bootstrap.php");
include("userutil.php");
include("adminutil.php");

session_start();
// unset($_SESSION["uname"]);
$op = $_GET["op"];
switch($op) {
	case "login":
		if (validateUser($db, $_POST["uname"], $_POST["pass"])) {
			$_SESSION["uname"] = $_POST["uname"]; 
		}
		else {
			header("refresh:2;url=dashboard.php");
			printf("There was an error signing in.");
		}
		break;
	case "register":
		// unfinished, not fully working
		if (registerUser($db, $_POST["uname"], $_POST["email"], 
			$_POST["pass1"], $_POST["pass2"])) {
		$_SESSION["uname"] = $_POST["uname"];
		}
		else {
			header("refresh:2;url=dashboard.php");
			printf("There was an error creating your account.");
		}
		break;
	case "logout":
		unset($_SESSION["uname"]);
		$op = "";
		break;
}
?>
<HTML>
<HEAD>
	<TITLE>REMSTER 0.0</TITLE>
	<link rel="stylesheet" href="style.css">
	<script src="adminscript.js"></script> 
	<script src="userscript.js"></script>
	<script src="howler.js"></script>
</HEAD>
<BODY>
<!--FULL OUTER SITE CONTAINER-->
<DIV class="container-fluid" style="height: 100vh; position: fixed;"> <!---->
<DIV id="outer-row" class="row">
	<!--SIDEBAR: MENUS, LOGIN-->
	<DIV class="col-md-2 c_menuBackdrop">
		<DIV class="f_headerText" style="text-align: center;"><b>REMster</b></DIV>
		<?php


		?>
		<A href="?op=logout">
		<DIV class="row sideMenu" style="white-space: nowrap">
			My Account
		</DIV></A>
		<!--SPACER-->
		<DIV class="col-md-2 c_menuBackdrop" style='height: 50px'></DIV>
		<A href="?op=home">
		<DIV class="row sideMenu" style="white-space: nowrap">
			Home
		</DIV></A>
		<A href="?op=search">
		<DIV class="row sideMenu" style="white-space: nowrap">
			Search
		</DIV></A>
		<A href="?op=liked">
		<DIV class="row sideMenu" style="white-space: nowrap">
			Liked Songs
		</DIV></A>
		<A href="?op=dj">
		<DIV class="row sideMenu" style="white-space: nowrap">
			DJ Mode
		</DIV></A>
		<?php
			if (isAdmin($db, $_SESSION["uname"])) {
				print("<A href='?op=uploadfm'>"
						."<DIV class='row sideMenu' style='white-space: nowrap'>"
						."Upload Album</DIV></A>");
			}
			if (isAdmin($db, $_SESSION["uname"])) {
				print("<A href='?op=removefm'>"
						."<DIV class='row sideMenu' style='white-space: nowrap'>"
						."Remove Album</DIV></A>");
			}
		?>
		<!--SPACER-->
		<DIV class="col-md-2 c_menuBackdrop" style='height: 50px'></DIV>
		<A href="?op=playlists">
		<DIV class="row sideMenu" style="white-space: nowrap">
			All Playlists
		</DIV></A>
		

		<!--put playlists down here!-->
	</DIV>
	<!--CONENT-->
	<DIV class="col-md-10 c_background"> <!--style=
	'padding-bottom: 100%; margin-bottom: -100%;'-->
		<?php
		if (!isset($_SESSION["uname"])) {
			showLandingPage();
		}
		else {
			$op = $_GET["op"];
			$userID = $_SESSION["uname"];
			switch ($op) {
				case "login":
				case "register":
				case "home":
					viewHomepage($db, $userID);
					break;
				case "search":
					viewSearch($db);
					break;
				case "searchfor":
					executeSearch($db, $_GET["query"], $userID);
					break;
				case "artist":
					viewArtist($db, $_GET["artid"]);
					break;
				case "album":
					viewAlbum($db, $_GET["aid"], $userID);
					break;
				// this refers to the "all playlists" menu item page
				case "playlists":
					viewPlaylistsPage($db, $userID);
					break;
				// this refers to viewing an individual playlist
				case "playlist":
					viewPlaylist($db, $_GET["pid"], $userID);
					break;
				case "liked":
					viewLiked($db, $userID);
					break;
				case "newplaylist":
					viewEditPlaylistPage($db, $userID, -1);
					break;
				case "editplaylist":
					viewEditPlaylistPage($db, $userID, $_GET["pid"]);
					break;
				case "submitedits":
					processEditPlaylist($db, $_POST, $_GET["pid"], $userID);
					break;
				case "addto": 
					addToPlaylist($db, $_POST, $userID);
					break;
				case "uploadfm":
					viewUploadForm($db, $userID);
					break;
				case "upload":
					processAlbumUpload($db, $userID, $_POST);
					break;
				case "removefm":
					viewRemoveForm($db, $userID);
					break;
				case "remove":
					processAlbumRemoval($db, $_POST);
					break;
				case "404":
					show404($_GET["src"]);
					break;
			}
		}
		?>
	</DIV>

<!--CONTROL DECK / PLAYER-->
<DIV class="player container-fluid" style="position:fixed; bottom:0">
<DIV class="row">
	<DIV id="progress-bar">
	<DIV id="progress"></DIV>
	</DIV>
</DIV>
<DIV class="row">
	<DIV class="col-md-3">
		<DIV id="nowPlaying"></DIV>
	</DIV>
	<DIV class="col-md-1">
	<DIV class="buttonBox d-flex align-items-center justify-content-between">
		<button type="button" class="controlButton" onclick="toggleShuffle()">
		<img src="art/assets/shuffle.png" id="shflimg" class="buttonImage" alt="Shuffle songs" style="height: 50%">
		</button>
		<button type="button" class="controlButton" onclick="toggleRepeat()">
		<img src="art/assets/repeat.png" id="rptimg" class="buttonImage" alt="Repeat songs" style="height: 50%">
		</button>
	</DIV>
	</DIV>
	<DIV class="col-md-4 d-flex justify-content-center">
		<DIV class="buttonBox">
		<button type="button" class="playerButton" onclick="chooseNextSong('P')">
		<img src="art/assets/prev.png" class="buttonImage" alt="Previous song" style="height: 50%">
		</button>
		<button type="button" class="playerButton" onclick="playOrPause()">
		<img src="art/assets/play.png" id="playButton" class="buttonImage" alt="Play button" style="height: 50%">
		</button>
		<button type="button" class="playerButton" onclick="chooseNextSong('N')">
		<img src="art/assets/next.png" class="buttonImage" alt="Next song" style="height: 50%">
		</button>
	</DIV>
	</DIV>
	<DIV class="col-md-1">
		<DIV class="buttonBox d-flex align-items-center">
		<img src="art/assets/volume.png" alt="Volume icon" style="height: 20%">
		<INPUT type="range" min="0" max="100" value="100" id="volume-slider">
		</DIV>

	</DIV>
	<DIV class="col-md-3 d-flex align-items-end justify-content-end">
		<FORM name='fmAddTo' method='POST' action='?op=addto'>
		<p class="f_standardText">Add Current Song To:</p>
		<INPUT type="hidden" id="passSid" name="sid" value=""/>
		<SELECT name='ddlAddtoPlaylist' class="addTo f_standardText">
		<?php
			$playlists = getUserPlaylists($db, $userID);
		for ($i = 0; $i < sizeof($playlists); $i++) {
			$currPid = $playlists[$i];
			$pnameQuery = "SELECT pname FROM playlist WHERE pid=$currPid";
			$currPname = $db->query($pnameQuery)->fetch()["pname"];
			printf("<OPTION value='$currPid'>$currPname</OPTION>\n");
		}
		?>
		</SELECT>
		<INPUT type="submit" value="Confirm" id="submitAdd" class="addTo f_standardText" disabled/>
		</FORM>
	</DIV>
</DIV>
</DIV>

</DIV>
</DIV>
</BODY>
</HTML>

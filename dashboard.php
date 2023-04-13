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
				case "home":
					viewHomepage($db, $userID);
					break;
				case "search":
					viewArtist($db, "1");
					break;
				case "artist":
					viewArtist($db, $_GET["artid"]);
					break;
				case "album":
					viewAlbum($db, $_GET["aid"]);
					break;
				case "playlist":
					viewPlaylist($db, $_GET["pid"], $userID);
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
</DIV>
<!--CONTROL DECK / PLAYER-->
<DIV class="row player" style="position:fixed; bottom:0">
	<p>testtesttesttesttesttesttesttesttesttesttesttest
	testtesttesttesttesttesttesttesttesttesttesttesttest
testtesttesttesttesttesttesttesttesttesttesttest<br>testtesttesttesttesttesttesttesttesttesttesttest
	testtesttesttesttesttesttesttesttesttesttesttesttest
testtesttesttesttesttesttesttesttesttesttesttest</p>
</DIV>
</DIV>

</BODY>
</HTML>

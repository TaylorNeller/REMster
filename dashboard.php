<!doctype html>
<?php
include_once("db_connect.php");
include("bootstrap.php");
include("userutil.php");

session_start();
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
</HEAD>
<BODY>
<!--FULL OUTER SITE CONTAINER-->
<DIV class="container-fluid" style="height: 100vh; position: fixed;"> <!---->
<DIV class="row">
	<!--SIDEBAR: MENUS, LOGIN-->
	<DIV class="col-md-2 c_menuBackdrop"> <!--style=
	'padding-bottom: 100%; margin-bottom: -100%;'-->
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
		print($_SESSION["uname"]);
		if (!isset($_SESSION["uname"])) {
			showLandingPage();
		}
		else {
			viewCollection($db, "1", "10");
		}
		?>
	</DIV>
</DIV>
<!--CONTROL DECK / PLAYER-->
<DIV class="row player">
	<p>testtesttesttesttesttesttesttesttesttesttesttest
	testtesttesttesttesttesttesttesttesttesttesttesttest
testtesttesttesttesttesttesttesttesttesttesttest<br>testtesttesttesttesttesttesttesttesttesttesttest
	testtesttesttesttesttesttesttesttesttesttesttesttest
testtesttesttesttesttesttesttesttesttesttesttest</p>
</DIV>
</DIV>

</BODY>
</HTML>

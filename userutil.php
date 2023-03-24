<?php
include_once("db_connect.php");

// INTERNAL ONLY function which takes array of song info
// prints single song in the context of a list of songs
function showSongsList($songsData) {
	// TODO: make clicking song name play song
	$trackNum = 1;
	foreach($songsData as $currSong) {
		print("<TR>\n");
		$currName = $currSong["name"];
		$currArtist = $currSong["artist"];
		$currDuration = $currSong["duration"];
		print("<TD>$trackNum</TD>\n");
		print("<TD>$currName<br>$currArtist</TD>\n");
		print("<TD align='right'></TD>\n");
		print("<TD>" . intdiv($currDuration, 60) . ":" . 
			$currDuration % 60 . "</TD>\n");
		print("</TR>\n");
		$trackNum++;
	}
}
function viewCollection($db, $collectionID, $userID) {
	print("viewColl");
	$albumTest = 	"SELECT * " . 
					"FROM album " .
					"WHERE aid=$collectionID";
	$albumTestResult = $db->query($albumTest);
	if ($albumTestResult != FALSE) {
		viewAlbum($db, $collectionID, $userID);
	}
	else {
		viewPlaylist($db, $collectionID, $userID);
	}
}
// returns the artists of a song or album given the database, the song or album ID, and the type of media.
// "S" if song, "A" if album.
function getArtists($db, $mediaID, $mediaType) {
	switch($mediaType) {
		case "A":
			$artistQuery = "SELECT artid, artname " . 
					"FROM album_artist NATURAL JOIN artist
					WHERE aid=$mediaID";
			break;
		case "S:":
			$artistQuery = "SELECT artid, artname " . 
					"FROM song NATURAL JOIN artist
					WHERE sid=$mediaID";
			break;
	}
	$artistResult = $db->query($artistQuery);
	$artids = [];
	$artnames = [];
	$i = 0;
	while ($artistInfo = $artistResult->fetch()) {
		$artids[$i] = $artistInfo["artid"];
		$artnames[$i++] = $artistInfo["artname"];
	}
	$artists = [$artids, $artnames];
	return $artists;
}


function viewAlbum($db, $albumID, $userID) {
	// query to fetch collection from given id
	$albumQuery = 	"SELECT * " . 
						"FROM album " .
						"WHERE pid=$albumID";
	$albumResult = $db->query($albumQuery);


	$albumArtist = getArtists($db, $albumID, "A");
	print_r($albumArtist);

	// // query to fetch IDs of songs in current collection
	// $songInQuery = 	"SELECT * " .
	// 				"FROM song_album " .
	// 				"WHERE aid=$albumID";

	// $songInResult = $db->query($songInQuery);

	// $artistQuery = 	"SELECT artid, artname " . 
	// 				"FROM album_artist NATURAL JOIN artist
	// 				WHERE aid=$albumID";

	// $artistResult = $db->query($artistQuery);

	// if ($albumResult == FALSE || $songInResult == FALSE) {
	// 	print("INTERNAL ERROR: could not find collection with id $albumID");
	// 	print("QUERY: $albumQuery");
	// 	// temporary. Make redirect to home
	// }

	// else {
	// 	// gather collection information
	// 	$album = $albumResult->fetch();
	// 	$albumName = $album["aname"];
	// 	$uploader = $album["uploader"];
	// 	$releaseDate = $album["release_date"];

	// 	$artids = [];
	// 	$artnames = [];
	// 	$i = 0;
	// 	while ($artistInfo = $artistResult->fetch()) {
	// 		$artids[$i] = $artistInfo["artid"];
	// 		$artnames[$i++] = $artistInfo["artname"];
	// 	}

	// 	// gather songIDs 
	// 	$songIDs = array();
	// 	while ($song = $songInResult->fetch()) {
	// 		array_push($songIDs, $song["sid"]);
	// 	}
	// }

	// // query to fetch the individual songs based on IDs gathered earlier
	// $songIDsString = "(" . implode(", ", $songIDs) . ")";
	// $songsQuery = 	"SELECT * " .
	// 				"FROM song " .
	// 				"WHERE sid IN $songIDsString";

	// $songsResult = $db->query($songsQuery);

	// if ($songsResult == FALSE) {
	// 	print("INTERNAL ERROR: could not fetch songs from $albumID");
	// 	print("QUERY: $songsQuery");
	// 	// temporary. Make redirect to home
	// }

	// // create array which maps songID to necessary song data
	// else {
	// 	$songs = array();
	// 	while ($currSongData = $songsResult->fetch()) {
	// 		$currSong = array();
	// 		$currSong["name"] = $currSongData["sname"];
	// 		$currSong["artist"] = $currSongData["artist"];
	// 		$currSong["releaseDate"] = $currSongData["release_date"];
	// 		$currSong["duration"] = $currSongData["duration"];

	// 		$songs[$currSongData["sid"]] = $currSong; 
	// 	}
	// }
	// print("<DIV class='container' style='height: 100vh; " . 
	// 	"overflow: auto; margin-left: 20px; margin-top: 20px'>\n");
	// 	print("<DIV class='row'>");
	// 		print("<DIV class='col-md-3'>\n");
	// 			$srcLink = "art/$collectionID.png";
	// 			print("<img src='$srcLink' alt='$collName cover' " . 
	// 				"height='100%', width='100%'>");
	// 		print("</DIV>");

	// 		print("<DIV class='col-md-9 my-auto'>\n");
	// 			// album vs. playlist header
	// 			print("<DIV class='row textRow'>\n");
	// 			if ($isAlbum == "T") {
	// 				print("ALBUM");
	// 			}
	// 			else {
	// 				print("PLAYLIST");
	// 			}
	// 			print("</DIV>\n");

	// 			// album name
	// 			print("<DIV class='row headerRow'>\n");
	// 				print("<b>" . $collName . "</b>");
	// 			print("</DIV>\n");

	// 			// other album info
	// 			print("<DIV class='row textRow'>\n");
	// 				// eventually: make this a link to their artist page
	// 				print($artist . "  |  ");
	// 				print($releaseDate . "  |  ");

	// 				// derive song count and album length
	// 				$numSongs = count($songs);
	// 				$runtime = 0;
	// 				foreach ($songs as $currSong) {
	// 					$runtime += $currSong["duration"];
	// 				}
	// 				print("$numSongs songs, " . intdiv($runtime, 60) . " min " . 
	// 					($runtime % 60) . " sec");
	// 			print("</DIV>\n");
	// 		print("</DIV>\n");
	// 	print("</DIV>\n");

	// 	// thought: use javascript to handle liked songs
	// 	print("<DIV class='row'>\n");
	// 	$tableHeader = "<TABLE class='f_standardText' " . 
	// 		"width='100%' cellpadding='5' style='margin-top: 10px'>" .
	// 		"<TR>\n" .
	// 		"<TH>#</TH>\n" .
	// 		"<TH>Title</TH>\n" .
	// 		"<TH>Liked</TH>\n" .
	// 		"<TH>Length</TH>\n" .
	// 		"</TR>\n";
	// 	print($tableHeader);

	// 	// shows songs in collection
	// 	showSongsList($songs);
	// 	print("</TABLE>\n</DIV>\n");

	// 	print("<DIV class='row c_text' style='height: 40px; margin-top: 20px'><p>");
	// 		print("uploaded by $uploader");
	// 	print("</p></DIV>");

	// print("</DIV>\n");
}

function showLandingPage() {
	print("<DIV class='container' style='height: 100vh; " . 
		"overflow: auto; margin-left: 20px; margin-top: 20px'>\n");
		print("<DIV class='f_headerText' style='text-align: center; " . 
			"width: 100%; margin-top: 50px'>\n");
		print("<b>Welcome to REMster.<br>All music, no hassle.</b>\n");
		print("</DIV>\n");
		print("<DIV class='row f_standardText' style='text-align: center; " . 
			"width: 100%; height: 100vh; margin-top: 50px'>\n");

			print("<DIV class='col-md-6'>\n");
				print("<p>Log in with your account...</p>");
				print("<FORM name = 'fmLogin' method='POST' action='?op=login'>\n");
				print("<INPUT type='text' name='uname' size='8' placeholder='username' />\n");
				print("<br>");
				print("<INPUT type='text' name='pass' size='8' placeholder='password' />\n");
				print("<br>");
			 	print("<INPUT type='submit' value='login' />\n");
				print("</FORM>\n");
			print("</DIV>\n");

			print("<DIV class='col-md-6'>\n");
				print("<p>Or create a new account.</p>");
				print("<FORM name = 'fmRegister' method='POST' action='?op=register'>\n");
				print("<INPUT type='text' name='uname' size='8' placeholder='username' />\n");
				print("<br>");
				print("<INPUT type='text' name='email' size='8' placeholder='email' />\n");
				print("<br>");
				print("<INPUT type='text' name='pass1' size='8' placeholder='password' />\n");
				print("<br>");
				print("<INPUT type='text' name='pass2' size='8' placeholder='re-enter' />\n");
				print("<br>");
			 	print("<INPUT type='submit' value='register' />\n");
				print("</FORM>\n");
			print("</DIV>\n");
		print("</DIV>\n");
	print("</DIV>\n");
}

function validateUser($db, $uname, $pass) {
	// returns boolean
	// add encryption!!!
	print("validation");
	$loginQuery = 	"SELECT * " . 
					"FROM users " .
					"WHERE uname='$uname' AND pass='$pass'";
	print($loginQuery);
	$loginResult = $db->query($loginQuery);
	print($loginResult);
	return $loginResult;
}

// unfinished, not fully working
function registerUser($db, $uname, $email, $pass1, $pass2) {
	if ($pass1 != $pass2) {
		header("refresh:2;url=dashboard.php");
		printf("Passwords do not match.");
	}

	else {
		// this validation is not working
		$validationQuery = 	"SELECT * " .
							"FROM users " .
							"WHERE email=$email";
		$validationResult = $db->query($validationQuery);
		if ($validationResult != FALSE) {
			header("refresh:2;url=dashboard.php");
			printf("A user with this email already exists.");
			exit();
		}

		else {
			$registerQuery = 	"INSERT INTO users " . 
								"VALUES ($uname, $pass1, $email)";
			$registerResult = $db->query($registerQuery);
			return TRUE;
		}
	}
}




?>
<?php
// INTERNAL ONLY function which takes array of song info
// prints single song in the context of a list of songs
function showSongsList($songsData) {
	// TODO: make clicking song name play song
	$trackNum = 1;
	foreach($songsData as $currSong) {
		print("<TR>\n");
		$currName = $currSong["name"];
		$currArtist = $currSong["artists"];
		$currDuration = $currSong["duration"];
		print("<TD>$trackNum</TD>\n");
		print("<TD>$currName<br>" . implode(", ", $currArtist). "</TD>\n");
		print("<TD align='right'></TD>\n");
		print("<TD>" . gmdate("i:s", $currDuration) . "</TD>\n");
		print("</TR>\n");
		$trackNum++;
	}
}
function viewCollection($db, $collectionID, $userID) {
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

function viewPlaylist($db, $playlistID, $userID) {
	//todo
}

function getAlbum($db, $albumID) {
	$albumQuery = 	"SELECT * " . 
						"FROM album " .
						"WHERE aid=$albumID";
	$albumResult = $db->query($albumQuery);
	if ($albumResult != FALSE) {
		$Target = $albumResult->fetch();
		$albumData = [];
		$albumData["name"] = $Target["aname"];
		$albumData["release_date"] = $Target["release_date"];
		$albumData["uploader"] = $Target["uploader"];
	}
	return $albumData;
}

// returns the artists of a song or album given the database, the song or album ID, and the type of media.
// "S" if song, "A" if album.
function getArtistsFromMedia($db, $mediaID, $mediaType) {
	switch($mediaType) {
		case "A":
			$artistQuery = "SELECT artid, artname " . 
					"FROM album_artist NATURAL JOIN artist " .
					"WHERE aid=$mediaID";
			break;
		case "S":
			$artistQuery = "SELECT artid, artname " . 
					"FROM song_artist NATURAL JOIN artist " .
					"WHERE sid=$mediaID";
			break;
	}
	$artistResult = $db->query($artistQuery);
	$artists = [];
	//todo add error handling
	while ($artistInfo = $artistResult->fetch()) {
		$artists[$artistInfo["artid"]] = $artistInfo["artname"];
	}
	return $artists;
}

// returns the songs of a playlist or album given the database, the playlist or album ID, and the type of media.
// "P" if playlist, "A" if album.
function getSongs($db, $mediaID, $mediaType) {
	switch($mediaType) {
		case "A":
			$songQuery = 	"SELECT sid, sname, duration, release_date " .
							"FROM song_album NATURAL JOIN song " .
							"WHERE aid=$mediaID";
			break;
		case "P":
			$songQuery = 	"SELECT sid, sname, duration, release_date " .
							"FROM song_playlist NATURAL JOIN song " .
							"WHERE aid=$mediaID";
			break;
	}
	$songResult = $db->query($songQuery);
	$songs = [];
	//todo add error handling
	while($currSongRes = $songResult->fetch()) {
		$currSong = [];
		$currSong["name"] = $currSongRes["sname"];
		$currSong["duration"] = $currSongRes["duration"];
		$currSong["release_date"] = $currSongRes["release_date"];
		$currSID = $currSongRes["sid"];
		$currSong["artists"] = getArtistsFromMedia($db, $currSID, "S");
		$currSong["sid"] = $currSID;
		$songs[$currSID] = $currSong;
	}
	return $songs;
}

//handles front-end display of an album.
function viewAlbum($db, $albumID, $userID) {
	// retrieve album data (metadata, artists, songs)
	$albumData = getAlbum($db, $albumID);
	$albumArtists = getArtistsFromMedia($db, $albumID, "A");
	$albumSongs = getSongs($db, $albumID, "A");
	//todo: implement error handling in those methods

	print("<DIV class='container' style='height: 100vh; " . 
		"overflow: auto; margin-left: 20px; margin-top: 20px'>\n");

		print("<DIV class='row'>");

			print("<DIV class='col-md-3'>\n");
				$srcLink = "art/$albumID.png";
				$alttext = $albumData["name"];
				print("<img src=$srcLink alt='$alttext cover' " . 
					"height='100%', width='100%'>");
			print("</DIV>");

			print("<DIV class='col-md-9 my-auto'>\n");

				print("<DIV class='row textRow'>\n");
					print("ALBUM");
				print("</DIV>\n");

				print("<DIV class='row headerRow'>\n");
					print("<b>" . $albumData["name"] . "</b>");
				print("</DIV>\n");

	 			// other album info
				print("<DIV class='row textRow'>\n");
					// eventually: make this a link to their artist page
					print(implode(", ", $albumArtists) . "  |  ");
					print($albumData["release_date"] . "  |  ");

					// derive song count and album length
					$numSongs = count($albumSongs);
					$runtime = 0;
					foreach ($albumSongs as $currSong) {
						$runtime += $currSong["duration"];
					}
					print("$numSongs songs, " . intdiv($runtime, 60) . " min " . 
						($runtime % 60) . " sec");
				print("</DIV>\n");

			print("</DIV>\n");
		print("</DIV>\n");

	// 	// thought: use javascript to handle liked songs
		print("<DIV class='row'>\n");
		$tableHeader = "<TABLE class='f_standardText' " . 
			"width='100%' cellpadding='5' style='margin-top: 10px'>" .
			"<TR>\n" .
			"<TH>#</TH>\n" .
			"<TH>Title</TH>\n" .
			"<TH>Liked</TH>\n" .
			"<TH>Length</TH>\n" .
			"</TR>\n";
		print($tableHeader);

	 	// shows songs in collection
		showSongsList($albumSongs);
		print("</TABLE>\n</DIV>\n");

		print("<DIV class='row c_text' style='height: 100px; margin-top: 20px'><p>");
			print("uploaded by " . $albumData["uploader"]);
		print("</p></DIV>");

	print("</DIV>\n");
}

// creates and returns a collection "tile": a clickable object linking to the album/playlist page
function showCollectionImage($db, $collectionID) {
	$albumTest = 	"SELECT * " . 
					"FROM album " .
					"WHERE aid=$collectionID";
	$albumTestResult = $db->query($albumTest);
	if ($albumTestResult != FALSE) {
		showAlbumImage($db, $collectionID);
	}
	else {
		showPlaylistImage($db, $collectionID);
	}
}

function showAlbumImage($db, $albumID) {
	$albumData = getAlbum($db, $albumID);




	$srcLink = "art/$albumID.png";
	$alttext = $albumData["name"];
	print("<a href='?op=album&aid=$albumID'>");
		print("<DIV class='tile'>\n");
			print("<img src=$srcLink alt='$alttext cover' " . 
				"height='100%', width='100%'>");
		print("</DIV>\n");
	print("</a>");
}

function showPlaylistImage($db, $playlistID) {

}

// returns the artist name and data for all albums created by that artist
function getArtist($db, $artistID) {
	print($artistID);
	$nameQuery = "SELECT artname FROM artist WHERE artid=$artistID";
	$nameResult = $db->query($nameQuery);
	if ($nameResult == FALSE) {
		//add error handling. likely break out to other method
		print("uh-oh! name result bad");
	}
	print($artistName);
	$artistName = $nameResult->fetch();
	$workedOnQuery = "SELECT aid FROM album_artist WHERE artid=$artistID";
	$workedOnResult = $db->query($workedOnQuery);
	if ($workedOnResult == FALSE) {
		//add error handling. likely break out to other method
		print("uh-oh! workedon result bad");
	}
	else {
		$i = 0;
		$albums = array();
		while ($currAlbumID = $workedOnResult->fetch()) {
			$currAlbumData = getAlbum($currAlbumID);
			print($currAlbumData);
			$albums[$i++] = $currAlbumData;
		}
	}
	$result = [$artistName, $albums];
	return $result;
}

function viewArtist($db, $artistID) {
	$artistInfo = getArtist($db, $artistID);
	print_r($artistInfo);
}

function showLandingPage() {
	// removed overflow to try to prevent scrollability
	print("<DIV class='container' style='height: 100vh; " . 
		"margin-left: 20px; margin-top: 20px'>\n");
		print("<DIV class='f_headerText' style='text-align: center; " . 
			"width: 100%; margin-top: 50px'>\n");
		print("<b>Welcome to REMster.<br>All music, no hassle.</b>\n");
		print("</DIV>\n");
		print("<DIV class='row f_standardText' style='text-align: center; " . 
			"width: 100%; height: 100vh; margin-top: 50px'>\n");

			print("<DIV class='col-md-6'>\n");
				print("<p>Log in with your account...</p>");
				print("<FORM name = 'fmLogin' method='POST' action='?op=login'>\n");
				print("<INPUT class='loginTextBox' type='text' name='uname' size='12' placeholder='username' />\n");
				print("<br>");
				print("<INPUT class='loginTextBox' type='text' name='pass' size='12' placeholder='password' />\n");
				print("<br>");
			 	print("<INPUT type='submit' value='login' style='margin-top: 20px'/>\n");
				print("</FORM>\n");
			print("</DIV>\n");

			print("<DIV class='col-md-6'>\n");
				print("<p>Or create a new account.</p>");
				print("<FORM name = 'fmRegister' method='POST' action='?op=register'>\n");
				print("<INPUT class='loginTextBox' type='text' name='uname' size='12' placeholder='username' />\n");
				print("<br>");
				print("<INPUT class='loginTextBox' type='text' name='email' size='12' placeholder='email' />\n");
				print("<br>");
				print("<INPUT class='loginTextBox' type='text' name='pass1' size='12' placeholder='password' />\n");
				print("<br>");
				print("<INPUT class='loginTextBox' type='text' name='pass2' size='12' placeholder='re-enter' />\n");
				print("<br>");
			 	print("<INPUT type='submit' value='register' style='margin-top: 20px'/>\n");
				print("</FORM>\n");
			print("</DIV>\n");
		print("</DIV>\n");
	print("</DIV>\n");
}

function validateUser($db, $uname, $pass) {
	// returns boolean
	// add encryption!!!
	$loginQuery = 	"SELECT * " . 
					"FROM users ";
					"WHERE uname='$uname' AND pass='$pass'";
	$result = $db->query($loginQuery);
	return $result;
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
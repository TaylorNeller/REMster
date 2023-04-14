<?php
if ($_SERVER["REQUEST_METHOD"] == "POST" && $_POST["like-id"] != FALSE) {
	// Code to process form data and run PHP script
	print('<p>lllllllll</p>');
  
	// Redirect to prevent resubmission on page refresh
	header("Location: ".$_SERVER['PHP_SELF']);
	exit();
  }
// INTERNAL ONLY function which takes array of song info
// prints single song in the context of a list of songs
function showSongsList($songsData) {
	// TODO: make clicking song name play song
	$trackNum = 1;
	foreach($songsData as $currSong) {
		print("<TR>\n");
		$currSid = $currSong["sid"];
		$currName = $currSong["name"];
		$currArtist = $currSong["artists"];
		$currDuration = $currSong["duration"];
		print("<TD>$trackNum</TD>\n");


		$artistString = "";
		foreach(array_keys($currArtist) as $currArtistID) {
			$artistString = $artistString . "<a href=?op=artist&artid=$currArtistID> ".
				$currArtist[$currArtistID] . "</a>,&nbsp;";
		}

		print("<TD>$currName<br>" . rtrim($artistString, ",&nbsp;"). "</TD>\n");
		print("<TD align='right'></TD>\n");
		print("<TD>" . gmdate("i:s", $currDuration) . "</TD>\n");
		print("<TD><form method='POST'>"
				. "<input type='hidden' name='like-id' value='$currSid'/>"
				. "<button type='submit' id='like-$currSid' class='like-btn' onclick='toggleLike($currSid)'>"
				. "<span class='heart-icon'></span></button></form></TD>\n");
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
		viewAlbum($db, $collectionID);
	}
	else {
		viewPlaylist($db, $collectionID, $userID);
	}
}

function getPlaylistData($db, $playlistID) {
	$playlistQuery = "SELECT * " . 
						"FROM playlist " . 
						"WHERE pid=$playlistID";
	$playlistResult = $db->query($playlistQuery);
	if ($playlistResult != FALSE) {
		$target = $playlistResult->fetch();
		$playlistData = [];
		$playlistData["name"] = $target["pname"];
		$playlistData["owner"] = $target["owner"];
		$playlistData["public"] = $target["is_public"];
	}
	else {
		// playlist doesn't exist, handle accordingly
	}
	return $playlistData;
}

function viewPlaylist($db, $playlistID, $userID) {
	// retrieve playlist metadata
	$playlistData = getPlaylistData($db, $playlistID);
	if ($playlistData["is_public"] == FALSE && $playlistData["owner"] != $userID) {
		// logged in user does not have access to this playlist
		// send to error function
	}
	// get songs with handy dandy method
	$playlistSongs = getSongs($db, $playlistID, "P");


	print("<DIV class='container contentContainer'>\n");

		print("<DIV class='row'>");

			print("<DIV class='col-md-3'>\n");

				$playlistArt = rand(1, 4);

				$srcLink = "art/playlist/$playlistArt.png";
				$alttext = $playlistData["name"];
				print("<img src=$srcLink alt='$alttext cover' " . 
					"height='100%', width='100%'>");
			print("</DIV>");

			print("<DIV class='col-md-9 my-auto'>\n");

				print("<DIV class='row textRow'>\n");
					print("PLAYLIST");
				print("</DIV>\n");

				print("<DIV class='row headerRow'>\n");
					print("<b>" . $playlistData["name"] . "</b>");
				print("</DIV>\n");

	 			// other album info
				print("<DIV class='row textRow'>\n");
					print("Created by " . $playlistData["owner"] . "&nbsp;&bull;&nbsp;");

					// if we add social features, the artist link can be replaced with link to user's page
					
					// foreach(array_keys($albumArtists) as $currArtistID) {
					// 	$artistString = $artistString . "<a href=?op=artist&artid=$currArtistID> ".
					// 		$albumArtists[$currArtistID] . "</a>,&nbsp;";
					// }
					// print(rtrim($artistString, ",&nbsp;") . "&nbsp;&bull; ");

					// derive song count and album length
					$numSongs = count($playlistSongs);
					$runtime = 0;
					foreach ($playlistSongs as $currSong) {
						$runtime += $currSong["duration"];
					}
					print("$numSongs songs, " . intdiv($runtime, 60) . " min " . 
						($runtime % 60) . " sec");
				print("</DIV>\n");

			print("</DIV>\n");
		print("</DIV>\n");

	 	// thought: use javascript to handle liked songs
		print("<DIV class='row'>\n");
		$tableHeader = "<TABLE class='f_standardText' " . 
			"width='100%' cellpadding='5' style='margin-top: 10px'>" .
			"<TR>\n" .
			"<TH>#</TH>\n" .
			"<TH>Title</TH>\n" .
			"<TH></TH>\n" .
			"<TH>Length</TH>\n" .
			"<TH></TH>\n" .
			"</TR>\n";
		print($tableHeader);

	 	// shows songs in collection
		showSongsList($playlistSongs);
		print("</TABLE>\n</DIV>\n");

		// spacer
		print("<DIV style='height: 200px; margin-top: 20px'></DIV>");

	print("</DIV>\n");


}

function getAlbumData($db, $albumID) {
	$albumQuery = 	"SELECT * " . 
						"FROM album " .
						"WHERE aid=$albumID";
	$albumResult = $db->query($albumQuery);
	if ($albumResult != FALSE) {
		$target = $albumResult->fetch();
		$albumData = [];
		$albumData["name"] = $target["aname"];
		$albumData["release_date"] = $target["release_date"];
		$albumData["uploader"] = $target["uploader"];
	}
	else {
		// album doesn't exist, handle accordingly
		// not working
		header("refresh:2;url=dashboard.php?op=404&src=album");
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
	while ($artistData = $artistResult->fetch()) {
		$artists[$artistData["artid"]] = $artistData["artname"];
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
							"WHERE pid=$mediaID";
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
function viewAlbum($db, $albumID) {
	// retrieve album data (metadata, artists, songs)
	$albumData = getAlbumData($db, $albumID);
	$albumArtists = getArtistsFromMedia($db, $albumID, "A");
	$albumSongs = getSongs($db, $albumID, "A");
	//todo: implement error handling in those methods

	print("<DIV class='container contentContainer'>\n");

		print("<DIV class='row'>");

			print("<DIV class='col-md-3'>\n");
				$srcLink = "art/cover/$albumID.png";
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
					$artistString = "";
					foreach(array_keys($albumArtists) as $currArtistID) {
						$artistString = $artistString . "<a href=?op=artist&artid=$currArtistID> ".
							$albumArtists[$currArtistID] . "</a>,&nbsp;";
					}
					print(rtrim($artistString, ",&nbsp;") . "&nbsp;&bull; ");
					print($albumData["release_date"] . "  &bull;  ");

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
			"<TH></TH>\n" .
			"<TH>Length</TH>\n" .
			"<TH></TH>\n" .
			"</TR>\n";
		print($tableHeader);

	 	// shows songs in collection
		showSongsList($albumSongs);
		print("</TABLE>\n</DIV>\n");

		print("<DIV class='row f_standardText' style='height: 100px; margin-top: 20px'><p>");
			print("uploaded by " . $albumData["uploader"]);
		print("</p></DIV>");
		// spacer
		print("<DIV style='height: 200px; margin-top: 20px'></DIV>");

	print("</DIV>\n");
}

// show "All Playlists" page
function viewPlaylistsPage($db, $userID) {
	print("<DIV class='container contentContainer'>\n");
		print("<DIV class='row headerRow'>");
			print("<b>$userID's playlists</b>\n");
		print("</DIV>\n");
	showCreateTile();
	print("</DIV>\n");
}

// draws the "create new playlist" tile seen on the Playlists homepage
function showCreateTile() {
	$srcLink = "art/playlist/c.png";
	print("<a href='?op=newplaylist'>");
		print("<DIV class='tile'>\n");
			print("<img src=$srcLink alt='create playlist icon' " . 
				"style='height:100%; width:100%; object-fit: cover'>");
			$tileInfo = "<b>Create New</b> ";
			print("<p class='textRow' style='text-align: center'>\n$tileInfo</p>\n");
		print("</DIV>\n");
	print("</a>");
}

function showAlbumTile($db, $albumID, $showArtists) {
	$albumData = getAlbumData($db, $albumID);
	$artistsData = getArtistsFromMedia($db, $albumID, "A");
	$srcLink = "art/cover/$albumID.png";
	$albumName = $albumData["name"];
	print("<a href='?op=album&aid=$albumID'>");
		print("<DIV class='tile'>\n");
			print("<img src=$srcLink alt='$albumName cover' " . 
				"style='height:100%; width:100%; object-fit: cover'>");
			$tileInfo = "<b>$albumName</b> &bull; ". substr($albumData["release_date"], 0, 4) . " <br>";
			if ($showArtists) {
				if (count($artistsData) > 2) {
					$tileInfo = $tileInfo . array_values($artistsData)[0] . "<br>\n";
					$tileInfo = $tileInfo . "and others";
				}
				else {
					foreach(array_values($artistsData) as $currArtist) {
						$tileInfo = $tileInfo . $currArtist . "<br>\n";
					}
				}
				
			}
			print("<p class='textRow' style='text-align: center'>\n$tileInfo</p>\n");
		print("</DIV>\n");
	print("</a>");
}

function showPlaylistTile($db, $playlistID) {
	$playlistData = getPlaylistData($db, $playlistID);
	$playlistArt = rand(1, 4);
	$srcLink = "art/playlist/$playlistID.png";
	$playlistName = $playlistData["name"];
	print("<a href='?op=playlist&pid=$playlistID'>");
		print("<DIV class='tile'>\n");
			print("<img src=$srcLink alt='$playlistName cover' " . 
				"style='height:100%; width:100%; object-fit: cover'>");
			$tileInfo = "<b>$playlistName</b> " . "<br>" . $playlistData["owner"];
			print("<p class='textRow' style='text-align: center'>\n$tileInfo</p>\n");
		print("</DIV>\n");
	print("</a>");
}

// returns the artist name and albumIDs created by that artist
function getArtist($db, $artistID) {
	$nameQuery = "SELECT artname FROM artist WHERE artid=$artistID";
	$nameResult = $db->query($nameQuery);
	if ($nameResult == FALSE) {
		//add error handling. likely break out to other method
		print("uh-oh! name result bad");
	}

	$artistName = $nameResult->fetch()["artname"];
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
			$albums[$i++] = $currAlbumID["aid"];
		}
	}
	$result = array();
	$result["name"] = $artistName;
	$result["albums"] = $albums;
	return $result;
}

function viewArtist($db, $artistID) {
	$artistData = getArtist($db, $artistID);
	$workedOnAlbums = $artistData["albums"];
	

	print("<DIV class='container contentContainer'>\n");

	print("<DIV class='row'>");

		// ART SPOT: could reuse this if adding artist PFPs
		print("<DIV class='col-md-3'>\n");
			$srcLink = "art/profile/$artistID.png";
			$alttext = $artistData["name"];
			print("<img src=$srcLink alt='$alttext cover' " . 
				"height='100%', width='100%'>");
		print("</DIV>");

		print("<DIV class='col-md-9 my-auto'>\n");

			print("<DIV class='row textRow'>\n");
				print("ARTIST");
			print("</DIV>\n");

			// artist name
			print("<DIV class='row headerRow'>\n");
				print("<b>" . $artistData["name"] . "</b>");
			print("</DIV>\n");

			$numAlbums = count($workedOnAlbums);
			if ($numAlbums > 1) {
				$numAlbumsString = $numAlbums . " albums";
			}
			else {
				$numAlbumsString = $numAlbums . " album";
			}
			print("<DIV class='row textRow'>\n");
				print("$numAlbumsString");
			print("</DIV>\n");

		print("</DIV>\n");

	print("</DIV>\n");

	print("<DIV class='row headerRow' style='font-size: 30px; margin-top: 20px'>\n");
		print("<b>Albums</b>");
	print("</DIV>\n");

	print("<DIV class='scrollable-container'>\n");
		print("<DIV class='scrollable-content' style='margin-top: 10px'>\n");
		foreach($workedOnAlbums as $currAlbumID) {
			showAlbumTile($db, $currAlbumID, FALSE);
		}
		print("</DIV>\n");
	print("</DIV>\n");

	// then, add a horizontally-scrolling list of some playlists (max 10) this artist appears in.
	// alternatively, a message: "this artist has been added to no playlists. Why not be the first?"

	print("<DIV class='row headerRow' style='font-size: 30px; margin-top: 20px'>\n");
		print("<b>Appears In</b>");
	print("</DIV>\n");

	$appearsInPlaylists = getAppearances($db, $artistID);

	print("<DIV class='scrollable-container'>\n");
		print("<DIV class='scrollable-content' style='margin-top: 10px'>\n");
		foreach($appearsInPlaylists as $currPlaylistID) {
			showPlaylistTile($db, $currPlaylistID);
		}
		print("</DIV>\n");
	print("</DIV>\n");


		// spacer
		print("<DIV style='height: 200px; margin-top: 20px'></DIV>");
	print("</DIV>\n");
	}

// returns an array of the playlists the given artist appears in
function getAppearances($db, $artistID) {
	$appearsInQuery = "SELECT DISTINCT pid FROM song_playlist NATURAL JOIN song_artist " . 
		"WHERE artid = $artistID";
	
	$appearsInResult = $db->query($appearsInQuery);
	if ($appearsInResult != FALSE) {
		$appearsInPIDs = [];
		foreach($appearsInResult->fetch() as $currPID) {
			array_push($appearsInPIDs, $currPID);
		}
	}
	else {
		print("ERROR WITH APPEARANCES QUERY" . $appearsInQuery);
	}
	return $appearsInPIDs;
}

function showLandingPage() {
	// removed overflow to try to prevent scrollability
	print("<DIV class='container contentContainer'>\n");
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

// view the main homepage of the site. 
// for some reason this gets rid of the control bar at the bottom.
function viewHomepage($db, $userID) {
	print("<DIV class='container contentContainer'>\n");
		print("<DIV class='headerRow'>\n");
		print("<p>Welcome back, <b>$userID.</b></p>\n");
		print("</DIV>\n");

		// fetch albums for "continue listening"
		// TODO make this actually pull from recent listening

		$albumQuery = "SELECT aid FROM album";
		$albumResult = $db->query($albumQuery);
		$maxIndex = 10 > count($albumResult) ? count($albumResult) : 10;

		print("<DIV class='row f_headerText' style='margin-top:20px'>\n");
		print("<p>Continue listening</p>\n");
		print("</DIV>\n");
			print("<DIV class='scrollable-container'>\n");
			print("<DIV class='scrollable-content' style='margin-top: 20px'>\n");
			for($i = 0; $i < $maxIndex; $i++) {
				$currAlbumID = $albumResult->fetch()["aid"];
				showAlbumTile($db, $currAlbumID, TRUE);
			}
			print("</DIV>\n");
			print("</DIV>\n");
		print("</DIV>\n");
	print("</DIV>\n");
}


// display an error page when going to an unknown artist, album, or playlist.
function show404($errorSource) {
	print("<DIV class='headerRow'>\n");
	print("<p><b>Whoops!</b></p>\n");
	print("</DIV>\n");

	$errorMsg = "You tried to access a";
	switch ($errorSource) {
		case "album":
			$errorMsg = $errorMsg . "n album";
			break;
		case "artist":
			$errorMsg = $errorMsg . "n artist";
			break;
		case "playlist":
			$errorMsg = $errorMsg . " playlist";
			break;
	}
	$errorMsg = $errorMsg . "that doesn't exist! Redirecting to home...";
	print("<DIV class='row f_headerText' style='margin-top:20px'>\n");
	print("<p>$errorMsg</p>\n");
	print("</DIV>\n");
	header("refresh:2;url=dashboard.php?op=home");
}



?>
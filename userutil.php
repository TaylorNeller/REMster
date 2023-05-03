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
function showSongsList($db, $songsData, $mediaID, $mediaType, $userID) {
	
	$likeQuery = "SELECT sid FROM " . 
				 "liked NATURAL JOIN song_playlist WHERE uname='$userID'";
	$likeResponse = $db->query($likeQuery);
	$liked = [];
	if ($likeResponse != FALSE) {
		while ($row = $likeResponse->fetch()) {
			$sid = $row["sid"];
			array_push($liked, $sid);
		}
	}
	$likePidQuery = "SELECT pid FROM liked WHERE uname='$userID'";
	$pidResponse = $db->query($likePidQuery);
	$likedPid = -1;
	if ($pidResponse != FALSE) {
		$row = $pidResponse->fetch();
		$likedPid = $row["pid"];
	}

	$trackNum = 1;
	foreach($songsData as $currSong) {
		print("<TR>\n");
		$currSid = $currSong["sid"];
		$currName = $currSong["name"];
		$currArtist = $currSong["artists"];
		$currDuration = $currSong["duration"];

		$currAid = getAlbumFromSong($db, $currSid);

		$otherSongsRaw = [];
		// need to get this to pass to JS for next/prev functionality
		if ($mediaType == "A") {
			$otherSongsRaw = getSongs($db, $mediaID, "A");
		}
		else if ($mediaType == "P") {
			$otherSongsRaw = getSongs($db, $mediaID, "P");
		}
		$otherSongs = "";
		foreach ($otherSongsRaw as $currOther) {
			$currOtherAid = getAlbumFromSong($db, $currOther["sid"]);
			$currOtherStr = $currOther["sid"] . "." . $currOtherAid . "." . 
				$currOther ["name"] . "." . $currOther["duration"]  . ".";
			foreach ($currOther["artists"] as $currOtherArtist) {
				$currOtherStr = $currOtherStr . $currOtherArtist . ".";
			}
			$currOtherStr = substr_replace($currOtherStr, "/", -1);
			$otherSongs = $otherSongs . $currOtherStr;
		}

		$jsArtists = implode(', ', $currArtist);
		$jsData = $currSid . ", " . $currAid . ', ' . $currName. ', ' . $currDuration. ', ' . $otherSongs . ', ' . $jsArtists;

		print("<TD><BUTTON type='button' class='tnum' value='$jsData' onclick='loadNewData(this.value)'>" . 
			"$trackNum</DIV></TD>\n");


		$artistString = "";
		foreach(array_keys($currArtist) as $currArtistID) {
			$artistString = $artistString . "<a href=?op=artist&artid=$currArtistID> ".
				$currArtist[$currArtistID] . "</a>,&nbsp;";
		}

		print("<TD>$currName<br>" . rtrim($artistString, ",&nbsp;"). "</TD>\n");
		print("<TD align='right'></TD>\n");
		print("<TD>" . gmdate("i:s", $currDuration) . "</TD>\n");
		print("<TD><form method='POST'>"
				. "<input type='hidden' name='like-sid' value='$currSid'/>"
				. "<input type='hidden' name='like-pid' value='$likedPid'/>"
				. "<button type='submit' id='like-$currSid' ");
		if (in_array($currSid, $liked)) {
			print("class='like-btn liked'");
		}
		else {
			print("class='like-btn not-liked'");
		}
				
		print(" onclick='toggleLike($currSid,$likedPid)'>"
				. "<span class='heart-icon'></span></button></form></TD>\n");
		print("</TR>\n");
		$trackNum++;
	}
}

function showSongsResultList($db, $songSIDs) {
	$sidString = implode(", ", $songSIDs);
	$metaQuery = "SELECT * FROM song WHERE sid IN ($sidString)";
	$metaResult = $db->query($metaQuery);
	if ($metaResult == FALSE) {
		print($metaQuery);
	}

	$trackNum = 1;
	while(($currSong = $metaResult->fetch()) && $trackNum <= 5) {
		print("<TR>\n");
		$currSid = $currSong["sid"];
		$currName = $currSong["sname"];
		$currDuration = $currSong["duration"];
		$currArtist = getArtistsFromMedia($db, $currSid, "S");
		$currAid = getAlbumFromSong($db, $currSid);

		$anameQuery = "SELECT aname FROM album WHERE aid=$currAid";
		$anameResult = $db->query($anameQuery);
		$anameValue = $anameResult->fetch()["aname"];

		$artistString = "";
		foreach(array_keys($currArtist) as $currArtistID) {
			$artistString = $artistString . "<a href=?op=artist&artid=$currArtistID> ".
				$currArtist[$currArtistID] . "</a>,&nbsp;";
		}

		$anameString ="<a href=?op=album&aid=$currAid>$anameValue</a>";
		print("<TD>$currName<br>" . rtrim($artistString, ",&nbsp;"). "</TD>\n");
		print("<TD>$anameString</TD>\n");
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

function getAlbumFromSong($db, $sid) {
	$AIDquery = "SELECT DISTINCT aid FROM song_album WHERE sid=$sid";
	$AIDresult = $db->query($AIDquery);
	if ($AIDresult != FALSE) {
		return $AIDresult->fetch()["aid"];
	}
	else {
		print($AIDquery);
	}
}

function showEditableSongsList($songsData) {
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
		print("<TD><INPUT type='checkbox' name='cbRemove[]' value=$currSid></TD>");
		print("</TR>\n");
		$trackNum++;
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
		$playlistData["is_public"] = $target["is_public"];
	}
	else {
		// playlist doesn't exist, handle accordingly
	}
	return $playlistData;
}

function viewLiked($db, $userID) {
	$pidQuery = "SELECT pid FROM liked WHERE uname='$userID'";
	$pidResult = $db->query($pidQuery);
	$pid = -1;
	if ($pidResult != FALSE) {
		$row = $pidResult->fetch();
		$pid = $row["pid"];
	}
	viewPlaylist($db, $pid, $userID);
}

function viewPlaylist($db, $playlistID, $userID) {

	$likePidQuery = "SELECT pid FROM liked WHERE uname='$userID'";
	$pidResponse = $db->query($likePidQuery);
	$likedPid = -1;
	if ($pidResponse != FALSE) {
		$row = $pidResponse->fetch();
		$likedPid = $row["pid"];
	}

	$curratedQuery = "SELECT pid FROM currated";
	$curratedResult = $db->query($curratedQuery);
	$isCurrated = FALSE;
	if ($curratedResult->fetch()["pid"] != "") {
		$isCurrated = TRUE;
	}

	// retrieve playlist metadata
	$playlistData = getPlaylistData($db, $playlistID);
	if ($playlistData["is_public"] == "F" && $playlistData["owner"] != $userID) {
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
				if ($isCurrated == TRUE) {
					print("CURRATED PLAYLIST");
				}
				else if ($playlistData["is_public"] == "T") {
					print("PUBLIC PLAYLIST");
				}
				else {
					print("PRIVATE PLAYLIST");
				}
					
				print("</DIV>\n");

				print("<DIV class='row headerRow'>\n");
					print("<b>" . $playlistData["name"] . "</b>");
				print("</DIV>\n");

	 			// other album info
				print("<DIV class='row textRow'>\n");
					// if we add social features, this can be replaced with link to user's page
					print("Created by " . $playlistData["owner"] . "&nbsp;&bull;&nbsp;");

					// derive song count and album length
					$numSongs = count($playlistSongs);
					$runtime = 0;
					foreach ($playlistSongs as $currSong) {
						$runtime += $currSong["duration"];
					}
					print("$numSongs songs, " . intdiv($runtime, 60) . " min " . 
						($runtime % 60) . " sec");
					$editLink = "<a href=?op=editplaylist&pid=$playlistID>Edit Playlist</a>";
					if ($userID == $playlistData["owner"] && $playlistID != $likedPid) {
						print("&nbsp;&bull;&nbsp;" . $editLink);
					}
				print("</DIV>\n");

			print("</DIV>\n");
		print("</DIV>\n");

	 	// thought: use javascript to handle liked songs
		if (sizeof($playlistSongs) == 0) {
			print("<DIV class='row textRow' style='margin-top: 60px; font-size: 26px'>\n");
			print("<p>This playlist has no songs! Add a song from the control bar.</p>");
			print("</DIV>\n");
		}

		else {
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
			showSongsList($db, $playlistSongs, $playlistID, "P", $userID);
			print("</TABLE>\n</DIV>\n");
		}

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
	$albumData = getAlbumData($db, $albumID);
	$albumArtists = getArtistsFromMedia($db, $albumID, "A");
	$albumSongs = getSongs($db, $albumID, "A");

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
		showSongsList($db, $albumSongs, $albumID, "A", $userID);
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
	
		print("<DIV class='row'>");
			showCreateTile();
			$userPlaylists = getUserPlaylists($db, $userID);
			for($i = 0; $i < sizeof($userPlaylists); $i++) {
				showPlaylistTile($db, $userPlaylists[$i], FALSE);
			}
			
		print("</DIV>\n");
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

// draws an album tile: album art, name, (optionally) artists
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

// draws a playlist tile: playlist art, name, (optionally) owner
function showPlaylistTile($db, $playlistID, $showOwner) {
	$playlistData = getPlaylistData($db, $playlistID);
	$playlistArt = rand(1, 4);
	$srcLink = "art/playlist/$playlistArt.png";
	$playlistName = $playlistData["name"];
	print("<a href='?op=playlist&pid=$playlistID'>");
		print("<DIV class='tile'>\n");
			print("<img src=$srcLink alt='$playlistName cover' " . 
				"style='height:100%; width:100%; object-fit: cover'>");
			$tileInfo = "<b>$playlistName</b>";
			if ($showOwner == TRUE) {
				$tileInfo = $tileInfo . "<br>" . $playlistData["owner"];
			}
			print("<p class='textRow' style='text-align: center'>\n$tileInfo</p>\n");
		print("</DIV>\n");
	print("</a>");
}

function showArtistTile($db, $artistID) {
	$artistData = getArtist($db, $artistID);
	$srcLink = "art/profile/$artistID.png";
	$artistName = $artistData["name"];
	print("<a href='?op=artist&artid=$artistID'>");
		print("<DIV class='tile'>\n");
			print("<img src=$srcLink alt='$artistName profile photo' " . 
				"style='height:100%; width:100%; object-fit: cover'>");
			$tileInfo = "<b>$artistName</b>";
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
		print("<DIV class='scrollable-content'>\n");
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
		print("<DIV class='scrollable-content'>\n");
		foreach($appearsInPlaylists as $currPlaylistID) {
			showPlaylistTile($db, $currPlaylistID, TRUE);
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
		while($currPID = $appearsInResult->fetch()["pid"]) {
			array_push($appearsInPIDs, $currPID);
		}
	}
	else {
		print("ERROR WITH APPEARANCES QUERY" . $appearsInQuery);
	}
	return $appearsInPIDs;
}

// returns playlists owned by a given user
function getUserPlaylists($db, $userID) {
	$playlistsQuery = "SELECT pid FROM playlist WHERE owner = '$userID'";
	$playlistsResult = $db->query($playlistsQuery);
	if ($playlistsResult != FALSE) {
		$userPlaylists = [];
		while ($currPID = $playlistsResult->fetch()["pid"]) {
			array_push($userPlaylists, $currPID);
		}
	}
	else {
		print("WEEWOO on $playlistsQuery");
	}
	return $userPlaylists;
	
}

function showLandingPage() {
	// removed overflow to try to prevent scrollability
	print("<DIV class='container contentContainer' style='overflow: hidden'>\n");
		print("<DIV class='f_headerText' style='text-align: center; " . 
			"width: 100%; margin-top: 50px'>\n");
		print("<b>Welcome to REMster.<br>All music, no hassle.</b>\n");
		print("</DIV>\n");
		print("<DIV class='row f_standardText' style='text-align: center; " . 
			"width: 100%; height: 100vh; margin-top: 50px'>\n");

			print("<DIV class='col-md-6'>\n");
				print("<p>Log in with your account...</p>");
				print("<FORM name = 'fmLogin' method='POST' action='?op=login'>\n");
				print("<INPUT class='loginTextBox' type='text' name='uname' size='16' placeholder='username' />\n");
				print("<br>");
				print("<INPUT class='loginTextBox' type='password' name='pass' size='16' placeholder='password' />\n");
				print("<br>");
			 	print("<INPUT type='submit' value='login' style='margin-top: 20px'/>\n");
				print("</FORM>\n");
			print("</DIV>\n");

			print("<DIV class='col-md-6'>\n");
				print("<p>Or create a new account.</p>");
				print("<FORM name = 'fmRegister' method='POST' action='?op=register'>\n");
				print("<INPUT class='loginTextBox' type='text' name='uname' size='16' placeholder='username' />\n");
				print("<br>");
				print("<INPUT class='loginTextBox' type='text' name='email' size='16' placeholder='email' />\n");
				print("<br>");
				print("<INPUT class='loginTextBox' type='password' name='pass1' size='16' placeholder='password' />\n");
				print("<br>");
				print("<INPUT class='loginTextBox' type='password' name='pass2' size='16' placeholder='re-enter' />\n");
				print("<br>");
			 	print("<INPUT type='submit' value='register' style='margin-top: 20px'/>\n");
				print("</FORM>\n");
			print("</DIV>\n");
		print("</DIV>\n");
	print("</DIV>\n");
}

function validateUser($db, $uname, $pass) {
	// returns boolean
	$passEncrypted = md5($pass);
	$loginQuery = 	"SELECT uname " . 
					"FROM users " .
					"WHERE uname='$uname' AND pass='$passEncrypted'";
	$result = $db->query($loginQuery);
	return $result->fetch()["uname"] == "" ? FALSE : TRUE;
}

// creates a new user account.
function registerUser($db, $uname, $email, $pass1, $pass2) {
	if ($pass1 != $pass2) {
		header("refresh:2;url=dashboard.php");
		printf("Passwords do not match.");
	}

	else {

		$passEncrypted = md5($pass1);

		$validationQuery = 	"SELECT uname " .
							"FROM users " .
							"WHERE email='$email'";
		$validationResult = $db->query($validationQuery);
		if ($validationResult->fetch["uname"] != "") {
			header("refresh:2;url=dashboard.php");
			printf("A user with this email already exists.");
		}

		else {
			$registerQuery = 	"INSERT INTO users " . 
								"VALUES ('$uname', '$passEncrypted', '$email')";
			$registerResult = $db->query($registerQuery);
			$playlistQuery =	"INSERT INTO playlist (pname, owner, is_public) " .
								"VALUES ('Liked Songs','$uname','F')";
			$playlistResult = $db->query($playlistQuery);
			$pid = $db->lastInsertId();
			$likedQuery =		"INSERT INTO liked " .
								"VALUES ('$uname',$pid)";
			$likedResult = $db->query($likedQuery);
			return TRUE;
		}
	}
	return FALSE;
}

function viewEditPlaylistPage($db, $userID, $playlistID) {
	if ($playlistID != -1) {
		$playlistData = getPlaylistData($db, $playlistID);
	}

	$adminQuery = "SELECT uname FROM admins WHERE uname='$userID'";
	$adminResult = $db->query($adminQuery);
	$isAdmin = FALSE;
	if ($adminResult->fetch()["uname"] != "") {
		$isAdmin = TRUE;
	}

	$curratedQuery = "SELECT pid FROM currated WHERE pid=$playlistID";
	$curratedResult = $db->query($curratedQuery);
	$isCurrated = FALSE;
	if ($curratedResult->fetch()["pid"] != "") {
		$isCurrated = TRUE;
	}


	print("<DIV class='container contentContainer'>\n");
	print("<FORM name='fmEdit' method='POST' action='?op=submitedits&pid=$playlistID'>\n");
		print("<DIV class='row'>");

			print("<DIV class='col-md-3'>\n");
				$srcLink = "art/playlist/1.png";
				$alttext = $playlistData["name"];
				print("<img src=$srcLink alt='$alttext cover' " . 
					"height='100%', width='100%'>");
			print("</DIV>");

			print("<DIV class='col-md-9 my-auto'>\n");

				print("<DIV class='row textRow'>\n");
					print("EDITING");
				print("</DIV>\n");
				print("<DIV class='row headerRow'>\n");
				if ($playlistID == -1) {
					$tfValue = "click to edit";
				}
				else {
					$tfValue = $playlistData["name"];
				}
				
				print("<INPUT type='text' class='playlistName' name='pname' value='$tfValue'>");
				print("</DIV>\n");


	 			// submit changes button
				print("<DIV class='row textRow' style='margin-top: 10px'>\n");
					print("<INPUT class='editPlaylistButtons' type='submit' value='Save Changes'>&emsp;\n");
					// only allow deletion if editing current playlist
					if ($playlistID != -1) {
						print("<p>Delete Playlist?&emsp;</p>");
						print("<INPUT name='cbDelPlaylist' type='checkbox' value='T'>&emsp;\n");
					}
					// if user is admin, allow adding to currated playlists
					if ($isAdmin == TRUE) {
						print("<p>Add to currated?&emsp;</p>");
						print("<INPUT name='cbRecommend' type='checkbox' value='T' ");
						// if currently in currated, check box by default
						if ($isCurrated == TRUE) {
							print("checked>\n");
						}
						else {
							print(">\n");
						}
					}
				print("</DIV>\n");

			print("</DIV>\n");
		print("</DIV>\n");
		
		if ($playlistID != -1) {
			print("<DIV class='row'>\n");
			$tableHeader = "<TABLE class='f_standardText' " . 
				"width='100%' cellpadding='5' style='margin-top: 10px'>" .
				"<TR>\n" .
				"<TH>#</TH>\n" .
				"<TH>Title</TH>\n" .
				"<TH></TH>\n" .
				"<TH>Length</TH>\n" .
				"<TH>Remove</TH>\n" .
				"</TR>\n";
			print($tableHeader);

			$playlistSongs = getSongs($db, $playlistID, "P");
			showEditableSongsList($playlistSongs);
			print("</TABLE>\n</DIV>\n");
		}

		print("<DIV class='row f_standardText' style='height: 100px; margin-top: 20px'>");
			print("Make public?<SELECT name='access' class='editPlaylistButtons'>\n");
			print("<OPTION value='T'>Yes</OPTION>\n");
			print("<OPTION value='F'>No</OPTION>\n</SELECT>\n");
		print("</DIV>\n");
		print("</FORM>\n");
		// spacer
		print("<DIV style='height: 200px; margin-top: 20px'></DIV>\n");

	print("</DIV>\n");
}

function processEditPlaylist($db, $data, $playlistID, $userID) {
	// extract data from POST var
	$newName = $data["pname"];
	$is_public = $data["access"];
	$delete = $data["cbDelPlaylist"];
	$currated = $data["cbRecommend"];

	// if deleting playlist
	if ($delete == "T") {
		$deleteMetaQuery = "DELETE FROM playlist WHERE pid=$playlistID";
		$deleteDataQuery = "DELETE FROM song_playlist WHERE pid=$playlistID";

		$metaResult = $db->query($deleteMetaQuery);
		$dataResult = $db->query($deleteDataQuery);
		if ($metaResult == FALSE || $dataResult == FALSE) {
			print("something wrong with " . $deleteMetaQuery . "or" . $deleteDataQuery);
		}
	}

	// else if new playlist
	else if ($playlistID != -1) {
		$metaUpdateQuery = "UPDATE playlist SET pname='$newName', " . 
					"is_public='$is_public' " . 
		"WHERE pid=$playlistID";

		$metaUpdateResult = $db->query($metaUpdateQuery);
		if ($metaUpdateResult == FALSE) {
			print("someting wrong with" . $metaUpdateQuery);
			// handle error
		}
	}
	
	// if editing existing playlist
	else {
		$newPlaylistQuery = "INSERT INTO playlist (pname, owner, is_public) " . 
							"VALUES ('$newName', '$userID', '$is_public')";
		$newPlaylistResult = $db->query($newPlaylistQuery);
		if ($newPlaylistResult == FALSE) {
			print("something wrong with " . $newPlaylistQuery);
		}
	}

	$songsToRemove = $data["cbRemove"];
	$toRemoveString = "(". implode( ", ",$songsToRemove) . ")";
	// only do this if anything to remove
	if (sizeof($songsToRemove) > 0) {
		$removeQuery = "DELETE FROM song_playlist WHERE sid IN $toRemoveString";
		$removeResult = $db->query($removeQuery);
		if ($removeResult == FALSE) {
			// handle error
			print("something wrong with " . $removeQuery);
		}
	}

	// if adding to currated playlists
	if ($currated == "T") {
		$recommendQuery = "INSERT INTO currated VALUE($playlistID)";
		$recommendResult = $db->query($recommendQuery);
	}
	else {
		$recommendQuery = "DELETE FROM currated WHERE pid=$playlistID";
		$recommendResult = $db->query($recommendQuery);
	}




	// if deleting playlist or creating new, go to overall playlist page
	if ($delete == "T" || $playlistID == -1) {
		viewPlaylistsPage($db, $userID);
	}

	// else editing playlist, redisplay playlist page
	else {
		viewPlaylist($db, $playlistID, $userID);
	}
	
}

function addToPlaylist($db, $data, $userID) {
	$sid = $data["sid"];
	$pid = $data["ddlAddtoPlaylist"];

	$playlistQuery = "SELECT * FROM playlist WHERE pid=$pid";
	$playlistResult = $db->query($playlistQuery)->fetch();
	if ($playlistResult["owner"] != $userID) {
		// handle
	}
	else {
		$updateQuery = "INSERT INTO song_playlist VALUE($sid, $pid)";
		$updateResult = $db->query($updateQuery);
		if ($updateResult == FALSE) {
			print("WHAT");
		}
		viewPlaylist($db, $pid, $userID);
	}
}

// view the main homepage of the site. 
function viewHomepage($db, $userID) {
	print("<DIV class='container contentContainer'>\n");
		print("<DIV class='row headerRow'>\n");
		print("<p>Welcome back, <b>$userID.</b></p>\n");
		print("</DIV>\n");

		$albumQuery = "SELECT aid FROM album";
		$albumResult = $db->query($albumQuery);

		print("<DIV class='row f_headerText' style='margin-top:20px'>\n");
		print("<p>Sample our Catalog</p>\n");
		print("</DIV>\n");
		print("<DIV class='row'>\n");
			print("<DIV class='scrollable-container'>\n");
			print("<DIV class='scrollable-content' style='margin-top: 20px'>\n");
			$i = 0;
			while(($currAlbumID = $albumResult->fetch()["aid"]) && $i < 10) {
				showAlbumTile($db, $currAlbumID, TRUE);
				$i++;
			}
			print("</DIV>\n");
			print("</DIV>\n");
		print("</DIV>\n");

		$curratedQuery = "SELECT pid FROM currated";
		$curratedResult = $db->query($curratedQuery);

		print("<DIV class='row f_headerText' style='margin-top:20px'>\n");
		print("<p>Currated by Genre</p>\n");
		print("</DIV>\n");
		print("<DIV class='row'>\n");
			print("<DIV class='scrollable-container'>\n");
			print("<DIV class='scrollable-content' style='margin-top: 20px'>\n");
			$i = 0;
			while(($currPlaylistID = $curratedResult->fetch()["pid"]) && $i < 10) {
				showPlaylistTile($db, $currPlaylistID, FALSE);
				$i++;
			}
			print("</DIV>\n");
			print("</DIV>\n");
		print("</DIV>\n");
	print("</DIV>\n");
}

// view the search page of the site.
function viewSearch($db) {
	print("<DIV class='container-fluid contentContainer'>\n");
		print("<DIV class='row headerRow'>\n");
			print("<p>Search</p>\n");
		print("</DIV>\n");
		print("<DIV class='row d-flex'>\n");
		print("<FORM name='fmSearch' method='GET'>\n");
			print("<INPUT name='op' type='hidden' value='searchfor'></INPUT>");
			print("<INPUT name='query' class='f_standardText searchBox' type='textbox'></INPUT>");
			print("<INPUT name='sendsearch' class='f_standardText searchButton' type='submit' value='GO'></INPUT>");
		print("</FORM>\n");
		print("</DIV>\n");
	print("</DIV>\n");
}
// executes search in three stages, zooming-out in terms of granularity.
// searches through artists, albums, songs, and playlists (if public or owner is user)
function executeSearch($db, $query, $userID) {
	// arrays for results. Only search by soundex if no results up to that point (arrays empty).
	$songResultsData = [];
	$albumResultsData = [];
	$artistResultsData = [];
	$playlistResultsData = [];

	// 1. search for the string directly, or things LIKE the string
	$firstSongQuery = "SELECT sid FROM song WHERE sname='$query' OR sname LIKE('%$query%')";
	$firstSongResult = $db->query($firstSongQuery);
	while ($currSid = $firstSongResult->fetch()["sid"]) {
		array_push($songResultsData, $currSid);
	}
	$firstAlbumQuery = "SELECT aid FROM album WHERE aname='$query' OR aname LIKE('%$query%')";
	$firstAlbumResult = $db->query($firstAlbumQuery);
	while ($currAid = $firstAlbumResult->fetch()["aid"]) {
		array_push($albumResultsData, $currAid);
	}
	$firstArtistQuery = "SELECT artid FROM artist WHERE artname='$query' OR artname LIKE ('%$query%')";
	$firstArtistResult = $db->query($firstArtistQuery);
	while ($currArtid = $firstArtistResult->fetch()["artid"]) {
		array_push($artistResultsData, $currArtid);
	}
	$firstPlaylistQuery = "SELECT pid FROM playlist WHERE (pname='$query' OR pname LIKE('%$query%'))" . 
	" AND (owner='$userID' OR is_public='T')";
	$firstPlaylistResult = $db->query($firstPlaylistQuery);
	while ($currPid = $firstPlaylistResult->fetch()["pid"]) {
		array_push($playlistResultsData, $currPid);
	}

	// 2. if no results so far, search for the soundex of the string
	if (sizeof($songResultsData) == 0 && sizeof($albumResultsData) == 0 &&
		sizeof($artistResultsData) == 0 && sizeof($playlistResultsData) == 0) {
		$finalSongQuery = "SELECT sid FROM song WHERE SOUNDEX(sname) = SOUNDEX('$query')";
		$finalSongResult = $db->query($finalSongQuery);
		while ($currSid = $finalSongResult->fetch()["sid"]) {
			array_push($songResultsData, $currSid);
		}
		$finalAlbumQuery = "SELECT aid FROM album WHERE SOUNDEX(aname) = SOUNDEX('$query')";
		$finalAlbumResult = $db->query($finalAlbumQuery);
		while ($currAid = $finalAlbumResult->fetch()["aid"]) {
			array_push($albumResultsData, $currAid);
		}
		$finalArtistQuery = "SELECT artid FROM artist WHERE SOUNDEX(artname) = SOUNDEX('$query')";
		$finalArtistResult = $db->query($finalArtistQuery);
		while ($currArtid = $finalArtistResult->fetch()["artid"]) {
			array_push($artistResultsData, $currArtid);
		}
		$finalPlaylistQuery = "SELECT pid FROM playlist WHERE SOUNDEX(pname) = SOUNDEX('$query')" . 
		" AND (owner='$userID' OR is_public='T')";
		$finalPlaylistResult = $db->query($finalPlaylistQuery);
		while ($currPid = $finalPlaylistResult->fetch()["pid"]) {
			array_push($playlistResultsData, $currPid);
		}
	}

	$songResultsData = array_unique($songResultsData);
	// add albums/playlists/artists where songs appear
	$sidString = implode(", ", $songResultsData);
	$suplAlbumQuery = "SELECT DISTINCT aid FROM song_album WHERE sid IN ($sidString)";
	$suplAlbumResult = $db->query($suplAlbumQuery);
	if ($suplAlbumResult != FALSE) {
		while ($currAid = $suplAlbumResult->fetch()["aid"]) {
			array_push($albumResultsData, $currAid);
		}
	}
	
	$suplArtistQuery = "SELECT DISTINCT artid FROM song_artist WHERE sid IN ($sidString)";
	$suplArtistResult = $db->query($suplArtistQuery);
	if ($suplArtistResult != FALSE) {
		while ($currArtid = $suplArtistResult->fetch()["artid"]) {
			array_push($artistResultsData, $currArtid);
		}
	}
	
	$suplPlaylistQuery = "SELECT DISTINCT pid FROM song_playlist WHERE sid IN ($sidString)" . 
	" AND (owner='$userID' OR is_public='T')";
	$suplPlaylistResult = $db->query($suplPlaylistQuery);
	if ($suplPlaylistResult != FALSE) {
		while ($currPid = $suplPlaylistResult->fetch()["pid"]) {
			array_push($playlistResultsData, $currPid);
		}
	}

	// clean results - remove dupes
	$albumResultsData = array_unique($albumResultsData);
	$artistResultsData = array_unique($artistResultsData);
	$playlistResultsData = array_unique($playlistResultsData);

	$totalData = [];
	array_push($totalData, $songResultsData);
	array_push($totalData, $albumResultsData);
	array_push($totalData, $artistResultsData);
	array_push($totalData, $playlistResultsData);
	viewResults($db, $totalData);
}

function viewResults($db, $data) {
	print("<DIV class='container-fluid contentContainer'>\n");
		print("<DIV class='row headerRow'>\n");
			print("<p>Results</p>\n");
		print("</DIV>\n");
		if (sizeof($data[0]) == 0 && sizeof($data[1]) == 0 &&
			sizeof($data[2]) == 0 && sizeof($data[3]) == 0) {
			print("<DIV class='row labelRow'>\n");
			print("<b>Your search had no results. Please check spelling and try again!</b>");
			print("</DIV>\n");
		}
		else {
			print("<DIV class='row labelRow'>\n");
			print("<b>Songs</b>");
			print("</DIV>\n");
			if (sizeof($data[0]) == 0) {
				print("<DIV class='row labelRow' style='font-size:20px'>\n");
				print("No song results");
				print("</DIV>\n");
			}
			else {
			print("<DIV class='row'>\n");
			$tableHeader = "<TABLE class='f_standardText' " . 
				"width='70%' cellpadding='5' style='margin-top: 10px'>" .
				"<TR>\n" .
				"<TH>Title</TH>\n" .
				"<TH>Album</TH>\n" .
				"<TH></TH>\n" .
				"<TH>Length</TH>\n" .
				"<TH></TH>\n" .
				"</TR>\n";
			print($tableHeader);
			showSongsResultList($db, $data[0]);
			print("</TABLE>\n</DIV>\n");
			}
			print("<hr class='solid'>");

			print("<DIV class='row labelRow'>\n");
			print("<b>Albums</b>");
			print("</DIV>\n");
			if (sizeof($data[1]) == 0) {
				print("<DIV class='row labelRow' style='font-size:20px'>\n");
				print("No album results");
				print("</DIV>\n");
			}
			else {
				print("<DIV class='row'>\n");
					print("<DIV class='scrollable-container'>\n");
					print("<DIV class='scrollable-content'>\n");
					foreach($data[1] as $currAid) {
						showAlbumTile($db, $currAid, TRUE);
					}
					print("</DIV>\n");
					print("</DIV>\n");
				print("</DIV>\n");
			}

			print("<hr class='solid'>");

			print("<DIV class='row labelRow'>\n");
			print("<b>Artists</b>");
			print("</DIV>\n");
			if (sizeof($data[2]) == 0) {
				print("<DIV class='row labelRow' style='font-size:20px'>\n");
				print("No artist results");
				print("</DIV>\n");
			}
			else {
				print("<DIV class='row'>\n");
					print("<DIV class='scrollable-container'>\n");
					print("<DIV class='scrollable-content'>\n");
					foreach($data[2] as $currArtid) {
						showArtistTile($db, $currArtid);
					}
					print("</DIV>\n");
					print("</DIV>\n");
				print("</DIV>\n");
			}

			print("<hr class='solid'>");

			print("<DIV class='row labelRow'>\n");
			print("<b>Playlists</b>");
			print("</DIV>\n");
			if (sizeof($data[3]) == 0) {
				print("<DIV class='row labelRow' style='font-size:20px'>\n");
				print("No playlist results");
				print("</DIV>\n");
			}
			else {
				print("<DIV class='row'>\n");
					print("<DIV class='scrollable-container'>\n");
					print("<DIV class='scrollable-content'>\n");
					foreach($data[3] as $currPid) {
						showPlaylistTile($db, $currPid, TRUE);
					}
					print("</DIV>\n");
					print("</DIV>\n");
				print("</DIV>\n");
			}	
		}
		// spacer
		print("<DIV style='height: 200px; margin-top: 20px'></DIV>");
	print("</DIV>\n");
}	

// view the MyAccount page, where one can logout.
function viewAccountPage($db, $userID) {
	print("<DIV class='container-fluid contentContainer'>\n");
	print("<DIV class='row headerRow d_flex align-items-center'>\n");
			print("<p><b>$userID's</b> account</p>&emsp;\n");
			print("<A href='?op=logout'>"
						."<DIV class='otherMenu d-flex justify-content-center'>"
						."Sign Out</DIV></A>");
		print("</DIV>\n");

		print("<DIV class='row labelRow' style='margin-top: 50px'>\n");
		print("<p>Change Password</p>");
		print("</DIV>\n");
			print("<DIV class='row'>\n");
				print("<FORM name = 'fmChangePass' method='POST' action='?op=changepass'>\n");
				print("<INPUT class='loginTextBox' type='password' name='oldpass' size='16' placeholder='old password' />\n");
				print("<br>");
				print("<INPUT class='loginTextBox' type='password' name='pass1' size='16' placeholder='password' />\n");
				print("<br>");
				print("<INPUT class='loginTextBox' type='password' name='pass2' size='16' placeholder='re-enter' />\n");
				print("<br>");
			 	print("<INPUT type='submit' class='otherMenu' value='Update' style='margin-top: 20px; width: 100%'/>\n");
				print("</FORM>\n");
			print("</DIV>\n");
		print("<DIV class='row labelRow' style='margin-top: 50px'>\n");
		print("<p>Become an Admin</p>");
		print("</DIV>\n");
		$adminQuery = "SELECT uname FROM admins WHERE uname='$userID'";
		$adminResult = $db->query($adminQuery);
		$isAdmin = FALSE;
		if ($adminResult->fetch()["uname"] != "") {
			$isAdmin = TRUE;
		}
			print("<DIV class='row'>\n");
			print("<DIV class='col-md-1'>\n");
			print("<FORM name='fmBecomeAdmin' method='POST' action='?op=makeadmin'>");
			print("<INPUT name='cbAdmin' type='checkbox' value='T' ");
			// if currently admin, check box by default
			if ($isAdmin == TRUE) {
				print("checked>\n");
			}
			else {
				print(">\n");
			}
			print("<INPUT type='submit' class='otherMenu' value='Save Changes' style='margin-top: 20px; width: 300%'/>\n");
			print("</FORM>\n");
			print("</DIV>\n");
			print("<DIV class='col-md-11 textRow'>\n");
			print("<p>If checked, user is an admin account and has access to upload/deletion privledges.</p>");
			print("</DIV>\n");
			print("</DIV>\n");
	print("</DIV>\n");
}

function processMakeAdmin($db, $userID, $isAdmin) {
	if ($isAdmin == "T") {
		$adminQuery = "INSERT INTO admins VALUE('$userID')";
		$adminResult = $db->query($adminQuery);
	}
	else {
		$adminQuery = "DELETE FROM admins WHERE uname='$userID'";
		$adminResult = $db->query($adminQuery);
	}
	header("refresh:2;url=dashboard.php?op=home");
	printf("<DIV class='headerRow'>Changes saved. Redirecting...</DIV>");
}

function processChangePassword($db, $userID, $data) {
	$oldpass = $data["oldpass"];
	$pass1 = $data["pass1"];
	$pass2 = $data["pass2"];

	$valid = validateUser($db, $userID, $oldpass);
	if ($pass1 != $pass2) {
		header("refresh:2;url=dashboard.php?op=account");
		printf("Passwords do not match.");
	}
	else if ($valid == FALSE) {
		header("refresh:2;url=dashboard.php?op=account");
		printf("<DIV class='textRow'>incorrect password.</DIV>");
	}
	else {
		$passEncrypted = md5($pass1);
		$updatePassQuery = "UPDATE users SET pass='$passEncrypted' WHERE uname='$userID'";
		$updatePassResult = $db->query($updatePassQuery);
		header("refresh:2;url=dashboard.php?op=home");
		printf("Successfully updated password!");
	}
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
<?php
include_once("db_connect.php");

function isAdmin($db, $user) {
	$query = "SELECT * FROM admins WHERE uname='$user'";
	$res = $db->query($query);
	if ($res != FALSE) {
		$row = $res->fetch();
		return $row["uname"] == $user;
	}
	return FALSE;
}

function viewUploadForm($db, $user) {
	if (isAdmin($db,$user)) {
		?>
			<!-- <script type="text/javascript">
    			counterReset();
			</script> -->
			<div id="form-container">
				<form name="upload-album" method="POST" action="?op=upload">
					<h1 class="fm-text">Album Name</h1>
					<input name="aname" class="fm-input" type="text"/>
					<div id="song-container">
						<h2 id="song-heading" class="fm-text">Songs:</h2>

						<div class="add-song">
							<p class="song-p">Name</p>
							<input name="sname[0]" class="fm-input" type="text"/>
							<p class="song-p">Genre</p>
							<input name="sgenre[0]" class="fm-input" type="text"/>		
							<p class="song-p">Duration</p>
							<input name="sdur[0]" class="fm-input" type="text"/>
							<p class="song-p">Artists</p>
							<input name="sarts[0]" class="fm-input" type="text"/>					
						</div>
						</br>
						<button id="add-song" name="add-song" type='button' class="btn song-control" onclick="addSongForm()">Add Song</button>
						<button id="remove-song" name="remove-song" type='button' class="btn song-control" onclick="removeSongForm()">Remove Song</button>
					</div>
					<p class="song-p">Release Date</p>
					<input name="rdate" class="fm-input" type="text"/>
					<input type="submit" value="Upload Album"/>
				</form>
			</div>
		<?php
	}
}

function processAlbumUpload($db, $user, $formData) {

	$aname = $formData['aname'];
	// print("<p>$aname</p>");
	$names = $formData["sname"];
	// foreach ($names as $n) {
	// 	print("<p>$n</p>");
	// }
	$genres = $formData["sgenre"];
	$genreArr = [];
	$genreSet = [];
	foreach ($genres as $n) {
		$songGenres = [];
		$sGenres = explode(",",$n);
		foreach ($sGenres as $genre) {
			$genre = trim($genre);
			array_push($songGenres, $genre);
			if (!in_array($genre, $genreSet)) {
				array_push($genreSet,$genre);
			}
		}
		array_push($genreArr, $songGenres);
	}
	// foreach ($genres as $n) {
	// 	print("<p>$n</p>");
	// }

	$durs = $formData["sdur"];
	// foreach ($durs as $n) {
	// 	print("<p>$n</p>");
	// }

	$arts = $formData["sarts"];
	$artsArr = [];
	$artsSet = [];
	foreach ($arts as $n) {
		$songArts = [];
		$sArts = explode(",",$n);
		foreach ($sArts as $artist) {
			$artist = trim($artist);
			array_push($songArts, $artist);
			if (!in_array($artist, $artsSet)) {
				array_push($artsSet,$artist);
			}
		}
		array_push($artsArr, $songArts);
	}

	$rdate = $formData['rdate'];
	// print("<p>$rdate</p>");

	// updates 'album'
	$q1 = "INSERT INTO album(aname,release_date,uploader) "
			."VALUE('$aname','$rdate','$user')";
	// $q2 = "SELECT aid FROM album WHERE aname=$aname AND release_date=$rdate AND uploader=$user";

	$r1 = $db->query($q1);
	if ($r1 == FALSE) {
		print("<h1>ERROR UPDAING 'album'</h1>");
	}
	// $r2 = $db->query($q1);
	// if ($r2 == FALSE) {
	// 	print("<h1>ERROR FINDING aid</h1>");
	// }
	// $aid = $r2->fetch()["aid"];
	$aid = $db->lastInsertId();
	// print("<h1>aid:$aid</h1>");

	// updates 'song'
	$sids = [];

	for ($i = 0; $i < count($names); $i++) {
		$name = $names[$i];
		$duration = $durs[$i];
		$sQuery = "INSERT INTO song(sname,duration,release_date) "
				."VALUE('$name','$duration','$rdate')";
		$res = $db->query($sQuery);
		if ($res == FALSE) {
			print("<h1>ERROR UPDATING 'song'</h1>");
		}
		$sid = $db->lastInsertId();
		array_push($sids,$sid);
	}

	// retrieves artist ids

	$artistIds = [];

	foreach ($artsSet as $artist) {
		$sQuery = "SELECT * FROM artist WHERE artname='$artist'";
		$res = $db->query($sQuery);
		if ($res == FALSE) {
			print("<h1>ERROR RETRIEVING 'artid'</h1>");
		}
		$id = $res->fetch()["artid"];
		array_push($artistIds, $id);
	}

	// updates 'album_artist'

	for ($i = 0; $i < count($artsSet); $i++) {
		$artId = $artistIds[$i];
		$sQuery = "INSERT INTO album_artist(aid,artid) "
				."VALUE($aid,$artId)";
		$res = $db->query($sQuery);
		if ($res == FALSE) {
			print("<h1>ERROR UPDATING 'album_artist'</h1>");
		}
	}

	// updates 'song_artist'
	for ($x = 0; $x < count($sids); $x++) {
		$sid = $sids[$x];
		for ($i = 0; $i < count($artsArr[$x]); $i++) {
			$index = array_search($artsArr[$x][$i],$artsSet);
			$artId = $artistIds[$index];
			$sQuery = "INSERT INTO song_artist(sid,artid) "
					."VALUE($sid,$artId)";
			$res = $db->query($sQuery);
			if ($res == FALSE) {
				print("<h1>ERROR UPDATING 'song_artist'</h1>");
			}
		}
	}




	// retrieves genre ids

	$genreIds = [];

	foreach ($genreSet as $genre) {
		$sQuery = "SELECT * FROM genre WHERE gname='$genre'";
		$res = $db->query($sQuery);
		if ($res == FALSE) {
			print("<h1>ERROR RETRIEVING 'gname'</h1>");
		}
		$id = $res->fetch()["gid"];
		array_push($genreIds, $id);
	}

	// updates 'album_genre'

	for ($i = 0; $i < count($genreSet); $i++) {
		$genreId = $genreIds[$i];
		$sQuery = "INSERT INTO album_genre(aid,gid) "
				."VALUE($aid,$genreId)";
		$res = $db->query($sQuery);
		if ($res == FALSE) {
			print("<h1>ERROR UPDATING 'album_genre'</h1>");
		}
	}

	// updates 'song_genre'

	for ($x = 0; $x < count($sids); $x++) {
		$sid = $sids[$x];
		for ($i = 0; $i < count($artsArr[$x]); $i++) {
			$index = array_search($genreArr[$x][$i],$genreSet);
			$genreId = $genreIds[$index];
			$sQuery = "INSERT INTO song_genre(sid,gid) "
					."VALUE($sid,$genreId)";
			$res = $db->query($sQuery);
			if ($res == FALSE) {
				print("<h1>ERROR UPDATING 'song_genre'</h1>");
			}
		}
	}

	// updates 'song_album'


	foreach ($sids as $sid) {
		$sQuery = "INSERT INTO song_album(sid,aid) "
					."VALUE($sid,$aid)";
		$res = $db->query($sQuery);
		if ($res == FALSE) {
			print("<h1>ERROR UPDATING 'song_album'</h1>");
		}
	}


}

function viewRemoveForm($db, $user) {
	$q1 = "SELECT * FROM album NATURAL JOIN album_artist NATURAL JOIN artist WHERE uploader='$user'";
	$res = $db->query($q1);
	if ($res != FALSE) {

		$table = [];

		while ($row = $res->fetch()) {

			$aid = $row["aid"];
			$aname = $row["aname"];
			$adate = $row["release_date"];
			$artname = $row["artname"];


			if (array_key_exists("$aid",$table)) {
				if (!in_array($artname,$table["$aid"][1])) {
					array_push($table["$aid"][1], $artname);
				}
			}
			else {
				$table["$aid"] = [$aname, [$artname], $adate];
			}
		}


		?>
			<form name="remove-albums" method="POST" action="?op=remove">
				<table>
				<tr><th>Album Name</th><th>Artist</th><th>Release Date</th><th>
					<input type="submit" value="Delete Checked Albums"/>
				</th></tr>
		<?php
			foreach ($table as $aid => $arr) {
				$aname = $arr[0];
				$artists = $arr[1];
				$a1 = $artists[0];
				$adate = $arr[2];

				print("<tr><td>$aname</td><td>$a1");
				for ($i = 1; $i < count($artists); $i++) {
					$artist = $artists[$i];
					print(", $artist");
				}
				print("</td><td>$adate</td>");
				print("<td><input name='cbAlbums[]' type='checkbox' value='$aid'/></td></tr>");
			}
		?>
				</table>
			</form>
		<?php
	}
}

function processAlbumRemoval($db, $formData) {
	$aids = $formData["cbAlbums"];

	// finds song ids

	$sids = [];

	$count = $aids[0];

	foreach ($aids as $aid) {
		$sQuery = "SELECT sid FROM song_album WHERE aid=$aid";
		$res = $db->query($sQuery);
		if ($res == FALSE) {
			print("<h1>ERROR RETRIEVING 'sid'</h1>");
		}
		while ($row = $res->fetch()) {
			$sid = $row["sid"];
			array_push($sids,$sid);
		}
	}
	// updates 'song_genre', 'song_artist', 'song'
	
	foreach ($sids as $sid) {
		$q1 = "DELETE FROM song_genre WHERE sid=$sid";
		$r2 = $db->query($q1);
		if ($r2 == FALSE) {
			print("<h1>ERROR UPDATING 'song_genre'</h1>");
		}
		$q2 = "DELETE FROM song_artist WHERE sid=$sid";
		$r2 = $db->query($q2);
		if ($r2 == FALSE) {
			print("<h1>ERROR UPDATING 'song_artist'</h1>");
		}
		$q3 = "DELETE FROM song WHERE sid=$sid";
		$r3 = $db->query($q3);
		if ($r3 == FALSE) {
			print("<h1>ERROR UPDATING 'song'</h1>");
		}
	}

	// updates 'album', 'album_genre', 'song_album', 'album_artist'


	foreach ($aids as $aid) {
		$q1 = "DELETE FROM album WHERE aid=$aid";
		$r1 = $db->query($q1);
		if ($r1 == FALSE) {
			print("<h1>ERROR UPDATING 'album'</h1>");
		}
		$q2 = "DELETE FROM album_genre WHERE aid=$aid";
		$r2 = $db->query($q2);
		if ($r2 == FALSE) {
			print("<h1>ERROR UPDATING 'album_genre'</h1>");
		}
		$q3 = "DELETE FROM song_album WHERE aid=$aid";
		$r3 = $db->query($q3);
		if ($r3 == FALSE) {
			print("<h1>ERROR UPDATING 'song_album'</h1>");
		}
		$q4 = "DELETE FROM album_artist WHERE aid=$aid";
		$r4 = $db->query($q4);
		if ($r4 == FALSE) {
			print("<h1>ERROR UPDATING 'album_artist'</h1>");
		}
	}


}


?>
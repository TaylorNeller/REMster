<?php
include_once("db_connect.php");
ini_set('upload_max_filesize', '100M');
ini_set('post_max_size', '100M');

function isAdmin($db, $user) {
	if ($user != "") {
		$query = "SELECT * FROM admins WHERE uname='$user'";
		$res = $db->query($query);
		if ($res != FALSE) {
			$row = $res->fetch();
			return $row["uname"] == $user;
		}
	}

	return FALSE;
}
function viewUploadArtist($db, $user) {
	if (isAdmin($db,$user)) {
		?>
			<div id="form-container">
				<form enctype="multipart/form-data" name="upload-artist" method="POST" action="?op=uploadArt">
					<h1 class="fm-text">Artist Upload</h1>
					<p class="song-p">Name</p>
    				<input name="aname" class="fm-input" type="text" required/>
    
    				<p class="song-p">Upload Profile Picture:</p>
    				<input type="hidden" name="MAX_FILE_SIZE" value="10000000" />
    				<input name="ppic" type="file" required>
  					</br><input id="upload-btn" class="btn" type="submit" value="Create Artist Profile"/>
				</form>
				<DIV style='height: 200px; margin-top: 20px'></DIV>
			</div>
		<?php
	}
}

function viewUploadForm($db, $user) {
	if (isAdmin($db,$user)) {
		?>
			<!-- <script type="text/javascript">
    			counterReset();
			</script> -->
			<div id="form-container">
				<form enctype="multipart/form-data" name="upload-album" method="POST" action="?op=upload">
					<h1 class="fm-text">Album Name</h1>
					<input name="aname" class="fm-input" type="text" required/>
					<h2 id="song-heading" class="fm-text">Songs:</h2>

					<div id="song-container">
						
						<script>addSongForm()</script>
						</br>
					</div>
					<button id="add-song" name="add-song" type='button' class="btn song-control" onclick="addSongForm()">Add Song</button>
					<button id="remove-song" name="remove-song" type='button' class="btn song-control" onclick="removeSongForm()">Remove Song</button>
					<p class="song-p">Release Date</p>
					<input type="date" name="rdate" id="fm-input" pattern="\d{4}-\d{2}-\d{2}" required>
					<!-- <input name="rdate" class="fm-input" type="text" required/> -->
					</br><p class="song-p">Upload Album Cover:</p>
					<input type="hidden" name="MAX_FILE_SIZE" value="1000000" />
  					<input type="file" id="c-art" name="cover-art" required>


  					</br><input id="upload-btn" class="btn" type="submit" value="Upload Album"/>
				</form>
				<DIV style='height: 200px; margin-top: 20px'></DIV>
			</div>
			
		<?php
	}
}

function processArtistUpload($db, $user, $formData) {
	$aname = $formData['aname'];

	$query = "INSERT INTO artist(artname) VALUE('$aname')";
	$res = $db->query($query);
	$aid = $db->lastInsertId();

	// upload cover art
	$image_dir = "art/profile/";
	$uploadfile = $image_dir . $aid . ".png";

	print("<p>");
	if (move_uploaded_file($_FILES['ppic']['tmp_name'], $uploadfile)) {
		echo "Uploaded successfully!.\n";
	} else {
		echo "Error in image upload!\n";
		echo 'Here is some more debugging info:';
		print_r($_FILES);
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

	$durs = $formData["sdur"];

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

	// retrieves artist ids

	$artistIds = [];

	foreach ($artsSet as $artist) {
		$sQuery = "SELECT * FROM artist WHERE UPPER(artname)=UPPER('$artist')";
		$res = $db->query($sQuery);
		$row = $res->fetch();
		if ($row == FALSE) {
			header("refresh:2;url=dashboard.php?op=uploadfmArt");
			printf("Please create all artists in album before uploading");
			return;
		}
		else {
			$id = $row["artid"];
			array_push($artistIds, $id);
		}

	}

	$rdate = $formData['rdate'];
	// print("<p>$rdate</p>");

	// updates 'album'
	$q1 = "INSERT INTO album(aname,release_date,uploader) "
			."VALUE('$aname','$rdate','$user')";

	$r1 = $db->query($q1);
	if ($r1 == FALSE) {
		print("<h1>ERROR UPDAING 'album'</h1>");
	}
	$aid = $db->lastInsertId(); //album id
	// print("<h1>aid:$aid</h1>");


	// upload cover art
	$image_dir = "art/cover/";
	$uploadfile = $image_dir . $aid . ".png";

	print("<p>");
	if (move_uploaded_file($_FILES['cover-art']['tmp_name'], $uploadfile)) {
		// echo "Image successfully uploaded.\n";
	} else {
		echo "Error in image upload!\n";
		echo 'Here is some more debugging info:';
		print_r($_FILES);
	}
	
	
	echo '</p>';


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

	// upload song mp3s
	for ($i = 0; $i < count($sids); $i++) {
		$sid = $sids[$i];
		$song_dir = "songs/";
		$uploadfile = $song_dir . $sid . ".mp3";
	
		print("<p>");
		if (move_uploaded_file($_FILES["smp3"]["tmp_name"][$i], $uploadfile)) {
			// echo "Song with sid $sid successfully uploaded.\n";
		} else {
			echo "Error in sid $sid upload!\n";
			echo 'Here is some more debugging info:';
			print_r($_FILES);
		}
	
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
		$sQuery = "SELECT * FROM genre WHERE UPPER(gname)=UPPER('$genre')";
		$res = $db->query($sQuery);
		$row = $res->fetch();
		if ($row == FALSE) {
			$addQuery = "INSERT INTO genre(gname) VALUE('$genre')";
			$res = $db->query($addQuery);
			$id = $db->lastInsertId();
			array_push($genreIds, $id);
		}
		else {
			$id = $row["gid"];
			array_push($genreIds, $id);
		}
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
		for ($i = 0; $i < count($genreArr[$x]); $i++) {
			$index = array_search($genreArr[$x][$i],$genreSet);
			$genre = $genreArr[$x][$i];
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
	echo "<p>Finished uploading</p>";


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
			<h2 class="rem-h2">Remove Uploaded Albums:</h2>
			<form name="remove-albums" method="POST" action="?op=remove">
				<table id="remove-table">
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
				print("<td class='cb-td'><input class='cb-td' name='cbAlbums[]' type='checkbox' value='$aid'/></td></tr>");
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

	// removes album art

	foreach ($aids as $aid) {
		$artPath = "art/cover/$aid.png";
		if (file_exists($artPath)) {
			unlink($artPath);
		}
	}
	// removes song mp3s

	foreach ($sids as $sid) {
		$songPath = "songs/$sid.png";
		if (file_exists($songPath)) {
    		unlink($songPath);
		}
	}
	echo "<p>Finished deleting</p>";

}


?>
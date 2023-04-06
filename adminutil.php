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
			<div id="form-container">
				<form name="upload-album" method="POST" action="?op=upload">
					<p>Album Name</p>
					<input name="aname" type="text"/>
					<div id="song-container">
						<h1>Songs:</h1>

						<div class="add-song">
							<p>Name</p>
							<input name="s1name" type="text"/>
							<p>Genre</p>
							<input name="s1genre" type="text"/>		
							<p>Duration</p>
							<input name="s1dur" type="text"/>
							<p>Artists</p>
							<input name="s1arts" type="text"/>					
						</div>
						<button type='button' onclick="addSongForm()">Add Song</button>
					</div>
					<p>Release Date</p>
					<input name="sname" type="text"/>
				</form>
			</div>
		<?php
	}
}

function processAlbumUpload($db, $formData) {

}


?>
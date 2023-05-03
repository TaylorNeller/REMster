<?php 
include_once('db_connect.php');

$sid = $_POST["like-sid"];
$pid = $_POST["like-pid"];

$checkQuery = "SELECT sid FROM song_playlist WHERE pid=$pid AND sid=$sid";
$checkResult = $db->query($checkQuery);
$has = FALSE;
if ($checkResult != FALSE) {
	while ($row = $checkResult->fetch()) {
		if ($row["sid"] == $sid) {
			$has = TRUE;
		}
	}
}

if ($has) {
	$sQuery = "DELETE FROM song_playlist "
				."WHERE sid=$sid AND pid=$pid";
	$res = $db->query($sQuery);
}
else {
	$sQuery = "INSERT INTO song_playlist(sid, pid) "
				."VALUE($sid,$pid)";
	$res = $db->query($sQuery);

}

?>
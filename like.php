<?php 
include_once('db_connect.php');

$aid = $_POST["like-id"];
$sQuery = "INSERT INTO song_album(sid, aid) "
				."VALUE(33,$aid)";
$res = $db->query($sQuery);

?>
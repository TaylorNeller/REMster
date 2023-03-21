<?php
include_once("db_connect.php");

function viewCollection($db, $collectionID, $userID) {
	$collectionQuery = 	"SELECT * " . 
						"FROM song " .
						"WHERE collid=$collectionID";
	$collectionResult = $db->query($collectionQuery);

	if ($collectionResult != FALSE) {
		$collection = $collectionResult->fetch();
		$songid = $collection["songid"];
		$name = $collection["name"];
		$artist = $collection["artist"];
	}

	else {
		print("unknown error has occurred! check query:");
		print($collectionQuery);
	}
}




?>
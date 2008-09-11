<?php
	session_start();

	include_once( "../classes/db.php" );
	include_once( "../classes/person.php" );
	include_once( "../classes/group.php" );
	include_once( "../classes/file.php" );
	include_once( "../classes/nugget.php" );	

	$db = new dbConnection();
		
	if (!is_numeric($_GET['id']))
		die("Invalid request");

	if ( $file = new File( $_GET['id'], $db ) ) {
	$query = $db->igroupsQuery("SELECT iNuggetID FROM nuggetFileMap WHERE iFileID={$file->getID()}");
	$row = mysql_fetch_row($query);
	if ($nugget = new Nugget($row[0], $db, 0)) {
		if ($nugget->isPrivate())
			die ("You must be logged in to download this file");
	}
	else
		die ("Invalid request");
	header("Content-Type: {$file->getMimeType()}");
	header("Content-Length: " . filesize($file->getDiskName()));
	header('Content-Disposition: attachment; filename="'.$file->getOriginalName().'"');
	header("Cache-Control: must-revalidate,post-check=0,pre-check=0");
	header("Cache-Control: public",false);
	header("Pragma: public");
	header("Expires: 0");

	readfile($file->getDiskName());
	}
?>

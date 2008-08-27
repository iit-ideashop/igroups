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

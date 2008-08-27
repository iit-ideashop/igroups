<?php
	session_start();

	include_once( "classes/db.php" );
	include_once( "classes/quota.php" );
	include_once( "classes/file.php" );
	include_once( "classes/group.php" );
	
	$db = new dbConnection();
	
	$db->igroupsQuery( "DELETE FROM FileQuota WHERE 1" );
	
	$files = $db->igroupsQuery( "SELECT iID FROM Files WHERE 1" );
	
	while ( $fileID = mysql_fetch_row( $files ) ) {
		$file = new File( $fileID[0], $db );
		$quota = new Quota( $file->getGroup(), $db );
		$quota->increaseUsed( $file->getFilesize() );
		$quota->updateDB();
	}
?>
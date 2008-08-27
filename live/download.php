<?php
	session_start();

	include_once( "classes/db.php" );
	include_once( "classes/person.php" );
	include_once( "classes/group.php" );
	include_once( "classes/file.php" );
	
	$db = new dbConnection();
	
	if ( isset( $_SESSION['userID'] ) ) 
		$currentUser = new Person( $_SESSION['userID'], $db );
	else
		die("You are not logged in.");
		
	if (!is_numeric($_GET['id']))
		die("Invalid request");

	if ( $file = new File( $_GET['id'], $db ) ) {
	$group = new Group($file->getGroupID(), $file->getGroupType(), $file->getSemester(), $db);
	if (!$currentUser->isGroupMember($group) && !$file->isIPROFile())
		die("You are not a member of this group");
	if ($file->isPrivate() && ($file->getAuthorID() != $currentUser->getID() && !$currentUser->isGroupAdministrator($file->getGroup())))
		die("You are not authorized to download this file.");
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

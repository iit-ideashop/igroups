<?php
	session_start();

	include_once( "classes/db.php" );
	include_once( "classes/person.php" );
	include_once( "classes/group.php" );
	include_once( "classes/file.php" );
	
	$db = new dbConnection();
	
	if(isset($_SESSION['userID']))
		$currentUser = new Person($_SESSION['userID'], $db);
	else if(isset($_COOKIE['userID']) && isset($_COOKIE['password']) && isset($_COOKIE['selectedGroup']))
	{
		if(strpos($_COOKIE['userID'], "@") === FALSE)
			$userName = $_COOKIE['userID']."@iit.edu";
		else
			$userName = $_COOKIE['userID'];
		$user = $db->iknowQuery("SELECT iID,sPassword FROM People WHERE sEmail='".$userName."'");
		if(($row = mysql_fetch_row($user)) && (md5($_COOKIE['password']) == $row[1]))
		{
			$_SESSION['userID'] = $row[0];
			$currentUser = new Person($row[0], $db);
			$group = explode(",", $_COOKIE['selectedGroup']);
			$_SESSION['selectedGroup'] = $group[0];
			$_SESSION['selectedGroupType'] = $group[1];
			$_SESSION['selectedSemester'] = $group[2];
		}
	}
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

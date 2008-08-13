<?php
	session_start();

	include_once( "classes/db.php" );
	include_once( "classes/person.php" );
	include_once( "classes/group.php" );
	include_once( "classes/email.php" );
	
	$db = new dbConnection();
	
	if ( isset( $_SESSION['userID'] ) ) 
		$currentUser = new Person( $_SESSION['userID'], $db );
	else
		die("You are not logged in.");
		
	if ( $email = new Email( $_GET['email'], $db ) ) {
	$group = $email->getGroup();
	if (!$group->isGroupMember($currentUser))
		die("You are not authorized to download this file.");
	$query = $db->igroupsQuery("SELECT * FROM EmailFiles WHERE iID={$_GET['id']}");
	$file = mysql_fetch_array($query);
	header("Content-Type: {$file['sMimeType']}");
        header("Content-Length: " . filesize("/files/igroups/emails/{$file['sDiskName']}"));
	header('Content-Disposition: attachment; filename="'.$file['sOrigName'].'"');
        header("Cache-Control: must-revalidate,post-check=0,pre-check=0");
        header("Cache-Control: public",false);
        header("Pragma: public");
        header("Expires: 0");

	readfile("/files/igroups/emails/{$file['sDiskName']}");
	}
?>

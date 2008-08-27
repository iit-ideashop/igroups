<?php
	session_start();

	include_once( "../classes/db.php" );
	include_once( "../classes/person.php" );
	include_once( "../classes/group.php" );
	include_once( "../classes/groupemail.php" );
	
	$db = new dbConnection();
	
	if ( isset( $_SESSION['userID'] ) ) 
		$currentUser = new Person( $_SESSION['userID'], $db );
	else
		die("You are not logged in.");
		
	if ( $email = new GroupEmail( $_GET['email'], $db ) ) {
	$query = $db->igroupsQuery("SELECT * FROM GroupEmailFiles WHERE iID={$_GET['id']}");
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

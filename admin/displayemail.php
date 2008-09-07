<?php
	session_start();

	include_once( "../classes/db.php" );
	include_once( "../classes/person.php" );
	include_once( "../classes/groupemail.php" );
	include_once( "../classes/group.php" );
	
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
	if ( !$currentUser->isAdministrator() )
               die("You must be an administrator to access this page.");
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<!-- This web-based application is Copyrighted &copy; 2008 Interprofessional Projects Program, Illinois Institute of Technology -->
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en"><head>
<title>iGroups - Display Email</title>
<link rel="stylesheet" href="default.css" type="text/css" />
</head>
<body>
<?php
	if ( $email = new GroupEmail( $_GET['id'], $db ) ) {
		$author = $email->getSender();
		print "<p><b>Subject:</b> ".$email->getSubjectHTML()."<br />";
		print "<b>From:</b> ".$author->getFullName()."<br />";
		print "<b>Date:</b> ".$email->getDate()."<br />";
		print "<b>To:</b> ".$email->getTo()."<br />";
		$files = $email->getAttachments();
		foreach($files as $file) {
			print "$file<br />";
		}
		print "</p><p>".$email->getBodyHTML()."</p>";
	}
?>
</body>
</html>

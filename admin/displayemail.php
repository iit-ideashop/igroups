<?php
	session_start();

	include_once( "../classes/db.php" );
	include_once( "../classes/person.php" );
	include_once( "../classes/groupemail.php" );
	include_once( "../classes/group.php" );
	
	$db = new dbConnection();
	
	if ( isset( $_SESSION['userID'] ) ) 
		$currentUser = new Person( $_SESSION['userID'], $db );
	else
		die("You are not logged in.");
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
   "http://www.w3.org/TR/html4/loose.dtd">

<!-- This web-based application is Copyrighted &copy; 2007 Interprofessional Projects Program, Illinois Institute of Technology -->

<html>
<head>
	<title>iGROUPS - Display Email</title>
	<link href="default.css" rel="stylesheet" type="text/css">
</head>

<body>
<?php
	if ( $email = new GroupEmail( $_GET['id'], $db ) ) {
		$author = $email->getSender();
		print "From: ".$author->getFullName()."<br>";
		print "To: ".$email->getTo()."<br>";
		print "Subject: ".$email->getSubjectHTML()."<br>";
		print "<p>".$email->getBodyHTML()."</p>";
		//print "<a href='sendemail.php?replyid=".$email->getID()."'>Reply to this email</a>";
	}
?>
</body>
</html>

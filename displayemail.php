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
	if (!is_numeric($_GET['id']))
		die("Invalid request");
	if ( $email = new Email( $_GET['id'], $db ) ) {
		if ($email->getGroupID() == null)
			die("No such email");
		$group = new Group($email->getGroupID(), $email->getGroupType(), $email->getSemester(),$db);
		if (!$currentUser->isGroupMember($group))
			die("You are not a member of this group");
		$author = $email->getSender();
		if ($email->prev || $email->next) 
			print "<table width='100%' border=0 cellspacing=0><tr><td align='center' bgcolor='lightgray'>";
		if ($email->prev) {
                        print "<a href='displayemail.php?id={$email->prev}'>Previous in Thread</a>";
                        if ($email->next)
                                print " | <a href='displayemail.php?id={$email->next}'>Next in Thread</a></td></tr></table>";
                        else
                                print "</td></tr></table>";
                }
                else {
                        if ($email->next)
                                print "<a href='displayemail.php?id={$email->next}'>Next in Thread</a></td></tr></table>";
                }
		print "<b>Subject:</b> ".$email->getSubjectHTML()."<br>";
		print "<b>From:</b> ".$author->getFullName()."<br>";
		print "<b>Date:</b> ".$email->getDateTime()."<br>";
		print "<b>To:</b> ".$email->getTo()."<br>";

		$files = $email->getAttachments();
		foreach($files as $file) {
			print "$file<br>";
		}
		$body = htmlspecialchars($email->getBody());
		print "<p>".$email->getBodyHTML()."</p>";
		//print "<pre>$body</pre>";
		print "<p><a href='sendemail.php?replyid=".$email->getID()."'>Reply to this email</a>";
		print "&nbsp;|&nbsp;<a href='forward.php?forward=".$email->getID()."'>Forward this email</a></p>";
	}
?>
</body>
</html>

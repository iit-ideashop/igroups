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

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<!-- This web-based application is Copyrighted &copy; 2008 Interprofessional Projects Program, Illinois Institute of Technology -->
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en"><head>
<title>iGroups - Display Email</title>
<link rel="stylesheet" href="default.css" type="text/css" />
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
			print "<table width=\"100%\" cellspacing=\"0\" style=\"border-style: none\"><tr><td align=\"center\" style=\"background: #AAAAAA\">";
		if ($email->prev) {
                        print "<a href=\"displayemail.php?id={$email->prev}\">Previous in Thread</a>";
                        if ($email->next)
                                print " | <a href=\"displayemail.php?id={$email->next}\">Next in Thread</a></td></tr></table>";
                        else
                                print "</td></tr></table>";
                }
                else {
                        if ($email->next)
                                print "<a href=\"displayemail.php?id={$email->next}\">Next in Thread</a></td></tr></table>";
                }
		print "<p><b>Subject:</b> ".$email->getSubjectHTML()."<br />";
		print "<b>From:</b> ".$author->getFullName()."<br />";
		print "<b>Date:</b> ".$email->getDateTime()."<br />";
		print "<b>To:</b> ".$email->getTo()."<br />";
		print "<a href=\"email.php?replyid=".$email->getID()."\">Reply to this email</a>";
                print "&nbsp;|&nbsp;<a href=\"email.php?forward=".$email->getID()."\">Forward this email</a><br /><br />";
		$files = $email->getAttachments();
		foreach($files as $file) {
			print "$file<br />";
		}
		print "</p>";
		$body = htmlspecialchars($email->getBody());
		print "<p>".str_replace("\n", "<br />", $body)."</p>";
	}
?>
</body>
</html>

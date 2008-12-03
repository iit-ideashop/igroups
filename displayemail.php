<?php
	include_once("globals.php");
	include_once("checklogin.php");
	include_once( "classes/email.php" );
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<!-- This web-based application is Copyrighted &copy; 2008 Interprofessional Projects Program, Illinois Institute of Technology -->
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en"><head>
<title><?php echo $appname; ?> - Display Email</title>
<?php
require("appearance.php");
echo "<link rel=\"stylesheet\" href=\"skins/$skin/default.css\" type=\"text/css\" title=\"$skin\" />\n";
foreach($altskins as $altskin)
	echo "<link rel=\"alternate stylesheet\" href=\"skins/$altskin/default.css\" type=\"text/css\" title=\"$altskin\" />\n";
?>
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
			print "<a href=\"#\" onclick=\"viewwin=dhtmlwindow.open('viewbox', 'ajax', 'displayemail.php?id=".$email->prev."', 'Display Email', 'width=650px,height=600px,left=300px,top=100px,resize=1,scrolling=1'); return false\">Previous in Thread</a>";
			if ($email->next)
				print " | <a href=\"#\" onclick=\"viewwin=dhtmlwindow.open('viewbox', 'ajax', 'displayemail.php?id=".$email->next."', 'Display Email', 'width=650px,height=600px,left=300px,top=100px,resize=1,scrolling=1'); return false\">Next in Thread</a></td></tr></table>";
			else
				print "</td></tr></table>";
		}
		else {
			if ($email->next)
				print "<a href=\"#\" onclick=\"viewwin=dhtmlwindow.open('viewbox', 'ajax', 'displayemail.php?id=".$email->next."', 'Display Email', 'width=650px,height=600px,left=300px,top=100px,resize=1,scrolling=1'); return false\">Next in Thread</a></td></tr></table>";
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
		print "<p>".anchorTags($body)."</p>";
	}
?>
</body>
</html>

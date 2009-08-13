<?php
	include_once('globals.php');
	include_once('checklogin.php');
	include_once('classes/email.php');
	
	//------Start XHTML Output--------------------------------------//
	
	require('doctype.php');
	require('appearance.php');
	echo "<link rel=\"stylesheet\" href=\"skins/$skin/default.css\" type=\"text/css\" title=\"$skin\" />\n";
	foreach($altskins as $altskin)
		echo "<link rel=\"alternate stylesheet\" href=\"skins/$altskin/default.css\" type=\"text/css\" title=\"$altskin\" />\n";
?>
<title><?php echo $appname; ?> - Display Email</title>
</head>
<body>
<?php
	if(!is_numeric($_GET['id']))
		errorPage('Invalid Email ID', 'The email ID provided is invalid', 400);
	if($email = new Email( $_GET['id'], $db))
	{
		if($email->getGroupID() == null)
			errorPage('Invalid Email ID', 'There is no such email ID', 400);
		$group = new Group($email->getGroupID(), $email->getGroupType(), $email->getSemester(),$db);
		if(!$currentUser->isGroupMember($group))
			errorPage('Group Credentials Required', 'You are not a member of this group', 403);
		$author = $email->getSender();
		if($email->prev || $email->next) 
			echo "<table width=\"100%\" cellspacing=\"0\" style=\"border-style: none\"><tr><td align=\"center\" style=\"background: #AAAAAA\">";
		if($email->prev)
		{
			echo "<a href=\"#\" onclick=\"viewwin=dhtmlwindow.open('viewbox', 'ajax', 'displayemail.php?id=".$email->prev."', 'Display Email', 'width=650px,height=600px,left=300px,top=100px,resize=1,scrolling=1'); return false\">Previous in Thread</a>";
			if($email->next)
				echo " | <a href=\"#\" onclick=\"viewwin=dhtmlwindow.open('viewbox', 'ajax', 'displayemail.php?id=".$email->next."', 'Display Email', 'width=650px,height=600px,left=300px,top=100px,resize=1,scrolling=1'); return false\">Next in Thread</a></td></tr></table>";
			else
				echo "</td></tr></table>";
		}
		else if ($email->next)
				echo "<a href=\"#\" onclick=\"viewwin=dhtmlwindow.open('viewbox', 'ajax', 'displayemail.php?id=".$email->next."', 'Display Email', 'width=650px,height=600px,left=300px,top=100px,resize=1,scrolling=1'); return false\">Next in Thread</a></td></tr></table>";
		echo "<p><b>Subject:</b> ".$email->getSubjectHTML()."<br />\n";
		echo "<b>From:</b> ".$author->getFullName()."<br />\n";
		echo "<b>Date:</b> ".$email->getDateTime()."<br />\n";
		echo "<b>To:</b> ".$email->getTo()."<br />\n";
		echo "<a href=\"email.php?replyid=".$email->getID()."\">Reply to this email</a>";
		echo "&nbsp;|&nbsp;<a href=\"email.php?forward=".$email->getID()."\">Forward this email</a><br /><br />\n";
		$files = $email->getAttachments();
		foreach($files as $file)
			echo "$file<br />\n";
		echo "</p>\n";
		$body = htmlspecialchars($email->getBody());
		echo "<p>".anchorTags($body)."</p>\n";
	}
?>
</body></html>

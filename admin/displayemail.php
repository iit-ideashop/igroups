<?php
	include_once("../globals.php");
	include_once( "checkadmin.php" );
	include_once( "../classes/groupemail.php" );
	include_once( "../classes/group.php" );
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<!-- This web-based application is Copyrighted &copy; 2008 Interprofessional Projects Program, Illinois Institute of Technology -->
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en"><head>
<?php require("../appearance.php"); ?>
<title><?php echo $appname;?> - Display Email</title>
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

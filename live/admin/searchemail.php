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
		 
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<!-- This web-based application is Copyrighted &copy; 2008 Interprofessional Projects Program, Illinois Institute of Technology -->
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en"><head>
<title>iGroups - Email Search</title>
<link rel="stylesheet" href="../default.css" type="text/css" />
</head>
<body>
<?php
require("sidebar.php");
?>
	<div id="content"><div id="topbanner">
<?php
		print "Group Emails";
?>
	</div>
	<table width="100%">
		<tr>
			<td>
				<h1>Search Text</h1>
				<form method="get" action="searchemail.php">
				Keyword: <input type="text" name="keyword" />
				<input type="submit" name="kwsearch" value="Search" />
				</form>
			</td>
		</tr>
	</table>
<?php
	
	if ( isset( $_GET['kwsearch'] ) ) {
		$query = $db->igroupsQuery("SELECT iID FROM GroupEmails WHERE sBody LIKE '%{$_GET['keyword']}%'"); 
		$emails = array();
		while ($row = mysql_fetch_row($query))
			$emails[] = new GroupEmail($row[0], $db);
	}
		
	
	if ( isset( $emails ) ) {
		print "<table>";
		foreach ( $emails as $email ) {
			$author = $email->getSender();
			print "<tr><td></td><td><a href='displayemail.php?id=".$email->getID()."'>".$email->getShortSubject()."</a></td><td>".$author->getFullName()."</td><td>".$email->getDate()."</td></tr>";
		}
		print "</table>";
	}
?>
</div></body>
</html>

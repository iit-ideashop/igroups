<?php
	session_start();

	include_once( "../classes/db.php" );
	include_once( "../classes/person.php" );
	include_once( "../classes/group.php" );
	include_once( "../classes/groupemail.php" );
	
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
		 
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
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
				<form method="get" action="searchemail.php"><fieldset>
				<label for="keyword">Keyword:</label><input type="text" name="keyword" id="keyword" />
				<input type="submit" name="kwsearch" value="Search" />
				</fieldset></form>
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
			print "<tr><td></td><td><a href=\"displayemail.php?id=".$email->getID()."\">".$email->getShortSubject()."</a></td><td>".$author->getFullName()."</td><td>".$email->getDate()."</td></tr>";
		}
		print "</table>";
	}
?>
</div></body>
</html>

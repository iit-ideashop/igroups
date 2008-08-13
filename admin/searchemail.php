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
		
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
   "http://www.w3.org/TR/html4/loose.dtd">

<!-- This web-based application is Copyrighted &copy; 2007 Interprofessional Projects Program, Illinois Institute of Technology -->

<html>
<head>
	<title>iGROUPS - Email Search</title>
	<link href="../default.css" rel="stylesheet" type="text/css">
</head>
<body>	
	<div id="topbanner">
<?php
		print "Group Emails";
?>
	</div>
	<table width=100%>
		<tr>
			<td>
				<h1>Search Text</h1>
				<form method="get" action="searchemail.php">
				Keyword: <input type="text" name="keyword">
				<input type="submit" name="kwsearch" value="Search">
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
</body>
</html>

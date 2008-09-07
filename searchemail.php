<?php
	session_start();

	include_once( "classes/db.php" );
	include_once( "classes/person.php" );
	include_once( "classes/group.php" );
	include_once( "classes/email.php" );
	
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
		 
	if ( isset($_SESSION['selectedGroup']) && isset($_SESSION['selectedGroupType']) && isset($_SESSION['selectedSemester']) )
		$currentGroup = new Group( $_SESSION['selectedGroup'], $_SESSION['selectedGroupType'], $_SESSION['selectedSemester'], $db );
	else
		die("You have not selected a valid group.");
?>
		
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<!-- This web-based application is Copyrighted &copy; 2008 Interprofessional Projects Program, Illinois Institute of Technology -->
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en"><head>
<title>iGroups - Email Search</title>
<link rel="stylesheet" href="default.css" type="text/css" />
</head>
<body>
<?php
require("sidebar.php");
?>
	<div id="content"><div id="topbanner">
<?php
		print $currentGroup->getName();
?>
	</div>
	<table width="100%">
		<tr>
			<td>
				<form method="get" action="searchemail.php"><fieldset><legend>Search Text</legend>
				<label for="keyword">Keyword:</label><input type="text" name="keyword" id="keyword" />
				<input type="submit" name="kwsearch" value="Search" />
				</fieldset></form>
			</td>
			<td>
				<form method="get" action="searchemail.php"><fieldset><legend>Search by Sender</legend>
				<label for="sender">Sender:</label><select name="sender" id="sender">
<?php
					$people = $currentGroup->getGroupMembers();
					foreach ( $people as $person ) {
						print "<option value=\"".$person->getID()."\">".$person->getFullName()."</option>";
					}
?>
				</select>
				<input type="submit" name="sendersearch" value="Search" />
				</fieldset></form>
			</td>
		</tr>
	</table>
<?php
	if ( isset( $_GET['sendersearch'] ) ) 
		$emails = $currentGroup->searchEmailBySender( $_GET['sender'] );
	
	if ( isset( $_GET['kwsearch'] ) )
		$emails = $currentGroup->searchEmailByText( $_GET['keyword'] );
	
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

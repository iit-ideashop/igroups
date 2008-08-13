<?php
	session_start();

	include_once( "classes/db.php" );
	include_once( "classes/person.php" );
	include_once( "classes/group.php" );

	$db = new dbConnection();
	
	if ( isset( $_SESSION['userID'] ) ) 
		$currentUser = new Person( $_SESSION['userID'], $db );
	else
		die("You are not logged in.");
		 
	if ( isset($_SESSION['selectedGroup']) && isset($_SESSION['selectedGroupType']) && isset($_SESSION['selectedSemester']) )
		$currentGroup = new Group( $_SESSION['selectedGroup'], $_SESSION['selectedGroupType'], $_SESSION['selectedSemester'], $db );
	else
		die("You have not selected a valid group.");
		
	function peopleSort( $array ) {
		$newArray = array();
		foreach ( $array as $person ) {
			$newArray[$person->getCommaName()] = $person;
		}
		ksort( $newArray );
		return $newArray;
	}
	
	function printTR() {
		static $i=0;
		if ( $i )
			print "<tr class='shade'>";
		else
			print "<tr>";
		$i=!$i;
	}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
   "http://www.w3.org/TR/html4/loose.dtd">

<!-- This web-based application is Copyrighted &copy; 2007 Interprofessional Projects Program, Illinois Institute of Technology -->

<html>
<head>
	<title>iGROUPS - Group Contact Info</title>
	<link href="default.css" rel="stylesheet" type="text/css">
</head>
<body>	
	<div id="topbanner">
<?php
		print $currentGroup->getName();
?>
	</div>
	<table cellpadding=2>
		<thead>
			<tr><td colspan='5'>Team Roster</td></tr>
			<tr><td>Name</td><td>Email</td><td>Phone #</td><td>Alt. Phone #<td>AIM</td></tr>
		</thead>
<?php
		$members = $currentGroup->getAllGroupMembers();
		$members = peopleSort( $members );
		foreach ( $members as $person ) {
			$profile = $person->getProfile();
			printTR();
			print "<td><a href='viewprofile.php?uID={$person->getID()}'>".$person->getCommaName()."</a></td>";
			print "<td>".$person->getEmail()."</td>";
			print "<td>".$profile['sPhone']."</td>";
			print "<td>".$profile['sPhone2']."</td>";
			print "<td>".$profile['sIM']."</td>";
			print "</tr>";
		}
?>
	</table>
	<br><br>
<?php

	$subgroups = $currentGroup->getSubGroups();
	foreach ($subgroups as $subgroup) {
		$members = $subgroup->getSubGroupMembers();
		print "<table><tr><th align='left'>{$subgroup->getName()}</th></tr>";
		foreach ($members as $member) {
			print "<tr><td>{$member->getFullName()}</td></tr>";
		}
		print "</table><br><br>";
	}

?>
	<a href='contactinfo.php'>Update your contact information</a>
</body>
</html>

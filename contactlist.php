<?php
	include_once("checklogin.php");
		
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
			print "<tr class=\"shade\">";
		else
			print "<tr>";
		$i=!$i;
	}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<!-- This web-based application is Copyrighted &copy; 2008 Interprofessional Projects Program, Illinois Institute of Technology -->
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en"><head>
<title>iGroups - Group Contact Info</title>
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
	<table cellpadding="2">
		<thead>
			<tr><td colspan="5">Team Roster</td></tr>
			<tr><td>Name</td><td>Email</td><td>Phone #</td><td>Alt. Phone #</td><td>AIM</td></tr>
		</thead>
<?php
		$members = $currentGroup->getAllGroupMembers();
		$members = peopleSort( $members );
		foreach ( $members as $person ) {
			$profile = $person->getProfile();
			printTR();
			print "<td><a href=\"viewprofile.php?uID={$person->getID()}\">".$person->getCommaName()."</a></td>";
			print "<td>".$person->getEmail()."</td>";
			print "<td>".htmlspecialchars($profile['sPhone'])."</td>";
			print "<td>".htmlspecialchars($profile['sPhone2'])."</td>";
			print "<td>".htmlspecialchars($profile['sIM'])."</td>";
			print "</tr>";
		}
?>
	</table>
	<br /><br />
<?php
	$subgroups = $currentGroup->getSubGroups();
	if(count($subgroups) > 0) {
		print "<table><tr>";
		foreach ($subgroups as $subgroup) {
			$members = $subgroup->getSubGroupMembers();
			print "<td style=\"vertical-align: top; border: thin solid black;\"><table><tr><th align=\"left\">".htmlspecialchars($subgroup->getName())."</th></tr>";
			foreach ($members as $member) {
				print "<tr><td>{$member->getFullName()}</td></tr>";
			}
			print "</table></td>";
		}
		print "</tr></table>";
	}
?>
<p><a href="contactinfo.php">Update your contact information</a></p>
</div></body>
</html>

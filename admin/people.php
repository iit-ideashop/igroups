<?php
	include_once( "checkadmin.php" );
	include_once("../classes/nugget.php");
	include_once("../classes/group.php");
	include_once("../classes/semester.php");
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<!-- This web-based application is Copyrighted &copy; 2008 Interprofessional Projects Program, Illinois Institute of Technology -->
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en"><head>
<title>iGroups - View Profile</title>
<link rel="stylesheet" href="../default.css" type="text/css" />
</head>
<body>
<?php
	require("sidebar.php");
	print "<div id=\"content\">";
	if(isset($_GET['email']) || (isset($_GET['uid']) && is_numeric($_GET['uid'])))
	{
		if(isset($_GET['uid']) && is_numeric($_GET['uid']))
			$contactInfo = mysql_fetch_array($db->igroupsQuery("SELECT * FROM People WHERE iID={$_GET['uid']}"));
		else if(isset($_GET['email']))
			$contactInfo = mysql_fetch_array($db->igroupsQuery("SELECT * FROM People WHERE sEmail='{$_GET['email']}'"));
		if ($contactInfo)
			$uid = $contactInfo['iID'];
		else
			die("No users matched that email address.");
		$profile = mysql_fetch_array($db->igroupsQuery("SELECT * FROM Profiles WHERE iPersonID={$uid}"));
		$query = $db->igroupsQuery("SELECT * FROM PeopleGroupMap WHERE iPersonID={$uid}");
		$groups = array();
		while($row = mysql_fetch_array($query))
			$groups[] = new Group($row['iGroupID'], 1, 0, $db);
		$query = $db->igroupsQuery("SELECT * FROM PeopleNuggetMap WHERE iPersonID={$uid} ORDER BY iNuggetID");
		$nuggets = array();
		while($row = mysql_fetch_array($query))
			$nuggets[] = new Nugget($row['iNuggetID'], $db, 1);
		$query = $db->igroupsQuery("SELECT * from nuggetAuthorMap WHERE iAuthorID={$uid} ORDER BY iNuggetID");
		$newnuggets = array();
		while($row = mysql_fetch_array($query))
			$newnuggets[] = new Nugget($row['iNuggetID'], $db, 0);
		$query = $db->igroupsQuery("SELECT * FROM PeopleProjectMap WHERE iPersonID={$uid} ORDER BY iSemesterID");
		while($row = mysql_fetch_array($query))
			$groups[] = new Group($row['iProjectID'], 0, $row['iSemesterID'], $db);
		$query = $db->igroupsQuery("SELECT * FROM UserTypes WHERE iID={$contactInfo['iUserTypeID']}");
		$row = mysql_fetch_array($query);
		$usertype = $row['sType'];
?>
<h1>Public Profile</h1>
<h2><?php print "{$contactInfo['sFName']} {$contactInfo['sLName']}"; ?></h2>
<?php
if ($profile['sPicture']) {
	print "<img src=\"../profile-pics/{$profile['sPicture']}\" width=\"200\" alt=\"{$profile['sPicture']}\" /><br />";
}
?>

<h3>Contact Information</h3>
<table cellspacing="5"> 
<?php
print "<tr><td>Primary E-mail: </td><td>{$contactInfo['sEmail']}</td></tr>";
if ($profile['sAltEmail'])
	print "<tr><td>Alternate E-mail: </td><td>".htmlspecialchars($profile['sAltEmail'])."</td></tr>";
if ($profile['sPhone'])
	print "<tr><td>Primary Phone: </td><td>".htmlspecialchars($profile['sPhone'])."</td></tr>";
if ($profile['sPhone2'])
	print "<tr><td>Home/Other Phone: </td><td>".htmlspecialchars($profile['sPhone2'])."</td></tr>";
if ($profile['sIM'])
	print "<tr><td>AIM Screen Name: </td><td>".htmlspecialchars($profile['sIM'])."</td></tr>";
?>
</table>

<h3>Personal Information</h3>
<table cellspacing="5">
<?php
if ($profile['sMajor'])
	print "<tr><td>Major: </td><td>".htmlspecialchars($profile['sMajor'])."</td></tr>";
if ($profile['sYear'])
	print "<tr><td>Year: </td><td>{$profile['sYear']}</td></tr>";
if ($profile['sNickname'])
	print "<tr><td>Nickname: </td><td>".htmlspecialchars($profile['sNickname'])."</td></tr>";
if ($profile['sHometown'])
	print "<tr><td>Hometown: </td><td>".htmlspecialchars($profile['sHometown'])."</td></tr>";
if ($profile['isResident'])
	print "<tr><td>Lives on Campus: </td><td>Yes</td></tr>";
if (isset($profile['isResident']) && $profile['isResident'] == 0)
	print "<tr><td>Lives on Campus: </td><td>No</td></tr>";
if ($profile['sBio']) {
	print "<tr><td valign=\"top\">Biography: </td><td valign=\"top\">".str_replace("\n", "<br />", htmlspecialchars($profile['sBio']))."</td></tr>";
}
if ($profile['sSkills']) {
	print "<tr><td valign=\"top\">Skills: </td><td valign=\"top\">".str_replace("\n", "<br />", htmlspecialchars($profile['sSkills']))."</td></tr>";
}
?>
</table>
<br />
<h1>Administrator features</h1>
<?php
		print "<h2>Additional user information</h2><table cellspacing=\"5\"><tr><td>Access level:</td><td>$usertype</td></tr><tr><td>Account created:</td><td>".$contactInfo['dCreateDate']."</td></tr><tr><td>User ID#:</td><td>$uid</td></tr></table>";
		print "<h2>Groups</h2>";
		if(count($groups) > 0)
		{
			print "<ul>";
			$currSem = false;
			foreach($groups as $group)
			{
				$sem = new Semester($group->getSemester(), $db);
				if(!$currSem)
				{
					if($sem->getName() == "")
						$semName = "Global Groups";
					else
						$semName = $sem->getName();
					print "<li><strong>".$semName."</strong><ul>";
					$currSem = $sem;
				}
				else if($currSem != $sem)
				{
					print "</ul></li><li><strong>".$sem->getName()."</strong><ul>";
					$currSem = $sem;
				}
				print "<li><a href=\"group.php?group=".$group->getID()."&amp;semester=".$group->getSemester()."&amp;selectAdminGroup=true&amp;selectSemester=true\">".$group->getName()."</a></li>";
			}
			print "</ul></li></ul>";
		}
		else
			print "<p>This user is in no groups.</p>";
		print "<h2>Nuggets</h2>";
		if((count($nuggets) + count($newnuggets)) > 0)
		{
			print "<ul>";
			foreach($nuggets as $nugget)
			{
				print "<li><a href=\"viewNugget.php?nuggetID=".$nugget->getID()."&amp;isOld=1\">".htmlspecialchars($nugget->getType())."</a></li>";
			}
			foreach($newnuggets as $nugget)
			{
				print "<li><a href=\"viewNugget.php?nuggetID=".$nugget->getID()."\">".htmlspecialchars($nugget->getType())."</a></li>";
			}
			print "</ul>";
		}
		else
			print "<p>This user has authored no nuggets.</p>";
	}
	else //if no email given in URL
	{
?>
	<form method="get" action="people.php"><fieldset>
		<label for="email">Email address query:</label><input type="text" name="email" id="email" /><input type="submit" name="Submit" />
	</fieldset></form>
<?php
	}
?>
</div></body>
</html>

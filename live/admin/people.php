<?php
	session_start();
	include_once("../classes/db.php");
	include_once("../classes/person.php");
	
	$db = new dbConnection();
	
	if (isset($_SESSION['userID']))
		$currentUser = new Person( $_SESSION['userID'], $db );
	else
		die("You are not logged in.");
	if(!$currentUser->isAdministrator())
		die("You must be an administrator to access this page.");
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<!-- This web-based application is Copyrighted &copy; 2008 Interprofessional Projects Program, Illinois Institute of Technology -->
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en"><head>
<title>iGroups - View Profile</title>
<link rel="stylesheet" href="../default.css" type="text/css" />
	<style type='text/css'>
	pre {
	 font-family: verdana, arial, sans-serif;
	 font-size:100%;
	 white-space: pre-wrap; /* css-3 */
	white-space: -moz-pre-wrap; /* Mozilla, since 1999 */
	white-space: -pre-wrap; /* Opera 4-6 */
	white-space: -o-pre-wrap; /* Opera 7 */
	word-wrap: break-word; /* Internet Explorer 5.5+ */
	_white-space: pre; /* IE only hack to re-specify in addition to word-wrap */
}
	</style>
</head>
<body>
<?php
	require("sidebar.php");
	print "<div id=\"content\">";
	if(isset($_GET['email']))
	{
		$contactInfo = mysql_fetch_array($db->igroupsQuery("SELECT * FROM People WHERE sEmail='{$_GET['email']}'"));
		if ($contactInfo)
			$uid = $contactInfo['iID'];
		else
			die("No users matched that email address.");
		$profile = mysql_fetch_array($db->igroupsQuery("SELECT * FROM Profiles WHERE iPersonID={$uid}"));
		$grp = mysql_fetch_array($db->igroupsQuery("SELECT * FROM PeopleGroupMap WHERE iPersonID={$uid}"));
		$nug = mysql_fetch_array($db->igroupsQuery("SELECT * FROM PeopleNuggetMap WHERE iPersonID={$uid}"));
		$prj = mysql_fetch_array($db->igroupsQuery("SELECT * FROM PeopleProjectMap WHERE iPersonID={$uid}"));
		
?>
<h1>Public Profile</h1>
<h2><?php print "{$contactInfo['sFName']} {$contactInfo['sLName']}"; ?></h2>
<?php
if ($profile['sPicture']) {
	print "<img src='profile-pics/{$profile['sPicture']}' width='200' /><br />";
}
?>

<h4>Contact Information</h4>
<table cellspacing='5'> 
<?php
print "<tr><td>Primary E-mail: </td><td>{$contactInfo['sEmail']}</td></tr>";
if ($profile['sAltEmail'])
        print "<tr><td>Alternate E-mail: </td><td>{$profile['sAltEmail']}</td></tr>";
if ($profile['sPhone'])
	print "<tr><td>Primary Phone: </td><td>{$profile['sPhone']}</td></tr>";
if ($profile['sPhone2'])
        print "<tr><td>Home/Other Phone: </td><td>{$profile['sPhone2']}</td></tr>";
if ($profile['sIM'])
	print "<tr><td>AIM Screen Name: </td><td>{$profile['sIM']}</td></tr>";
?>
</table>

<h4>Personal Information</h4>
<table cellspacing='5'>
<?php
if ($profile['sMajor'])
	print "<tr><td>Major: </td><td>{$profile['sMajor']}</td></tr>";
if ($profile['sYear'])
        print "<tr><td>Year: </td><td>{$profile['sYear']}</td></tr>";
if ($profile['sNickname'])
        print "<tr><td>Nickname: </td><td>{$profile['sNickname']}</td></tr>";
if ($profile['sHometown'])
        print "<tr><td>Hometown: </td><td>{$profile['sHometown']}</td></tr>";
if ($profile['isResident'])
        print "<tr><td>Lives on Campus: </td><td>Yes</td></tr>";
if (isset($profile['isResident']) && $profile['isResident'] == 0)
        print "<tr><td>Lives on Campus: </td><td>No</td></tr>";
if ($profile['sBio']) {
        print "<tr><td valign='top'>Biography: </td><td valign='top'><pre>{$profile['sBio']}</pre></td></tr>";
}
if ($profile['sSkills']) {
        print "<tr><td valign='top'>Skills: </td><td valign='top'><pre>{$profile['sSkills']}</pre></td></tr>";
}
?>
</table>
<br />
<h1>Administrator features</h1>
<?php
		print "<h2>Groups</h2>";
		if(count($grp['iGroupID']) > 0)
		{
			print "<ul>";
			foreach($grp['iGroupID'] as $id)
			{
				$group = mysql_fetch_array($db->igroupsQuery("SELECT * FROM Groups WHERE iID={$id}"));
				print "<li>".$group['sName']."</li>";
			}
			print "</ul>";
		}
		print "<h2>Projects</h2>";
		if(count($prj['iProjectID']) > 0)
		{
			print "<ul>";
			foreach($prj['iProjectID'] as $id)
			{
				$project = mysql_fetch_array($db->igroupsQuery("SELECT * FROM Projects WHERE iID={$id}"));
				print "<li>".$project['sIITID'].": ".$project['sName']."</li>";
			}
			print "</ul>";
		}
		print "<h2>Nuggets</h2>";
		if(count($nug['iNuggetID']) > 0)
		{
			print "<ul>";
			foreach($nug['iNuggetID'] as $id)
			{
				$nugget = mysql_fetch_array($db->igroupsQuery("SELECT * FROM Nuggets WHERE iID={$id}"));
				print "<li><a href=\"../viewNugget.php?nug=".$id."&amp;isOld=0\">".$nugget['sTitle']."</a></li>";
			}
			print "</ul>";
		}
	}
	else //if no email given in URL
	{
?>
	<form method="get" action="people.php">
		Email address query: <input type="text" name="email" /><input type="submit" name="Submit" />
	</form>
<?php
	}
?>
</div></body>
</html>

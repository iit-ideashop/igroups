<?php
	session_start();

	include_once( "classes/db.php" );
	include_once( "classes/person.php" );
	
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
	if (isset($_GET['uID'])) {
		if (is_numeric($_GET['uID'])) {
			$query = $db->igroupsQuery("SELECT * FROM Profiles WHERE iPersonID={$_GET['uID']}");
			$profile = mysql_fetch_array($query);
			if ($profile) {
			foreach ($profile as $key => $val)
				$profile[$key] = htmlspecialchars($profile[$key]);
			}
			$query = $db->iknowQuery("SELECT * FROM People WHERE iID={$_GET['uID']}");
			$contactInfo = mysql_fetch_array($query);
		}
	}
	else
		die("No profile selected.");

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<!-- This web-based application is Copyrighted &copy; 2008 Interprofessional Projects Program, Illinois Institute of Technology -->
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en"><head>
<title>iGroups - View Profile</title>
<link rel="stylesheet" href="default.css" type="text/css" />
</head>
<body>
<?php
require("sidebar.php");
?>
<div id="content"><h1>View User Profile</h1>
<h2><?php print "{$contactInfo['sFName']} {$contactInfo['sLName']}"; ?></h2>
<?php
if ($profile['sPicture']) {
	print "<img src=\"profile-pics/{$profile['sPicture']}\" alt=\"{$profile['sPicture']}\" width=\"200\" /><br />";
}
?>

<h4>Contact Information</h4>
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

<h4>Personal Information</h4>
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
<a href="contactlist.php">&lt;&lt;&lt; Back</a>
</div></body>
</html>

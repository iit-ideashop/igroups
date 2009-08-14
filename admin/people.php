<?php
	include_once('../globals.php');
	include_once('checkadmin.php');
	include_once('../classes/nugget.php');
	include_once('../classes/group.php');
	include_once('../classes/semester.php');
	
	//---------Start XHTML Output-----------------------------------//
	
	require('../doctype.php');
	require('../iknow/appearance.php');

	echo "<link rel=\"stylesheet\" href=\"../skins/$skin/default.css\" type=\"text/css\" title=\"$skin\" />\n";
	foreach($altskins as $altskin)
		echo "<link rel=\"alternate stylesheet\" href=\"../skins/$altskin/default.css\" type=\"text/css\" title=\"$altskin\" />\n";
?>
<title><?php echo $appname;?> - View Profile</title>
</head>
<body>
<?php
	require('sidebar.php');
	echo "<div id=\"content\">";
	if(isset($_GET['email']) || (isset($_GET['uid']) && is_numeric($_GET['uid'])))
	{
		if(isset($_GET['uid']) && is_numeric($_GET['uid']))
			$contactInfo = mysql_fetch_array($db->query("SELECT * FROM People WHERE iID={$_GET['uid']}"));
		else if(isset($_GET['email']))
			$contactInfo = mysql_fetch_array($db->query("SELECT * FROM People WHERE sEmail='{$_GET['email']}'"));
		if($contactInfo)
			$uid = $contactInfo['iID'];
		else
			die("No users matched that email address.");
		$profile = mysql_fetch_array($db->query("SELECT * FROM Profiles WHERE iPersonID={$uid}"));
		$query = $db->query("SELECT * FROM PeopleGroupMap WHERE iPersonID={$uid}");
		$groups = array();
		while($row = mysql_fetch_array($query))
			$groups[] = new Group($row['iGroupID'], 1, 0, $db);
		$query = $db->query("SELECT * FROM PeopleNuggetMap WHERE iPersonID={$uid} ORDER BY iNuggetID");
		$nuggets = array();
		while($row = mysql_fetch_array($query))
			$nuggets[] = new Nugget($row['iNuggetID'], $db, 1);
		$query = $db->query("SELECT * from nuggetAuthorMap WHERE iAuthorID={$uid} ORDER BY iNuggetID");
		$newnuggets = array();
		while($row = mysql_fetch_array($query))
			$newnuggets[] = new Nugget($row['iNuggetID'], $db, 0);
		$query = $db->query("SELECT * FROM PeopleProjectMap WHERE iPersonID={$uid} ORDER BY iSemesterID");
		while($row = mysql_fetch_array($query))
			$groups[] = new Group($row['iProjectID'], 0, $row['iSemesterID'], $db);
		$query = $db->query("SELECT * FROM UserTypes WHERE iID={$contactInfo['iUserTypeID']}");
		$row = mysql_fetch_array($query);
		$usertype = $row['sType'];
?>
<h1>Public Profile</h1>
<h2><?php echo "{$contactInfo['sFName']} {$contactInfo['sLName']}"; ?></h2>
<p><a href="editperson.php?id=<?php echo $uid; ?>">Edit this person</a></p>
<?php
if($profile['sPicture'])
	echo "<img src=\"../profile-pics/{$profile['sPicture']}\" width=\"200\" alt=\"{$profile['sPicture']}\" /><br />";
?>
<h3>Contact Information</h3>
<table cellspacing="5"> 
<?php
	echo "<tr><td>Primary E-mail: </td><td>{$contactInfo['sEmail']}</td></tr>\n";
	if($profile['sAltEmail'])
		echo "<tr><td>Alternate E-mail: </td><td>".htmlspecialchars($profile['sAltEmail'])."</td></tr>\n";
	if($profile['sPhone'])
		echo "<tr><td>Primary Phone: </td><td>".htmlspecialchars($profile['sPhone'])."</td></tr>\n";
	if($profile['sPhone2'])
		echo "<tr><td>Home/Other Phone: </td><td>".htmlspecialchars($profile['sPhone2'])."</td></tr>\n";
	if($profile['sIM'])
		echo "<tr><td>AIM Screen Name: </td><td>".htmlspecialchars($profile['sIM'])."</td></tr>\n";
?>
</table>
<h3>Personal Information</h3>
<table cellspacing="5">
<?php
	if($profile['sMajor'])
		echo "<tr><td>Major: </td><td>".htmlspecialchars($profile['sMajor'])."</td></tr>\n";
	if($profile['sYear'])
		echo "<tr><td>Year: </td><td>{$profile['sYear']}</td></tr>\n";
	if($profile['sNickname'])
		echo "<tr><td>Nickname: </td><td>".htmlspecialchars($profile['sNickname'])."</td></tr>\n";
	if($profile['sHometown'])
		echo "<tr><td>Hometown: </td><td>".htmlspecialchars($profile['sHometown'])."</td></tr>\n";
	if($profile['isResident'])
		echo "<tr><td>Lives on Campus: </td><td>Yes</td></tr>\n";
	if(isset($profile['isResident']) && $profile['isResident'] == 0)
		echo "<tr><td>Lives on Campus: </td><td>No</td></tr>\n";
	if($profile['sBio'])
		echo "<tr><td valign=\"top\">Biography: </td><td valign=\"top\">".str_replace("\n", "<br />", htmlspecialchars($profile['sBio']))."</td></tr>\n";
	if($profile['sSkills'])
		echo "<tr><td valign=\"top\">Skills: </td><td valign=\"top\">".str_replace("\n", "<br />", htmlspecialchars($profile['sSkills']))."</td></tr>\n";
?>
</table>
<br />
<h1>Administrator features</h1>
<?php
		echo "<h2>Additional user information</h2><table cellspacing=\"5\"><tr><td>Access level:</td><td>$usertype</td></tr><tr><td>Account created:</td><td>".$contactInfo['dCreateDate']."</td></tr><tr><td>User ID#:</td><td>$uid</td></tr></table>\n";
		echo "<h2>Groups</h2>\n";
		if(count($groups) > 0)
		{
			echo "<ul>";
			$currSem = false;
			foreach($groups as $group)
			{
				$sem = new Semester($group->getSemester(), $db);
				if(!$currSem)
				{
					if($sem->getName() == '')
						$semName = 'Global Groups';
					else
						$semName = $sem->getName();
					echo "<li><strong>".$semName."</strong><ul>\n";
					$currSem = $sem;
				}
				else if($currSem != $sem)
				{
					echo "</ul></li>\n<li><strong>".$sem->getName()."</strong><ul>\n";
					$currSem = $sem;
				}
				echo "<li><a href=\"group.php?group=".$group->getID()."&amp;semester=".$group->getSemester()."&amp;selectAdminGroup=true&amp;selectSemester=true\">".$group->getName()."</a></li>\n";
			}
			echo "</ul></li></ul>\n";
		}
		else
			echo "<p>This user is in no groups.</p>\n";
		echo "<h2>Nuggets</h2>\n";
		if((count($nuggets) + count($newnuggets)) > 0)
		{
			echo "<ul>";
			foreach($nuggets as $nugget)
			{
				echo "<li><a href=\"viewNugget.php?nuggetID=".$nugget->getID()."&amp;isOld=1\">".htmlspecialchars($nugget->getType())."</a></li>\n";
			}
			foreach($newnuggets as $nugget)
			{
				echo "<li><a href=\"viewNugget.php?nuggetID=".$nugget->getID()."\">".htmlspecialchars($nugget->getType())."</a></li>\n";
			}
			echo "</ul>\n";
		}
		else
			echo "<p>This user has authored no nuggets.</p>\n";
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
</div></body></html>

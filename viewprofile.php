<?php
	include_once('globals.php');
	include_once('checklogingroupless.php');
	if(isset($_GET['uID']) && is_numeric($_GET['uID']))
	{
		$query = $db->igroupsQuery("SELECT * FROM Profiles WHERE iPersonID={$_GET['uID']}");
		$profile = mysql_fetch_array($query);
		if($profile)
		{
			foreach($profile as $key => $val)
				$profile[$key] = htmlspecialchars($profile[$key]);
		}
		$query = $db->igroupsQuery("SELECT * FROM People WHERE iID={$_GET['uID']}");
		$contactInfo = mysql_fetch_array($query);
	}
	else
		errorPage('Invalid user ID', 'The user ID you have selected is invalid', 400);
	
	//----------Start XHTML Output----------------------------------//
		
	require('doctype.php');
	require('appearance.php');
	echo "<link rel=\"stylesheet\" href=\"skins/$skin/default.css\" type=\"text/css\" title=\"$skin\" />\n";
	foreach($altskins as $altskin)
		echo "<link rel=\"alternate stylesheet\" href=\"skins/$altskin/default.css\" type=\"text/css\" title=\"$altskin\" />\n";
?>
<title><?php echo $appname; ?> - View Profile</title>
</head>
<body>
<?php
	require('sidebar.php');
?>
<div id="content"><h1>View User Profile</h1>
<h2><?php echo "{$contactInfo['sFName']} {$contactInfo['sLName']}"; ?></h2>
<?php
	if($profile['sPicture'])
		echo "<img src=\"profile-pics/{$profile['sPicture']}\" alt=\"{$profile['sPicture']}\" width=\"200\" /><br />\n";
?>

<h4>Contact Information</h4>
<table cellspacing="5"> 
<?php
	echo "<tr><td>Primary E-mail:</td><td>{$contactInfo['sEmail']}</td></tr>";
	if($profile['sAltEmail'])
		echo "<tr><td>Alternate E-mail:</td><td>".htmlspecialchars($profile['sAltEmail'])."</td></tr>\n";
	if($profile['sPhone'])
		echo "<tr><td>Primary Phone:</td><td>".htmlspecialchars($profile['sPhone'])."</td></tr>\n";
	if($profile['sPhone2'])
		echo "<tr><td>Home/Other Phone:</td><td>".htmlspecialchars($profile['sPhone2'])."</td></tr>\n";
	if($profile['sIM'])
		echo "<tr><td>AIM Screen Name:</td><td>".htmlspecialchars($profile['sIM'])."</td></tr>\n";
?>
</table>

<h4>Personal Information</h4>
<table cellspacing="5">
<?php
	if($profile['sMajor'])
		echo "<tr><td>Major:</td><td>".htmlspecialchars($profile['sMajor'])."</td></tr>\n";
	if($profile['sYear'])
		echo "<tr><td>Year:</td><td>{$profile['sYear']}</td></tr>\n";
	if($profile['sNickname'])
		echo "<tr><td>Nickname:</td><td>".htmlspecialchars($profile['sNickname'])."</td></tr>\n";
	if($profile['sHometown'])
		echo "<tr><td>Hometown:</td><td>".htmlspecialchars($profile['sHometown'])."</td></tr>\n";
	if($profile['isResident'])
		echo "<tr><td>Lives on Campus:</td><td>Yes</td></tr>\n";
	if(isset($profile['isResident']) && $profile['isResident'] == 0)
		echo "<tr><td>Lives on Campus:</td><td>No</td></tr>\n";
	if($profile['sBio'])
		echo "<tr><td valign=\"top\">Biography:</td><td valign=\"top\">".str_replace("\n", "<br />", htmlspecialchars($profile['sBio']))."</td></tr>\n";
	if($profile['sSkills'])
		echo "<tr><td valign=\"top\">Skills:</td><td valign=\"top\">".str_replace("\n", "<br />", htmlspecialchars($profile['sSkills']))."</td></tr>\n";
?>
</table>
<br />
<a href="contactlist.php">&lt;&lt;&lt; Back</a>
</div></body></html>

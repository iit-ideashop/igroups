<?php
	include_once('globals.php');
	include_once('checklogin.php');
		
	function peopleSort($array)
	{
		$newArray = array();
		foreach($array as $person)
			$newArray[$person->getCommaName()] = $person;
		ksort($newArray);
		return $newArray;
	}
	
	//------Start XHTML Output--------------------------------------//
	
	require('doctype.php');
	require('appearance.php');
	echo "<link rel=\"stylesheet\" href=\"skins/$skin/default.css\" type=\"text/css\" title=\"$skin\" />\n";
	foreach($altskins as $altskin)
		echo "<link rel=\"alternate stylesheet\" href=\"skins/$altskin/default.css\" type=\"text/css\" title=\"$altskin\" />\n";
?>
<title><?php echo $appname; ?> - Contact List</title>
<script type="text/javascript" src="ChangeLocation.js"></script>
</head>
<body>	
<?php
	require('sidebar.php');
	echo "<div id=\"content\"><div id=\"topbanner\">{$currentGroup->getName()}</div>\n";
?>
	<table cellpadding="2">
	<thead>
		<tr><td colspan="5">Team Roster</td></tr>
		<tr><td>Name</td><td>Email</td><td>Phone #</td><td>Alt. Phone #</td><td>AIM</td></tr>
	</thead>
<?php
	$members = $currentGroup->getAllGroupMembers();
	$members = peopleSort($members);
	foreach($members as $person)
	{
		$profile = $person->getProfile();
		printTR();
		echo "<td><a href=\"viewprofile.php?uID={$person->getID()}\">".$person->getCommaName()."</a></td>";
		echo "<td>".$person->getEmail()."</td>";
		echo "<td>".htmlspecialchars($profile['sPhone'])."</td>";
		echo "<td>".htmlspecialchars($profile['sPhone2'])."</td>";
		echo "<td>".htmlspecialchars($profile['sIM'])."</td>";
		echo "</tr>\n";
	}
?>
	</table>
	<br /><br />
<?php
	$subgroups = $currentGroup->getSubGroups();
	if(count($subgroups) > 0)
	{
		echo "<table><tr>";
		foreach($subgroups as $subgroup)
		{
			$members = $subgroup->getSubGroupMembers();
			echo "<td style=\"vertical-align: top; border: thin solid black;\"><table><tr><th align=\"left\">".htmlspecialchars($subgroup->getName())."</th></tr>\n";
			foreach ($members as $member)
				echo "<tr><td>{$member->getFullName()}</td></tr>\n";
			echo "</table></td>";
		}
		echo "</tr></table>";
	}
?>
<p><a href="contactinfo.php">Update your contact information</a></p>
</div></body>
</html>

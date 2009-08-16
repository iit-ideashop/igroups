<?php
	include_once('../globals.php');
	include_once('checkadmin.php');
	include_once('../classes/nugget.php');
	include_once('../classes/group.php');
	include_once('../classes/semester.php');

	if(isset($_GET['selectSemester']))
		$_SESSION['selectedIPROSemester'] = $_GET['semester'];
	
	if(!isset($_SESSION['selectedIPROSemester']))
	{
		$semester = $db->query('SELECT iID FROM Semesters WHERE bActiveFlag=1');
		$row = mysql_fetch_row($semester);
		$_SESSION['selectedIPROSemester'] = $row[0];
	}
	
	if($_SESSION['selectedIPROSemester'] != 0)
		$currentSemester = new Semester($_SESSION['selectedIPROSemester'], $db);
	else
		$currentSemester = 0;
	
	function groupSort($array)
	{
		$newArray = array();
		foreach($array as $group)
		{
			if($group)
				$newArray[$group->getName()] = $group;
		}
		ksort($newArray);
		return $newArray;
	}

	//---------Start XHTML Output-----------------------------------//
	
	require('../doctype.php');
	require('../iknow/appearance.php');
	
	echo "<link rel=\"stylesheet\" href=\"../skins/$skin/default.css\" type=\"text/css\" title=\"$skin\" />\n";
	foreach($altskins as $altskin)
		echo "<link rel=\"alternate stylesheet\" href=\"../skins/$altskin/default.css\" type=\"text/css\" title=\"$altskin\" />\n";
?>
<title><?php echo $appname;?> - IPRO Team Nuggets</title>
</head>
<body>
<?php
	require('sidebar.php');
	echo '<div id="content"><div id="topbanner">';
	if($currentSemester)
		echo $currentSemester->getName();
	else
		echo 'All iGROUPS';
?>
	</div>
	<div id="semesterSelect">
	<form method="get" action="nuggets.php"><fieldset>
	<select name="semester">
<?php
	$semesters = $db->query( "SELECT iID FROM Semesters ORDER BY iID DESC" );
	while($row = mysql_fetch_row($semesters))
	{
		$semester = new Semester($row[0], $db);
		if($currentSemester && $currentSemester->getID() == $semester->getID())
			echo "<option value=\"".$semester->getID()."\" selected=\"selected\">".$semester->getName()."</option>\n";
		else
			echo "<option value=\"".$semester->getID()."\">".$semester->getName()."</option>\n";
	}
	if(!$currentSemester)
		echo "<option value=\"0\" selected=\"selected\">All iGROUPS</option>";
	else
		echo "<option value=\"0\">All iGROUPS</option>";
?>
	</select>
	<input type="submit" name="selectSemester" value="Select Semester" />
	</fieldset></form>
	</div>
	
	<p style="text-align: center; font-style: italic">* Signifies a protected nugget</p>

	<table cellpadding="4" cellspacing="0" style="border: thin solid black">
	<thead>
	<tr><td>Group</td><td>Project Plan</td><td>Abstract</td><td>Code of Ethics</td><td>Midterm Report</td><td>Poster</td><td>Website</td><td>Final Presentation</td><td>Meeting Minutes</td><td>Final Report</td></tr>
	</thead>
	<tfoot>
	<tr><td>Group</td><td>Project Plan</td><td>Abstract</td><td>Code of Ethics</td><td>Midterm Report</td><td>Poster</td><td>Website</td><td>Final Presentation</td><td>Meeting Minutes</td><td>Final Report</td></tr></tfoot>
	<tbody>
<?php
	$groups = $currentSemester->getGroups();
	$groups = groupSort($groups);

	foreach($groups as $group)
	{
		echo "<tr><td><a href=\"viewIproNuggets.php?id={$group->getID()}\">{$group->getName()}</a></td>";
		if($currentSemester->getID() >= 32)
		{
			$_DEFAULTNUGGETS = array('Project Plan', 'Abstract', 'Code of Ethics', 'Midterm Report', 'Poster', 'Website', 'Final Presentation', 'Team Minutes', 'Final Report');
			$nuggets = getNuggetStatus($group, $currentSemester->getID());
			if($nuggets['Project Plan'] != 0)
			{
				$nug = new Nugget($nuggets['Project Plan'], $db, 0);
				if($nug->isPrivate())
					$priv = '*';
				else
					$priv = '';
				echo "<td align=\"center\"><a href=\"viewNugget.php?nuggetID={$nuggets['Project Plan']}&amp;groupID={$group->getID()}\">View$priv</a></td>";
			}
			else
				echo "<td align=\"center\"><b>N/A</b></td>";
			if($nuggets['Abstract'] != 0)
			{
				$nug = new Nugget($nuggets['Abstract'], $db, 0);
				if($nug->isPrivate())
					$priv = '*';
				else
					$priv = '';
				echo "<td align=\"center\"><a href=\"viewNugget.php?nuggetID={$nuggets['Abstract']}&amp;groupID={$group->getID()}\">View$priv</a></td>";
			}
			else
				echo "<td align=\"center\"><b>N/A</b></td>";
			if($nuggets['Code of Ethics'] != 0)
			{
				$nug = new Nugget($nuggets['Code of Ethics'], $db, 0);
				if($nug->isPrivate())
					$priv = '*';
				else
					$priv = '';
				echo "<td align=\"center\"><a href=\"viewNugget.php?nuggetID={$nuggets['Code of Ethics']}&amp;groupID={$group->getID()}\">View$priv</a></td>";
			}
			else
				echo "<td align=\"center\"><b>N/A</b></td>";
			if($nuggets['Midterm Report'] != 0)
			{
				$nug = new Nugget($nuggets['Midterm Report'], $db, 0);
				if($nug->isPrivate())
					$priv = '*';
				else
					$priv = '';
				echo "<td align=\"center\"><a href=\"viewNugget.php?nuggetID={$nuggets['Midterm Report']}&amp;groupID={$group->getID()}\">View$priv</a></td>";
			}
			else
				echo "<td align=\"center\"><b>N/A</b></td>";
			if($nuggets['Poster'] != 0)
			{
				$nug = new Nugget($nuggets['Poster'], $db, 0);
				if($nug->isPrivate())
					$priv = '*';
				else
					$priv = '';
				echo "<td align=\"center\"><a href=\"viewNugget.php?nuggetID={$nuggets['Poster']}&amp;groupID={$group->getID()}\">View$priv</a></td>";			
			}
			else
				echo "<td align=\"center\"><b>N/A</b></td>";
			if($nuggets['Website'] != 0)
			{
				$nug = new Nugget($nuggets['Website'], $db, 0);
				if($nug->isPrivate())
					$priv = '*';
				else
					$priv = '';
				echo "<td align=\"center\"><a href=\"viewNugget.php?nuggetID={$nuggets['Website']}&amp;groupID={$group->getID()}\">View$priv</a></td>";
			}
			else
				echo "<td align=\"center\"><b>N/A</b></td>";
			if($nuggets['Final Presentation'] != 0)
			{
				$nug = new Nugget($nuggets['Final Presentation'], $db, 0);
				if($nug->isPrivate())
					$priv = '*';
				else
					$priv = '';
				echo "<td align=\"center\"><a href=\"viewNugget.php?nuggetID={$nuggets['Final Presentation']}&amp;groupID={$group->getID()}\">View$priv</a></td>";
			}
			else
				echo "<td align=\"center\"><b>N/A</b></td>";
			if($nuggets['Team Minutes'] != 0)
			{
				$nug = new Nugget($nuggets['Team Minutes'], $db, 0);
				if($nug->isPrivate())
					$priv = '*';
				else
					$priv = '';
				echo "<td align=\"center\"><a href=\"viewNugget.php?nuggetID={$nuggets['Team Minutes']}&amp;groupID={$group->getID()}\">View$priv</a></td>";
			}
			else
				echo "<td align=\"center\"><b>N/A</b></td>";
			if($nuggets['Final Report'] != 0)
			{
				$nug = new Nugget($nuggets['Final Report'], $db, 0);
				if($nug->isPrivate())
					$priv = '*';
				else
					$priv = '';
				echo "<td align=\"center\"><a href=\"viewNugget.php?nuggetID={$nuggets['Final Report']}&amp;groupID={$group->getID()}\">View$priv</a></td>";
			}
			else
				echo "<td align=\"center\"><b>N/A</b></td>";
		}
		else
		{
			$_DEFAULTNUGGETS = array('Project Plan', 'Abstract', 'Code of Ethics', 'Midterm Report', 'Poster', 'Web Site', 'Final Presentation', 'Team Minutes', 'Final Report');
			$nuggets = getOldNuggetsByGroupAndSemester($group, $currentSemester->getID());
			foreach($_DEFAULTNUGGETS as $def)
			{
				$link = null;
				foreach($nuggets as $nug)
				{
					if($nug->isPrivate())
						$priv = '*';
					else
						$priv = '';
					if(strstr($nug->getType(), $def))
						$link = "<a href=\"viewNugget.php?nuggetID={$nug->getID()}&amp;groupID={$group->getID()}&amp;isOld=1\">View$priv</a>&nbsp;";
				}	
				if(!$link)
					$link = "<b>N/A</b>";
				echo "<td align=\"center\">$link</td>";
			}
		}
		echo "</tr>\n";
	}
?>
</tbody></table></div></body></html>

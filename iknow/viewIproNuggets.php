<?php
	session_start();
	include_once('../globals.php');
	include_once('../classes/person.php');
	include_once('../classes/nugget.php');
	include_once('../classes/group.php');
	include_once('../classes/db.php');
	include_once('../nuggetTypes.php');

	if(is_numeric($_SESSION['userID']))
		$currentUser = new Person($_SESSION['userID'], $db);

	function displayNuggets($currentGroup, $semID, $db)
	{
		global $_DEFAULTNUGGETS, $skin;
		$query = $db->query("SELECT sSemester FROM Semesters where iID=$semID");
		$row = mysql_fetch_row($query);
		echo "<h1>{$row[0]} Deliverable Nuggets</h1>";

		//Get the list of nuggets
		if($semID < 32)
			$nuggets = getOldNuggetsByGroupAndSemester($currentGroup, $semID);
		else
			$nuggets = getNuggetStatus($currentGroup, $semID);
		$nugCount = 0;
		echo "<table cellpadding=\"3\"><tr>";

		if($semID >= 32)
		{
			foreach($_DEFAULTNUGGETS as $nug)
			{
				if($nugCount == 2)
				{
					echo "</tr><tr>";
					$nugCount = 0;
				}
				if($nuggets[$nug] != 0)
				{
					$gID = $currentGroup->getID();
					echo "<td><img src=\"../skins/$skin/img/upload.png\" alt=\"Y\" />&nbsp;$nug</td><td><a href='viewNugget.php?nuggetID=".$nuggets[$nug]."&amp;groupID=$gID'>View</a></td>";
				}
				else
					echo "<td><img src=\"../skins/$skin/img/no_upload.png\" alt=\"N\" />&nbsp;".$nug."</td><td>Not Uploaded</td>";
				$nugCount++;
			}
		}
		//iKnow nuggets
		else
		{
			foreach($_DEFAULTNUGGETS as $def)
			{
				if($nugCount == 2)
				{
					echo "</tr>\n<tr>";
					$nugCount = 0;
				}

				$link = null;
				if($def == 'Website')
					$def = 'Web Site';

				foreach($nuggets as $nug)
				{
					if($nugCount == 2)
					{
						$link .= "</tr>\n<tr>";
						$nugCount = 0;
					}				
					$id = $nug->getID();
					$type = $nug->getType();
					$gID = $currentGroup->getID();
					if(strstr($type, $def))
					{
						$link .= "<td><img src=\"../skins/$skin/img/upload.png\" alt=\"Y\" />&nbsp;$def</td><td><a href=\"viewNugget.php?nuggetID=$id&amp;groupID=$gID&amp;isOld=1\">View</a></td>";
						$nugCount++;
					}
				}
				if(!$link)
				{
					$link = "<td><img src=\"../skins/$skin/img/no_upload.png\" alt=\"N\" />&nbsp;$def</td><td>Not Uploaded</td>";
					$nugCount++;
				}
				echo "$link";
			}
		}
		echo "</tr></table>\n";
	}

	function displayNonDefaultNuggets($currentGroup, $semID, $db)
	{
		$query = $db->query("SELECT sSemester FROM Semesters where iID=$semID");
		$row = mysql_fetch_row($query);
		echo "<h1>{$row[0]} Non-Deliverable Nuggets</h1>";

		if($semID >= 32)
		{
			$nuggets = allActiveByTypeandID("Other", $currentGroup->getID(), $currentGroup->getSemester());
			if(count($nuggets) > 0)
			{
				echo '<table>';
				foreach($nuggets as $nugget)
					echo "<tr><td><a href=\"viewNugget.php?nuggetID=".($nugget->getID())."\">".($nugget->getType())."</a></td></tr>";
				echo "</table><br />\n";
			}
			else
				echo "<p>There are currently no Non-Deliverable Nuggets for this semester.</p>\n";
		}
		else
		{
			global $_DEFAULTNUGGETS;
			$nuggets = getOldNuggetsByGroupAndSemester($currentGroup, $currentGroup->getSemester());
			if(count($nuggets) > 0)
			{
				echo "<table><tr><td></td></tr>\n";
				$nugs = false;
				foreach($nuggets as $nug)
				{
			       		$found = false;
					$id = $nug->getID();
					$type = $nug->getType();

					foreach($_DEFAULTNUGGETS as $def)
					{
						if ($def == 'Website')
							$def = 'Web Site';
						if(strstr($type, $def))
							$found = true;
					}
					if(!$found)
					{
						echo "<tr><td><a href=\"viewNugget.php?nuggetID=$id&amp;isOld=1\">$type</a></td></tr>";
						$nugs = true;
					}
				}
				echo "</table>\n";
			}
			else
				echo "<p>There are currently no Non-Deliverable Nuggets for this semester.</p>\n";
		}
	}

	function displayOldNuggets($currentGroup)
	{
		$oldNuggets = $currentGroup->getInactiveNuggets();
		echo "<h1>Other Semesters Nuggets</h1>";
		if(count($oldNuggets)!= 0)
		{
			echo '<table><tr>';
			$nuggetCount = 0;
			foreach($oldNuggets as $tempNugget)
			{
				if($nuggetCount == 2)
				{
					echo "</tr>\n<tr>";
					$nuggetCount = 0;
				}
				echo "<td>";
				echo "<a href=\"viewNugget.php?nuggetID=".$tempNugget->getID()."&amp;old=".$tempNugget->isOld()."\">".$tempNugget->getType()."</a>";
				echo "</td>";
				$nuggetCount++;
			}
			echo "</tr></table>\n";
		}
		else
			echo "There are no previous nuggets created with the igroups nugget system.<br />\n";
	}
	
	if(!is_numeric($_GET['id']))
		errorPage('Select an IPRO', 'You must select an IPRO to browse', 400);
	
	//------------Start XHTML Output--------------------------------//
	
	require('../doctype.php');
	require('appearance.php');
	echo "<link rel=\"stylesheet\" href=\"../skins/$skin/nuggets.css\" type=\"text/css\" title=\"$skin\" />\n";
	foreach($altskins as $altskin)
		echo "<link rel=\"alternate stylesheet\" href=\"../skins/$altskin/nuggets.css\" type=\"text/css\" title=\"$altskin\" />\n";
?>
<title><?php echo $appname; ?> - Nuggets</title>
</head>
<body>
<?php
	require('sidebar.php');
	echo "<div id=\"content\">";
	//Prints all notifications
	
	$id = $_GET['id'];
	$sem = $db->query("Select iSemesterID FROM ProjectSemesterMap WHERE iProjectID=$id ORDER BY iSemesterID DESC");
	$row = mysql_fetch_row($sem);
	$semID = $row[0];
	$currentGroup = new Group($_GET['id'], 0, $semID, $db);
	echo "<h2>{$currentGroup->getName()}</h2>\n";
	echo "<h3>{$currentGroup->getDesc()}</h3>\n";
	displayNuggets($currentGroup, $semID, $db);
	echo "<br />\n";
	displayNonDefaultNuggets($currentGroup, $semID, $db);
	echo "<br /><a href=\"main.php\">Back</a>\n";
?>
<br /><br /></div></body></html>

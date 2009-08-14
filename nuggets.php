<?php
	include_once('globals.php');
	include_once('checklogin.php');
	include_once('classes/nugget.php');
	include_once('classes/semester.php');
	include_once('classes/file.php');
	include_once('classes/quota.php');
	include_once('nuggetTypes.php');

	global $_DEFAULTNUGGETS;

	function displayNuggets($currentGroup)
	{
		global $_DEFAULTNUGGETS, $skin, $db;
		echo "<h1>Current Semester's IPRO Deliverable Nuggets</h1>\n";
							
		//Get the list of nuggets
		if($_SESSION['selectedSemester'] < 32)
			$nuggets = getOldNuggetsByGroupAndSemester($currentGroup, $_SESSION['selectedSemester']);
		else
		{
			$nuggets = getNuggetStatus($currentGroup, $_SESSION['selectedSemester']);
			$currsem = new Semester($_SESSION['selectedSemester'], $db);
			$active = $currsem->isActive();
		}
		$nugCount = 0;
?>
		<table cellpadding="3">
		<tr>
<?php
		//nuggets is an associative array that has the various nugget types for keys and their id's if they exists, as values
		//iGroups 2.1 nuggets
		if($_SESSION['selectedSemester'] >= 32)
		{
			foreach($_DEFAULTNUGGETS as $nug)
			{
				if($nug == 'Code of Ethics')
					$nugprint = 'Ethics Statement';
				else if($nug == 'Website')
					$nugprint = 'Website (optional)';
				else if($nug == 'Midterm Report')
					$nugprint = 'Midterm Presentation';
				else if($nug == 'Team Minutes')
					$nugprint = 'Team Minutes (optional)';
				else if($nug == 'Final Report')
					$nugprint = 'Final Report or Grant Proposal';
				else
					$nugprint = $nug;
				if($nugCount == 2)
				{
					echo "</tr>\n<tr>";
					$nugCount = 0;
				}
			
				if($nuggets[$nug] != 0 && $active)
					echo "<td><img src=\"skins/$skin/img/upload.png\" alt=\"Y\" title=\"$nugprint has been uploaded\" />&nbsp;<a href=\"viewNugget.php?nug={$nuggets[$nug]}\">$nugprint</a></td><td><a href=\"editNugget.php?edit=true&amp;nugID={$nuggets[$nug]}\">Edit</a></td>";
				else if($active)
					echo "<td><img src=\"skins/$skin/img/no_upload.png\" alt=\"N\" title=\"$nugprint not uploaded\" />&nbsp;$nugprint</td><td><a href=\"addNugget.php?type=$nug\">Add Nugget</a></td>";
				else if($nuggets[$nug] != 0)
					echo "<td><img src=\"skins/$skin/img/upload.png\" alt=\"Y\" title=\"$nugprint has been uploaded\" />&nbsp;<a href=\"viewNugget.php?nug={$nuggets[$nug]}\">$nugprint</a></td><td><a href=\"viewNugget.php?nug={$nuggets[$nug]}\">View</a></td>";
				else
					echo "<td><img src=\"skins/$skin/img/no_upload.png\" alt=\"N\" title=\"$nugprint not uploaded\" />&nbsp;$nugprint</td><td>Not Uploaded</td>";
				$nugCount++;
			}
		}
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
				if ($def == 'Website')
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
					if(strstr($type, $def))
					{
						$link .= "<td><img src=\"skins/$skin/img/upload.png\" alt=\"Y\" title=\"Uploaded\" />&nbsp;$def</td><td><a href=\"viewNugget.php?nug=$id&amp;isOld=1\">View</a></td>";
						$nugCount++;
					}
				}
				if(!$link)
				{
					$link = "<td><img src=\"skins/$skin/img/no_upload.png\" alt=\"N\" title=\"Not uploaded\" />&nbsp;$def</td><td>Not Uploaded</td>";
					$nugCount++;
				}
				echo $link;
			}
		}
		echo "</tr></table>\n";
	}
	
	function displayNonDefaultNuggets($currentGroup)
	{
		global $_DEFAULTNUGGETS, $db;
		echo "<h1>Current Semester's Non-Deliverable Nuggets</h1>\n";
		if($_SESSION['selectedSemester'] >= 32)
		{
			$currsem = new Semester($_SESSION['selectedSemester'], $db);
			$active = $currsem->isActive();
			$nuggets = allActiveByTypeandID("Other", $currentGroup->getID(), $_SESSION['selectedSemester']);
			if(count($nuggets) > 0)
			{
				echo "<table cellpadding=\"3\">\n";
				echo "<tr><th>Name</th><th>Edit/Delete</th></tr>\n";
				foreach($nuggets as $nugget)
				{
					echo "<tr>";
					printNugPreview($nugget, $active);
					echo "</tr>";
				}
				echo "</table>\n";
				echo "<br />";
				if($active)
					echo "<a href=\"addNugget.php?type=Other\">Start a new Non-Deliverable Nugget</a>";		
			}
			else
			{
				echo "There are currently no Non-deliverable Nuggets for this semester<br />";
				if($active)
					echo "<a href=\"addNugget.php?type=Other\">Start a new Non-Deliverable Nugget</a>";
			}
		}
		// display iKnow non-default nuggets
		else
		{
			$nuggets = getOldNuggetsByGroupAndSemester($currentGroup, $_SESSION['selectedSemester']);
			echo "<table cellpadding=\"3\">";
			$nugs = false;
			foreach($nuggets as $nug)
			{

				$found = false;
				$id = $nug->getID();
				$type = $nug->getType();

				foreach($_DEFAULTNUGGETS as $def)
				{
					if($def == 'Website')
						$def = 'Web Site';
					if(strstr($type, $def))
						$found = true;
				}
				if(!$found)
				{
					echo "<tr><td><a href=\"viewNugget.php?nug=$id&amp;isOld=1\">$type</a></td></tr>\n";
					$nugCount++;
					$nugs = true;
				}
				else
					echo "<tr><td></td></tr>\n";
			}	
			echo "</table>";
			if (!$nugs)
				echo "There are currently no Non-Deliverable Nuggets for this semester<br />";
		}
	}
	
	function displayOldNuggets($currentGroup)
	{
		$oldNuggets = $currentGroup->getInactiveNuggets();
		echo "<h1>Other Semesters' Nuggets</h1>";
		if(count($oldNuggets)!= 0)
		{
			echo "<table><tr>\n";
			$nuggetCount = 0;
			foreach($oldNuggets as $tempNugget)
			{
				if($nuggetCount == 2)
				{
					echo "</tr>\n<tr>";
					$nuggetCount = 0;
				}
				echo "<td>";
				echo "<a href=\"viewNugget.php?nug=".$tempNugget->getID()."\">".$tempNugget->getType()."</a>";
				echo "</td>";
				$nuggetCount++;
			}
			echo "</tr></table>\n";
		}
		else
			echo "There are no previous nuggets created with the iGroups nugget system.<br />";
		echo "To display nuggets prior to 3.0 release date visit <a href='http://iknow.iit.edu'>http://iknow.iit.edu</a>";
	}
	
	function printNugPreview($nugget, $active)
	{
		$title = $nugget->getType();
		$desc = $nugget->getDescShort();
		$id = $nugget->getID();
		$status = $nugget->getStatus();
		echo "<td><a href=\"viewNugget.php?nug=$id\">".$title."</a></td><td>";
		if($active)
			echo "<a href=\"editNugget.php?edit=true&amp;nugID=$id\">Edit</a></td>";
		else
			echo '</td>';
	}
	
	function printNuggetNoEdit($nugget)
	{
		$authors = $nugget->getAuthors();
		$files = $nugget->getFiles();
		$semester = $nugget->getSemester();
		
		echo "<div class='item'><strong>Nugget Type/Name:</strong>";
		echo $nugget->getType()."</div>";
		echo '<div class="item"><strong>Description:</strong> '.substr($nugget->getDesc(),0,40).'</div>';
		echo '<div class="item"><strong>Date Created:</strong> '.$nugget->getDate().'</div>';
		echo '<div class="item"><strong>Authors:</strong> <br />';
		
		if(count($authors) > 0)
		{
			echo '<ul>';
			foreach($authors as $author)
			{
				//checks to see if the author is the primary author
				if($nugget->isPrimaryAuthor($author->getID()))
				{
					//if it is, the following parameter will be added to its radio button
					$toCheck = 'checked';
				}
				else
				{//or nothing will be added
					$toCheck = '';
				}
				//print the author name
				echo '<li>';
				echo $author->getFullName();
				if($toCheck == 'checked')
					echo '&nbsp;&nbsp;&nbsp;<strong>Primary Author</strong>';
				echo '</li>';
				
			}
			echo '</ul></div>';
		}
		else
			echo "There are currently no authors for this nugget.</div>";
		echo '<div class="item"><strong>Files:</strong>'.'<br />';
		
		if(count($files) > 0)
		{
			echo '<ul>';
			foreach($files as $file)
			{
				echo '<li>';
				echo '<a href="/igroups/download.php?id='.$file->getID().'">'.$file->getNameNoVer().'</a>&nbsp;';
				echo '</li>';
			}
			echo '</ul></div>';
		}
		else
			echo "There are no files for this nugget.</div>";
	}
	
	//----------Start XHTML Output----------------------------------//
	
	require('doctype.php');
	require('appearance.php');
	echo "<link rel=\"stylesheet\" href=\"skins/$skin/nuggets.css\" type=\"text/css\" title=\"$skin\" />\n";
	foreach($altskins as $altskin)
		echo "<link rel=\"alternate stylesheet\" href=\"skins/$altskin/nuggets.css\" type=\"text/css\" title=\"$altskin\" />\n";
?>
<title><?php echo $appname; ?> - Nuggets</title>
<script type="text/javascript">
//<![CDATA[
	function nuggetRedirect(nugget){
		form = document.getElementById("redirectForm");
		form.nuggetType.value= nugget;
		form.submit();
	}
//]]>		
</script>
</head>
<body>
<?php
	require('sidebar.php');
	echo "<div id=\"content\"><div id=\"topbanner\">";
	echo $currentGroup->getName()."</div>";
	displayNuggets($currentGroup);
	echo "<br />";
	displayNonDefaultNuggets($currentGroup);
	echo "<br />";
	//displayOldNuggets($currentGroup);
?>
</div></body></html>

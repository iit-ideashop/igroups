<?php
	session_start();

	include_once( "classes/db.php" );
	include_once( "classes/person.php" );
	include_once( "classes/group.php" );
	include_once( "classes/nugget.php" );
	include_once( "classes/semester.php" );
	include_once( "classes/file.php" );
	include_once( "classes/quota.php" );
	include_once( "nuggetTypes.php" );

	$db = new dbConnection();
	global $_DEFAULTNUGGETS;
	
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

	if ( isset($_SESSION['selectedGroup']) && isset($_SESSION['selectedGroupType']) && isset($_SESSION['selectedSemester']) ){
		$currentGroup = new Group( $_SESSION['selectedGroup'], $_SESSION['selectedGroupType'], $_SESSION['selectedSemester'], $db );
		$_SESSION['currentGroup']=$currentGroup;
	}
	else{
		die("You have not selected a valid group.");
	}

	function displayNuggets($currentGroup){
		global $_DEFAULTNUGGETS;
?>
		<h1>Current Semester's IPRO Deliverable Nuggets</h1>
<?php
							
		//Get the list of nuggets
		if ($_SESSION['selectedSemester'] < 32)
			$nuggets = getOldNuggetsByGroupAndSemester($currentGroup, $_SESSION['selectedSemester']);
		else
			$nuggets = getNuggetStatus($currentGroup, $_SESSION['selectedSemester']);
		$nugCount = 0;
?>
		<table cellpadding="3">
		<tr>
<?php
		//nuggets is an associative array that has the various nugget types for keys and their id's if they exists, as values
		//iGroups 2.1 nuggets
		if ($_SESSION['selectedSemester'] >= 32) {
		foreach($_DEFAULTNUGGETS as $nug){
			if($nug == "Code of Ethics")
				$nugprint = "Ethics Statement";
			else if($nug == "Website")
				$nugprint = "Website (optional)";
			else if($nug == "Midterm Report")
				$nugprint = "Midterm Presentation";
			else if($nug == "Team Minutes")
				$nugprint = "Team Minutes (optional)";
			else if($nug == "Final Report")
				$nugprint = "Final Report or Grant Proposal";
			else
				$nugprint = $nug;
			if($nugCount == 2){
				print "</tr><tr>";
				$nugCount = 0;
			}
			
			if($nuggets[$nug] != 0){
				print "<td><img src=\"img/upload.png\" alt=\"Y\" title=\"$nugprint has been uploaded\" />&nbsp;<a href=\"viewNugget.php?nug=".$nuggets[$nug]."\">".$nugprint."</a></td><td><a href=\"editNugget.php?edit=true&amp;nugID=".$nuggets[$nug]."\">Edit</a></td>";
			}else{
				print "<td><img src=\"img/no_upload.png\" alt=\"N\" title=\"$nugprint not uploaded\" />&nbsp;".$nugprint."</td><td><a href=\"addNugget.php?type=".$nug."\">Add Nugget</a></td>";
			}
			$nugCount++;
		}
		}
		//iKnow nuggets
		else {		
			foreach($_DEFAULTNUGGETS as $def){
			if($nugCount == 2){
				print "</tr><tr>";
				$nugCount = 0;
			}

			$link = null;
			if ($def == "Website")
				$def = "Web Site";

			foreach($nuggets as $nug) {
				if($nugCount == 2){
					$link .= "</tr><tr>";
					$nugCount = 0;
				}
				$id = $nug->getID();
				$type = $nug->getType();
				if(strstr($type, $def)){
					$link .= "<td><img src=\"img/upload.png\" alt=\"Y\" title=\"Uploaded\" />&nbsp;$def</td><td><a href=\"viewNugget.php?nug=$id&amp;isOld=1\">View</a></td>";
					$nugCount++;
				}
			}
			if (!$link) {
				$link = "<td><img src=\"img/no_upload.png\" alt=\"N\" title=\"Not uploaded\" />&nbsp;$def</td><td>Not Uploaded</td>";
				$nugCount++;
			}
			print "$link";
			
			}
		}

?>
		</tr>
		</table>
<?php
		
	}
	
	function displayNonDefaultNuggets($currentGroup){
		global $_DEFAULTNUGGETS;
?>
		<h1> Current Semester's Non-Deliverable Nuggets </h1>
<?php
		if ($_SESSION['selectedSemester'] >= 32) {
		$nuggets = allActiveByTypeandID("Other", $currentGroup->getID(), $_SESSION['selectedSemester']);
		if(count($nuggets) > 0){
?>
			<table cellpadding='3'>
			<tr><th>Name</th><th>Edit/Delete</th></tr>
<?php
			foreach($nuggets as $nugget){
				print "<tr>";
				printNugPreview($nugget);
				print "</tr>";
			}
?>
			</table>
			
<?php
			print "<br />";

			print "<a href=\"addNugget.php?type=Other\">Start a new Non-Deliverable Nugget</a>";
			
		}else{
			print "There are currently no Non-deliverable Nuggets for this semester<br />";
			print "<a href=\"addNugget.php?type=Other\">Start a new Non-Deliverable Nugget</a>";
		}
		}
		// display iKnow non-default nuggets
		else {
			$nuggets = getOldNuggetsByGroupAndSemester($currentGroup, $_SESSION['selectedSemester']);
			print "<table cellpadding=\"3\">";
			$nugs = false;
			foreach($nuggets as $nug){

			$found = false;
			$id = $nug->getID();
			$type = $nug->getType();

			foreach($_DEFAULTNUGGETS as $def) {
				if ($def == "Website")
					$def = "Web Site";
				if(strstr($type, $def)){
					$found = true;
				}
			}
			if (!$found) {
				print "<tr><td><a href=\"viewNugget.php?nug=$id&amp;isOld=1\">$type</a></td></tr>";
				$nugCount++;
				$nugs = true;
			}
			else
				print "<tr><td></td></tr>";
		}	
			print "</table>";
			if (!$nugs)
				print "There are currently no Non-Deliverable Nuggets for this semester<br />";
		}
	}
	
	function displayOldNuggets($currentGroup){
		$oldNuggets = $currentGroup->getInactiveNuggets();
		print "<h1>Other Semesters' Nuggets</h1>";
		if(count($oldNuggets)!= 0){
?>
			<table>
			<tr>
<?php
			$nuggetCount = 0;
			foreach($oldNuggets as $tempNugget){
					if($nuggetCount == 2){
						print "</tr><tr>";
						$nuggetCount = 0;
					}
					print "<td>";
					print "<a href=\"viewNugget.php?nug=".$tempNugget->getID()."\">".$tempNugget->getType()."</a>";
					print "</td>";
					$nuggetCount++;
			}
?>	
			</tr>
			</table>
			
<?php
		}else{
			print "There are no previous nuggets created with the iGroups nugget system.<br />";
		}
		print "To display nuggets prior to 3.0 release date visit <a href='http://iknow.iit.edu'>http://iknow.iit.edu</a>";
	}
	
	function printNugPreview($nugget){
		$title = $nugget->getType();
		$desc = $nugget->getDescShort();
		$id = $nugget->getID();
		$status = $nugget->getStatus();
		print "<td><a href=\"viewNugget.php?nug=$id\">".$title."</a></td><td>";
		print "<a href=\"editNugget.php?edit=true&amp;nugID=$id\">Edit</a></td>";
	}
	
	function printNuggetNoEdit($nugget){
		$authors = $nugget->getAuthors();
		$files = $nugget->getFiles();
		$semester = $nugget->getSemester();
		
		print "<div class='item'><strong>Nugget Type/Name:</strong> ";
		print $nugget->getType()."</div>";
		print '<div class="item"><strong>Description:</strong> '.substr($nugget->getDesc(),0,40).'</div>';
		print '<div class="item"><strong>Date Created:</strong> '.$nugget->getDate().'</div>';
		print '<div class="item"><strong>Authors:</strong> <br />';
		
		if(count($authors) > 0){
			print '<ul>';
			
			foreach($authors as $author){
					
				//checks to see if the author is the primary author
				if($nugget->isPrimaryAuthor($author->getID())){
					//if it is, the following parameter will be added to its radio button
					$toCheck = 'checked';
				}
				else {//or nothing will be added
					$toCheck = '';
				}
				//print the author name
				print '<li>';
				print $author->getFullName();
				if($toCheck == 'checked'){
					print '&nbsp&nbsp&nbsp<strong>Primary Author</strong>';
				}
				print '</li>';
				
			}
			print '</ul></div>';
		}
		else{
			print "There are currently no authors for this nugget.</div>";
		}
		print '<div class="item"><strong>Files:</strong>'.'<br />';
		
		if(count($files)>0){
			print '<ul>';
			foreach($files as $file){
				print '<li>';
				print '<a href="/igroups/download.php?id='.$file->getID().'">'.$file->getNameNoVer().'</a>&nbsp';
				print '</li>';
			}
			print '</ul></div>';
		}
		else{
			print "There are no files for this nugget.</div>";
		}
	}
	
	$msg = array();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<!-- This web-based application is Copyrighted &copy; 2008 Interprofessional Projects Program, Illinois Institute of Technology -->
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en"><head>
<title>iGroups - Nuggets</title>
<link rel="stylesheet" href="default.css" type="text/css" />
	<style type="text/css">
		table.nugget {
			width: 70%;
		}
		
		table.nugget tr {
			
		}	
			
		table.nugget td {
			border: 3px solid #ccc;
			padding: 20px;
			width:50%;
		}	
		
		.item {
			padding-top:5px;
			padding-bottom:5px;
			border-bottom:1px solid #ccc;
		}	
	</style>
	<script type="text/javascript">
	<!--
		function showMessage( msg ) {
			msgDiv = document.createElement("div");
			msgDiv.id="messageBox";
			msgDiv.innerHTML=msg;
			document.body.insertBefore( msgDiv, null );
			window.setTimeout( function() { msgDiv.style.display='none'; }, 3000 );
		}

		function nuggetRedirect(nugget){
			form = document.getElementById("redirectForm");
			form.nuggetType.value= nugget;
			form.submit();
		}
	//-->		
	</script>
</head>
<body>
	
<?php
	require("sidebar.php");
	print "<div id=\"content\"><div id=\"topbanner\">";
	print $currentGroup->getName()."</div>";
	//Prints all notifications
	if( isset ($msg)){
		foreach($msg as $ms){
			print '<script type="text/javascript">showMessage(\''.$ms.'\')</script>';
		}
	}
	
	displayNuggets($currentGroup);
	print "<br />";
	displayNonDefaultNuggets($currentGroup);
	print "<br />";
	//displayOldNuggets($currentGroup);
	
?>
	<br /><br />
</div></body></html>

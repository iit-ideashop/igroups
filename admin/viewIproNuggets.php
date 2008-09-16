<?php
	include_once( "checkadmin.php" );
	include_once("../classes/nugget.php");
	include_once("../nuggetTypes.php");

	function displayNuggets($currentGroup, $semID, $db){
		global $_DEFAULTNUGGETS;
		$query = $db->igroupsQuery("SELECT sSemester FROM Semesters where iID=$semID");
		$row = mysql_fetch_row($query);
		print "<h1>{$row[0]} Deliverable Nuggets</h1>";

		//Get the list of nuggets
		if ($semID < 32)
			$nuggets = getOldNuggetsByGroupAndSemester($currentGroup, $semID);
		else
			$nuggets = getNuggetStatus($currentGroup, $semID);
		$nugCount = 0;
?>
		<table cellpadding="3">
		<tr>
<?php

		if ($semID >= 32) {
		foreach($_DEFAULTNUGGETS as $nug){
			if($nugCount == 2){
				print "</tr><tr>";
				$nugCount = 0;
			}
			if($nuggets[$nug] != 0){
				$gID = $currentGroup->getID();
				print "<td><img src=\"../img/upload.png\" alt=\"Y\" />&nbsp;$nug</td><td><a href=\"viewNugget.php?nuggetID=".$nuggets[$nug]."&amp;groupID=$gID\">View</a></td>";
			}else{
				print "<td><img src=\"../img/no_upload.png\" alt=\"N\" />&nbsp;".$nug."</td><td>Not Uploaded</td>";
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
				$gID = $currentGroup->getID();
				if(strstr($type, $def)){
					$link .= "<td><img src=\"../img/upload.png\" alt=\"Y\" />&nbsp;$def</td><td><a href='viewNugget.php?nuggetID=$id&amp;groupID=$gID&amp;isOld=1'>View</a></td>";
					$nugCount++;
				}
			}
			if (!$link) {
				$link = "<td><img src=\"../img/no_upload.png\" alt=\"N\" />&nbsp;$def</td><td>Not Uploaded</td>";
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

	function displayNonDefaultNuggets($currentGroup, $semID, $db) {
		$query = $db->igroupsQuery("SELECT sSemester FROM Semesters where iID=$semID");
		$row = mysql_fetch_row($query);
		print "<h1>{$row[0]} Non-Deliverable Nuggets</h1>";

		if ($semID >= 32) {
		$nuggets = allActiveByTypeandID("Other", $currentGroup->getID(), $currentGroup->getSemester());
		if(count($nuggets) > 0){
?>
			<table>
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

		}else{
			print "There are currently no Non-Deliverable Nuggets for this semester<br />";
		}
		}
		else {
			global $_DEFAULTNUGGETS;
			$nuggets = getOldNuggetsByGroupAndSemester($currentGroup, $currentGroup->getSemester());
			print "<table><tr>";
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
				print "<tr><td><a href=\"viewNugget.php?nuggetID=$id&amp;isOld=1\">$type</a></td></tr>";
				$nugs = true;
			}
			}
			print "</table>";
			if (!$nugs)
				print "There are currently no Non-Deliverable Nuggets for this semester<br />";
		}
	}

	function displayOldNuggets($currentGroup){
		$oldNuggets = $currentGroup->getInactiveNuggets();
		print "<h1>Other Semesters Nuggets</h1>";
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
				print "<a href=\"viewNugget.php?nuggetID=".$tempNugget->getID()."&amp;old=".$tempNugget->isOld()."\">".$tempNugget->getType()."</a>";
				print "</td>";
				$nuggetCount++;
			}
?>
			</tr>
			</table>

<?php
		}else{
			print "There are no previous nuggets created with the igroups nugget system.<br />";
		}
	}

	function printNugPreview($nugget){
		$title = $nugget->getType();
		$desc = $nugget->getDescShort();
		$id = $nugget->getID();
		$status = $nugget->getStatus();
		print "<td><a href=\"viewNugget.php?nuggetID=$id\">".$title."</a></td>";
	}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<!-- This web-based application is Copyrighted &copy; 2008 Interprofessional Projects Program, Illinois Institute of Technology -->
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en"><head>
<title>iGroups - Nuggets</title>
<link rel="stylesheet" href="../default.css" type="text/css" />
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
</head>

<body>

<?php
	require("sidebar.php");
	print "<div id=\"content\">";
	//Prints all notifications
	$id = $_GET['id'];
	$sem = $db->igroupsQuery("Select iSemesterID FROM ProjectSemesterMap WHERE iProjectID = $id ORDER BY iSemesterID DESC");
	$row = mysql_fetch_row($sem);
	$semID = $row[0];
	$currentGroup = new Group($_GET['id'],0,$semID,$db);
	print "<h2>{$currentGroup->getName()}</h2>";
	print "<h3>{$currentGroup->getDesc()}</h3>";
	displayNuggets($currentGroup, $semID, $db);
	print "<br />";
	displayNonDefaultNuggets($currentGroup, $semID, $db);
	//print "<br />";
	//displayOldNuggets($currentGroup);
	print "<br /><a href=\"nuggets.php\">Back</a>";
?>
       <br />
       <br />
</div></body>
</html>

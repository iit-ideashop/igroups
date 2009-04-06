<?php
	include_once('globals.php');
	include_once('checklogin.php');
	include_once('classes/subgroup.php');
	
	if(is_numeric($_GET['taskid']))
	{	
		$query = $db->igroupsQuery('select * from Tasks where iID='.$_GET['taskid']);
		if(mysql_num_rows($query))
		{
			$ok = false;
			$task = mysql_fetch_array($query);
			$query2 = $db->igroupsQuery('select * from TaskAssignments where iTaskID='.$_GET['taskid'].' and iPersonID='.$currentUser->getID());
			if(mysql_num_rows($query2))
				$ok = true;
			else
			{
				$query2 = $db->igroupsQuery('select * from TaskSubgroupAssignments where iTaskID='.$_GET['taskid']);
				while($row = mysql_fetch_array($query2))
				{
					$sg = new SubGroup($row['iSubgroupID'], $db);
					if($sg->isSubGroupMember($currentUser))
					{
						$ok = true;
						break;
					}
				}
			}
			if(!$ok)
				errorPage('Access Denied', 'You (or one of your subgroups) must be assigned to a task to add hours to it.', 403);
			//else OK
		}
		else
			errorPage('Invalid Task ID', 'The task ID provided is invalid.', 400);
		if($_POST['form'] == 'edit')
		{
			$ids = explode(',', $_POST['ids']);
			$message = '';
			foreach($ids as $id)
			{
				if(!is_numeric($id))
				{
					$message .= "<li>ID value <b>$id</b> encountered; not numeric. Skipping.</li>\n";
					continue;
				}
				$query = $db->igroupsQuery("select * from Hours where iID=$id");
				$hour = mysql_fetch_array($query);
				if(!$hour)
				{
					$message .= "<li>ID value <b>$id</b> does not exist in the database. Skipping.</li>\n";
					continue;
				}
				else if($hour['iTaskID'] != $task['iID'])
				{
					$message .= "<li>ID value <b>$id</b> does not belong to this task. Skipping.</li>\n";
					continue;
				}
				else if($hour['iPersonID'] != $currentUser->getID())
				{
					$message .= "<li>ID value <b>$id</b> does not belong to the logged in user. Skipping.</li>\n";
					continue;
				}
				else if(!is_numeric($_POST["D$id"]) || $_POST["D$id"] < 0)
				{
					$message .= "<li>Invalid input <b>{$_POST["D$id"]}</b> entered for ID value <b>$id</b>. Skipping.</li>\n";
					continue;
				}
				if($_POST["D$id"] > 0)
					$db->igroupsQuery("update Hours set fHours={$_POST["D$id"]} where iID=$id");
				else if($_POST["D$id"] == 0)
					$db->igroupsQuery("delete from Hours where iID=$id");
			}
			if($message == '')
				$message = 'All values successfully updated.';
			else
				$message = "The following problems occurred when processing your request:<ul>$message</ul>";
		}
		else if($_POST['form'] == 'new')
		{
			//Verify input
			$subdate = strtotime($_POST['date']);
			if(!is_numeric($_POST['hours']))
				$message = 'ERROR: Hours must be numeric';
			else if($_POST['hours'] <= 0)
				$message = 'ERROR: Hours must be positive';
			else if($_POST['date'] !== false && $subdate <= time())
			{
				$sqldate = date('Y-m-d', $subdate);
				//Make sure date doesn't already exist
				$query = $db->igroupsQuery("select * from Hours where iTaskID={$task['iID']} and iPersonID={$currentUser->getID()} and dDate=\"$sqldate\"");
				if(mysql_num_rows($query))
					$message = 'ERROR: The entered date already exists for this task. You must use Edit Hours to change the hours for this date.';
				else
					$message = $db->igroupsQuery("insert into Hours (iTaskID, iPersonID, dDate, fHours) values ({$task['iID']}, {$currentUser->getID()}, \"$sqldate\", {$_POST['hours']})") ? 'Hours added' : 'ERROR: An SQL error occurred. Please contact the software maintainer to correct this problem.<br />'.mysql_error();
			}
			else if($_POST['date'] === false)
				$message = 'ERROR: The entered date is not in a recognizable format. Please use the ISO 8601 format (YYYY-MM-DD).';
			else //Remove this error message when time travel is invented
				$message = 'ERROR: The entered date is in the future.';
		}
	}
	else if($_GET['taskid'])
		errorPage('Invalid Task ID', 'The task ID provided is invalid.', 400);
	else
		errorPage('Missing Task ID', 'No task ID was provided.', 400);
		
	$query = $db->igroupsQuery('select * from Hours where iTaskID='.$task['iID'].' and iPersonID='.$currentUser->getID().' order by dDate asc');
	$hours = array();
	$dates = array();
	$totalhours = 0;
	while($row = mysql_fetch_array($query))
	{
		$hours[$row['iID']] = $row['fHours'];
		$dates[$row['iID']] = $row['dDate'];
		$totalhours += $row['fHours'];
	}
	unset($query);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<!-- This web-based application is Copyrighted &copy; 2009 Interprofessional Projects Program, Illinois Institute of Technology -->
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en"><head>
<?php
require('appearance.php');
echo "<link rel=\"stylesheet\" href=\"skins/$skin/tasks.css\" type=\"text/css\" title=\"$skin\" />\n";
foreach($altskins as $altskin)
	echo "<link rel=\"alternate stylesheet\" href=\"skins/$altskin/tasks.css\" type=\"text/css\" title=\"$altskin\" />\n";
?>
<title><?php echo $appname; ?> - Task Hours</title>
</head>
<body>
<?php
	require('sidebar.php');
?>
<div id="content"><div id="topbanner"><?php echo $currentGroup->getName(); ?></div>
<?php
	echo '<p>We are assigning hours for <b>'.$task['sName']."</b>. Thus far, you have spent <b>$totalhours</b> hours on this task.</p>\n";
	if(count($dates) > 0)
	{
		echo "<form method=\"post\" action=\"taskhours.php?taskid={$_GET['taskid']}\"><fieldset><legend>Edit Hours</legend>\n";
		echo "<table><tr><th>Date</th><th>Hours</th></tr>\n";
		foreach($dates as $id => $date)
		{
			echo "<tr><td><label for=\"D$id\">$date</label></td><td><input type=\"text\" name=\"D$id\" id=\"D$id\" value=\"{$hours[$id]}\" /></td></tr>\n";
		}
		echo "</table>\n";
		$ids = '';
		foreach($hours as $id => $thehour)
			$ids .= "$id,";
		$ids = trim($ids, ',');
		echo "<input type=\"hidden\" name=\"ids\" value=\"$ids\" />\n";
		echo "<input type=\"submit\" value=\"Edit Hours\" /><input type=\"reset\" /><input type=\"hidden\" name=\"form\" value=\"edit\" /></fieldset></form>\n";
	}
	
	echo "<form method=\"post\" action=\"taskhours.php?taskid={$_GET['taskid']}\"><fieldset><legend>Add Hours</legend>\n";
	echo "<label>Date: <input type=\"text\" name=\"date\" /></label><br />\n";
	echo "<label>Hours: <input type=\"text\" name=\"hours\" /></label><br />\n";
	echo "<input type=\"submit\" value=\"Add Hours\" /><input type=\"hidden\" name=\"form\" value=\"new\" /></fieldset></form>\n";
	echo "<p><a href=\"tasks.php\">Cancel and return to tasks listing</a></p>\n";
?>
</div></body></html>

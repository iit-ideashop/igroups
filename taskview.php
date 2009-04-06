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
			if($task['iTeamID'] != $currentGroup->getID())
				errorPage('Cannot Access Task', 'This task is not assigned to the selected group.', 403);
		}
		else
			errorPage('Invalid Task ID', 'The task ID provided is invalid.', 400);
	}
	else if($_GET['taskid'])
		errorPage('Invalid Task ID', 'The task ID provided is invalid.', 400);
	else
		errorPage('Missing Task ID', 'No task ID was provided.', 400);

	if($_POST['form'] == 'edittask')
	{
		$name = mysql_real_escape_string($_POST['name']);
		$hurdle = 0;
		if(!strlen($name))
			$message = 'ERROR: Could not edit task: You must enter a name for the task.';
		else if(mysql_num_rows($db->igroupsQuery('select iID from Tasks where iTeamID='.$currentGroup->getID()." and sName=\"$name\" and iID<>{$task['iID']}")))
			$message = 'Your group already has a task with that name.';
		else
			$hurdle++;
		$date = strtotime($_POST['due']);
		if(!$date)
			$message = 'ERROR: Could not edit task: Invalid due date';
		else
		{
			$date = strftime('%Y-%m-%d', $date);
			$hurdle++;
		}
		$desc = mysql_real_escape_string($_POST['desc']);
		if($hurdle == 2)
		{
			$ok = $db->igroupsQuery("update Tasks set sName=\"$name\", sDescription=\"$desc\", dDue=\"$date\"");
			if($ok)
				$message = 'Task successfully edited';
			else
				$message = 'Task editing failed: '.mysql_error();
		}
	}

	foreach($task as $key => $val)
		$task[$key] = htmlspecialchars(stripslashes($val));
	//Create variables we'll use later ($overdue, $creator, $assigned, $sgassigned)
	$overdue = (!$task['dClosed'] && strtotime($task['dDue']) <= time());
	$creator = ($task['iOwnerID'] == $currentUser->getID());
	$query = $db->iGroupsQuery("select * from TaskAssignments where iTaskID={$task['iID']} and iPersonID={$currentUser->getID()}");
	$assigned = mysql_num_rows($query) ? true : false;
	$query = $db->igroupsQuery('select * from TaskSubgroupAssignments where iTaskID='.$_GET['taskid']);
	$sgassigned = false;
	while($row = mysql_fetch_array($query))
	{
		$sg = new SubGroup($row['iSubgroupID'], $db);
		if($sg->isSubGroupMember($currentUser))
		{
			$sgassigned = true;
			break;
		}
	}
	$query = $db->igroupsQuery("select sum(fHours) from Hours where iTaskID={$task['iID']} and iPersonID={$currentUser->getID()}");
	$row = mysql_fetch_row($query);
	$hours = $row[0];
	$query = $db->igroupsQuery("select sum(fHours) from Hours where iTaskID={$task['iID']}");
	$row = mysql_fetch_row($query);
	$tothours = $row[0];
	$percenthours = ($tothours > 0 ? number_format(100*$hours/$tothours, 1).'%' : '0%');
	$query = $db->igroupsQuery('select * from TaskAssignments where iTaskID='.$task['iID']);
	$assignments = array();
	while($assign = mysql_fetch_array($query))
	{
		$nm = mysql_fetch_row($db->igroupsQuery('select sFName, sLName from People where iID='.$assign['iPersonID']));
		$assignments[$assign['iPersonID']] = $nm[0].' '.$nm[1];
	}
	$query = $db->igroupsQuery('select * from TaskSubgroupAssignments where iTaskID='.$task['iID']);
	$sgassignments = array();
	while($assign = mysql_fetch_array($query))
	{
		$nm = mysql_fetch_row($db->igroupsQuery('select sName from SubGroups where iID='.$assign['iSubgroupID']));
		$sgassignments[$assign['iSubgroupID']] = $nm[0];
	}
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
<title><?php echo $appname; ?> - View Task</title>
<script type="text/javascript">
function toggle(id)
{
	document.getElementById(id).style.display = (document.getElementById(id).style.display == 'block') ? 'none' : 'block';
}
</script>
</head>
<body>
<?php
	require('sidebar.php');
?>
<div id="content"><div id="topbanner"><?php echo $currentGroup->getName(); ?></div>
<?php
	echo "<h1>{$task['sName']}</h1>\n";
	echo "<ul id=\"notices\">\n";
	if($overdue)
		echo "\t<li id=\"overdue\">This task is overdue. It was due on {$task['dDue']}.</li>\n";
	else
		echo "\t<li>This task is due on {$task['dDue']}.</li>\n";
	if($creator)
		echo "\t<li>You are the creator of this task.</li>\n";
	if($assigned)
		echo "\t<li>You are currently assigned to this task. You may <a href=\"taskhours.php?taskid={$task['iID']}\">add hours</a> to this task.</li>\n";
	else if($sgassigned)
		echo "\t<li>One or more of your subgroups are currently assigned to this task. You may <a href=\"taskhours.php?taskid={$task['iID']}\">add hours</a> to this task.</li>\n";
	else
		echo "\t<li>You are not assigned to this task.</li>\n";
	if($assigned || $sgassigned || $hours > 0)
		echo "\t<li>You have contributed <b>$hours</b> hours of work to this task, out of <b>$tothours</b> hours overall (<b>$percenthours</b>)</li>\n";
	echo "</ul>\n<h2>Description</h2>\n<div id=\"taskdesc\"><p>{$task['sDescription']}</p></div>\n";
	echo "<h2>Assignments</h2>\n";
	if(count($assignments))
	{
		echo "<ul id=\"assignments\">\n";
		foreach($assignments as $asn)
			echo "<li>$asn</li>\n";
		echo "</ul>\n";
	}
	else if(count($sgassignments))
		echo "<p>No one is individually assigned to this task; however, subgroups are.</p>\n";
	else
		echo "<p>No one is assigned to this task.</p>\n";
	echo "<h3>Subgroup Assignments</h3>\n";
	if(count($sgassignments))
	{
		echo "<ul id=\"sgassignments\">\n";
		foreach($sgassignments as $sgid => $asn)
		{
			echo "<li>$asn <a href=\"javascript:toggle('SG$sgid')\" class=\"toggle\">Toggle</a></strong>";
			$sg = new SubGroup($sgid, $db);
			$people = $sg->getSubGroupMembers();
			if(count($people))
			{
				echo "<ul class=\"sgpersonlist\" id=\"SG$sgid\">\n";
				foreach($people as $person)
					echo "\t<li>{$person->getShortName()}</li>\n";
				echo "\t</ul>\n";
			}
			echo "</li>\n";
		}
		echo "</ul>\n";
	}
	else
		echo "<p>No subgroups are assigned to this task.</p>\n";
	if($creator || $currentUser->isGroupModerator($currentGroup))
	{
		echo "<h2>Task Moderator Options</h2>\n";
		echo "<ul id=\"taskmod\">\n";
		echo "<li><a href=\"taskassign.php?taskid={$task['iID']}\">Change assignments</a></li>\n";
		echo "<li><a href=\"taskcomplete.php?taskid={$task['iID']}\">Mark this task as completed</a></li>\n";
		echo "<li><a href=\"tasks.php?del={$task['iID']}\">Delete this task</a> (This action cannot be undone!)</li>\n";
		echo "</ul>\n";
		echo "<form method=\"post\" action=\"taskview.php\"><fieldset><legend id=\"taskedit\">Edit this Task</legend>\n";
		echo "<label>Name: <input type=\"text\" name=\"name\" value=\"{$task['sName']}\" /></label><br />\n";
		echo "<label>Due: <input name=\"due\" type=\"text\" value=\"{$task['dDue']}\" /></label><br />\n";
		echo "<label>Description:<br /><textarea name=\"desc\" rows=\"5\" cols=\"80\">{$task['sDescription']}</textarea></label><br />\n";
		echo "<input value=\"Edit Task\" type=\"submit\" /><input type=\"reset\" /><input name=\"form\" value=\"edittask\" type=\"hidden\" /></fieldset></form>\n";
	}
?>
</div></body></html>

<?php
	include_once('globals.php');
	include_once('checklogin.php');
	include_once('classes/task.php');
	
	if(is_numeric($_GET['taskid']))
	{	
		$task = new Task($_GET['taskid'], $currentGroup->getType(), $currentGroup->getSemester(), $db);
		if($task->isValid())
		{
			if($task->getTeam()->getID() != $currentGroup->getID())
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
		else if(mysql_num_rows($db->igroupsQuery('select iID from Tasks where iTeamID='.$currentGroup->getID()." and sName=\"$name\" and iID<>{$task->getID()}")))
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
			$ok = $db->igroupsQuery("update Tasks set sName=\"$name\", sDescription=\"$desc\", dDue=\"$date\" where iID={$task->getID()}");
			if($ok)
			{
				$message = 'Task successfully edited';
				$task = new Task($_GET['taskid'], $currentGroup->getType(), $currentGroup->getSemester(), $db); //Resync
			}
			else
				$message = 'Task editing failed: '.mysql_error();
		}
	}

	//Create variables we'll use later
	$overdue = (!$task->getClosed() && strtotime($task->getDue()) <= time());
	$creator = ($task->getCreator()->getID() == $currentUser->getID());
	$assigned = $task->isAssignedPerson($currentUser);
	$sgassigned = $task->isAssigned($currentUser);
	$hours = $task->getTotalHoursFor($currentUser);
	$tothours = $task->getTotalHours();
	$percenthours = ($tothours > 0 ? number_format(100*$hours/$tothours, 1).'%' : '0%');
	$assignments = $task->getAssignedPeople();
	$sgassignments = $task->getAssignedSubgroups();
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
	echo "<h1>{$task->getName()}</h1>\n";
	echo "<ul id=\"notices\">\n";
	if($overdue)
		echo "\t<li id=\"overdue\">This task is overdue. It was due on {$task->getDue()}.</li>\n";
	else if(!$task->getClosed())
		echo "\t<li>This task is due on {$task->getDue()}.</li>\n";
	else
		echo "\t<li>This task was closed on {$task->getClosed()}</li>\n";
	if($creator)
		echo "\t<li>You are the creator of this task.</li>\n";
	if($assigned)
		echo "\t<li>You are currently assigned to this task. You may <a href=\"taskhours.php?taskid={$task->getID()}\">add hours</a> to this task.</li>\n";
	else if($sgassigned)
		echo "\t<li>One or more of your subgroups are currently assigned to this task. You may <a href=\"taskhours.php?taskid={$task->getID()}\">add hours</a> to this task.</li>\n";
	else
		echo "\t<li>You are not assigned to this task.</li>\n";
	if($assigned || $sgassigned || $hours > 0)
		echo "\t<li>You have contributed <b>$hours</b> hours of work to this task, out of <b>$tothours</b> hours overall (<b>$percenthours</b>)</li>\n";
	echo "</ul>\n<h2>Description</h2>\n<div id=\"taskdesc\"><p>{$task->getDesc()}</p></div>\n";
	echo "<h2>Assignments</h2>\n";
	if(count($assignments))
	{
		echo "<ul id=\"assignments\">\n";
		foreach($assignments as $asn)
			echo "<li>{$asn->getShortName()}</li>\n";
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
			echo "<li>{$asn->getName()} <strong><a href=\"javascript:toggle('SG$sgid')\" class=\"toggle\">Toggle</a></strong>";
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
		echo "<li><a href=\"taskassign.php?taskid={$task->getID()}\">Change assignments</a></li>\n";
		echo "<li><a href=\"taskcomplete.php?taskid={$task->getID()}\">Mark this task as completed</a></li>\n";
		echo "<li><a href=\"tasks.php?del={$task->getID()}\">Delete this task</a> (This action cannot be undone!)</li>\n";
		echo "</ul>\n";
		echo "<form method=\"post\" action=\"taskview.php?taskid={$task->getID()}\"><fieldset><legend id=\"taskedit\">Edit this Task</legend>\n";
		echo "<label>Name: <input type=\"text\" name=\"name\" value=\"{$task->getName()}\" /></label><br />\n";
		echo "<label>Due: <input name=\"due\" type=\"text\" value=\"{$task->getDue()}\" /></label><br />\n";
		echo "<label>Description:<br /><textarea name=\"desc\" rows=\"5\" cols=\"80\">{$task->getDesc()}</textarea></label><br />\n";
		echo "<input value=\"Edit Task\" type=\"submit\" /><input type=\"reset\" /><input name=\"form\" value=\"edittask\" type=\"hidden\" /></fieldset></form>\n";
	}
	echo "<p><a href=\"tasks.php\">Return to main tasks listing</a></p>\n";
?>
</div></body></html>

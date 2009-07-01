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

	//Create variables we'll use later
	$overdue = (!$task->getClosed() && strtotime($task->getDue()) <= time());
	if($overdue)
		$status = '<b>OVERDUE</b>';
	else if($task->getClosed())
		$status = 'Closed';
	else
		$status = 'Open';
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
	echo "<div style=\"float:right;border:thin solid black\">\n";
	echo "\t<h2>Task Essentials</h2>\n";
	echo "\t<ul style=\"list-style-type:none\">\n";
	echo "\t\t<li><b>Status</b>: $status</li>\n";
	if(!$task->getClosed())
		echo "\t\t<li><b>Due</b>: {$task->getDue()}</li>\n";
	echo "\t\t<li><b>Creator</b>: {$task->getCreator()->getFullName()} (<a href=\"sendemail.php?to={$task->getCreator()->getID()}\">email</a>)</li>\n";
	echo "\t</ul>\n";
	if($task->isAssigned($currentUser))
	{
		echo "\t<h2>Assignee Actions</h2>\n";
		echo "\t<ul style=\"list-style-type:none\">\n";
		echo "\t\t<a href=\"taskhours.php?taskid={$task->getID()}\">Add/View Hours</a>\n";
		echo "\t</ul>\n";
	}
	if($currentUser->isGroupModerator($currentGroup) || $task->getCreator()->getID() == $currentUser->getID())
	{
		echo "\t<h2>Moderator Actions</h2>\n";
		echo "\t<ul style=\"list-style-type:none\">\n";
		echo "\t\t<li><a href=\"taskassign.php?taskid={$task->getID()}\">Change Assignments</a></li>\n";
		echo "\t\t<li><a href=\"taskedit.php?taskid={$task->getID()}\">Edit Task</a></li>\n";
		echo "\t\t<li><a href=\"taskcomplete.php?taskid={$task->getID()}\">Close Task</a></li>\n";
		echo "\t\t<li><a href=\"tasks.php?del={$task->getID()}\">Delete Task</a></li>\n";
		echo "\t</ul>\n";
	}
	echo "</div>\n";
	echo "<ul id=\"notices\">\n";
	if($overdue)
		echo "\t<li id=\"overdue\">This task is overdue. It was due on {$task->getDue()}.</li>\n";
	else if(!$task->getClosed())
		echo "\t<li>This task is due on {$task->getDue()}.</li>\n";
	else
		echo "\t<li>This task was closed on {$task->getClosed()}</li>\n";
	if($creator)
		echo "\t<li>You are the creator of this task.</li>\n";
	else
		echo "\t<li>The creator of this task is {$task->getCreator()->getFullName()} (<a href=\"sendemail.php?to={$task->getCreator()->getID()}\">email</a>).</li>\n";
	if($assigned)
		echo "\t<li>You are currently assigned to this task. You may <a href=\"taskhours.php?taskid={$task->getID()}\">add hours</a> to this task.</li>\n";
	else if($sgassigned)
		echo "\t<li>One or more of your subgroups are currently assigned to this task. You may <a href=\"taskhours.php?taskid={$task->getID()}\">add hours</a> to this task.</li>\n";
	else
		echo "\t<li>You are not assigned to this task.</li>\n";
	if($assigned || $sgassigned || $hours > 0)
		echo "\t<li>You have contributed <b>$hours</b> hours of work to this task, out of <b>$tothours</b> hours overall (<b>$percenthours</b>)</li>\n";
	echo "</ul>\n";
	
	echo "<h2>Description</h2>\n<div id=\"taskdesc\"><p>{$task->getDesc()}</p></div>\n";
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
	echo "<p><a href=\"tasks.php\">Return to main tasks listing</a></p>\n";
?>
</div></body></html>

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
	else if(is_numeric($_GET['del']))
	{	
		$task = new Task($_GET['del'], $currentGroup->getType(), $currentGroup->getSemester(), $db);
		if($task->isValid())
		{
			if($task->getTeam()->getID() != $currentGroup->getID())
				errorPage('Cannot Access Task', 'This task is not assigned to the selected group.', 403);
			else if($task->getCreator()->getID() != $currentUser->getID() && !$currentUser->isGroupModerator($currentGroup))
				errorPage('Cannot Delete Task', 'You must be either the task creator or a group moderator to delete a task.', 403);
		}
		else
			errorPage('Invalid Task ID', 'The task ID provided is invalid.', 400);
	}
	else if($_GET['taskid'] || $_GET['del'])
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
	//$sgassigned = $task->isAssigned($currentUser);
	$hours = $task->getTotalHoursFor($currentUser);
	$tothours = $task->getTotalHours();
	$esthours = $task->getEstimatedHours();
	$pesthours = $esthours == 1 ? 'hour' : 'hours';
	$percenthours = ($tothours > 0 ? number_format(100*$hours/$tothours, 1).'%' : '0%');
	$assignments = $task->getAssignedPeople();
	$sgassignments = $task->getAssignedSubgroups();
	
	//------Start XHTML Output--------------------------------------//

	require('doctype.php');
	require('appearance.php');
	echo "<link rel=\"stylesheet\" href=\"skins/$skin/tasks.css\" type=\"text/css\" title=\"$skin\" />\n";
	foreach($altskins as $altskin)
		echo "<link rel=\"alternate stylesheet\" href=\"skins/$altskin/tasks.css\" type=\"text/css\" title=\"$altskin\" />\n";
	$view = is_numeric($_GET['del']) ? 'Delete Task?' : 'View Task';
	echo "<title>$appname - $view</title>\n";
?>
<script type="text/javascript">
function toggle(id)
{
	document.getElementById(id).style.display = (document.getElementById(id).style.display == 'block') ? 'none' : 'block';
}
</script>
</head>
<body>
<?php
	
  /**** begin html head *****/
   require('htmlhead.php'); //starts main container
  /****end html head content ****/

?>
<div id="topbanner"><?php echo $currentGroup->getName(); ?></div>
<?php
	echo "<h1>{$task->getName()}</h1>\n";
	if(is_numeric($_GET['del']))
	{
		echo "<p>Are you sure you want to delete this task? This cannot be undone.</p>\n";
		echo "<p><a href=\"tasks.php?del={$task->getID()}\" title=\"Delete\">Yes, delete the task.</a> -- <a href=\"taskview.php?taskid={$task->getID()}\" title=\"Don't Delete\">No, do not delete.</a></p>\n";
		die('</div></body></html>');
	}
	echo "<div style=\"float:right;border:thin solid black\">\n";
	echo "\t<h2>Task Essentials</h2>\n";
	echo "\t<ul style=\"list-style-type:none\">\n";
	echo "\t\t<li><b>Status</b>: $status</li>\n";
	if(!$task->getClosed())
		echo "\t\t<li><b>Due</b>: {$task->getDue()}</li>\n";
	echo "\t\t<li><b>Creator</b>: {$task->getCreator()->getFullName()} (<a href=\"email.php?to={$task->getCreator()->getID()}\">email</a>)</li>\n";
	echo "\t</ul>\n";
	if($task->isAssigned($currentUser))
	{
		echo "\t<h2>Assignee Actions</h2>\n";
		echo "\t<ul style=\"list-style-type:none\">\n";
		echo "\t\t<li><a href=\"taskhours.php?taskid={$task->getID()}\">Add Hours</a></li>\n";
		echo "\t\t<li><a href=\"hours.php?taskid={$task->getID()}\">View Hours</a></li>\n";
		echo "\t\t<li><a href=\"edithours.php?taskid={$task->getID()}\">Edit Hours</a></li>\n";
		echo "\t</ul>\n";
	}
	if($currentUser->isGroupModerator($currentGroup) || $task->getCreator()->getID() == $currentUser->getID())
	{
		echo "\t<h2>Moderator Actions</h2>\n";
		echo "\t<ul style=\"list-style-type:none\">\n";
		echo "\t\t<li><a href=\"taskassign.php?taskid={$task->getID()}\">Change Assignments</a></li>\n";
		echo "\t\t<li><a href=\"hours.php?taskid={$task->getID()}\">View Hour Summary</a></li>\n";
		echo "\t\t<li><a href=\"taskedit.php?taskid={$task->getID()}\">Edit Task</a></li>\n";
		echo "\t\t<li><a href=\"taskcomplete.php?taskid={$task->getID()}\">Close Task</a></li>\n";
		echo "\t\t<li><a href=\"taskview.php?del={$task->getID()}\">Delete Task</a></li>\n";
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
		echo "\t<li>The creator of this task is {$task->getCreator()->getFullName()} (<a href=\"email.php?to={$task->getCreator()->getID()}\">email</a>).</li>\n";
	if($assigned)
		echo "\t<li>You are currently assigned to this task. You may <a href=\"taskhours.php?taskid={$task->getID()}\">add hours</a> to this task.</li>\n";
	//else if($sgassigned)
	//	echo "\t<li>One or more of your subgroups are currently assigned to this task. You may <a href=\"taskhours.php?taskid={$task->getID()}\">add hours</a> to this task.</li>\n";
	else
		echo "\t<li>You are not assigned to this task.</li>\n";
	if($assigned || $sgassigned || $hours > 0)
		echo "\t<li>You have contributed <b>$hours</b> hours of work to this task, out of <b>$tothours</b> hours overall (<b>$percenthours</b>)</li>\n";
	if($esthours)
		echo "<li>This task is estimated to require $esthours $pesthours of work.</li>\n";
	echo "</ul>\n";
	
	echo "<h2>Description</h2>\n<div id=\"taskdesc\"><p>".str_replace("\n", '<br />', $task->getDesc())."</p></div>\n";
	echo "<h2>Assignments</h2>\n";
	if(count($assignments))
	{
		echo "<ul id=\"assignments\">\n";
		foreach($assignments as $asn)
			echo "<li>{$asn->getShortName()}</li>\n";
		echo "</ul>\n";
	}
	//else if(count($sgassignments))
	//	echo "<p>No one is individually assigned to this task; however, subgroups are.</p>\n";
	else
		echo "<p>No one is assigned to this task.</p>\n";
	/*echo "<h3>Subgroup Assignments</h3>\n";
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
		echo "<p>No subgroups are assigned to this task.</p>\n";*/
	echo "<p><a href=\"tasks.php\">Return to main tasks listing</a></p>\n";

  //include rest of html layout file
  require('htmlcontentfoot.php');// ends main container

?>

</body></html>

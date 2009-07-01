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
			else if($currentUser->getID() != $task->getCreator()->getID() && !$currentUser->isGroupModerator($currentGroup))
				errorPage('Access Denied', 'You must be either the task owner or a group moderator to edit a task.', 403);
			//else OK
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
				header('Location: taskview.php?taskid='.$task->getID());
			}
			else
				$message = 'Task editing failed: '.mysql_error();
		}
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
<script type="text/javascript" src="Calendar.js">
var cal = new CalendarPopup("caldiv");
cal.showNavigationDropdowns();
</script>
<title><?php echo $appname; ?> - Edit Task</title>
</head>
<body>
<?php
	require('sidebar.php');
?>
<div id="content"><div id="topbanner"><?php echo $currentGroup->getName(); ?></div>
<?php
	$name = htmlspecialchars(stripslashes($task->getName()));
	$desc = htmlspecialchars(stripslashes($task->getDesc()));
	echo "<h1>$name</h1>\n";
	echo "<form method=\"post\" action=\"taskedit.php?taskid={$task->getID()}\"><fieldset><legend id=\"taskedit\">Edit this Task</legend>\n";
	echo "<label>Name: <input type=\"text\" name=\"name\" value=\"$name\" /></label><br />\n";
	echo "<label>Due: <input name=\"due\" type=\"text\" value=\"{$task->getDue()}\" /></label> <a href=\"#\" onclick=\"cal.select(document.forms[0].due,'calsel','yyyy-MM-dd'); return false;\" id=\"calsel\">Select</a><br />\n";
	echo "<label>Description:<br /><textarea name=\"desc\" rows=\"5\" cols=\"80\">$desc</textarea></label><br />\n";
	echo "<input value=\"Edit Task\" type=\"submit\" /><input type=\"reset\" /><input name=\"form\" value=\"edittask\" type=\"hidden\" /></fieldset></form>\n";
	echo "<p>Cancel and <a href=\"tasks.php\">return to main tasks listing</a> or <a href=\"taskview.php?taskid={$task->getID()}\">return to task</a></p>\n";
?>
</div></body></html>

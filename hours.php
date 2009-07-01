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
<title><?php echo $appname; ?> - View Hours</title>
</head>
<body>
<?php
	require('sidebar.php');
?>
<div id="content"><div id="topbanner"><?php echo $currentGroup->getName(); ?></div>
<?php
	if($currentUser->isModerator() || $task->getCreator()->getID() == $currentUser->getID())
	{
		$users = $task->getAllAssigned();
		
		//We want the current user to be the topmost table in the output, so put him in position 0 and re-sort by key
		$users[0] = $currentUser;
		unset($users[$currentUser->getID()]);
		ksort($users);
	}
	else
		$users = array(0 => $currentUser);
	
	foreach($users as $user)
	{
		$hours = $task->getHours($user);
		echo "<table class=\"taskhours\">\n";
		echo "\t<thead>\n";
		echo "\t\t<tr><th colspan=\"2\">Hours Summary for {$user->getFullName()}</th></tr>\n";
		echo "\t\t<tr><th>Date</th><th>Hours Spent</th></tr>\n";
		echo "\t</thead>\n";
		echo "\t<tfoot>\n";
		echo "\t\t<tr><td>Total</td><td>{$task->getTotalHoursFor($user)}</td></tr>\n";
		echo "\t</tfoot>\n";
		echo "\t<tbody>\n";
		foreach($hours as $hour)
			echo "\t\t<tr><td>{$hour->getDate()}</td><td>{$hour->getHours()}</td></tr>\n";
		echo "\t</tbody>\n";
		echo "</table>\n";
	}
?>
</div></body></html>

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
	
	//------Start XHTML Output--------------------------------------//
	
	require('doctype.php');
	require('appearance.php');
	echo "<link rel=\"stylesheet\" href=\"skins/$skin/tasks.css\" type=\"text/css\" title=\"$skin\" />\n";
	foreach($altskins as $altskin)
		echo "<link rel=\"alternate stylesheet\" href=\"skins/$altskin/tasks.css\" type=\"text/css\" title=\"$altskin\" />\n";
?>
<title><?php echo $appname; ?> - View Hours</title>
</head>
<body>
<?php
	/**** begin html head *****/
   require('htmlhead.php'); //starts main container
  /****end html head content ****/
?>
<div id="topbanner"><?php echo $currentGroup->getName(); ?></div>
<?php
	if($currentUser->isGroupModerator($currentGroup) || $task->getCreator()->getID() == $currentUser->getID())
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
		echo "\t\t<tr><th colspan=\"3\">Hours Summary for {$user->getFullName()}</th></tr>\n";
		echo "\t\t<tr><th>Date</th><th>Hours Spent</th><th>Description</th></tr>\n";
		echo "\t</thead>\n";
		echo "\t<tfoot>\n";
		echo "\t\t<tr><td>Total</td><td>{$task->getTotalHoursFor($user)}</td><td></td></tr>\n";
		echo "\t</tfoot>\n";
		echo "\t<tbody>\n";
		foreach($hours as $hour)
			echo "\t\t<tr><td>{$hour->getDate()}</td><td>{$hour->getHours()}</td><td>".htmlspecialchars($hour->getDesc())."</td></tr>\n";
		echo "\t</tbody>\n";
		echo "</table>\n";
	}
	echo "<p><a href=\"tasks.php\">Return to main tasks listing</a> or <a href=\"taskview.php?taskid={$task->getID()}\">return to task</a></p>\n</fieldset></form><div id=\"caldiv\"></div>\n";
?>

<?php
  //include rest of html layout file
  require('htmlcontentfoot.php');// ends main container
?>
</body></html>

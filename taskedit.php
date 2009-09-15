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
		else if(mysql_num_rows($db->query('select iID from Tasks where iTeamID='.$currentGroup->getID()." and sName=\"$name\" and iID<>{$task->getID()}")))
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
		$esthours = 0;
		if(is_numeric($_POST['esthours']) && $_POST['esthours'] > 0)
			$esthours = intval($_POST['esthours']);
		if($hurdle == 2)
		{
			$ok = $db->query("update Tasks set sName=\"$name\", sDescription=\"$desc\", dDue=\"$date\", iEstimatedHours=$esthours where iID={$task->getID()}");
			if($ok)
			{
				$message = 'Task successfully edited';
				header('Location: taskview.php?taskid='.$task->getID());
			}
			else
				$message = 'Task editing failed: '.mysql_error();
		}
	}
	
	//------Start XHTML Output--------------------------------------//
	
	require('doctype.php');
	require('appearance.php');
	echo "<link rel=\"stylesheet\" href=\"skins/$skin/tasks.css\" type=\"text/css\" title=\"$skin\" />\n";
	foreach($altskins as $altskin)
		echo "<link rel=\"alternate stylesheet\" href=\"skins/$altskin/tasks.css\" type=\"text/css\" title=\"$altskin\" />\n";
?>
<script type="text/javascript" src="Calendar.js"></script>
<script type="text/javascript">
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
	echo "<form method=\"post\" action=\"taskedit.php?taskid={$task->getID()}\" style=\"float: left\"><fieldset><legend id=\"taskedit\">Edit this Task</legend>\n";
	echo "<table>\n";
	echo "<tr><td><label for=\"name\">Name:</label></td><td><input type=\"text\" name=\"name\" id=\"name\" value=\"$name\" /></td></tr>\n";
	echo "<tr><td><label for=\"due\">Due:</label></td><td><input name=\"due\" id=\"due\" type=\"text\" value=\"{$task->getDue()}\" /> <a href=\"#\" onclick=\"cal.select(document.forms[0].due,'calsel','yyyy-MM-dd'); return false;\" id=\"calsel\">Select</a></td></tr>\n";
	echo "<tr><td><label for=\"esthours\">Estimated hours required (optional):</label></td><td><input type=\"text\" name=\"esthours\" id=\"esthours\" value=\"{$task->getEstimatedHours()}\" /></td></tr>\n";
	echo "<tr><td><label for=\"desc\">Description:</label></td><td><textarea name=\"desc\" id=\"desc\" rows=\"5\" cols=\"80\">$desc</textarea></td></tr>\n";
	echo "</table>\n";
	echo "<input value=\"Edit Task\" type=\"submit\" /><input type=\"reset\" /><input name=\"form\" value=\"edittask\" type=\"hidden\" />\n";
	echo "<p>Cancel and <a href=\"tasks.php\">return to main tasks listing</a> or <a href=\"taskview.php?taskid={$task->getID()}\">return to task</a></p>\n</fieldset></form><div id=\"caldiv\"></div>\n";
?>
</div></body></html>

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
	
	//------Process form input--------------------------------------//
	
	if(array_key_exists('submitted', $_POST))
	{
		$dates = array();
		$hours = array();
		$descs = array();
		
		foreach($_POST as $key => $val)
		{
			$id = substr($key, 1);
			if(is_numeric($id))
			{
				$id = intval($id);
				if($key[0] == 'd')
					$dates[$id] = $val;
				else if($key[0] == 'h')
					$hours[$id] = $val;
				else if($key[0] == 's')
					$descs[$id] = stripslashes($val);
			}
		}
		
		foreach($dates as $id => $date)
		{
			$hourobj = new Hour($id, $db);
			if($currentUser->getID() == $hourobj->getPerson()->getID() && is_numeric($id) && array_key_exists($id, $hours))
			{ //is_numeric check is redundant per above foreach, but better safe than sorry since we use it in a raw SQL query
				$newhours = $hours[$id];
				$newdate = date('Y-m-d', strtotime($date));
				$newdesc = mysql_real_escape_string($descs[$id]);
				if(strtotime($newdate) <= time() && is_numeric($newhours))
					$db->query("update Hours set fHours=$newhours, dDate=\"$newdate\", sDesc=\"$newdesc\" where iID=$id");
			}
		}
		header("Location: hours.php?taskid={$_GET['taskid']}");
	}
		
	//------Start XHTML Output--------------------------------------//
	
	require('doctype.php');
	require('appearance.php');
	echo "<link rel=\"stylesheet\" href=\"skins/$skin/tasks.css\" type=\"text/css\" title=\"$skin\" />\n";
	foreach($altskins as $altskin)
		echo "<link rel=\"alternate stylesheet\" href=\"skins/$altskin/tasks.css\" type=\"text/css\" title=\"$altskin\" />\n";
?>
<title><?php echo $appname; ?> - Edit Hours</title>
</head>
<body>
<?php
	require('sidebar.php');
?>
<div id="content"><div id="topbanner"><?php echo $currentGroup->getName(); ?></div>
<?php
	$hours = $task->getHours($currentUser);
	echo "<form method=\"post\" action=\"edithours.php?taskid={$task->getID()}\"><fieldset><legend>Edit Hours</legend>\n";
	echo "<table class=\"taskhours\">\n";
	echo "\t<thead>\n";
	echo "\t\t<tr><th colspan=\"3\">Hours Summary for {$currentUser->getFullName()}</th></tr>\n";
	echo "\t\t<tr><th>Date</th><th>Hours Spent</th><th>Description</th></tr>\n";
	echo "\t</thead>\n";
	echo "\t<tfoot>\n";
	echo "\t\t<tr><td>Total</td><td>{$task->getTotalHoursFor($currentUser)}</td><td></td></tr>\n";
	echo "\t</tfoot>\n";
	echo "\t<tbody>\n";
	foreach($hours as $hour)
		echo "\t\t<tr><td><input type=\"text\" name=\"d{$hour->getID()}\" value=\"{$hour->getDate()}\" /></td><td><input type=\"text\" name=\"h{$hour->getID()}\" value=\"{$hour->getHours()}\" /></td><td><input type=\"text\" name=\"s{$hour->getID()}\" value=\"".htmlspecialchars($hour->getDesc())."\" /></td></tr>\n";
	echo "\t</tbody>\n";
	echo "</table>\n";
	echo "<input type=\"submit\" name=\"submitted\" value=\"Submit Hours\" /> <input type=\"reset\" /></fieldset></form>\n";
	echo "<p>Cancel and <a href=\"tasks.php\">return to main tasks listing</a> or <a href=\"taskview.php?taskid={$task->getID()}\">return to task</a></p>\n</fieldset></form><div id=\"caldiv\"></div>\n";
?>
</div></body></html>
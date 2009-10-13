<?php
	include_once('globals.php');
	include_once('checklogin.php');
	include_once('classes/task.php');
	
	if(is_numeric($_GET['taskid']))
	{	
		$task = new Task($_GET['taskid'], $currentGroup->getType(), $currentGroup->getSemester(), $db);
		if($task->isValid())
		{
			if(!$task->isAssigned($currentUser))
				errorPage('Access Denied', 'You (or one of your subgroups) must be assigned to a task to add hours to it.', 403);
			//else OK
		}
		else
			errorPage('Invalid Task ID', 'The task ID provided is invalid.', 400);
		if($_POST['form'] == 'new')
		{
			//Verify input
			$subdate = strtotime($_POST['date']);
			if($POST['date'] == '')
				$message = 'ERROR: You must enter a date';
			else if(!is_numeric($_POST['hours']))
				$message = 'ERROR: Hours must be numeric';
			else if($_POST['hours'] <= 0)
				$message = 'ERROR: Hours must be positive';
			else if($_POST['date'] !== false && $subdate <= time())
				$message = $task->setHours($subdate, $currentUser, $_POST['hours'], $_POST['desc']) ? 'Hours added' : 'ERROR: An SQL error occurred. Please contact the software maintainer to correct this problem.<br />'.mysql_error();
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
<title><?php echo $appname; ?> - Task Hours</title>
</head>
<body>
<?php
	require('sidebar.php');
?>
<div id="content"><div id="topbanner"><?php echo $currentGroup->getName(); ?></div>
<?php
	echo '<p>We are assigning hours for <b>'.$task->getName()."</b>. A complete list of your entered hours is below.</p>\n";
	$hours = $task->getHours($currentUser);
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
		echo "\t\t<tr><td>{$hour->getDate()}</td><td>{$hour->getHours()}</td><td>".htmlspecialchars($hour->getDesc())."</td></tr>\n";
	echo "\t</tbody>\n";
	echo "</table>\n";
	
	echo "<form method=\"post\" action=\"taskhours.php?taskid={$_GET['taskid']}\" style=\"float: left\"><fieldset><legend>Add Hours</legend>\n";
	echo "<table>\n";
	echo "<tr><td><label for=\"date\">Date:</label></td><td><input type=\"text\" name=\"date\" id=\"date\" /> <a href=\"#\" onclick=\"cal.select(document.forms[0].date,'calsel','yyyy-MM-dd'); return false;\" id=\"calsel\">Select</a></td></tr>\n";
	echo "<tr><td><label for=\"hours\">Hours:</label></td><td><input type=\"text\" name=\"hours\" id=\"hours\" /></td></tr>\n";
	echo "<tr><td><label for=\"desc\">Short description (optional):</label></td><td><input type=\"text\" name=\"desc\" id=\"desc\" /></td></tr></table>\n";
	echo "<input type=\"submit\" value=\"Add Hours\" /><input type=\"hidden\" name=\"form\" value=\"new\" />\n";
	echo "<p>Cancel and <a href=\"tasks.php\">return to main tasks listing</a> or <a href=\"taskview.php?taskid={$task->getID()}\">return to task</a></p>\n</fieldset></form><div id=\"caldiv\"></div>\n";
?>
</div></body></html>

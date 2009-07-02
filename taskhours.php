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
		if($_POST['form'] == 'edit')
		{
			$ids = explode(',', $_POST['ids']);
			$message = '';
			foreach($ids as $id)
			{
				if(!is_numeric($id))
				{
					$message .= "<li>ID value <b>$id</b> encountered; not numeric. Skipping.</li>\n";
					continue;
				}
				$hour = new Hour($id, $db);
				if(!$hour->isValid())
				{
					$message .= "<li>ID value <b>$id</b> does not exist in the database. Skipping.</li>\n";
					continue;
				}
				else if($hour->getTaskID() != $task->getID())
				{
					$message .= "<li>ID value <b>$id</b> does not belong to this task. Skipping.</li>\n";
					continue;
				}
				else if($hour->getPerson()->getID() != $currentUser->getID())
				{
					$message .= "<li>ID value <b>$id</b> does not belong to the logged in user. Skipping.</li>\n";
					continue;
				}
				else if(!is_numeric($_POST["D$id"]) || $_POST["D$id"] < 0)
				{
					$message .= "<li>Invalid input <b>{$_POST["D$id"]}</b> entered for ID value <b>$id</b>. Skipping.</li>\n";
					continue;
				}
				if(!$hour->setHours($_POST["D$id"]))
					$message .= "<li>Unknown error setting hours for $id</li>\n";
			}
			if($message == '')
				header('Location: taskview.php?taskid='.$task->getID());
			else
				$message = "The following problems occurred when processing your request:<ul>$message</ul>";
		}
		else if($_POST['form'] == 'new')
		{
			//Verify input
			$subdate = strtotime($_POST['date']);
			if(!is_numeric($_POST['hours']))
				$message = 'ERROR: Hours must be numeric';
			else if($_POST['hours'] <= 0)
				$message = 'ERROR: Hours must be positive';
			else if($_POST['date'] !== false && $subdate <= time())
				$message = $task->setHours($subdate, $currentUser, $_POST['hours']) ? 'Hours added' : 'ERROR: An SQL error occurred. Please contact the software maintainer to correct this problem.<br />'.mysql_error();
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
		
	$hours = $task->getHours($currentUser);
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
	echo "\t\t<tr><th colspan=\"2\">Hours Summary for {$currentUser->getFullName()}</th></tr>\n";
	echo "\t\t<tr><th>Date</th><th>Hours Spent</th></tr>\n";
	echo "\t</thead>\n";
	echo "\t<tfoot>\n";
	echo "\t\t<tr><td>Total</td><td>{$task->getTotalHoursFor($currentUser)}</td></tr>\n";
	echo "\t</tfoot>\n";
	echo "\t<tbody>\n";
	foreach($hours as $hour)
		echo "\t\t<tr><td>{$hour->getDate()}</td><td>{$hour->getHours()}</td></tr>\n";
	echo "\t</tbody>\n";
	echo "</table>\n";
	if(count($dates) > 0)
	{
		echo "<form method=\"post\" action=\"taskhours.php?taskid={$_GET['taskid']}\"><fieldset><legend>Edit Hours</legend>\n";
		echo "<table><tr><th>Date</th><th>Hours</th></tr>\n";
		foreach($hours as $id => $hour)
		{
			echo "<tr><td><label for=\"D$id\">{$hour->getDate()}</label></td><td><input type=\"text\" name=\"D$id\" id=\"D$id\" value=\"{$hour->getHours()}\" /></td></tr>\n";
		}
		echo "</table>\n";
		$ids = '';
		foreach($hours as $id => $thehour)
			$ids .= "$id,";
		$ids = trim($ids, ',');
		echo "<input type=\"hidden\" name=\"ids\" value=\"$ids\" />\n";
		echo "<input type=\"submit\" value=\"Edit Hours\" /><input type=\"reset\" /><input type=\"hidden\" name=\"form\" value=\"edit\" /></fieldset></form>\n";
	}
	
	echo "<form method=\"post\" action=\"taskhours.php?taskid={$_GET['taskid']}\" style=\"float: left\"><fieldset><legend>Add Hours</legend>\n";
	echo "<label>Date: <input type=\"text\" name=\"date\" /></label> <a href=\"#\" onclick=\"cal.select(document.forms[0].date,'calsel','yyyy-MM-dd'); return false;\" id=\"calsel\">Select</a><div id=\"caldiv\"></div><br />\n";
	echo "<label>Hours: <input type=\"text\" name=\"hours\" /></label><br />\n";
	echo "<input type=\"submit\" value=\"Add Hours\" /><input type=\"hidden\" name=\"form\" value=\"new\" /></fieldset></form>\n";
	echo "<p>Cancel and <a href=\"tasks.php\">return to main tasks listing</a> or <a href=\"taskview.php?taskid={$task->getID()}\">return to task</a></p>\n";
?>
</div></body></html>

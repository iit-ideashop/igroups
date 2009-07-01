<?php
	include_once('globals.php');
	include_once('checklogin.php');
	include_once('classes/task.php');
	
	if(is_numeric($_GET['taskid']))
	{	
		$task = new Task($_GET['taskid'], $currentGroup->getType(), $currentGroup->getSemester(), $db);
		if($task->isValid())
		{
			if($currentUser->getID() != $task->getCreator()->getID() && !$currentUser->isGroupModerator($currentGroup))
				errorPage('Access Denied', 'You must be either the task owner or a group moderator to close a task.', 403);
			//else OK
		}
		else
			errorPage('Invalid Task ID', 'The task ID provided is invalid.', 400);
		$thedate = date('Y-m-d');
		if($_POST['form'] == 'submit')
		{
			$subdate = strtotime($_POST['date']);
			if($_POST['date'] !== false && $subdate <= time())
			{
				$task->setClosed($subdate);
				header('Location: taskview.php?taskid='.$task->getID());
			}
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
<title><?php echo $appname; ?> - Close Task</title>
</head>
<body>
<?php
	require('sidebar.php');
?>
<div id="content"><div id="topbanner"><?php echo $currentGroup->getName(); ?></div>
<?php
	echo '<p>We are closing the task <b>'.$task->getName()."</b></p>\n";
	echo "<form method=\"post\" action=\"taskcomplete.php?taskid={$_GET['taskid']}\"><fieldset><legend>Complete Task</legend>\n";
	echo "<label>Date completed: <input type=\"text\" name=\"date\" value=\"$thedate\" /></label> <a href=\"#\" onclick=\"cal.select(document.forms[0].date,'calsel','yyyy-MM-dd'); return false;\" id=\"calsel\">Select</a><br />\n";
	echo "<input type=\"submit\" value=\"Complete Task\" /><input type=\"reset\" /><input type=\"hidden\" name=\"form\" value=\"submit\" /></fieldset></form>\n";
	echo "<p>Cancel and <a href=\"tasks.php\">return to main tasks listing</a> or <a href=\"taskview.php?taskid={$task->getID()}\">return to task</a></p>\n";
?>
</div></body></html>

<?php
	include_once('globals.php');
	include_once('checklogin.php');
	
	if(is_numeric($_GET['taskid']))
	{	
		$query = $db->igroupsQuery('select * from Tasks where iID='.$_GET['taskid']);
		if(mysql_num_rows($query))
		{
			$task = mysql_fetch_array($query);
			if($currentUser->getID() != $task['iOwnerID'] && !$currentUser->isGroupModerator($currentGroup))
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
				$sqldate = date('Y-m-d', $subdate);
				$db->igroupsQuery("update Tasks set dClosed='$sqldate' where iID={$_GET['taskid']}");
				header('Location: tasks.php');
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
<title><?php echo $appname; ?> - Close Task</title>
</head>
<body>
<?php
	require('sidebar.php');
?>
<div id="content">
<?php
	echo '<p>We are closing the task <b>'.$task['sName']."</b></p>\n";
	echo "<form method=\"post\" action=\"taskcomplete.php?taskid={$_GET['taskid']}\"><fieldset><legend>Complete Task</legend>\n";
	echo "<label>Date completed: <input type=\"text\" name=\"date\" value=\"$thedate\" /></label>\n";
	echo "<input type=\"submit\" value=\"Complete Task\" /><input type=\"reset\" /><input type=\"hidden\" name=\"form\" value=\"submit\" /></fieldset></form>\n";
?>
</div></body></html>

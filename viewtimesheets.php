<?php
	include_once("globals.php");
	include_once("checklogin.php");
	include_once( "classes/timelog.php" );
	include_once( "classes/timeentry.php" );
	
	if (isset($_GET['user'])) {
		if (!is_numeric($_GET['user']))
			errorPage('Invalid user ID', 'The user you have selected is invalid', 400);
		$user = new Person($_GET['user'], $db);
		if ($user->getID() && !$currentGroup->isGroupMember($user))
			errorPage('Group Credentials Required', 'The user you have selected is not in your group', 403);

		$_SESSION['user'] = $_GET['user'];
	}

	function getWeekID ($date, $db) {
		$query = $db->igroupsQuery("SELECT iID FROM Weeks where dStartDate <= \"$date\" and dEndDate >= \"$date\"");
		$result = mysql_fetch_row($query);
		return $result[0];
	}

	if (isset($_GET['week'])) {
		$currentWeek = $_GET['week'];
		if ($currentWeek == 0)
			$nextWeek = 0;
		else
			$nextWeek = $_GET['week'] + 1;
	}
	else {
		$currentWeek = 0;
		$nextWeek = $currentWeek + 1;;
	}

	if (isset($_GET['editTask'])) {
		$editTask = new TimeEntry($_GET['editTask'], $db);
		if ($editTask->getUserID() != $currentUser->getID() && !$currentUser->isGroupAdministrator($currentGroup))
			unset($editTask);
	}
	if (isset($_GET['editTime'])) {
		$editTime = new TimeEntry($_GET['editTime'], $db);
		if ($editTime->getUserID() != $currentUser->getID() && !$currentUser->isGroupAdministrator($currentGroup))
			unset($editTime);
	}

	if (isset($_POST['edittime'])) {
		$editEntry = new TimeEntry($_POST['entryID'], $db);
		$newEntry = createTimeEntry( $editEntry->getUserID(), $currentGroup->getID(), $currentGroup->getSemester(), $_POST['date'], $_POST['hours'], $_POST['description'], $db );
		$editEntry->delete();
		$message = "Timesheet entry successfully edited";
	}
	if (isset($_POST['editProjTask'])) {
		$editEntry = new TimeEntry($_POST['entryID'], $db);
		$newEntry = createProjTask( $editEntry->getUserID(), $currentGroup->getID(), $currentGroup->getSemester(), $_POST['taskDate'], $_POST['taskHours'], $_POST['taskDescription'], $db );
		$editEntry->delete();
		$message = "Projected task successfully edited";
	}
	if (isset($_POST['deltime'])) {
		$editEntry = new TimeEntry($_POST['entryID'], $db);
		$editEntry->delete();
		$message = "Timesheet entry successfully deleted";
	}
	if (isset($_POST['delProjTask'])) {
		$editEntry = new TimeEntry($_POST['entryID'], $db);
		$editEntry->delete();
		$message = "Projected task successfully deleted";
	}
	if (isset($_GET['print'])) {
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<!-- This web-based application is Copyrighted &copy; 2008 Interprofessional Projects Program, Illinois Institute of Technology -->
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en"><head>
<title><?php echo $appname; ?> - View Timesheet Reports</title>
<?php
require("appearance.php");
echo "<link rel=\"stylesheet\" href=\"skins/$skin/timesheet.css\" type=\"text/css\" title=\"$skin\" />\n";
foreach($altskins as $altskin)
	echo "<link rel=\"alternate stylesheet\" href=\"skins/$altskin/timesheet.css\" type=\"text/css\" title=\"$altskin\" />\n";
?>
		<script type="text/javascript">
		<!--
		function init() {
		if(window.print)
			window.print();
		else
			alert("Please select Print from the file menu");
		}
		//-->
		</script>

	</head>
		<body onload="init()">
<?php
 /**** begin html head *****/
   require('htmlhead.php'); //starts main container
  /****end html head content ****/

	$log = $currentGroup->getTimeLog();
	$users = $currentGroup->getGroupUsers();
	$usersWithTime = array();
	$query = $db->igroupsQuery("SELECT DISTINCT iUserID FROM Timesheets WHERE iGroupID={$currentGroup->getID()} AND bProjTask=0 AND iSemesterID={$_SESSION['selectedSemester']}");
	while ($row = mysql_fetch_row($query))
		$usersWithTime[] = new Person($row[0], $db);
	$query = $db->igroupsQuery("SELECT DISTINCT t.iWeekID, w.dStartDate, w.dEndDate FROM Timesheets t, Weeks w WHERE t.iWeekID=w.iID AND t.iGroupID={$currentGroup->getID()} AND t.bProjTask=0 AND t.iSemesterID={$_SESSION['selectedSemester']} ORDER BY t.iWeekID");
	$allWeeks = array();
	while ($row = mysql_fetch_array($query))
		$allWeeks[] = $row;
	
	if ($_GET['print'] == 'indiv') {
	$timeLog = $currentGroup->getTimeLog();
	$thisWeek = getWeekID(date("Y-m-d"), $db);
	if ($_GET['week'] == '')
		$currentWeek = 0;
	else if ($_GET['week'] != 0)
		$currentWeek = $_GET['week'];
	else
		$currentWeek = 0;

		$query = $db->igroupsQuery("SELECT * FROM Weeks WHERE iID=$currentWeek");
		$week = mysql_fetch_array($query);
		$start = explode('-', $week['dStartDate']);
		$end = explode('-', $week['dEndDate']);
		if ( isset( $_SESSION['user'] ) ) {
		$selectedUser = new Person( $_SESSION['user'], $db );
		print "<h1>Timesheet Report for ".$selectedUser->getFullName()."</h1>";
		if (!$currentWeek)
			print "<h2>All Weeks</h2>"; 
		else
			print "<h2>$start[1]/$start[2]/$start[0] - $end[1]/$end[2]/$end[0]</h2>";
?>
	<table width="85%"><tr><td valign="top">
	<b>Completed Tasks</b><br />
	<table width="500">
		<thead>
		<tr><td>Date</td><td>Hours Spent</td><td>Task</td></tr>
		</thead><tfoot><tr><td colspan="3">Total Hours Spent: 
<?php
	if ($currentWeek)
		echo "<b>{$timeLog->getHoursSpentByUserAndWeek($selectedUser->getID(), $currentWeek)}</b>";
	else
		echo "<b>{$timeLog->getHoursSpentByUser($selectedUser->getID())}</b>";
?>
	</td></tr></tfoot><tbody>
<?php
		if ($currentWeek)
		       $entries = $timeLog->getEntriesByUserAndWeek($selectedUser->getID(), $currentWeek);
		else
		       $entries = $timeLog->getEntriesByUser( $selectedUser->getID() );

		foreach ( $entries as $entry ) {
			print "<tr><td valign=\"top\">".$entry->getDate()."</td><td valign=\"top\" align=\"center\">".$entry->getHoursSpent()."</td><td>".str_replace("&lt;br /&gt;", "<br />", htmlspecialchars($entry->getTaskDescription()))."</td></tr>";
		}
		if ( $entries == array() )
			print "<tr><td colspan=\"3\">No timesheet entries recorded.</td></tr>";

?>
	</tbody></table>
	</td>
	<td valign="top">
	<b>Projected Tasks for Next Week</b><br />
	<table width="500">
		<thead>
		<tr><td>Date</td><td>Hours</td><td>Task</td></tr>
		</thead>
<?php
		if ($nextWeek)
		       $entries = $timeLog->getTasksByUserAndWeek($selectedUser->getID(), $nextWeek);
		else
		       $entries = 0;

		if ($entries) {
		foreach ( $entries as $entry ) {
			print "<tr><td valign=\"top\">".$entry->getDate()."</td><td valign=\"top\" align=\"center\">".$entry->getHoursSpent()."</td><td>".str_replace("&lt;br /&gt;", "<br />", htmlspecialchars($entry->getTaskDescription()))."</td></tr>";
		}
		}
		if ( $entries == array() )
			print "<tr><td colspan=\"3\">No projected tasks recorded.</td></tr>";
		if ($entries == 0)
			print "<tr><td colspan=\"3\">Select a week from the menu on the left to view projected tasks for the next week</td></tr>";

?>
	<thead><tr><td colspan="3">Total Hours:
<?php
	if ($nextWeek)
		echo "<b>{$timeLog->getTaskHoursSpentByUserAndWeek($selectedUser->getID(), $nextWeek)}</b>";
?>
	</td></tr></thead>
	</table>
	</td></tr></table>
<?php
	} 
	}
  //include rest of html layout file
  require('htmlcontentfoot.php');// ends main container

		print "</body></html>";
		die();
	}	
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<!-- This web-based application is Copyrighted &copy; 2008 Interprofessional Projects Program, Illinois Institute of Technology -->
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en"><head>
<title>iGroups - View Timesheet Reports</title>
<?php
require("appearance.php");
echo "<link rel=\"stylesheet\" href=\"skins/$skin/timesheet.css\" type=\"text/css\" title=\"$skin\" />\n";
foreach($altskins as $altskin)
	echo "<link rel=\"alternate stylesheet\" href=\"skins/$altskin/timesheet.css\" type=\"text/css\" title=\"$altskin\" />\n";
?>
</head>
<body>
<?php
  /**** begin html head *****/
   require('htmlhead.php'); //starts main container
  /****end html head content ****/
?>
<div id="topbanner">
<?php
		print $currentGroup->getName();
?>
	</div>
<?php
	$log = $currentGroup->getTimeLog();
	$users = $currentGroup->getGroupUsers();
	$usersWithTime = array();
	$query = $db->igroupsQuery("SELECT DISTINCT iUserID FROM Timesheets WHERE iGroupID={$currentGroup->getID()} AND bProjTask=0 AND iSemesterID={$_SESSION['selectedSemester']}");
	while ($row = mysql_fetch_row($query))
		$usersWithTime[] = new Person($row[0], $db);
	$query = $db->igroupsQuery("SELECT DISTINCT t.iWeekID, w.dStartDate, w.dEndDate FROM Timesheets t, Weeks w WHERE t.iWeekID=w.iID AND t.iGroupID={$currentGroup->getID()} AND t.bProjTask=0 AND t.iSemesterID={$_SESSION['selectedSemester']} ORDER BY t.iWeekID");
	$allWeeks = array();
	while ($row = mysql_fetch_array($query))
		$allWeeks[] = $row;

?>
	<h1>Semester Hours Summary</h1>
	<ul>
	<li><a href="viewgrouptime.php">View Semester Hours Table</a></li>
	</ul>

	<br />

	<h1>Individual Time Reports</h1>
	<b>Click on user to view report</b><br />
	<table width="500">
		<thead>
		<tr><td>User</td><td align="center">Total Time Spent</td></tr>
		</thead><tfoot>
<?php
		print "<tr><td>Total</td><td>".$log->getTotalHoursSpent()."</td></tr>";
?>
	</tfoot><tbody>
<?php
	foreach ( $users as $user ) {
		print "<tr><td><a href=\"viewtimesheets.php?user=".$user->getID()."\">".$user->getFullName()."</a></td><td align=\"center\">".number_format($log->getHoursSpentByUser( $user->getID() ),1)."</td></tr>";
	}
?>
	</tbody>
	</table><br />
<table border="0" width="100%">
<tr valign="top"><td>
<?php
	if (isset($editTime)) {
?>
	<form method="post" action="viewtimesheets.php"><fieldset><legend>Edit Timesheet Entry</legend>
		<label for="date">Date (MM/DD/YYYY):</label><input type="text" id="date" name="date" onclick="ds_sh(this);" style="cursor: text" value="<?php print "{$editTime->getDate()}"; ?>" /><br />
		<label for="hours">Hours Spent:</label><input type="text" name="hours" id="hours" value="<?php print "{$editTime->getHoursSpent()}"; ?>" />
<br />
		<label for="description">Tasks Worked On:</label><br />
		<textarea name="description" id="description" cols="50" rows="5"><?php print(str_replace("&lt;br /&gt;", "\r\n", htmlspecialchars($editTime->getTaskDescription()))); ?></textarea><br />
<?php

	print "<input type=\"hidden\" name=\"entryID\" value=\"{$editTime->getID()}\" /><input type=\"submit\" name=\"edittime\" value=\"Edit Entry\" />&nbsp;<input type=\"submit\" name=\"deltime\" value=\"Delete Entry\" />";
?>
	</fieldset></form>
<?php
}
	else if (isset($editTask)) {
?>
	<form method="post" action="viewtimesheets.php"><fieldset><legend>Edit Projected Task</legend>
		<label for="taskDate">Date (MM/DD/YYYY):</label><input type="text" id="taskDate" name="taskDate" onclick="ds_sh(this);" style="cursor: text" value="<?php if (isset($editTask)) print "{$editTask->getDate()}"; ?>" /><br />
		<label for="taskHours">Estimated Hours:</label><input type="text" name="taskHours" id="taskHours" value="<?php print "{$editTask->getHoursSpent()}"; ?>" /><br />
		<label for="taskDescription">Tasks:</label><br />
		<textarea name="taskDescription" id="taskDescription" cols="50" rows="5"><?php print(str_replace("&lt;br /&gt;", "\r\n", htmlspecialchars($editTask->getTaskDescription()))); ?></textarea><br />
<?php
	print "<input type=\"hidden\" name=\"entryID\" value=\"{$editTask->getID()}\" /><input type=\"submit\" name=\"editProjTask\" value=\"Edit Task\" />&nbsp;<input type=\"submit\" name=\"delProjTask\" value=\"Delete Task\" />";
?>
	</fieldset></form>
<?php
}
	print "</td></tr></table><br />";
	if ( isset( $_SESSION['user'] ) ) {
		$selectedUser = new Person( $_SESSION['user'], $db );
		print "<form method=\"get\" action=\"viewtimesheets.php\"><fieldset><legend>Timesheet Report for ".$selectedUser->getFullName()."</legend>";
?>
	
	<select name="week">
<?php
	$timeLog = $currentGroup->getTimeLog();
	$weeks = $timeLog->getWeeks($selectedUser->getID());
	foreach ($weeks as $week) {
		$temp1 = explode( "-", $week['dStartDate'] );
		$temp2 = explode( "-", $week['dEndDate'] );
		$startDate = date( "m/d/Y", mktime( 0, 0, 0, $temp1[1], $temp1[2], $temp1[0] ) );
		$endDate = date( "m/d/Y", mktime( 0, 0, 0, $temp2[1], $temp2[2], $temp2[0] ) );
		if ($week['iID'] == $currentWeek)
			print "<option value=\"{$week['iID']}\" selected=\"selected\">{$startDate} - {$endDate}</option>";
		else
			print "<option value=\"{$week['iID']}\">{$startDate} - {$endDate}</option>";
	}
	if ($currentWeek == 0)
		print "<option value=\"0\" selected=\"selected\">All Weeks</option>";
	else
		print "<option value=\"0\">All Weeks</option>";
?>
	</select>
	<input type="submit" name="submitWeek" value="View by Week" />
	</fieldset></form>
	<br />
	[<a href="viewtimesheets.php?print=indiv&amp;week=<?php print "{$_GET['week']}"; ?>">Click to Print</a>]<br />
	<table width="100%"><tbody><tr><td valign="top">
	<b>Completed Tasks</b><br />
	<table width="500">
		<thead>
		<tr><td>Date</td><td>Hours Spent</td><td>Task</td></tr>
		</thead><tfoot><tr><td colspan="3">Total Hours Spent: 
<?php
	if ($currentWeek)
		echo "<b>{$timeLog->getHoursSpentByUserAndWeek($selectedUser->getID(), $currentWeek)}</b>";
	else
		echo "<b>{$timeLog->getHoursSpentByUser($selectedUser->getID())}</b>";
?>
	</td></tr></tfoot><tbody>
<?php
		if ($currentWeek)
		       $entries = $timeLog->getEntriesByUserAndWeek($selectedUser->getID(), $currentWeek);
		else
		       $entries = $timeLog->getEntriesByUser( $selectedUser->getID() );

		foreach ( $entries as $entry ) {
			print "<tr><td valign=\"top\">".$entry->getDate()."</td><td valign=\"top\" align=\"center\">".$entry->getHoursSpent()."</td><td><a href=\"viewtimesheets.php?editTime={$entry->getID()}\">".str_replace("&lt;br /&gt;", "<br />", htmlspecialchars($entry->getTaskDescription()))."</a></td></tr>";
		}
		if ( $entries == array() )
			print "<tr><td colspan=\"3\">No timesheet entries recorded.</td></tr>";

?>
	</tbody></table>
	</td>
	<td valign="top">
	<b>Projected Tasks for Next Week</b><br />
	<table width="500">
		<thead>
		<tr><td>Date</td><td>Hours</td><td>Task</td></tr>
		</thead><tfoot><tr><td colspan="3">Total Hours:
<?php
	if ($nextWeek)
		echo "<b>{$timeLog->getTaskHoursSpentByUserAndWeek($selectedUser->getID(), $nextWeek)}</b>";
?>
	</td></tr></tfoot><tbody>
<?php
		if ($nextWeek)
		       $entries = $timeLog->getTasksByUserAndWeek($selectedUser->getID(), $nextWeek);
		else
		       $entries = 0;

		if ($entries) {
		foreach ( $entries as $entry ) {
			print "<tr><td valign=\"top\">".$entry->getDate()."</td><td valign=\"top\" align=\"center\">".$entry->getHoursSpent()."</td><td><a href=\"viewtimesheets.php?editTask={$entry->getID()}\">".str_replace("&lt;br /&gt;", "<br />", htmlspecialchars($entry->getTaskDescription()))."</a></td></tr>";
		}
		}
		if ( $entries == array() )
			print "<tr><td colspan=\"3\">No projected tasks recorded.</td></tr>";
		if ($entries == 0)
			print "<tr><td colspan=\"3\">Select a week from the menu on the left to view projected tasks for the next week</td></tr>";

?>
	</tbody>
	</table>
	</td></tr></tbody></table><br />
<?php
	}
?>

<?php
  //include rest of html layout file
  require('htmlcontentfoot.php');// ends main container
?>
</body>
</html>

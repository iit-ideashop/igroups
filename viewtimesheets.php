<?php
	session_start();

	include_once( "classes/db.php" );
	include_once( "classes/person.php" );
	include_once( "classes/group.php" );
	include_once( "classes/timelog.php" );
	
	$db = new dbConnection();
	
	if ( isset( $_SESSION['userID'] ) ) 
		$currentUser = new Person( $_SESSION['userID'], $db );
	else
		die("You are not logged in.");
		 
	if ( isset($_SESSION['selectedGroup']) && isset($_SESSION['selectedGroupType']) && isset($_SESSION['selectedSemester']) )
		$currentGroup = new Group( $_SESSION['selectedGroup'], $_SESSION['selectedGroupType'], $_SESSION['selectedSemester'], $db );
	else
		die("You have not selected a valid group.");
	
	if (isset($_GET['user'])) {
		if (!is_numeric($_GET['user']))
			die("Invalid request");
		$user = new Person($_GET['user'], $db);
		if ($user->getID() && !$currentGroup->isGroupMember($user))
			die("This person is not in your group");

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

	if (isset($_GET['print'])) {
?>
		<html><head>
		<script language="JavaScript1.2">
		function init() {
        	if(window.print)
                	window.print();
        	else
                	alert("Please select Print from the file menu");
		}
		</script>
		<title>iGROUPS - View Timesheet Reports</title>
        <style type="text/css">
                @import url("default.css");

                table {
                        text-align:left;
                }

                thead {
                        background-color:#DDD;
                }
        </style>
	</head>
		<body onload="init()">
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
	<table width='100%'><tr><td valign='top'>
	<b>Completed Tasks</b><br>
	<table width='500'>
		<thead>
		<tr><td>Date</td><td>Hours Spent</td><td>Task</td></tr>
		</thead>
<?php
		if ($currentWeek)
                       $entries = $timeLog->getEntriesByUserAndWeek($selectedUser->getID(), $currentWeek);
                else
                       $entries = $timeLog->getEntriesByUser( $selectedUser->getID() );

		foreach ( $entries as $entry ) {
			print "<tr><td valign='top'>".$entry->getDate()."</td><td valign='top' align='center'>".$entry->getHoursSpent()."</td><td>".$entry->getTaskDescription()."</td></tr>";
		}
		if ( $entries == array() )
			print "<tr><td colspan=3>No timesheet entries recorded.</td></tr>";

?>
	<thead><tr><td colspan=3>Total Hours Spent: 
<?php
        if ($currentWeek)
                echo "<b>{$timeLog->getHoursSpentByUserAndWeek($selectedUser->getID(), $currentWeek)}</b>";
        else
                echo "<b>{$timeLog->getHoursSpentByUser($selectedUser->getID())}</b>";
?>
	</td></tr></thead>
	</table>
	</td>
	<td valign='top'>
	<b>Projected Tasks for Next Week</b><br>
	<table width='500'>
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
                        print "<tr><td valign='top'>".$entry->getDate()."</td><td valign='top' align='center'>".$entry->getHoursSpent()."</td><td>".$entry->getTaskDescription()."</td></tr>";
                }
		}
                if ( $entries == array() )
                        print "<tr><td colspan=3>No projected tasks recorded.</td></tr>";
		if ($entries == 0)
			print "<tr><td colspan=3>Select a week from the menu on the left to view projected tasks for the next week</td></tr>";

?>
        <thead><tr><td colspan=3>Total Hours:
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
		print "</body></html>";
		die();
	}	
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
   "http://www.w3.org/TR/html4/loose.dtd">

<!-- This web-based application is Copyrighted &copy; 2007 Interprofessional Projects Program, Illinois Institute of Technology -->

<html>
<head>
	<title>iGROUPS - View Timesheet Reports</title>
	<style type="text/css">
		@import url("default.css");
		
		table {
			text-align:left;
		}
		
		thead {
			background-color:#DDD;
		}
	</style>
</head>
<body>
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
	<li><a href='viewgrouptime.php' target='_blank'>View Semester Hours Table</a>
	</ul>

	<br>

	<h1>Individual Time Reports</h1>
	<b>Click on user to view report</b><br>
	<table width='500'>
		<thead>
		<tr><td>User</td><td align='center'>Total Time Spent</td></tr>
		</thead>
<?php
	foreach ( $users as $user ) {
		print "<tr><td><a href='viewtimesheets.php?user=".$user->getID()."'>".$user->getFullName()."</a></td><td align='center'>".number_format($log->getHoursSpentByUser( $user->getID() ),1)."</td></tr>";
	}
?>
	<thead>
<?php
		print "<tr><td>Total</td><td>".$log->getTotalHoursSpent()."</td></tr>";
?>
	</thead>
	</table><br>
<?php
	if ( isset( $_SESSION['user'] ) ) {
		$selectedUser = new Person( $_SESSION['user'], $db );
		print "<h1>Timesheet Report for ".$selectedUser->getFullName()."</h1>";
?>
	<form method="get" action="viewtimesheets.php">
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
                        print "<option value={$week['iID']} selected>{$startDate} - {$endDate}</option>";
                else
                        print "<option value={$week['iID']}>{$startDate} - {$endDate}</option>";
        }
        if ($currentWeek == 0)
                print "<option value=0 selected>All Weeks</option>";
        else
                print "<option value=0>All Weeks</option>";
?>
        </select>
        <input type="submit" name="submitWeek" value="View by Week">
        </form>
	<br>
	[<a href='viewtimesheets.php?print=indiv&week=<?php print "{$_GET['week']}"; ?>' target='_blank'>Click to Print</a>]<br>
	<table width='100%'><tr><td valign='top'>
	<b>Completed Tasks</b><br>
	<table width='500'>
		<thead>
		<tr><td>Date</td><td>Hours Spent</td><td>Task</td></tr>
		</thead>
<?php
		if ($currentWeek)
                       $entries = $timeLog->getEntriesByUserAndWeek($selectedUser->getID(), $currentWeek);
                else
                       $entries = $timeLog->getEntriesByUser( $selectedUser->getID() );

		foreach ( $entries as $entry ) {
			print "<tr><td valign='top'>".$entry->getDate()."</td><td valign='top' align='center'>".$entry->getHoursSpent()."</td><td>".$entry->getTaskDescription()."</td></tr>";
		}
		if ( $entries == array() )
			print "<tr><td colspan=3>No timesheet entries recorded.</td></tr>";

?>
	<thead><tr><td colspan=3>Total Hours Spent: 
<?php
        if ($currentWeek)
                echo "<b>{$timeLog->getHoursSpentByUserAndWeek($selectedUser->getID(), $currentWeek)}</b>";
        else
                echo "<b>{$timeLog->getHoursSpentByUser($selectedUser->getID())}</b>";
?>
	</td></tr></thead>
	</table>
	</td>
	<td valign='top'>
	<b>Projected Tasks for Next Week</b><br>
	<table width='500'>
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
                        print "<tr><td valign='top'>".$entry->getDate()."</td><td valign='top' align='center'>".$entry->getHoursSpent()."</td><td>".$entry->getTaskDescription()."</td></tr>";
                }
		}
                if ( $entries == array() )
                        print "<tr><td colspan=3>No projected tasks recorded.</td></tr>";
		if ($entries == 0)
			print "<tr><td colspan=3>Select a week from the menu on the left to view projected tasks for the next week</td></tr>";

?>
        <thead><tr><td colspan=3>Total Hours:
<?php
        if ($nextWeek)
                echo "<b>{$timeLog->getTaskHoursSpentByUserAndWeek($selectedUser->getID(), $nextWeek)}</b>";
?>
        </td></tr></thead>
        </table>
	</td></tr></table><br>
<?php
	}
?>

</body>
</html>

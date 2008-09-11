<?php
	session_start();

	include_once( "classes/db.php" );
	include_once( "classes/person.php" );
	include_once( "classes/group.php" );
	include_once( "classes/timelog.php" );
	
	$db = new dbConnection();
	
	if(isset($_SESSION['userID']))
		$currentUser = new Person($_SESSION['userID'], $db);
	else if(isset($_COOKIE['userID']) && isset($_COOKIE['password']) && isset($_COOKIE['selectedGroup']))
	{
		if(strpos($_COOKIE['userID'], "@") === FALSE)
			$userName = $_COOKIE['userID']."@iit.edu";
		else
			$userName = $_COOKIE['userID'];
		$user = $db->iknowQuery("SELECT iID,sPassword FROM People WHERE sEmail='".$userName."'");
		if(($row = mysql_fetch_row($user)) && (md5($_COOKIE['password']) == $row[1]))
		{
			$_SESSION['userID'] = $row[0];
			$currentUser = new Person($row[0], $db);
			$group = explode(",", $_COOKIE['selectedGroup']);
			$_SESSION['selectedGroup'] = $group[0];
			$_SESSION['selectedGroupType'] = $group[1];
			$_SESSION['selectedSemester'] = $group[2];
		}
	}
	else
		die("You are not logged in.");
		 
	if ( isset($_SESSION['selectedGroup']) && isset($_SESSION['selectedGroupType']) && isset($_SESSION['selectedSemester']) )
		$currentGroup = new Group( $_SESSION['selectedGroup'], $_SESSION['selectedGroupType'], $_SESSION['selectedSemester'], $db );
	else
		die("You have not selected a valid group.");
	
	if (isset($_GET['user']))
		$_SESSION['user'] = $_GET['user'];

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
		$now = date("Y-m-d");
		$currentWeek = getWeekID($now, $db);
		$nextWeek = $currentWeek + 1;;
	}

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
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<!-- This web-based application is Copyrighted &copy; 2008 Interprofessional Projects Program, Illinois Institute of Technology -->
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en"><head>
<title>iGroups - View Timesheet Reports</title>
<link rel="stylesheet" href="default.css" type="text/css" />
	<style type="text/css">
		table {
			text-align:left;
		}
		
		thead {
			background-color:#DDD;
		}

		tfoot {
			background-color:#DDD;
		}
	</style>
	<script type="text/javascript">
		function init() {
		if(window.print)
			window.print();
		else
			alert("Please select Print from the file menu");
		}
	</script>

</head>
<body>
<?php
require("sidebar.php");
?>
	<div id="content"><div id="topbanner">
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
	[<a href="#" onclick="init()">Click to Print</a>]<br />
	<table cellpadding="4" cellspacing="0" width="100%" style="border: thin solid black">
		<thead>
		<tr><td>User</td>
<?php
	foreach ($allWeeks as $week) {
		$temp1 = explode( "-", $week['dStartDate'] );
		$temp2 = explode( "-", $week['dEndDate'] );
		$startDate = date( "n/j", mktime( 0, 0, 0, $temp1[1], $temp1[2] ) );
		$endDate = date( "n/j", mktime( 0, 0, 0, $temp2[1], $temp2[2] ) );
		print "<td>$startDate - $endDate</td>";
	}
		print "<td>Semester Total</td></tr></thead><tfoot><tr><td>Week Average</td>";
	foreach($allWeeks as $week)
		print "<td>{$log->getAvgHoursSpentByWeek($week['iWeekID'])}</td>";
	print "<td>&nbsp;</td>";
	print "</tr>";
	print "<tr><td>Week Total</td>";
	foreach($allWeeks as $week)
		print "<td>{$log->getHoursSpentByWeek($week['iWeekID'])}</td>";
	print "<td>{$log->getTotalHoursSpent()}</td>";
	print "</tr></tfoot><tbody>";
	foreach ($usersWithTime as $user) {
		print "<tr><td>{$user->getFullName()}</td>";
		foreach ($allWeeks as $week)
			print "<td align=\"center\">{$log->getHoursSpentByUserAndWeek($user->getID(), $week['iWeekID'])}&nbsp;</td>";
		print "<td style=\"background: #DDDDDD; text-align: center\">{$log->getHoursSpentByUser($user->getID())}</td>";
		print "</tr>";
	}
	print "</tbody>";

?>
	</table><br />
</div></body>
</html>

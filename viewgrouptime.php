<?php
	include_once("globals.php");
	include_once("checklogin.php");
	include_once( "classes/timelog.php" );
	
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
<title><?php echo $appname; ?> - View Timesheet Reports</title>
<?php
require("appearance.php");
echo "<link rel=\"stylesheet\" href=\"skins/$skin/timesheet.css\" type=\"text/css\" title=\"$skin\" />\n";
foreach($altskins as $altskin)
	echo "<link rel=\"alternate stylesheet\" href=\"skins/$altskin/timesheet.css\" type=\"text/css\" title=\"$altskin\" />\n";
?>
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

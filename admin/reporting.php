<?php
	session_start();

	include_once( "../classes/db.php" );
	include_once( "../classes/person.php" );
	include_once( "../classes/group.php" );
	include_once( "../classes/quota.php" );
	include_once( "../classes/semester.php" );
	include_once( "../classes/email.php" );
	
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
		 
	if ( !$currentUser->isAdministrator() )
		die("You must be an administrator to access this page.");

	function groupSort( $array ) {
		$newArray = array();
		foreach ( $array as $group ) {
			if ( $group )
				$newArray[$group->getName()] = $group;
		}
		ksort( $newArray );
		return $newArray;
	}
	
	function peopleSort( $array ) {
		$newArray = array();
		foreach ( $array as $person ) {
			$newArray[$person->getCommaName()] = $person;
		}
		ksort( $newArray );
		return $newArray;
	}
	
	function printTR() {
		static $i=0;
		if ( $i )
			print "<tr class='shade'>";
		else
			print "<tr>";
		$i=!$i;
	}

	function getDetails( $groupID, $type, $by, $db) {
		switch ($by) 	{
			case 'all':
				$sql = "SELECT COUNT(*) FROM {$type} where iGroupID=\"{$groupID}\"";
				$result = $db->igroupsQuery( $sql );
				$num = mysql_fetch_row( $result );
				return $num[0];
				break;
			case 'week':
				$lastWeek = time() - (7*24*60*60);
				$date = date('Y-m-d H:i:s', $lastWeek);
				if ($type != 'Events')
					$sql = "SELECT COUNT(*) FROM {$type} where iGroupID=\"{$groupID}\" AND dDate>=\"{$date}\"";
				else
					$sql = "SELECT COUNT(*) FROM {$type} where iGroupID=\"{$groupID}\" AND dCreateDate>=\"{$date}\"";
				$result = $db->igroupsQuery($sql);
				$num = mysql_fetch_row( $result );
				return $num[0];
				break;
			case 'month':
				$lastMonth = time() - (30*24*60*60);
				$date = date('Y-m-d H:i:s', $lastMonth);
				if ($type != 'Events')
					$sql = "SELECT COUNT(*) FROM {$type} where iGroupID=\"{$groupID}\" AND dDate>=\"{$date}\"";
				else
					$sql = "SELECT COUNT(*) FROM {$type} where iGroupID=\"{$groupID}\" AND dCreateDate>=\"{$date}\"";
				$result = $db->igroupsQuery($sql);
				$num = mysql_fetch_row($result);
				return $num[0];
				break;
		}
	}

	function getStats( $type, $sem, $db ) {
		switch ($type) {
			case 'People':
				if ($sem == -1)
					$sql = "SELECT COUNT(*) FROM {$type}";
				else
					$sql = "SELECT COUNT(*) FROM (SELECT DISTINCT iPersonID FROM PeopleProjectMap WHERE iSemesterID={$sem}) a";
				$result = $db->iknowQuery($sql);
				$num = mysql_fetch_row($result);
				return $num[0];
				break;
			case 'Projects':
				if ($sem == -1)
					$sql = "SELECT COUNT(*) FROM {$type}";
				else
					$sql = "SELECT COUNT(*) FROM ProjectSemesterMap WHERE iSemesterID={$sem}";
				$result = $db->iknowQuery($sql);
				$num = mysql_fetch_row($result);
				if ($sem == -1) {
				$sql2 = "SELECT COUNT(*) FROM Groups";
				$result2 = $db->igroupsQuery($sql2);
				$num2 = mysql_fetch_row($result2);
				return ($num[0] + $num2[0]);
				}
				else
					return $num[0];
				break;
			case 'Files':
				if ($sem == -1)
					$sql = "SELECT COUNT(*) FROM {$type}";
				else
					$sql = "SELECT COUNT(*) FROM {$type} WHERE iSemesterID={$sem}";
				$result = $db->igroupsQuery($sql);
				$num = mysql_fetch_row($result);
				return $num[0];
				break;
			case 'Emails':
				if ($sem == -1)
					$sql = "SELECT COUNT(*) FROM {$type}";
				else
					$sql = "SELECT COUNT(*) FROM {$type} WHERE iSemesterID={$sem}";
				$result = $db->igroupsQuery($sql);
				$num = mysql_fetch_row($result);
				return $num[0];
				break;
			case 'Pictures':
				if ($sem == -1)
					$sql = "SELECT COUNT(*) FROM {$type}";
				else
					$sql = "SELECT COUNT(*) FROM {$type} WHERE iSemesterID={$sem}";
				$result = $db->igroupsQuery($sql);
				$num = mysql_fetch_row($result);
				return $num[0];
				break;
			case 'FileQuota':
				if ($sem == -1)
					$sql = "SELECT SUM(iUsed) FROM {$type}";
				else
					$sql = "SELECT SUM(iUsed) FROM {$type} WHERE iSemesterID={$sem}";
				$result = $db->igroupsQuery($sql);
				$num = mysql_fetch_row($result);
				return ((int)($num[0]/1048576));
				break;
		}
	}

	function getUserStats ( $userID, $groupID, $type, $db ) {
		switch ($type) {
			case 'Emails':
				$sql = "SELECT COUNT(*) FROM {$type} WHERE iSenderID={$userID} AND iGroupID={$groupID}";
				$result = $db->igroupsQuery($sql);
				$num = mysql_fetch_row($result);
				return $num[0];
				break;
			case 'Files':
				$sql = "SELECT COUNT(*) FROM {$type} WHERE iAuthorID={$userID} AND iGroupID={$groupID}";
				$result = $db->igroupsQuery($sql);
				$num = mysql_fetch_row($result);
				return $num[0];
				break;
		}
	}

	if ( isset( $_GET['selectSemester'] ) ) {
		$_SESSION['selectedIPROSemester'] = $_GET['semester'];
		unset( $_SESSION['selectedIPROGroup'] );
	}
	
	if ( !isset( $_SESSION['selectedIPROSemester'] ) ) {
		$semester = $db->iknowQuery( "SELECT iID FROM Semesters WHERE bActiveFlag=1" );
		$row = mysql_fetch_row( $semester );
		$_SESSION['selectedIPROSemester'] = $row[0];
	}

	if ( isset($_GET['selectGroup'])) {
		header("Location: http://igroups.iit.edu/admin/reporting.php#{$_GET['group']}");
	}
	
	if ( $_SESSION['selectedIPROSemester'] != 0)
		$currentSemester = new Semester( $_SESSION['selectedIPROSemester'], $db );
	else
		$currentSemester = 0;

?>		
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<!-- This web-based application is Copyrighted &copy; 2008 Interprofessional Projects Program, Illinois Institute of Technology -->
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en"><head>
<title>iGroups - IPRO Group Reporting</title>
<link rel="stylesheet" href="../default.css" type="text/css" />
	<style type="text/css">
		#groupSelect {
			margin-bottom:10px;
		}
	</style>
</head>
<body>
<?php
	require("sidebar.php");
?>
	<div id="content"><div id="topbanner">
<?php
		if ( $currentSemester )
			print $currentSemester->getName();
		else
			print "All iGROUPS";
?>
	</div>
	<div id="semesterSelect">
		<form method="get" action="reporting.php"><fieldset>
			<select name="semester">
<?php
			$semesters = $db->iknowQuery( "SELECT iID FROM Semesters ORDER BY iID DESC" );
			while ( $row = mysql_fetch_row( $semesters ) ) {
				$semester = new Semester( $row[0], $db );
				if ( isset($_SESSION['selectedIPROSemester']) && $_SESSION['selectedIPROSemester'] != $semester->getID())
				print "<option value=\"".$semester->getID()."\">".$semester->getName()."</option>";
				else
				print "<option value=\"".$semester->getID()."\" selected=\"selected\">".$semester->getName()."</option>";
			}
			if ( isset($_SESSION['selectedIPROSemester']) && $_SESSION['selectedIPROSemester'] == 0)
				print "<option value=\"0\" selected=\"selected\">All iGROUPS</option>";
			else
				print "<option value=\"0\">All iGROUPS</option>";
?>
			</select>
			<input type="submit" name="selectSemester" value="Select Semester" />
		</fieldset></form>
	</div>
<?php
	if ( $currentSemester ) {
		$groups = $currentSemester->getGroups();
		$groups = groupSort($groups);
	}
	else {
		$groupResults = $db->igroupsQuery( "SELECT iID FROM Groups" );
		$groups = array();
		while ( $row = mysql_fetch_row( $groupResults ) ) {
			$groups[] = new Group( $row[0], 1, 0, $db );
		}
		$groups = groupSort($groups);
	}
	
?>
<div id="groupSelect">
		<form method="get" action="reporting.php"><fieldset>
			<select name="group">
<?php
			$groups = groupSort( $groups );
			foreach ( $groups as $group ) {
				print "<option value=\"".$group->getID()."\">".$group->getName()."</option>";
			}
?>
			</select>
			<input type="submit" name="selectGroup" value="Jump to Group" />
		</fieldset></form>
	</div>
	<br />
<?php
	$numUsers = getStats('People', -1, $db);
	$numGroups = getStats('Projects', -1, $db);
	$numFiles = getStats('Files', -1, $db);
	$numEmails = getStats('Emails', -1, $db);
	$numPictures = getStats('Pictures', -1, $db);
	$size = getStats('FileQuota', -1, $db);
?>
	<div style="text-align: center">
	<h3>System-wide Statistics:</h3><br />
	<hr />
	<p><b>Tracking <?php echo "$numUsers"; ?> users in <?php echo "$numGroups" ?> groups.</b></p>
	<p><b>Files: </b><?php echo "$numFiles"; ?> using <?php echo "$size"; ?> MiB</p>
	<p><b>Emails: </b><?php echo "$numEmails"; ?></p>
	<p><b>Pictures: </b><?php echo "$numPictures"; ?></p>
	</div>
	<br />
<?php
	if ($currentSemester)
		$sID = $currentSemester->getID();
	else
		$sID = 0;

	if ($sID) {

	$numUsers = getStats('People', $sID, $db);
	$numGroups = getStats('Projects', $sID, $db);
	$numFiles = getStats('Files', $sID, $db);
	$numEmails = getStats('Emails', $sID, $db);
	$numPictures = getStats('Pictures', $sID, $db);
	$size = getStats('FileQuota', $sID, $db);
	$avgFiles = round($numFiles/$numGroups, 0);
	$avgEmails = round($numEmails/$numGroups, 0);
	$avgEmailsPerUser = round($numEmails/$numUsers, 0);
?>
	<div style="text-align: center">
	<h3>Semester Statistics:</h3><br />
	<hr />
	<p><b><?php echo "$numUsers"; ?> users in <?php echo "$numGroups" ?> groups.</b></p>
	<p><b>Files: </b><?php echo "$numFiles"; ?> using <?php echo "$size"; ?> MiB</p>
	<p><b>Emails: </b><?php echo "$numEmails"; ?></p>
	<p><b>Pictures: </b><?php echo "$numPictures"; ?></p>
	</div>
	<br />

	<table width="75%" cellpadding="3" style="border: thin solid black; style: text-align: center">
		<tr>
			<td style="background: red; text-align: center; color: white; font-size: larger; font-weight: bold" colspan="7">Usage Comparison</td>
		</tr>
		<tr align="center" style="background: #dddddd">
			<td><b>IPRO</b></td><td><b>Files Uploaded</b></td><td><b>Percentage of Mean</b></td><td><b>Emails Sent</b></td><td><b>Percentage of Mean</b></td><td><b>Emails per User</b></td><td><b>Percentage of Mean</b></td></tr>
			<tr align="center"><td>Mean of All Groups</td><td><?php print "$avgFiles"; ?></td><td>100%</td><td><?php print "$avgEmails"; ?></td><td>100%</td><td><?php print "$avgEmailsPerUser"; ?></td><td>100%</td>
		</tr>
<?php
		foreach($groups as $group) {
			$emails = getDetails($group->getID(), 'Emails', 'all', $db);
			$users = $group->getGroupMembers();
			if(count($users) != 0) { $emailsPerUser = round($emails / count($users), 1); }
			else { $emailsPerUser = 0; }
			$files = getDetails($group->getID(), 'Files', 'all', $db);
			if ($avgEmails != 0)
				$emailsPer = (round($emails/$avgEmails, 2))*100;
			if ($avgFiles != 0)
				$filesPer = (round($files/$avgFiles, 2))*100;
			if ($avgEmailsPerUser != 0)
				$emailsPerUserPer = (round($emailsPerUser/$avgEmailsPerUser, 2))*100;
			print "<tr align=\"center\"><td>{$group->getName()}</td><td>{$files}</td><td>$filesPer%</td><td>$emails</td><td>$emailsPer%</td><td>$emailsPerUser</td><td>$emailsPerUserPer%</td></tr>";
		}
?>
	</table>
<?php
	}

	if (isset($groups)) {
	foreach($groups as $group) {
		$groupID = $group->getID();
	
		$emailsAll = getDetails($groupID, 'Emails', 'all', $db);
		$emailsWeek = getDetails($groupID, 'Emails', 'week', $db);
		$emailsMonth = getDetails($groupID, 'Emails', 'month', $db);
		$filesAll = getDetails($groupID, 'Files', 'all', $db);
		$filesWeek = getDetails($groupID, 'Files', 'week', $db);
		$filesMonth = getDetails($groupID, 'Files', 'month', $db);
		$eventsAll = getDetails($groupID, 'Events', 'all', $db);
		$eventsWeek = getDetails($groupID, 'Events', 'week', $db);
		$eventsMonth = getDetails($groupID, 'Events', 'month', $db);
		$picsAll = getDetails($groupID, 'Pictures', 'all', $db);
		$picsWeek = getDetails($groupID, 'Pictures', 'week', $db);
		$picsMonth = getDetails($groupID, 'Pictures', 'month', $db);

		$users = $group->getGroupMembers();
		$users = peopleSort($users);

		$emailsAvg = round($emailsAll / count($users), 1);
		$filesAvg = round($filesAll / count($users), 1);
		$eventsAvg = round($eventsAll / count($users), 1);
		$picsAvg = round($picsAll / count($users), 1);
?>
<br />
<a name="<?php echo "{$group->getID()}"; ?>"></a>
	<table width="75%" style="border: thin solid black" cellpadding="3">
		<tr>
			<td colspan="5" style="font-size: larger; font-weight: bold; background: red; color: white; text-align: center"><?php echo "{$group->getName()}"; ?></td>
		</tr>
		<tr align="center" style="background: #dddddd">
			<td> </td> 
			<td><b>E-mails Sent</b></td>
			<td><b>Files Uploaded</b></td>
			<td><b>Events Posted</b></td>
			<td><b>Pictures Uploaded</b></td>
		</tr>
		<tr align="center">
			<td style="background: #dddddd"><b>This Week</b></td>
			<td><?php echo "$emailsWeek"; ?></td>
			<td><?php echo "$filesWeek"; ?></td>
			<td><?php echo "$eventsWeek"; ?></td>
			<td><?php echo "$picsWeek"; ?></td>
		</tr>
		<tr align="center">
			<td style="background: #dddddd"><b>This Month</b></td>
			<td><?php echo "$emailsMonth"; ?></td>
			<td><?php echo "$filesMonth"; ?></td>
			<td><?php echo "$eventsMonth"; ?></td>
			<td><?php echo "$picsMonth"; ?></td>
		</tr>
		<tr align="center">
			<td style="background: #dddddd"><b>All</b></td>
			<td><?php echo "$emailsAll"; ?></td>
			<td><?php echo "$filesAll"; ?></td>
			<td><?php echo "$eventsAll"; ?></td>
			<td><?php echo "$picsAll"; ?></td>
		</tr>
		<tr align="center">
			<td style="background: #dddddd"><b>User Avg.</b></td>
			<td><?php echo "$emailsAvg"; ?></td>
			<td><?php echo "$filesAvg"; ?></td>
			<td><?php echo "$eventsAvg"; ?></td>
			<td><?php echo "$picsAvg"; ?></td>
		</tr>
		<tr>
			<td align="center" style="background: #ffcccc" colspan="5"><b>User Statistics</b></td>
		</tr>
		<tr align="center">
		<td colspan="5">
		<table width="100%" cellpadding="3" style="border: thin solid black">
		<tr style="background: #dddddd" align="center">
			<td style="width:33%"><b>Name</b></td>
			<td style="width:33%"><b>E-mails Sent (% of group mean) (% of total mean)</b></td>
			<td style="width:33%"><b>Files Uploaded (% of mean)</b></td>
		</tr>
<?php
		foreach ($users as $user) {
			$numEmails = getUserStats($user->getID(), $group->getID(), 'Emails', $db);
			$numFiles = getUserStats($user->getID(), $group->getID(), 'Files', $db);
			if ($emailsAvg != 0)
				$emailsPer = round($numEmails / $emailsAvg, 3) * 100 . '%';
			if ($filesAvg != 0)
				$filesPer = round($numFiles / $filesAvg, 3) * 100 . '%';
			if ($avgEmailsPerUser != 0)
				$emailsPerUser = round($numEmails / $avgEmailsPerUser, 3) * 100 . '%';
			echo "<tr>";
			echo "<td>{$user->getFullName()}</td>";
			echo "<td align=\"center\">$numEmails ($emailsPer) ($emailsPerUser)</td>";
			echo "<td align=\"center\">$numFiles ($filesPer)</td>";
			echo "</tr>";
		}		

?>
		</table>
		</td>
		</tr>
</table>
<br />
<hr />						
<?php
	}
	}
?>
</div></body>
</html>

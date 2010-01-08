<?php
	include_once('../globals.php');
	include_once('checkadmin.php');
	include_once('../classes/group.php');
	include_once('../classes/quota.php');
	include_once('../classes/semester.php');
	include_once('../classes/email.php');

	function groupSort($array)
	{
		$newArray = array();
		foreach($array as $group)
		{
			if($group)
				$newArray[$group->getName()] = $group;
		}
		ksort($newArray);
		return $newArray;
	}
	
	function peopleSort($array)
	{
		$newArray = array();
		foreach($array as $person)
			$newArray[$person->getCommaName()] = $person;
		ksort($newArray);
		return $newArray;
	}

	function getDetails($groupID, $type, $by, $db)
	{
		switch($by)
		{
			case 'all':
				$sql = "SELECT COUNT(*) FROM {$type} where iGroupID=\"{$groupID}\"";
				$result = $db->query( $sql );
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
				$result = $db->query($sql);
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
				$result = $db->query($sql);
				$num = mysql_fetch_row($result);
				return $num[0];
				break;
		}
	}

	function getStats($type, $sem, $db)
	{
		switch ($type)
		{
			case 'People':
				if($sem == -1)
					$sql = "SELECT COUNT(*) FROM {$type}";
				else
					$sql = "SELECT COUNT(*) FROM (SELECT DISTINCT iPersonID FROM PeopleProjectMap WHERE iSemesterID={$sem}) a";
				$result = $db->query($sql);
				$num = mysql_fetch_row($result);
				return $num[0];
				break;
			case 'Projects':
				if($sem == -1)
					$sql = "SELECT COUNT(*) FROM {$type}";
				else
					$sql = "SELECT COUNT(*) FROM ProjectSemesterMap WHERE iSemesterID={$sem}";
				$result = $db->query($sql);
				$num = mysql_fetch_row($result);
				if($sem == -1)
				{
					$sql2 = "SELECT COUNT(*) FROM Groups";
					$result2 = $db->query($sql2);
					$num2 = mysql_fetch_row($result2);
					return ($num[0] + $num2[0]);
				}
				else
					return $num[0];
				break;
			case 'Files':
				$sql = "SELECT COUNT(*) FROM $type";
				if($sem != -1)
					$sql .= " WHERE iSemesterID=$sem";
				$result = $db->query($sql);
				$num = mysql_fetch_row($result);
				return $num[0];
				break;
			case 'Emails':
				$sql = "SELECT COUNT(*) FROM $type";
				if($sem != -1)
					$sql .= " WHERE iSemesterID=$sem";
				$result = $db->query($sql);
				$num = mysql_fetch_row($result);
				return $num[0];
				break;
			case 'Pictures':
				$sql = "SELECT COUNT(*) FROM $type";
				if($sem != -1)
					$sql .= " WHERE iSemesterID=$sem";
				$result = $db->query($sql);
				$num = mysql_fetch_row($result);
				return $num[0];
				break;
			case 'FileQuota':
				$sql = "SELECT SUM(iUsed) FROM $type";
				if($sem != -1)
					$sql .= " WHERE iSemesterID=$sem";
				$result = $db->query($sql);
				$num = mysql_fetch_row($result);
				return ((int)($num[0]/1048576));
				break;
		}
	}

	function getUserStats($userID, $groupID, $type, $db)
	{
		switch ($type)
		{
			case 'Emails':
				$sql = "SELECT COUNT(*) FROM $type WHERE iSenderID=$userID AND iGroupID=$groupID";
				$result = $db->query($sql);
				$num = mysql_fetch_row($result);
				return $num[0];
				break;
			case 'Files':
				$sql = "SELECT COUNT(*) FROM $type WHERE iAuthorID=$userID AND iGroupID=$groupID";
				$result = $db->query($sql);
				$num = mysql_fetch_row($result);
				return $num[0];
				break;
		}
	}

	if(isset($_GET['selectSemester']))
	{
		$_SESSION['selectedIPROSemester'] = $_GET['semester'];
		unset($_SESSION['selectedIPROGroup']);
	}
	
	if(!isset($_SESSION['selectedIPROSemester']))
	{
		$semester = $db->query('SELECT iID FROM Semesters WHERE bActiveFlag=1');
		$row = mysql_fetch_row($semester);
		$_SESSION['selectedIPROSemester'] = $row[0];
	}

	if(isset($_GET['selectGroup']))
		header("Location: $appurl/admin/reporting.php#{$_GET['group']}");
	
	if($_SESSION['selectedIPROSemester'] != 0)
		$currentSemester = new Semester( $_SESSION['selectedIPROSemester'], $db);
	else
		$currentSemester = 0;

	//---------Begin XHTML Output-----------------------------------//
	
	require('../doctype.php');
	require('../iknow/appearance.php');
	
	echo "<link rel=\"stylesheet\" href=\"../skins/$skin/reporting.css\" type=\"text/css\" title=\"$skin\" />\n";
	foreach($altskins as $altskin)
		echo "<link rel=\"alternate stylesheet\" href=\"../skins/$altskin/reporting.css\" type=\"text/css\" title=\"$altskin\" />\n";
?>
<title><?php echo $appname;?> - IPRO Group Reporting</title>
<style type="text/css">
#groupSelect {
	margin-bottom:10px;
}
</style>
</head>
<body>
<?php
		 /**** begin html head *****/
   require('htmlhead.php'); 
  //starts main container
  /****end html head content ****/
	echo '<div id="topbanner">';
	if($currentSemester)
		echo $currentSemester->getName();
	else
		echo 'All iGROUPS';
	echo "</div>\n";
?>
	<div id="semesterSelect">
		<form method="get" action="reporting.php"><fieldset>
			<select name="semester">
<?php
			$semesters = $db->query('SELECT iID FROM Semesters ORDER BY iID DESC');
			while($row = mysql_fetch_row($semesters))
			{
				$semester = new Semester($row[0], $db);
				if(isset($_SESSION['selectedIPROSemester']) && $_SESSION['selectedIPROSemester'] != $semester->getID())
					echo "<option value=\"".$semester->getID()."\">".$semester->getName()."</option>\n";
				else
					echo "<option value=\"".$semester->getID()."\" selected=\"selected\">".$semester->getName()."</option>\n";
			}
			if(isset($_SESSION['selectedIPROSemester']) && $_SESSION['selectedIPROSemester'] == 0)
				echo "<option value=\"0\" selected=\"selected\">All groups</option>\n";
			else
				echo "<option value=\"0\">All groups</option>\n";
?>
			</select>
			<input type="submit" name="selectSemester" value="Select Semester" />
		</fieldset></form>
	</div>
<?php
	if($currentSemester)
	{
		$groups = $currentSemester->getGroups();
		$groups = groupSort($groups);
	}
	else
	{
		$groupResults = $db->query('SELECT iID FROM Groups');
		$groups = array();
		while($row = mysql_fetch_row($groupResults))
			$groups[] = new Group($row[0], 1, 0, $db);
		$groups = groupSort($groups);
	}
?>
<div id="groupSelect">
		<form method="get" action="reporting.php"><fieldset>
			<select name="group">
<?php
	$groups = groupSort($groups);
	foreach($groups as $group)
		echo "<option value=\"".$group->getID()."\">".$group->getName()."</option>\n";
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

	echo "<div style=\"text-align: center\">\n<h3>System-wide Statistics:</h3><br /><hr />\n";
	echo "<p><b>Tracking $numUsers users in $numGroups groups.</b></p>\n";
	echo "<p><b>Files: </b>$numFiles using $size MiB</p>\n";
	echo "<p><b>Emails: </b>$numEmails</p>\n";
	echo "<p><b>Pictures: </b>$numPictures</p>\n";
	echo "</div><br />\n";

	if($currentSemester)
		$sID = $currentSemester->getID();
	else
		$sID = 0;

	if($sID)
	{

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

		<table>
		<tr class="toptitle" >
			<th colspan="7">Usage Comparison</td>
		</tr>
		<tr class="coltitle">
			<th>IPRO</th><th>Files Uploaded</th><th>Percentage of Mean</th><th>Emails Sent</th><th>Percentage of Mean</th><th>Emails per User</th><th>Percentage of Mean</th></tr>
			<tr><td>Mean of All Groups</td><td><?php print "$avgFiles"; ?></td><td>100%</td><td><?php print "$avgEmails"; ?></td><td>100%</td><td><?php print "$avgEmailsPerUser"; ?></td><td>100%</td>
		</tr>
<?php
		foreach($groups as $group)
		{
			$emails = getDetails($group->getID(), 'Emails', 'all', $db);
			$users = $group->getGroupMembers();
			if(count($users))
				$emailsPerUser = round($emails / count($users), 1);
			else
				$emailsPerUser = 0;
			$files = getDetails($group->getID(), 'Files', 'all', $db);
			if($avgEmails != 0)
				$emailsPer = (round($emails/$avgEmails, 2))*100;
			if($avgFiles != 0)
				$filesPer = (round($files/$avgFiles, 2))*100;
			if($avgEmailsPerUser != 0)
				$emailsPerUserPer = (round($emailsPerUser/$avgEmailsPerUser, 2))*100;
			echo "<tr><td>{$group->getName()}</td><td>{$files}</td><td>$filesPer%</td><td>$emails</td><td>$emailsPer%</td><td>$emailsPerUser</td><td>$emailsPerUserPer%</td></tr>\n";
		}
?>
	</table>
<?php
	}

	if(isset($groups))
	{
		foreach($groups as $group)
		{
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
      
			if( count($users) < 1)
			{    
				$usercount = 1;
			}
			else {
				$usercount = count($users);
			}
				$emailsAvg = round($emailsAll / usercount, 1);
				$filesAvg = round($filesAll / usercount, 1);
				$eventsAvg = round($eventsAll / usercount, 1);
				$picsAvg = round($picsAll / usercount, 1);
     
?>
			<br />
			<a name="<?php echo "{$group->getID()}"; ?>"></a>
			<table>
			<tr class="toptitle">
			<th colspan="5"><?php echo "{$group->getName()}"; ?></th>
			</tr>
			<tr class="coltitle">
			<th></th>
			<th>E-mails Sent</th>
			<th>Files Uploaded</th>
			<th>Events Posted</th>
			<th>Pictures Uploaded</th>
			</tr>
			<tr>
			<th class="lefttitle">This Week</th>
			<td><?php echo "$emailsWeek"; ?></td>
			<td><?php echo "$filesWeek"; ?></td>
			<td><?php echo "$eventsWeek"; ?></td>
			<td><?php echo "$picsWeek"; ?></td>
			</tr>
			<tr>
			<th class="lefttitle">This Month</th>
			<td><?php echo "$emailsMonth"; ?></td>
			<td><?php echo "$filesMonth"; ?></td>
			<td><?php echo "$eventsMonth"; ?></td>
			<td><?php echo "$picsMonth"; ?></td>
			</tr>
			<tr>
			<th class="lefttitle">All</th>
			<td><?php echo "$emailsAll"; ?></td>
			<td><?php echo "$filesAll"; ?></td>
			<td><?php echo "$eventsAll"; ?></td>
			<td><?php echo "$picsAll"; ?></td>
			</tr>
			<tr>
			<th class="lefttitle">User Avg.</th>
			<td><?php echo "$emailsAvg"; ?></td>
			<td><?php echo "$filesAvg"; ?></td>
			<td><?php echo "$eventsAvg"; ?></td>
			<td><?php echo "$picsAvg"; ?></td>
			</tr>
			<tr class="subtitle">
			<th colspan="5">User Statistics</th>
			</tr>
			<tr>
			<td colspan="5">
			<table>
			<tr class="coltitle">
			<th style="width:33%">Name</th>
			<th style="width:33%">E-mails Sent (% of group mean) (% of total mean)</th>
			<th style="width:33%">Files Uploaded (% of mean)</th>
			</tr>
<?php
			foreach($users as $user)
			{
				$numEmails = getUserStats($user->getID(), $group->getID(), 'Emails', $db);
				$numFiles = getUserStats($user->getID(), $group->getID(), 'Files', $db);
				if($emailsAvg != 0)
					$emailsPer = round($numEmails / $emailsAvg, 3) * 100 . '%';
				if($filesAvg != 0)
					$filesPer = round($numFiles / $filesAvg, 3) * 100 . '%';
				if($avgEmailsPerUser != 0)
					$emailsPerUser = round($numEmails / $avgEmailsPerUser, 3) * 100 . '%';
				echo "<tr>";
				echo "<td>{$user->getFullName()}</td>";
				echo "<td>$numEmails ($emailsPer) ($emailsPerUser)</td>";
				echo "<td>$numFiles ($filesPer)</td>";
				echo "</tr>\n";
			}		
			echo "</table></td></tr></table><br /><hr />\n";
		}
	}
?>
<?php
 	/**** begin html footer*****/
  //include rest of html layout file
  require('htmlfoot.php');
  // ends main container
  /****** end html footer*****/
?>
</body></html>

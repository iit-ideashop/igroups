<?php
	include_once('globals.php');
	include_once('checklogin.php');
	include_once('classes/task.php');
	
	function cmpByDate($hour1, $hour2) //Used in usort below for sorting hours
	{
		$date1 = strtotime($hour1->getDate());
		$date2 = strtotime($hour2->getDate());
		if($date1 == $date2)
			return 0;
		else if($hour1->getDate() > $hour2->getDate())
			return 1;
		else
			return -1;
	}
	
	function peopleSort($array)
	{
		$newArray = array();
		foreach($array as $person)
			$newArray[$person->getCommaName()] = $person;
		ksort($newArray);
		return $newArray;
	}
	
	if(!$currentUser->isGroupModerator($currentGroup) && $_GET['uid'] != $currentUser->getID())
		errorPage('Credentials Required', 'You must be a group moderator to view hours summaries', 403);
	if(is_numeric($_GET['uid']) && $_GET['uid'] > 0)
	{
		$user = new Person($_GET['uid'], $db);
		if(!$user->isValid())
			errorPage('Cannot find person', 'That person was not found', 400);
		$tasks = $user->getAssignedTasksByName($currentGroup);
	}
	//else
	//	errorPage('uID not numeric', 'uID must be a positive integer', 400);
	
	//------Start XHTML Output--------------------------------------//

	require('doctype.php');
	require('appearance.php');
	echo "<link rel=\"stylesheet\" href=\"skins/$skin/tasks.css\" type=\"text/css\" title=\"$skin\" />\n";
	foreach($altskins as $altskin)
		echo "<link rel=\"alternate stylesheet\" href=\"skins/$altskin/tasks.css\" type=\"text/css\" title=\"$altskin\" />\n";
?>
<title><?php echo $appname; ?> - Hours Summary</title>
</head>
<body>
<?php
	require('sidebar.php');
?>
<div id="content"><div id="topbanner"><?php echo $currentGroup->getName(); ?></div>
<?php
if(isset($user) && isset($tasks))
{ //Show an individual hours summary
	echo "<h1>Hours Summary for {$user->getFullName()}</h1>\n";
	$count = count($tasks);
	if($count)
	{
		$allhours = array();
		$toecho = '';
		$total = 0;
		$list = array();
		foreach($tasks as $task)
		{
			$hours = $task->getHours($currentUser);
			$toecho .= "<a name=\"T{$task->getID()}\"></a><table class=\"taskhours\">\n";
			$toecho .= "\t<thead>\n";
			$toecho .= "\t\t<tr><th colspan=\"3\">Hours Summary for {$task->getName()}</th></tr>\n";
			$toecho .= "\t\t<tr><th>Date</th><th>Hours Spent</th><th>Description</th></tr>\n";
			$toecho .= "\t</thead>\n";
			$toecho .= "\t<tfoot>\n";
			$toecho .= "\t\t<tr><td>Total</td><td>{$task->getTotalHoursFor($currentUser)}</td><td></td></tr>\n";
			$toecho .= "\t</tfoot>\n";
			$toecho .= "\t<tbody>\n";
			if(count($hours))
			{
				foreach($hours as $hour)
				{
					$allhours[$hour->getID()] = $hour;
					$toecho .= "\t\t<tr><td>{$hour->getDate()}</td><td>{$hour->getHours()}</td></tr>\n";
					if(!isset($mindate) || strtotime($hour->getDate()) < $mindate)
						$mindate = strtotime($hour->getDate());
					else if(!isset($maxdate) || strtotime($hour->getDate()) > $maxdate)
						$maxdate = strtotime($hour->getDate());
				}
			}
			else
				$toecho .= "<tr><td colspan=\"3\">No hours</td></tr>\n";
			$toecho .= "\t</tbody>\n";
			$toecho .= "</table><br />\n";
			$total += $task->getTotalHoursFor($currentUser);
			$list[$task->getID()] = $task->getName();
		}
		usort($allhours, 'cmpByDate');
		$avg = number_format($total / $count, 2);
		echo "<p>{$user->getFirstName()} has recorded $total hours for $count tasks, averaging $avg hours per task.</p>\n";
		echo "<p>Jump to...</p>\n";
		echo "<ul>\n";
		foreach($list as $id => $name)
			echo "<li><a href=\"#T$id\">$name</a></li>\n";
		echo "</ul>\n";
		
		echo "<h2>By Week</h2>\n";
		//$startdate should be a Sunday
		$startdate = getDate($mindate);
		$mindate -= $startdate['wday']*86400;
		$startdate = getDate($mindate);
		
		//$enddate can be any day
		$enddate = getDate($maxdate);
		echo "<table class=\"taskhours\"><thead><tr><th>Week Starting</th><th>Hours Recorded</th></tr></thead>\n";
		echo "<tfoot><tr><td>Total</td><td>$total</td></tr>\n";
		$numweeks = 0;
		$echoqueue2 = '';
		if($total > 0)
		{
			for($currSunday = $mindate; $currSunday <= $maxdate; $currSunday += 604800)
			{
				$currdate = getDate($currSunday);
				$currdatepretty = $currdate['month'].' '.$currdate['mday'].', '.$currdate['year'];
			
				$hoursworked = 0;
				foreach($allhours as $id => $hour)
				{
					$thistime = strtotime($hour->getDate());
					if($thistime >= $currSunday)
					{
						if($thistime >= $currSunday + 604800)
							break;
						$hoursworked += $hour->getHours();
					}
				}
				$echoqueue2 .= "<tr><td>$currdatepretty</td><td>$hoursworked</td></tr>\n";
				++$numweeks;
			}
		}
		else
			$echoqueue2 .= "<tr><td colspan=\"2\">No hours.</td></tr>\n";
		$wkavg = ($numweeks > 0 ? number_format($total / $numweeks, 2) : 0);
		echo "<tr><td>Weekly Average</td><td>$wkavg</td></tr></tfoot>\n<tbody>\n";
		echo $echoqueue2;
		echo "</tbody></table>\n";
		
		echo "<h2>By Task</h2>\n";
		
		echo $toecho;
	}
	else
		echo "<p>{$user->getFirstName()} is not assigned to any tasks, and so can have no hours.</p>\n";
}
else
{ //Show a groupwide hours summary by week.
	$totals = array(); //Holds total hours keyed by user ID
	$allhours = array(); //2D array keyed first on user ID, then contains all hours for that person
	$users = $currentGroup->getGroupMembers();
	
	foreach($users as $user)
	{
		$tasks = $user->getAssignedTasksByName($currentGroup);
		foreach($tasks as $task)
		{
			$hours = $task->getHours($user);
			if(count($hours))
			{
				foreach($hours as $hour)
				{
					if(!array_key_exists($user->getID(), $allhours))
						$allhours[$user->getID()] = array();
					$allhours[$user->getID()][$hour->getID()] = $hour;
					if(!isset($mindate) || strtotime($hour->getDate()) < $mindate)
						$mindate = strtotime($hour->getDate());
					else if(!isset($maxdate) || strtotime($hour->getDate()) > $maxdate)
						$maxdate = strtotime($hour->getDate());
				}
			}
			$totals[$user->getID()] += $task->getTotalHoursFor($currentUser);
		}
	}
	
	//$startdate should be a Sunday
	$startdate = getDate($mindate);
	$mindate -= $startdate['wday']*86400;
	$startdate = getDate($mindate);
	
	//$enddate can be any day
	$enddate = getDate($maxdate);
	
	$users = peopleSort($users);
	$numusers = count($users);
	echo "<table class=\"taskhours\"><thead><tr><th rowspan=\"2\">Week Starting</th><th colspan=\"$numusers\">Hours Recorded</th></tr>\n";
	echo "<tr>";
	foreach($users as $user)
		echo "<th>{$user->getFullName()}</th>\n";
	echo "</tr></thead>\n";
	$numweeks = 0;
	$echoqueue2 = '';
	for($currSunday = $mindate; $currSunday <= $maxdate; $currSunday += 604800)
	{
		$currdate = getDate($currSunday);
		$currdatepretty = $currdate['month'].' '.$currdate['mday'].', '.$currdate['year'];
	
		$hoursworked = array();
		foreach($allhours as $userid => $arr)
		{
			$hoursworked[$userid] = 0;
			foreach($arr as $hourid => $hour)
			{
				$thistime = strtotime($hour->getDate());
				if($thistime >= $currSunday)
				{
					if($thistime >= $currSunday + 604800)
						break;
					$hoursworked[$userid] += $hour->getHours();
				}
			}
		}
		$echoqueue2 .= "<tr><td>$currdatepretty</td>";
		foreach($users as $user)
			$echoqueue2 .= "<td>{$hoursworked[$user->getID()]}</td>";
		$echoqueue2 .= "</tr>\n";
		++$numweeks;
	}
	echo "<tfoot><tr><td>Total</td>";
	foreach($users as $user)
		echo "<td>{$totals[$user->getID()]}</td>"
	echo "</tr>\n<tr><td>Weekly Average</td>";
	foreach($users as $user)
	{
		$wkavg = ($numweeks > 0 ? number_format($totals[$user->getID()] / $numweeks, 2) : 0);
		echo "<td>$wkavg</td>";
	}
	echo "</tr></tfoot>\n<tbody>\n";
	echo $echoqueue2;
	echo "</tbody></table>\n";
}
?>
</div></body></html>

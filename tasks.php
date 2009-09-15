<?php
	include_once('globals.php');
	include_once('checklogin.php');
	include_once('classes/task.php');
	
	function alpha($person1, $person2)
	{ //Used in usort below to sort by name
		if($person1->getLastName() < $person2->getLastName())
			return -1;
		else if($person2->getLastName() < $person1->getLastName())
			return 1;
		else
			return 0;
	}
	
	if($_POST['form'] == 'addtask')
	{	//We have a new task to process
		$name = mysql_real_escape_string($_POST['name']);
		$hurdle = 0;
		if(!strlen($name))
			$message = 'ERROR: Could not create task: You must enter a name for the task.';
		else if(mysql_num_rows($db->query('select iID from Tasks where iTeamID='.$currentGroup->getID()." and sName=\"$name\"")))
			$message = 'Your group already has a task with that name.';
		else
			$hurdle++;
		$date = strtotime($_POST['due']);
		if(!$date)
			$message = 'ERROR: Could not create task: Invalid due date';
		else
		{
			$date = strftime('%Y-%m-%d', $date);
			$hurdle++;
		}
		$desc = mysql_real_escape_string($_POST['desc']);
		$esthours = 0;
		if(is_numeric($_POST['esthours']) && $_POST['esthours'] > 0)
			$esthours = intval($_POST['esthours']);
		if($hurdle == 2)
		{
			$ok = $db->query('insert into Tasks (iTeamID, iOwnerID, sName, sDescription, dDue, iEstimatedHours) values ('.$currentGroup->getID().', '.$currentUser->getID().", \"$name\", \"$desc\", \"$date\", $esthours)");
			if($ok)
				header('Location: taskassign.php?taskid='.$db->insertID());
			else
				$message = 'Task creation failed: '.mysql_error();
		}
	}
	else if(is_numeric($_GET['del']) && $currentUser->isGroupModerator($currentGroup))
	{
		$task = new Task($_GET['del'], $currentGroup->getType(), $currentGroup->getSemester(), $db);
		if($currentGroup->getID() == $task->getTeam()->getID())
		{
			$task->delete();
			$message = 'Task deletion successful';
		}
	}
	else if(is_numeric($_GET['del']))
		$message = 'ERROR: You do not have the requisite privileges to delete this task.';
	
	if(is_numeric($_GET['sort']))
		$_SESSION['taskSort'] = $_GET['sort'];
	else
		$_SESSION['taskSort'] = 2;
	$taskabs = $_SESSION['taskSort'] > 0 ? $_SESSION['taskSort'] : $_SESSION['taskSort']*-1;
	$asc = $_SESSION['taskSort'] > 0 ? 'asc' : 'desc';
	$join = '';
	$select = 'Tasks.*';
	if($taskabs == 1)
		$orderby = "order by sName $asc";
	else if($taskabs == 3)
	{
		$select .= ', count(distinct TaskAssignments.iPersonID) as count';
		$join = 'left join TaskAssignments on Tasks.iID=TaskAssignments.iTaskID';
		$orderby = "group by Tasks.iID order by count $asc";
	}
	else if($taskabs == 4)
	{
		$select .= ', sum(Hours.fHours) as hours';
		$join = 'left join Hours on Tasks.iID=Hours.iTaskID and Hours.iPersonID='.$currentUser->getID();
		$orderby = "group by Tasks.iID order by hours $asc";
	}
	else if($taskabs == 5)
		$orderby = "order by dClosed $asc";
	else //taskabs == 2
		$orderby = "order by dDue $asc";
	$viewTasks = is_numeric($_GET['viewTasks']) && ($_GET['viewTasks'] >= 1 && $_GET['viewTasks'] <= 4) ? $_GET['viewTasks'] : 3;
	$ampurl = "&amp;viewTasks=$viewTasks";
	if($viewTasks == 1)
		$tasks = $db->query("select $select from Tasks $join where Tasks.iTeamID={$currentGroup->getID()} and Tasks.iID in (select iTaskID from TaskAssignments where iPersonID={$currentUser->getID()}) and Tasks.dClosed is null $orderby");
	else if($viewTasks == 2)
		$tasks = $db->query("select $select from Tasks $join where Tasks.iTeamID={$currentGroup->getID()} and (Tasks.iID in (select iTaskID from TaskAssignments where iPersonID={$currentUser->getID()}) or Tasks.iID in (select iTaskID from TaskSubgroupAssignments where iSubgroupID in (select iSubGroupID from PeopleSubGroupMap where iPersonID={$currentUser->getID()}))) and Tasks.dClosed is null $orderby");
	else if($viewTasks == 4)
		$tasks = $db->query("select $select from Tasks $join where Tasks.iTeamID={$currentGroup->getID()} $orderby");
	else
		$tasks = $db->query("select $select from Tasks $join where Tasks.iTeamID={$currentGroup->getID()} and Tasks.dClosed is null $orderby");
	$taskSelect = array(1 => '', 2 => '', 3 => '', 4 => '');
	$taskSelect[$viewTasks] = ' selected="selected"';
	
	//------Start XHTML Output--------------------------------------//

	require('doctype.php');
	require('appearance.php');
	echo "<link rel=\"stylesheet\" href=\"skins/$skin/tasks.css\" type=\"text/css\" title=\"$skin\" />\n";
	foreach($altskins as $altskin)
		echo "<link rel=\"alternate stylesheet\" href=\"skins/$altskin/tasks.css\" type=\"text/css\" title=\"$altskin\" />\n";
?>
<title><?php echo $appname; ?> - Tasks</title>
<script type="text/javascript" src="Calendar.js"></script>
<script type="text/javascript">
function toggle(id)
{
	document.getElementById(id).style.display = (document.getElementById(id).style.display == 'block') ? 'none' : 'block';
}
var cal = new CalendarPopup("caldiv");
cal.showNavigationDropdowns();
</script>
</head>
<body>
<?php
	require('sidebar.php');
?>
<div id="content"><div id="topbanner"><?php echo $currentGroup->getName(); ?></div>
<?php
	if($currentUser->isGroupModerator($currentGroup))
		echo "<div id=\"tasksstuff\">\n"; //Needed for proper columning of the "individual summaries" list, if it appears
	//List tasks (choose: My tasks, my tasks + my subgroups, all group tasks)
	echo "<form method=\"get\" action=\"tasks.php\"><fieldset><legend>Filter Tasks</legend><select name=\"viewTasks\">\n";
	echo "<option value=\"1\"{$taskSelect[1]}>My uncompleted tasks</option>\n";
	//echo "<option value=\"2\"{$taskSelect[2]}>My uncompleted tasks (plus my subgroups)</option>\n";
	echo "<option value=\"3\"{$taskSelect[3]}>All uncompleted tasks</option>\n";
	echo "<option value=\"4\"{$taskSelect[4]}>All tasks</option>\n";
	echo "</select><input type=\"submit\" value=\"View Tasks\" /></fieldset></form>\n";
	$i = 0;
	if(mysql_num_rows($tasks))
	{
		echo '<table id="tasks"><tr>';
		$columns = array('Task', 'Due Date', 'Assigned to', 'Hours', 'Completed');
		$i = 1;
		foreach($columns as $column)
		{
			if($i == $_SESSION['taskSort'] || $i == -1*$_SESSION['taskSort'])
			{
				$arrow = $_SESSION['taskSort'] > 0 ? '&#x2193;' : '&#x2191;';
				$j = $_SESSION['taskSort'] * -1;
				echo "<th><a href=\"tasks.php?sort=$j$ampurl\">$column $arrow</a></th>";
			}
			else
				echo "<th><a href=\"tasks.php?sort=$i$ampurl\">$column</a></th>";
			$i++;
		}
		echo "</tr>\n";
		$i = 0;
		while($t = mysql_fetch_array($tasks))
		{
			$task = new Task($t['iID'], $currentGroup->getType(), $currentGroup->getSemester(), $db);
			$assignments = $db->query('select * from TaskAssignments where iTaskID='.$task->getID());
			$asns = array();
			while($assign = mysql_fetch_array($assignments))
			{
				$nm = mysql_fetch_row($db->query('select sFName, sLName from People where iID='.$assign['iPersonID']));
				$asns[$assign['iPersonID']] = $nm[0].' '.$nm[1];
				$asns[$assign['iPersonID']] .= strlen($assign['sRole']) ? ': <span class="role">'.$assign['sRole'].'</span>' : '';
			}
			/*$sgassignments = $db->query('select * from TaskSubgroupAssignments where iTaskID='.$task->getID());
			$sgasns = array();
			while($assign = mysql_fetch_array($sgassignments))
			{
				$nm = mysql_fetch_row($db->query('select sName from SubGroups where iID='.$assign['iSubgroupID']));
				$sgasns[$assign['iSubgroupID']] = $nm[0];
			}*/
			$people = count($asns) == 1 ? 'person' : 'people';
			$taskassn = count($asns) ? "<strong>".count($asns)." $people: <a href=\"javascript:toggle('P{$task->getID()}')\" class=\"toggle\">Toggle</a></strong><br /><ul id=\"P{$task->getID()}\" class=\"toggleable\">" : 'Nobody';
			$mytask = false;
			foreach($asns as $personid => $asn)
			{
				$taskassn .= "<li>$asn</li>";
				if($personid == $currentUser->getID())
					$mytask = true;
			}
			$taskassn .= count($asns) ? '</ul>' : '<br />';
			/*$taskassn .= count($sgasns) ? "<strong>Subgroups: <a href=\"javascript:toggle('S{$task->getID()}')\" class=\"toggle\">Toggle</a></strong><br /><ul id=\"S{$task->getID()}\">" : 'No subgroups';
			foreach($sgasns as $personid => $asn)
			{
				$taskassn .= "<li>$asn</li>";
				$subgr = new SubGroup($personid, $db);
				if($subgr->isSubGroupMember($currentUser))
					$mytask = true;
			}
			$taskassn .= count($sgasns) ? '</ul>' : '<br />';*/
			$overdue = (!$task->getClosed() && strtotime($task->getDue()) <= time()) ? " class=\"overdue$i\"" : " class=\"normal$i\"";
			if($mytask)
			{
				$hours = mysql_fetch_row($db->query("select sum(fHours) from Hours where iTaskID={$task->getID()} and iPersonID={$currentUser->getID()}"));
				$myhours = ($hours[0] == '') ? '0' : $hours[0];
			}
			else
				$myhours = 'N/A';
			$taskClosed = $task->getClosed() ? $task->getClosed() : 'No';
			echo "\n<tr$overdue><td><a href=\"taskview.php?taskid={$task->getID()}\">{$task->getName()}</a></td><td>{$task->getDue()}</td><td class=\"assignments\">$taskassn</td><td>$myhours</td><td>$taskClosed</td></tr>";
			$i = ($i ? 0 : 1);
		}
		echo "\n</table><br />\n";
	}
	
	//Add a task
	if(!$currentUser->isGroupGuest($currentGroup))
	{
		echo "<form method=\"post\" action=\"tasks.php\" id=\"addtask\" style=\"float: left\"><fieldset><legend>Add Task</legend>\n";
		echo "<table>\n";
		echo "<tr><td><label for=\"name\">Name:</label></td><td><input type=\"text\" name=\"name\" id=\"name\" /></td></tr>\n";
		echo "<tr><td><label for=\"due\">Due:</label></td><td><input type=\"text\" name=\"due\" id=\"due\" /> <a href=\"#\" onclick=\"cal.select(document.forms[1].due,'calsel','yyyy-MM-dd'); return false;\" id=\"calsel\">Select</a><br /></td></tr>\n";
		echo "<tr><td><label for=\"esthours\">Estimated hours required (optional):</label></td><td><input type=\"text\" name=\"esthours\" id=\"esthours\" /></td></tr>\n";
		echo "<tr><td><label for=\"desc\">Description:</label></td><td><textarea name=\"desc\" id=\"desc\" rows=\"5\" cols=\"40\"></textarea></td></tr>\n";
		echo "</table>\n";
		echo "<input type=\"submit\" value=\"Add\" /><input type=\"hidden\" name=\"form\" value=\"addtask\" /></fieldset></form>\n";
		$printcaldiv = true;
	}
	else
		$printcaldiv = false;
	
	//Individual summaries
	if($currentUser->isGroupModerator($currentGroup))
	{
		echo "</div><div id=\"indsummaries\">\n";
		echo "<h1>Hours Summaries</h1>\n";
		echo "<h2>Groupwide</h2>\n";
		echo "<ul><li><a href=\"hourssummary.php\">By Week</a></li></ul>\n";
		echo "<h2>Individual</h2>\n";
		echo "<ul>\n";
		$members = $currentGroup->getGroupMembers();
		usort($members, 'alpha');
		foreach($members as $person)
			echo "<li><a href=\"hourssummary.php?uid={$person->getID()}\">{$person->getFullName()}</a></li>\n";
		echo "</ul>\n";
		echo "<div id=\"caldiv\"></div></div>\n";
		$printcaldiv = false;
	}
	if($printcaldiv)
		echo "<div id=\"caldiv\"></div>\n";
?>
</div></body></html>

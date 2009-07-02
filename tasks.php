<?php
	include_once('globals.php');
	include_once('checklogin.php');
	include_once('classes/task.php');
	
	if($_POST['form'] == 'addtask')
	{	//We have a new task to process
		$name = mysql_real_escape_string($_POST['name']);
		$hurdle = 0;
		if(!strlen($name))
			$message = 'ERROR: Could not create task: You must enter a name for the task.';
		else if(mysql_num_rows($db->igroupsQuery('select iID from Tasks where iTeamID='.$currentGroup->getID()." and sName=\"$name\"")))
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
		if($hurdle == 2)
		{
			$ok = $db->igroupsQuery('insert into Tasks (iTeamID, iOwnerID, sName, sDescription, dDue) values ('.$currentGroup->getID().', '.$currentUser->getID().", \"$name\", \"$desc\", \"$date\")");
			if($ok)
				header('Location: taskassign.php?taskid='.$db->igroupsInsertID());
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
	
	$viewTasks = is_numeric($_GET['viewTasks']) && ($_GET['viewTasks'] >= 1 && $_GET['viewTasks'] <= 4) ? $_GET['viewTasks'] : 3;
	if($viewTasks == 1)
		$tasks = $db->igroupsQuery('select * from Tasks where iTeamID='.$currentGroup->getID().' and iID in (select iTaskID from TaskAssignments where iPersonID='.$currentUser->getID().') and dClosed is null order by dDue');
	else if($viewTasks == 2)
		$tasks = $db->igroupsQuery('select * from Tasks where iTeamID='.$currentGroup->getID().' and (iID in (select iTaskID from TaskAssignments where iPersonID='.$currentUser->getID().') or iID in (select iTaskID from TaskSubgroupAssignments where iSubgroupID in (select iSubGroupID from PeopleSubGroupMap where iPersonID='.$currentUser->getID().'))) and dClosed is null order by dDue');
	else if($viewTasks == 4)
		$tasks = $db->igroupsQuery('select * from Tasks where iTeamID='.$currentGroup->getID().' order by dDue');
	else
		$tasks = $db->igroupsQuery('select * from Tasks where iTeamID='.$currentGroup->getID().' and dClosed is null order by dDue');
	$taskSelect = array(1 => '', 2 => '', 3 => '', 4 => '');
	$taskSelect[$viewTasks] = ' selected="selected"';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<!-- This web-based application is Copyrighted &copy; 2008 Interprofessional Projects Program, Illinois Institute of Technology -->
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en"><head>
<?php
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
	document.getElementById(id).style.display = (document.getElementById(id).style.display == 'none') ? 'block' : 'none';
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
	//List tasks (choose: My tasks, my tasks + my subgroups, all group tasks)
	echo "<form method=\"get\" action=\"tasks.php\"><fieldset><legend>Filter Tasks</legend><select name=\"viewTasks\">\n";
	echo "<option value=\"1\"{$taskSelect[1]}>My uncompleted tasks</option>\n";
	echo "<option value=\"2\"{$taskSelect[2]}>My uncompleted tasks (plus my subgroups)</option>\n";
	echo "<option value=\"3\"{$taskSelect[3]}>All uncompleted tasks</option>\n";
	echo "<option value=\"4\"{$taskSelect[4]}>All tasks</option>\n";
	echo "</select><input type=\"submit\" value=\"View Tasks\" /></fieldset></form>\n";
	if(mysql_num_rows($tasks))
	{
		echo '<table id="tasks"><tr><th>Task</th><th>Due Date</th><th>Assigned to</th><th>Hours</th><th>Completed</th></tr>';
		while($t = mysql_fetch_array($tasks))
		{
			$task = new Task($t['iID'], $currentGroup->getType(), $currentGroup->getSemester(), $db);
			$assignments = $db->igroupsQuery('select * from TaskAssignments where iTaskID='.$task->getID());
			$asns = array();
			while($assign = mysql_fetch_array($assignments))
			{
				$nm = mysql_fetch_row($db->igroupsQuery('select sFName, sLName from People where iID='.$assign['iPersonID']));
				$asns[$assign['iPersonID']] = $nm[0].' '.$nm[1];
				$asns[$assign['iPersonID']] .= strlen($assign['sRole']) ? ': <span class="role">'.$assign['sRole'].'</span>' : '';
			}
			$sgassignments = $db->igroupsQuery('select * from TaskSubgroupAssignments where iTaskID='.$task->getID());
			$sgasns = array();
			while($assign = mysql_fetch_array($sgassignments))
			{
				$nm = mysql_fetch_row($db->igroupsQuery('select sName from SubGroups where iID='.$assign['iSubgroupID']));
				$sgasns[$assign['iSubgroupID']] = $nm[0];
			}
			$taskassn = count($asns) ? "<strong>People: <a href=\"javascript:toggle('P{$task->getID()}')\" class=\"toggle\">Toggle</a></strong><br /><ul id=\"P{$task->getID()}\">" : 'No people';
			$mytask = false;
			foreach($asns as $personid => $asn)
			{
				$taskassn .= "<li>$asn</li>";
				if($personid == $currentUser->getID())
					$mytask = true;
			}
			$taskassn .= count($asns) ? '</ul>' : '<br />';
			$taskassn .= count($sgasns) ? "<strong>Subgroups: <a href=\"javascript:toggle('S{$task->getID()}')\" class=\"toggle\">Toggle</a></strong><br /><ul id=\"S{$task->getID()}\">" : 'No subgroups';
			foreach($sgasns as $personid => $asn)
			{
				$taskassn .= "<li>$asn</li>";
				$subgr = new SubGroup($personid, $db);
				if($subgr->isSubGroupMember($currentUser))
					$mytask = true;
			}
			$taskassn .= count($sgasns) ? '</ul>' : '<br />';
			if($currentUser->getID() == $task->getCreator()->getID() || $currentUser->isGroupModerator($currentGroup))
				$taskassn .= '<a href="taskassign.php?taskid='.$task->getID().'" class="taskassign">Change assignments</a>';
			$overdue = (!$task->getClosed() && strtotime($task->getDue()) <= time()) ? ' class="overdue"' : '';
			if($mytask)
			{
				$hours = mysql_fetch_row($db->igroupsQuery("select sum(fHours) from Hours where iTaskID={$task->getID()} and iPersonID={$currentUser->getID()}"));
				$myhours = "{$hours[0]}<br /><a href=\"taskhours.php?taskid={$task->getID()}\">Add hours</a>";
			}
			else
				$myhours = 'N/A';
			$taskClosed = $task->getClosed() ? $task->getClosed() : (($currentUser->isGroupModerator($currentGroup) || $task->getCreator()->getID() == $currentUser->getID()) ? '<a href="taskcomplete.php?taskid='.$task->getID().'">Close task</a>' : '');
			echo "\n<tr$overdue><td><a href=\"taskview.php?taskid={$task->getID()}\">{$task->getName()}</a></td><td>{$task->getDue()}</td><td class=\"assignments\">$taskassn</td><td>$myhours</td><td>$taskClosed</td></tr>";
		}
		echo "\n</table>\n";
	}
	//Add a task
	echo "<form method=\"post\" action=\"tasks.php\" style=\"float: left\"><fieldset><legend>Add Task</legend>\n";
	echo "<label>Name: <input type=\"text\" name=\"name\" /></label><br />\n";
	echo "<label>Due: <input type=\"text\" name=\"due\" /></label> <a href=\"#\" onclick=\"cal.select(document.forms[1].due,'calsel','yyyy-MM-dd'); return false;\" id=\"calsel\">Select</a><div id=\"caldiv\"></div><br />\n";
	echo "<label>Description:<br /><textarea name=\"desc\" rows=\"5\" cols=\"80\"></textarea></label><br />\n";
	echo "<input type=\"submit\" value=\"Add\" /><input type=\"hidden\" name=\"form\" value=\"addtask\" /></fieldset></form>\n";
?>
</div></body>
</html>

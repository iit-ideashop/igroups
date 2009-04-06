<?php
	include_once('globals.php');
	include_once('checklogin.php');
	include_once('classes/task.php');
	
	function peopleSort($array)
	{
		$newArray = array();
		foreach($array as $person)
			$newArray[$person->getCommaName()] = $person;
		ksort($newArray);
		return $newArray;
	}
		
	if(is_numeric($_GET['taskid']))
	{	
		$task = new Task($_GET['taskid'], $currentGroup->getType(), $currentGroup->getSemester(), $db);
		if($task->isValid())
		{
			if($currentUser->getID() != $task->getCreator()->getID() && !$currentUser->isGroupModerator($currentGroup))
				errorPage('Access Denied', 'You must be either the task owner or a group moderator to make assignments to a task.', 403);
			//else OK
		}
		else
			errorPage('Invalid Task ID', 'The task ID provided is invalid.', 400);
		if($_POST['form'] == 'submit')
		{
			$db->igroupsQuery('delete from TaskAssignments where iTaskID='.$task->getID());
			$db->igroupsQuery('delete from TaskSubgroupAssignments where iTaskID='.$task->getID());
			if(is_array($_POST['person']))
			{
				foreach($_POST['person'] as $id => $person)
					$db->igroupsQuery('insert into TaskAssignments (iTaskID, iPersonID) values ('.$task->getID().", $id)");
			}
			if(is_array($_POST['subgroup']))
			{
				foreach($_POST['subgroup'] as $id => $subgroup)
					$db->igroupsQuery('insert into TaskSubgroupAssignments (iTaskID, iSubgroupID) values ('.$task->getID().", $id)");
			}
			header('Location: tasks.php');
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
<title><?php echo $appname; ?> - Task Assignments</title>
</head>
<body>
<?php
	require('sidebar.php');
?>
<div id="content"><div id="topbanner"><?php echo $currentGroup->getName(); ?></div>
<?php
	echo '<p>We are assigning tasks for <b>'.$task->getName()."</b></p>\n";
	echo "<form method=\"post\" action=\"taskassign.php?taskid={$_GET['taskid']}\">\n";
	echo "<fieldset><fieldset id=\"people\"><legend>People</legend>\n";
	echo "<table>\n";
	$members = $currentGroup->getGroupMembers();
	$members = peopleSort($members);
	$people = $task->getAssignedPeople();
	$i = 1;
	foreach($members as $person)
	{
		if($i == 1)
			echo '<tr>';
		if($people[$person->getID()])
			echo '<td><input type="checkbox" name="person['.$person->getID().']" id="person'.$person->getID().'" checked="checked" /></td>';
		else
			echo '<td><input type="checkbox" name="person['.$person->getID().']" id="person'.$person->getID().'" /></td>';

		echo '<td><label for="person'.$person->getID().'">'.$person->getFullName().'</label></td>';
		if($i == 3)
		{
			print "</tr>\n";
			$i = 1;
		}
		else
			$i++;
	}			
	echo "</table></fieldset><br />\n";
	$subgroups = $currentGroup->getSubGroups();
	if($subgroups)
	{
		$subgr = $task->getAssignedSubgroups();
		echo "<fieldset id=\"subgroups\"><legend>Subgroups:</legend>\n";
		echo "<table>\n";
		$i=1;
		foreach($subgroups as $subgroup)
		{
			if($i == 1)
				echo '<tr>';
			echo '<td><input type="checkbox" id="subgroup'.$subgroup->getID().'" name="subgroup['.$subgroup->getID().']"'.(($subgr[$subgroup->getID()]) ? ' checked="checked"' : '').' />&nbsp;';
			echo '<label for="subgroup'.$subgroup->getID().'">'.$subgroup->getName().'</label></td>';
			if($i == 3)
			{
				echo "</tr>\n";
				$i = 1;
			}
			else
				$i++;
		}
		if($i != 1)
			echo '</tr>';
		echo "</table></fieldset>\n";
	}
	echo "<input type=\"submit\" value=\"Submit Assignments\" /><input type=\"reset\" /><input type=\"hidden\" name=\"form\" value=\"submit\" /></fieldset></form>\n";
	echo "<p>Cancel and <a href=\"tasks.php\">return to main tasks listing</a> or <a href=\"taskview.php?taskid={$task->getID()}\">return to task</a></p>\n";
?>
</div></body></html>

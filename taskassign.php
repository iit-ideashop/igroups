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
			$oldassigned = $task->getAllAssigned();
			$db->query('delete from TaskAssignments where iTaskID='.$task->getID());
			//$db->query('delete from TaskSubgroupAssignments where iTaskID='.$task->getID());
			if(is_array($_POST['person']))
			{
				foreach($_POST['person'] as $id => $person)
					if(is_numeric($id))
						$task->assignPerson(new Person($id, $db));
			}
			/*if(is_array($_POST['subgroup']))
			{
				foreach($_POST['subgroup'] as $id => $subgroup)
					$db->query('insert into TaskSubgroupAssignments (iTaskID, iSubgroupID) values ('.$task->getID().", $id)");
			}*/
			
			//Inform newly-assigned and newly-deassigned people of the change
			$newassigned = $task->getAllAssigned();
			foreach($newassigned as $id => $person)
			{
				if(!array_key_exists($id, $oldassigned) && $person->receivesNotifications())
				{
					$msg = "This is an auto-generated $appname notification to let you know that you have been assigned to a task. Task information is below.\n\n";
					$msg .= "Group: {$task->team->getName()}\nTask Name: {$task->getName()}\nURL: $appurl/taskview.php?taskid={$task->getID()}\n\n";
					$msg .= "--- $appname System Auto-Generated Massage\n\n";
					$msg .= "To stop receiving task assignment notifications, visit $appurl/contactinfo.php";
			
					mail($person->getEmail(), "[$appname] Task Assignment", $msg, "From: $appname Support <$contactemail>");
				}
			}
			foreach($oldassigned as $id => $person)
			{
				if(!array_key_exists($id, $newassigned) && $person->receivesNotifications())
				{
					$msg = "This is an auto-generated $appname notification to let you know that you have been unassigned from a task. Task information is below.\n\n";
					$msg .= "Group: {$task->team->getName()}\nTask Name: {$task->getName()}\nURL: $appurl/taskview.php?taskid={$task->getID()}\n\n";
					$msg .= "--- $appname System Auto-Generated Massage\n\n";
					$msg .= "To stop receiving task assignment notifications, visit $appurl/contactinfo.php";
			
					mail($person->getEmail(), "[$appname] Task Assignment", $msg, "From: $appname Support <$contactemail>");
				}
			}
			header('Location: taskview.php?taskid='.$task->getID());
		}
	}
	else if($_GET['taskid'])
		errorPage('Invalid Task ID', 'The task ID provided is invalid.', 400);
	else
		errorPage('Missing Task ID', 'No task ID was provided.', 400);
	
	//------Start XHTML Output--------------------------------------//

	require('doctype.php');
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
	/*$subgroups = $currentGroup->getSubGroups();
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
	}*/
	echo "<input type=\"submit\" value=\"Submit Assignments\" /><input type=\"reset\" /><input type=\"hidden\" name=\"form\" value=\"submit\" /></fieldset></form>\n";
	echo "<p>Cancel and <a href=\"tasks.php\">return to main tasks listing</a> or <a href=\"taskview.php?taskid={$task->getID()}\">return to task</a></p>\n";
?>
</div></body></html>

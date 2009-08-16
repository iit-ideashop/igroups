<?php
	include_once('globals.php');
	include_once('checklogin.php');
	include_once('classes/task.php');
	
	if(!$currentUser->isGroupModerator($currentGroup) && $_GET['uid'] != $currentUser->getID())
		errorPage('Credentials Required', 'You must be a group moderator to view hours summaries', 403);
	if(is_numeric($_GET['uid']) && $_GET['uid'] > 0)
	{
		$user = new Person($_GET['uid'], $db);
		if(!$user->isValid())
			errorPage('Cannot find person', 'That person was not found', 400);
	}
	else
		errorPage('uID not numeric', 'uID must be a positive integer', 400);
	
	$tasks = $user->getAssignedTasksByName($currentGroup);
	
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
	echo "<h1>Hours Summary for {$user->getFullName()}</h1>\n";
	$count = count($tasks);
	if($count)
	{
		$toecho = '';
		$total = 0;
		$avg = number_format($total / $count, 2);
		$list = array();
		foreach($tasks as $task)
		{
			$hours = $task->getHours($currentUser);
			$toecho .= "<a name=\"T{$task->getID()}\"></a><table class=\"taskhours\">\n";
			$toecho .= "\t<thead>\n";
			$toecho .= "\t\t<tr><th colspan=\"2\">Hours Summary for {$task->getName()}</th></tr>\n";
			$toecho .= "\t\t<tr><th>Date</th><th>Hours Spent</th></tr>\n";
			$toecho .= "\t</thead>\n";
			$toecho .= "\t<tfoot>\n";
			$toecho .= "\t\t<tr><td>Total</td><td>{$task->getTotalHoursFor($currentUser)}</td></tr>\n";
			$toecho .= "\t</tfoot>\n";
			$toecho .= "\t<tbody>\n";
			foreach($hours as $hour)
				$toecho .= "\t\t<tr><td>{$hour->getDate()}</td><td>{$hour->getHours()}</td></tr>\n";
			$toecho .= "\t</tbody>\n";
			$toecho .= "</table><br />\n";
			$total += $task->getTotalHoursFor($currentUser);
			$list[$task->getID()] = $task->getName();
		}
		echo "<p>{$user->getFirstName()} has recorded $total hours for $count tasks, averaging $avg hours per task.</p>\n";
		echo "<p>Jump to...</p>\n";
		echo "<ul>\n";
		foreach($list as $id => $name)
			echo "<li><a href=\"#T$id\">$name</a></li>\n";
		echo "</ul>\n";
		echo $toecho;
	}
	else
		echo "<p>{$user->getFirstName()} is not assigned to any tasks, and so can have no hours.</p>\n";
?>
</div></body></html>

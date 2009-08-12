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
	
	$tasks = $user->getAssignedTasks($currentGroup);

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
	foreach($tasks as $task)
	{
		$hours = $task->getHours($currentUser);
		echo "<table class=\"taskhours\">\n";
		echo "\t<thead>\n";
		echo "\t\t<tr><th colspan=\"2\">Hours Summary for {$task->getName()}</th></tr>\n";
		echo "\t\t<tr><th>Date</th><th>Hours Spent</th></tr>\n";
		echo "\t</thead>\n";
		echo "\t<tfoot>\n";
		echo "\t\t<tr><td>Total</td><td>{$task->getTotalHoursFor($currentUser)}</td></tr>\n";
		echo "\t</tfoot>\n";
		echo "\t<tbody>\n";
		foreach($hours as $hour)
			echo "\t\t<tr><td>{$hour->getDate()}</td><td>{$hour->getHours()}</td></tr>\n";
		echo "\t</tbody>\n";
		echo "</table>\n";
	}
?>
</div></body></html>

<?php
	include_once('../globals.php');
	include_once('../checklogingroupless.php');
	require_once('../classes/group.php');
	require_once('../classes/topic.php');
	require_once('../classes/globaltopic.php');
	require_once('../classes/thread.php');
	require_once('../classes/post.php');
	require_once('../classes/semester.php');
	
	if(isset($_SESSION['selectedGroup']) && isset($_SESSION['selectedGroupType']) && isset($_SESSION['selectedSemester']) )
		$currentGroup = new Group( $_SESSION['selectedGroup'], $_SESSION['selectedGroupType'], $_SESSION['selectedSemester'], $db );

	if($currentUser->isAdministrator() && isset($_GET['adminView']))
		$_SESSION['adminView'] = 1;

	if(isset($_GET['a']))
		unset($_SESSION['adminView']);

	if(isset($_GET['selectSemester']))
	{
		$currentSemesterID = $_GET['semester'];
		if(isset($_GET['adminView']))
			$_SESSION['adminSemester'] = $_GET['semester'];
	}
	else if(isset($_SESSION['adminSemester']))
		$currentSemesterID = $_SESSION['adminSemester'];
	else if(isset($currentGroup))
		$currentSemesterID = $currentGroup->getSemester();
	else
		$currentSemesterID = 0;

	// if superadmin, get all groups in semester
	if(isset($_SESSION['adminView']))
	{
		$query = $db->query("SELECT * FROM Projects p, ProjectSemesterMap m WHERE m.iSemesterID={$currentSemesterID} AND m.iProjectID=p.iID ORDER BY p.sIITID");
		$groups = array();
		while ($result = mysql_fetch_array($query))
			$groups[] = new Group($result['iID'], 0, $currentSemesterID, $db);
	}
	else
		$groups = $currentUser->getGroupsBySemester($currentSemesterID);

	$globalTopics = getGlobalTopics($db);
	
	//-------Begin XHTML Output-------------------------------------//
	
	require('../doctype.php');
	require('../iknow/appearance.php');
	
	echo "<link rel=\"stylesheet\" href=\"../skins/$skin/dboard.css\" type=\"text/css\" title=\"$skin\" />\n";
	foreach($altskins as $altskin)
		echo "<link rel=\"alternate stylesheet\" href=\"../skins/$altskin/dboard.css\" type=\"text/css\" title=\"$altskin\" />\n";
?>
<title><?php echo $appname; ?> - Discussion Board</title>
<script type="text/javascript" src="ChangeLocation.js"></script>
</head>
<body>
<?php
	
  /**** begin html head *****/
   require('htmlhead.php'); 
  //starts main container
  /****end html head content ****/
?>
	<h1>About Discussion Board</h1>
	<p>Welcome to the new <?php echo $appname; ?> Discussion Board. Listed below are the discussion board topics you have access to: one for every group you are a member of in the selected semester, and some additional global topics. In global topics, you can communicate with people outside of your IPRO, including IPRO support staff. Each topic is then organized into threads, which consist of a list of posts. You can contribute to an existing thread by posting a reply, or starting a new thread.</p>
	<p>The Discussion Board is currently in beta version, so please bear with us as we work out the bugs. We plan on expanding its features in the future, and we welcome your thoughts and suggestions.</p>
	<hr />
	<br />
	<form method="get" action="dboard.php"><fieldset>
	<select name="semester"><option value="0">All semesters</option>
<?php
	if(isset($_SESSION['adminView']))
		$semesters = $db->query("SELECT iID FROM Semesters ORDER BY iID DESC");
	else
		$semesters = $db->query("select distinct iSemesterID from PeopleProjectMap where iPersonID=".$currentUser->getID()." order by iSemesterID desc");
	while($row = mysql_fetch_row($semesters))
	{
		$semester = new Semester($row[0], $db);
		if ($currentSemesterID == $semester->getID())
			echo "<option value=\"".$semester->getID()."\" selected=\"selected\">".$semester->getName()."</option>";
		else
			echo "<option value=\"".$semester->getID()."\">".$semester->getName()."</option>";
	}
?>
	</select><input type="submit" name="selectSemester" value="Select Semester" />
	</fieldset></form><br />
	<table width="85%" cellspacing="0" cellpadding="5">
	<tr><th style="width: 65%">Topics</th><th>Threads</th><th>Posts</th><th>Last Post</th></tr>
	<?php
	if(count($groups) > 0)
	{
		echo "<tr><td class=\"topic_heading\" colspan=\"4\">IPRO Project Discussion</td></tr>";

		foreach($groups as $group)
		{
			$topic = new Topic($group->getID(), $db);
			$lastPost = $topic->getLastPost();
			if($lastPost)
			{
				$author = $lastPost->getAuthor();
				$text = "{$lastPost->getDateTime()}<br />By: {$lastPost->getAuthorLink()}";
			}
			else
				$text = "<i>No Posts</i>";
			if(!$currentSemesterID || $group->getSemester() == $currentSemesterID)
				echo "<tr><td class=\"subtopic_heading\"><a href=\"viewTopic.php?id={$group->getID()}&amp;type={$group->getType()}&amp;semester={$group->getSemester()}\">{$group->getName()} Discussion</a></td><td align=\"center\">{$topic->getThreadCount()}</td><td align=\"center\">{$topic->getPostCount()}</td><td align=\"center\">$text</td></tr>";
		}
	}
	echo "<tr><td class=\"topic_heading\" colspan=\"4\">Global Topic Discussion</td></tr>";

	foreach($globalTopics as $topic)
	{
		$lastPost = $topic->getLastPost();
		if($lastPost)
		{
			$author = $lastPost->getAuthor();
			$text = "{$lastPost->getDateTime()}<br />By: {$lastPost->getAuthorLink()}";
		}
		else
			$text = "<i>No Posts</i>";

		echo "<tr><td class=\"subtopic_heading\"><a href=\"viewTopic.php?id={$topic->getID()}&amp;global=true\">{$topic->getName()}</a></td><td align=\"center\">{$topic->getThreadCount()}</td><td align=\"center\">{$topic->getPostCount()}</td><td align=\"center\">{$text}</td></tr>";
	}
?>
</table>
<?php
 	/**** begin html footer*****/
  //include rest of html layout file
  require('htmlfoot.php');
  // ends main container
  /****** end html footer*****/
?>

</body>
</html>

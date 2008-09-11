<?php
	session_start();
	require_once( "../classes/db.php" );
	require_once( "../classes/person.php" );
	require_once( "../classes/group.php" );
	require_once( "../classes/topic.php" );
	require_once( "../classes/globaltopic.php" );
	require_once( "../classes/thread.php" );
	require_once( "../classes/post.php" );
	require_once( "../classes/semester.php" );

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

	if(isset($_SESSION['selectedGroup']) && isset($_SESSION['selectedGroupType']) && isset($_SESSION['selectedSemester']) )
		$currentGroup = new Group( $_SESSION['selectedGroup'], $_SESSION['selectedGroupType'], $_SESSION['selectedSemester'], $db );
	else if(isset($_SESSION['adminView']) || isset($_GET['adminView']))
	{
	}
	else
		die("You have not selected a valid group.");

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
		$query = $db->igroupsQuery("SELECT * FROM Projects p, ProjectSemesterMap m WHERE m.iSemesterID={$currentSemesterID} AND m.iProjectID=p.iID ORDER BY p.sIITID");
		$groups = array();
		while ($result = mysql_fetch_array($query))
			$groups[] = new Group($result['iID'], 0, $currentSemesterID, $db);
	}
	else
		$groups = $currentUser->getGroupsBySemester($currentSemesterID);

	$globalTopics = getGlobalTopics($db);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<!-- This web-based application is Copyrighted &copy; 2008 Interprofessional Projects Program, Illinois Institute of Technology -->
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en"><head>
<title>iGroups - Discussion Board</title>
<link rel="stylesheet" href="dboard.css" type="text/css" />
<link rel="stylesheet" href="../default.css" type="text/css" />
</head>
<body>
<?php
require("sidebar.php");
?>
<div id="content"><div id="topbanner">
<?php
	if(isset($currentGroup))
		print $currentGroup->getName();
?>	
</div>
<h1>About Discussion Board</h1>
<p>Welcome to the new iGroups Discussion Board. Listed below are the discussion board topics you have access to: one for every group you are a member of in the selected semester, and some additional global topics. In global topics, you can communicate with people outside of your IPRO, including IPRO support staff. Each topic is then organized into threads, which consist of a list of posts. You can contribute to an existing thread by posting a reply, or starting a new thread.</p>
<p>The Discussion Board is currently in beta version, so please bear with us as we work out the bugs. We plan on expanding its features in the future, and we welcome your thoughts and suggestions.</p>
<hr />
<br />
<form method="get" action="dboard.php"><fieldset>
	<select name="semester">
<?php
		$semesters = $db->iknowQuery("SELECT iID FROM Semesters ORDER BY iID DESC");
		while($row = mysql_fetch_row($semesters))
		{
			$semester = new Semester( $row[0], $db );
			if ($currentSemesterID == $semester->getID())
				print "<option value=\"".$semester->getID()."\" selected=\"selected\">".$semester->getName()."</option>";
			else
				print "<option value=\"".$semester->getID()."\">".$semester->getName()."</option>";
		}
?>
	</select><input type="submit" name="selectSemester" value="Select Semester" />
</fieldset></form><br />
<table width="85%" cellspacing="0" cellpadding="5">
<tr><th style="width: 65%">Topics</th><th>Threads</th><th>Posts</th><th>Last Post</th></tr>
<?php
if(count($groups) > 0)
{
	print "<tr><td class=\"topic_heading\" colspan=\"4\">IPRO Project Discussion</td></tr>";

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
		print "<tr><td class=\"subtopic_heading\"><a href=\"viewTopic.php?id={$group->getID()}&amp;type={$group->getType()}&amp;semester={$group->getSemester()}\">{$group->getName()} Discussion</a></td><td align=\"center\">{$topic->getThreadCount()}</td><td align=\"center\">{$topic->getPostCount()}</td><td align=\"center\">$text</td></tr>";
	}
}
print "<tr><td class=\"topic_heading\" colspan=\"4\">Global Topic Discussion</td></tr>";

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

	print "<tr><td class=\"subtopic_heading\"><a href=\"viewTopic.php?id={$topic->getID()}&amp;global=true\">{$topic->getName()}</a></td><td align=\"center\">{$topic->getThreadCount()}</td><td align=\"center\">{$topic->getPostCount()}</td><td align=\"center\">{$text}</td></tr>";
}
?>
</table>
</div></body></html>

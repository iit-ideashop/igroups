<?php
	session_start();
	include_once( "../classes/db.php" );
	include_once( "../classes/person.php" );
	include_once( "../classes/group.php" );
	include_once( "../classes/topic.php" );
	include_once( "../classes/globaltopic.php" );
	include_once( "../classes/thread.php" );
	include_once( "../classes/post.php" );

	$db = new dbConnection();

	$THREADS_PER_PAGE = 10;

	if ( isset( $_SESSION['userID'] ) )
                $currentUser = new Person( $_SESSION['userID'], $db );
        else
                 die("You are not logged in.");

	if (isset($_GET['id'])) {
		if (!is_numeric($_GET['id']))
			die("Invalid Request");
		if (isset($_GET['global'])) {
			$currentTopic = new GlobalTopic($_GET['id'], $db);
			if (!$currentTopic)
	                        die("No such topic");
			$link = "viewTopic.php?id={$currentTopic->getID()}&global=true";
			$_SESSION['global'] = 1;
		}
		else {
			if (!is_numeric($_GET['type']) || !is_numeric($_GET['semester']))
				die("Invalid Request");
			$currentTopic = new Topic($_GET['id'], $db);
			$currentGroup = new Group($currentTopic->getID(), $_GET['type'], $_GET['semester'], $db);
			if (!$currentGroup)
				die("No such topic");
			$link = "viewTopic.php?id={$currentTopic->getID()}&type={$currentGroup->getType()}&semester={$currentGroup->getSemester()}";
			$_SESSION['global'] = 0;
		}
	}
	else
		die("No topic selected");

	if (isset($currentGroup) && !$currentUser->isGroupMember($currentGroup) && !isset($_SESSION['adminView']))
		die("You are not a member of this group");

	if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
		if ((isset($currentGroup) && $currentUser->isGroupModerator($currentGroup)) || isset($_SESSION['adminView'])) {
			$thread = new Thread($_GET['delete'], $db);
			$thread->delete();
		}
	}

	$allThreads = $currentTopic->getThreads();
	$_SESSION['topicID'] = $currentTopic->getID();
	$_SESSION['topicLink'] = $link;
	
	if (isset($currentGroup)) {
		$_SESSION['groupType'] = $currentGroup->getType();
		$_SESSION['groupSemester'] = $currentGroup->getSemester();
	}

	// determine pages
	$pages = array();
	$threads = array();
	if (isset($_GET['start']) && is_numeric($_GET['start']) && $_GET['start'] > 0 && $_GET['start'] % $THREADS_PER_PAGE == 0) {
		$currentPage = (int)($_GET['start']/$THREADS_PER_PAGE)+1;
		$lastPage = ceil(count($allThreads)/$THREADS_PER_PAGE);
                $lastStart = floor(count($allThreads)/$THREADS_PER_PAGE)*$THREADS_PER_PAGE;

		$prevStart1 = $_GET['start'] - $THREADS_PER_PAGE;
                $prevPage1 = $currentPage-1;
                $pages[] = "<a href='{$link}&start={$prevStart1}'>Previous</a>";

		if ($currentPage > 2 && !($currentPage == 3 && $currentPage == $lastPage)) {
			$firstStart1 = 0;
			$firstPage1 = 1;
			$pages[] = "<a href='{$link}&start={$firstStart1}'>$firstPage1</a>";
			$pages[] = "...";
		}	
		
		if ($currentPage == $lastPage && ($lastPage-2 > 0)) {
			$lastPage2 = $lastPage-2;
			$lastStart2 = $lastStart-2*$THREADS_PER_PAGE;
			$pages[] = "<a href='{$link}&start={$lastStart2}'>$lastPage2</a>";
		}

		$pages[] = "<a href='{$link}&start={$prevStart1}'>{$prevPage1}</a>";

		$pages[] = "{$currentPage}";

		if ((count($allThreads)-$_GET['start']) > $THREADS_PER_PAGE) {
			$nextStart1 = $_GET['start'] + $THREADS_PER_PAGE;
			$nextPage1 = $currentPage+1;
			$pages[] = "<a href='{$link}&start={$nextStart1}'>$nextPage1</a>";
			if ((count($allThreads)-$_GET['start']) > 2*$THREADS_PER_PAGE) {
				$pages[] = "...";
                                $pages[] = "<a href='{$link}&start={$lastStart}'>$lastPage</a>";
			}
			$pages[] = "<a href='{$link}&start={$nextStart1}'>Next</a>";
		}
		// get thread range
		for ($i=$_GET['start']; $i<($_GET['start']+$THREADS_PER_PAGE); $i++) {
			if ($allThreads[$i])
				$threads[] = $allThreads[$i];
		}
	}
	else {
		$currentPage = 1;	
		$pages[] = "1";
		if (count($allThreads) > $THREADS_PER_PAGE) {
			$pages[] = "<a href='{$link}&start={$THREADS_PER_PAGE}'>2</a>";
			if (count($allThreads) > 2*$THREADS_PER_PAGE) {
				$newStart = 2*$THREADS_PER_PAGE;
				$pages[] = "<a href='{$link}&start={$newStart}'>3</a>";
			}
			if (count($allThreads) > 3*$THREADS_PER_PAGE) {
				$pages[] = "...";
				$lastPage = ceil(count($allThreads)/$THREADS_PER_PAGE);
				$lastStart = floor(count($allThreads)/$THREADS_PER_PAGE)*$THREADS_PER_PAGE;
				$pages[] = "<a href='{$link}&start={$lastStart}'>$lastPage</a>";
			}
			$pages[] = "<a href='{$link}&start={$THREADS_PER_PAGE}'>Next</a>";
		}
		// first 20 threads
		for ($i=0; $i<$THREADS_PER_PAGE; $i++) {
			if ($allThreads[$i])
				$threads[] = $allThreads[$i];
		}
	}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" http://www.w3.org/TR/html4/loose.dtd">

<!-- This web-based application is Copyrighted &copy; 2007 Interprofessional Projects Program, Illinois Institute of Technology -->

<html>
<head>
<style type="text/css">
        @import url("dboard.css");
</style>
</head>
<body>

<div id="topbanner">
<?php
	if (!isset($currentGroup)) {
		$topicName = $currentTopic->getName();
		print "$topicName";
	}
	else {
		$topicName = $currentGroup->getName() . " Discussion";
		print "$topicName";
	}

	$_SESSION['topicName'] = $topicName;
?>        
</div>

<table class='noborder' width='90%' align='center'>
<tr><td align='left'><font size='-1'><b>Goto Page: 
<?php
foreach($pages as $page)
	print "$page&nbsp;";
?>
</b></td>
<td align='center'><?php print "<font size='-1' style='bold'><b><a href='dboard.php'>iGroups Discussion Board</a> -> <a href='$link'>$topicName</a></b></font>"; ?></td><td align='right' class='post_options'><b><a href='create.php?mode=thread'><img src='img/newthread.gif' border='0'></a></b></font></td></tr></table>

<table width='90%' cellspacing='0' cellpadding='5' border='0' align='center'>
<tr><th width="45%" colspan='2'>Threads</th><th>Replies</th><th>Author</th><th>Views</th><th>Last Post</th></tr>
<?php

if (count($threads) > 0) {
foreach ($threads as $thread) {
	$lastPost = $thread->getLastPost();
	if ($lastPost) 
		$text = "{$lastPost->getDateTime()}<br>{$lastPost->getAuthorLink()}";
	else
		$text = "<i>No Posts</i>";
	if ((isset($currentGroup) && $currentUser->isGroupModerator($currentGroup)) || isset($_SESSION['adminView'])) {
		$delete = "&nbsp;&nbsp;[<a href='{$_SESSION['topicLink']}&delete={$thread->getID()}'>Delete</a>]";
	}
	else
		$delete = "";
	print "<tr><td width='1%'><img src='img/thread.gif'></td><td><b><a href='viewThread.php?id={$thread->getID()}'><font size='-1'>{$thread->getName()}</font></a></b>$delete</td><td align='center'>{$thread->getPostCount()}</td><td align='center'>{$thread->getAuthorLink()}</td><td align='center'>{$thread->getViews()}</td><td align='center'>$text</td></tr>";
}
}
else
	print "<tr><td colspan='6' align='center'><i>There are currently no threads for this topic.</i></td></tr>";

?>
</table>
<table class='noborder' width='90%' align='center'><tr><td align='left'><font size='-1'><b>Goto Page: 
<?php
foreach($pages as $page)
        print "$page&nbsp;";
?>
</b></td><td align='center'><?php print "<font size='-1' style='bold'><b><a href='dboard.php'>iGroups Discussion Board</a> -> <a href='$link'>$topicName</a></b></font>"; ?></td><td align='right' class='post_options'><b><a href='create.php?mode=thread'><img src='img/newthread.gif' border='0'></a></b></font></td></tr><tr><td><font size='-1'><b>Page # <?php print "$currentPage"; ?></b></font></td></tr></table>

</body>
</html>

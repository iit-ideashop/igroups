<?php
	session_start();
	include_once( "../classes/db.php" );
	include_once( "../classes/person.php" );
	include_once( "../classes/group.php" );
	include_once( "../classes/topic.php" );
	include_once( "../classes/globaltopic.php" );
	include_once( "../classes/thread.php" );
	include_once( "../classes/post.php" );
	include_once( "../classes/watchlist.php" );

	$db = new dbConnection();

	$POSTS_PER_PAGE = 20;

	if ( isset( $_SESSION['userID'] ) )
                $currentUser = new Person( $_SESSION['userID'], $db );
        else
                 die("You are not logged in.");

	if (isset($_SESSION['topicID'])) {
                 if ($_SESSION['global']) {
                        $currentTopic = new GlobalTopic($_SESSION['topicID'], $db);
                 }
                 else {
                        $currentTopic = new Topic($_SESSION['topicID'], $db);
			$currentGroup = new Group($_SESSION['topicID'], $_SESSION['groupType'], $_SESSION['groupSemester'], $db);
                 }
        }
        else
                 die("No topic selected");

	if (isset($_GET['id'])) {
		if (!is_numeric($_GET['id']))
                        die("Invalid Request");
		$currentThread = new Thread($_GET['id'], $db);
		if (!$currentThread)
			die("No such thread");
		if ($currentThread->getTopicID() != $_SESSION['topicID'])
			die("No such thread");	
	}
	else
		die("No thread selected");

	if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
                if ((isset($currentGroup) && $currentUser->isGroupModerator($currentGroup)) || isset($_SESSION['adminView'])) {
                        $post = new Post($_GET['delete'], $db);
			$thread = new Thread($post->getThreadID(), $db);
			if ($thread->getPostCount() == 1) {
				$thread->delete();
				$post->delete();
				header("Location: {$_SESSION['topicLink']}");
			}
                        $post->delete();
                }
        }

	$watchList = new WatchList($currentThread->getID(), $db);
	
	if (isset($_GET['watchThread']))
		$watchList->addToWatchList($currentUser);

	if (isset($_GET['unwatchThread']))
		$watchList->removeFromWatchList($currentUser);

	$_SESSION['threadID'] = $currentThread->getID();
	$allPosts = $currentThread->getPosts();
	$currentThread->incViews();
	$currentThread->updateDB();
	$nextThread = $currentThread->getNext();
	$prevThread = $currentThread->getPrev();
	$link = "viewThread.php?id={$currentThread->getID()}";

	// determine pages
        $pages = array();
        $posts = array();

        if (isset($_GET['start']) && is_numeric($_GET['start']) && $_GET['start'] > 0 && $_GET['start'] % $POSTS_PER_PAGE == 0) {
                $currentPage = (int)($_GET['start']/$POSTS_PER_PAGE)+1;
                $lastPage = ceil(count($allPosts)/$POSTS_PER_PAGE);
                $lastStart = floor(count($allPosts)/$POSTS_PER_PAGE)*$POSTS_PER_PAGE;

                $prevStart1 = $_GET['start'] - $POSTS_PER_PAGE;
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
                        $lastStart2 = $lastStart-2*$POSTS_PER_PAGE;
                        $pages[] = "<a href='{$link}&start={$lastStart2}'>$lastPage2</a>";
                }

                $pages[] = "<a href='{$link}&start={$prevStart1}'>{$prevPage1}</a>";

                $pages[] = "{$currentPage}";

                if ((count($allPosts)-$_GET['start']) > $POSTS_PER_PAGE) {
                        $nextStart1 = $_GET['start'] + $POSTS_PER_PAGE;
                        $nextPage1 = $currentPage+1;
                        $pages[] = "<a href='{$link}&start={$nextStart1}'>$nextPage1</a>";
                        if ((count($allPosts)-$_GET['start']) > 2*$POSTS_PER_PAGE) {
                                $pages[] = "...";
                                $pages[] = "<a href='{$link}&start={$lastStart}'>$lastPage</a>";
                        }
                        $pages[] = "<a href='{$link}&start={$nextStart1}'>Next</a>";
                }
                // get thread range
                for ($i=$_GET['start']; $i<($_GET['start']+$POSTS_PER_PAGE); $i++) {
                        if ($allPosts[$i])
                                $posts[] = $allPosts[$i];
                }
        }
	 else {
                $currentPage = 1;
                $pages[] = "1";
                if (count($allPosts) > $POSTS_PER_PAGE) {
                        $pages[] = "<a href='{$link}&start={$POSTS_PER_PAGE}'>2</a>";
                        if (count($allPosts) > 2*$POSTS_PER_PAGE) {
                                $newStart = 2*$POSTS_PER_PAGE;
                                $pages[] = "<a href='{$link}&start={$newStart}'>3</a>";
                        }
                        if (count($allPosts) > 3*$POSTS_PER_PAGE) {
                                $pages[] = "...";
                                $lastPage = ceil(count($allPosts)/$POSTS_PER_PAGE);
                                $lastStart = floor(count($allPosts)/$POSTS_PER_PAGE)*$POSTS_PER_PAGE;
                                $pages[] = "<a href='{$link}&start={$lastStart}'>$lastPage</a>";
                        }
                        $pages[] = "<a href='{$link}&start={$POSTS_PER_PAGE}'>Next</a>";
                }
                // first 20 threads
                for ($i=0; $i<$POSTS_PER_PAGE; $i++) {
                        if ($allPosts[$i])
                                $posts[] = $allPosts[$i];
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
	print "{$_SESSION['topicName']}";
?>        
</div>

<table class='noborder' width='90%' align='center'><tr><td colspan='3'><font size='+1'><b><u><?php print "{$currentThread->getName()}"; ?></u></b></font></td></tr>
<tr><td align='left' colspan='3'>
<?php
        if (!$watchList->isOnWatchlist($currentUser)) {
                print "<a href='{$link}&watchThread=true'>Watch This Thread</a>&nbsp;&nbsp;";
                print "(Receive an e-mail every time a new post is added.)<br>";
        }
        else {
                print "<i>You are currently watching this thread.</i><br>";
                print "<a href='{$link}&unwatchThread=true'>Unwatch This Thread</a><br>";
        }
?>
</td></tr>
<tr><td align='left'><font size='-1'><b>Goto Page: 
<?php
foreach($pages as $page)
        print "$page&nbsp;";
?>
</b></td><td align='center'><?php print "<font size='-1' style='bold'><b><a href='dboard.php'>iGroups Discussion Board</a> -> <a href='{$_SESSION['topicLink']}'>{$_SESSION['topicName']}</a></b></font>"; ?></td><td class='post_options' align='right'><b><a href='create.php?mode=thread'><img src='img/newthread.gif' border='0'></a>&nbsp;<a href='create.php?mode=post'><img src='img/newpost.gif' border='0'></a></b></font></td></tr></table>

<table width='90%' cellspacing='0' cellpadding='5' border='1' align='center'>
<tr><td class='view_options' colspan='2' align='right'><b>
<?php
	if ($nextThread && $prevThread)
		$nav = "<a href='viewThread.php?id={$prevThread->getID()}'>View Previous Thread</a> :: <a href='viewThread.php?id={$nextThread->getID()}'>View Next Thread</a>";
	else if ($nextThread)
		$nav = "<a href='viewThread.php?id={$nextThread->getID()}'>View Next Thread</a>";
	else if ($prevThread)
		$nav = "<a href='viewThread.php?id={$prevThread->getID()}'>View Previous Thread</a>";
	else
		$nav = '';
	print "$nav";
?>
</b></td></tr>
<a name='top'><tr><th>Author</th><th>Message</th></tr></a>
<?php

foreach ($posts as $post) {
	print "<tr><td valign='top' width='20%'><font size='-1'><b>{$post->getAuthorLink()}</b></font><br>";
	$author = $post->getAuthor();
	if (!$_SESSION['global']) {
		$group = new Group ($currentThread->getGroupID(), $_SESSION['groupType'], $_SESSION['groupSemester'], $db);
		if ($author->isGroupAdministrator($group))
			$title = "Group Administrator";
		else if ($author->isGroupModerator($group))
			$title = "Group Moderator";
		else if ($author->isGroupGuest($group))
			$title = "Group Guest";
		else if ($author->isGroupMember($group))
			$title = "Group Member";
		else
			$title = "IPRO Staff";
	}
	else if ($author->isAdministrator())
		$title = "IPRO Staff";
	else
		$title = "";
	print "<font size='-1'><b>$title</b></font></td>";
	if ((isset($currentGroup) && $currentUser->isGroupModerator($currentGroup)) || isset($_SESSION['adminView']))
		$delete = "<br>[<a href='viewThread.php?id={$currentThread->getID()}&delete={$post->getID()}'>Delete</a>]";
	else
		$delete = "";
	print "<td><img src='img/icon_minipost.gif'><font size='-2'>Posted: {$post->getDateTime()}</font>&nbsp; $delete<hr>";
	print "<pre>{$post->getBody()}</pre><br><br></td></tr>";
	print "<tr><td><font size='-1'><a href='#top'>Back to Top</a></font></td><td>&nbsp;</td></tr>";
	print "<tr><td class='divide' colspan='2'></td></tr>";
}

?>
<tr><td class='view_options' colspan='2' align='right'><b><?php print "$nav"; ?></b></td></tr>
</table>
<table class='noborder' width='90%' align='center'><tr><td align='left'><font size='-1'><b>Goto Page: 
<?php
foreach($pages as $page)
        print "$page&nbsp;";
?>
</b></td><td align='center'><?php print "<font size='-1' style='bold'><b><a href='dboard.php'>iGroups Discussion Board</a> -> <a href='{$_SESSION['topicLink']}'>{$_SESSION['topicName']}</a></b></font>"; ?></td><td class='post_options' align='right'><b><a href='create.php?mode=thread'><img src='img/newthread.gif' border='0'></a>&nbsp;<a href='create.php?mode=post'><img src='img/newpost.gif' border='0'></a></b></font></td></tr></table>
</body>
</html>

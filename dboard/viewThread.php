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

        if(isset($_GET['id']))
        {
        	$query = mysql_fetch_array($db->igroupsQuery("select * from Threads where iID=".$_GET['id']));
        	if(!count($query))
        		die("Invalid thread ID");
        	$query2 = mysql_fetch_array($db->igroupsQuery("select * from GlobalTopics where iID=".$query['iTopicID']));
        	if($_COOKIE['global'])
        	{
        		$currentTopic = new GlobalTopic($query['iTopicID'], $db);
        		setcookie('topic', $currentTopic->getID(), time()+60*60*6);
        	}
        	else
        	{
        		$currentTopic = new Topic($query['iTopicID'], $db);
        		setcookie('topic', $currentTopic->getID(), time()+60*60*6);
        		$currentGroup = new Group($currentTopic->getID(), $_COOKIE['groupType'], $_COOKIE['groupSemester'], $db);
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
		if ($currentThread->getTopicID() != $_COOKIE['topic'])
			die("Thread and topic mismatch");	
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
				header("Location: {$_COOKIE['topicLink']}");
			}
                        $post->delete();
                }
        }

	$watchList = new WatchList($currentThread->getID(), $db);
	
	if (isset($_GET['watchThread']))
		$watchList->addToWatchList($currentUser);

	if (isset($_GET['unwatchThread']))
		$watchList->removeFromWatchList($currentUser);

	setcookie('thread', $currentThread->getID(), time()*60*60*6);
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
                $pages[] = "<a href=\"{$link}&start={$prevStart1}\">Previous</a>";

                if ($currentPage > 2 && !($currentPage == 3 && $currentPage == $lastPage)) {
                        $firstStart1 = 0;
                        $firstPage1 = 1;
                        $pages[] = "<a href=\"{$link}&amp;start={$firstStart1}\">$firstPage1</a>";
                        $pages[] = "...";
                }

                if ($currentPage == $lastPage && ($lastPage-2 > 0)) {
                        $lastPage2 = $lastPage-2;
                        $lastStart2 = $lastStart-2*$POSTS_PER_PAGE;
                        $pages[] = "<a href=\"{$link}&amp;start={$lastStart2}\">$lastPage2</a>";
                }

                $pages[] = "<a href=\"{$link}&amp;start={$prevStart1}\">{$prevPage1}</a>";

                $pages[] = "{$currentPage}";

                if ((count($allPosts)-$_GET['start']) > $POSTS_PER_PAGE) {
                        $nextStart1 = $_GET['start'] + $POSTS_PER_PAGE;
                        $nextPage1 = $currentPage+1;
                        $pages[] = "<a href=\"{$link}&amp;start={$nextStart1}\">$nextPage1</a>";
                        if ((count($allPosts)-$_GET['start']) > 2*$POSTS_PER_PAGE) {
                                $pages[] = "...";
                                $pages[] = "<a href=\"{$link}&amp;start={$lastStart}\">$lastPage</a>";
                        }
                        $pages[] = "<a href=\"{$link}&amp;start={$nextStart1}\">Next</a>";
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
                        $pages[] = "<a href='{$link}&amp;start={$POSTS_PER_PAGE}'>2</a>";
                        if (count($allPosts) > 2*$POSTS_PER_PAGE) {
                                $newStart = 2*$POSTS_PER_PAGE;
                                $pages[] = "<a href=\"{$link}&amp;start={$newStart}\">3</a>";
                        }
                        if (count($allPosts) > 3*$POSTS_PER_PAGE) {
                                $pages[] = "...";
                                $lastPage = ceil(count($allPosts)/$POSTS_PER_PAGE);
                                $lastStart = floor(count($allPosts)/$POSTS_PER_PAGE)*$POSTS_PER_PAGE;
                                $pages[] = "<a href=\"{$link}&amp;start={$lastStart}\">$lastPage</a>";
                        }
                        $pages[] = "<a href=\"{$link}&amp;start={$POSTS_PER_PAGE}\">Next</a>";
                }
                // first 20 threads
                for ($i=0; $i<$POSTS_PER_PAGE; $i++) {
                        if ($allPosts[$i])
                                $posts[] = $allPosts[$i];
                }
        }

	if($_COOKIE['global'])
		$globaltext = "&amp;topicID=".$_GET['id']."&amp;global=true";
	else
		$globaltext = "&amp;topicID=".$_GET['id'];
	$threadtext = "&amp;thread=".$_COOKIE['thread'];
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<!-- This web-based application is Copyrighted &copy; 2008 Interprofessional Projects Program, Illinois Institute of Technology -->
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en"><head>
<title>iGroups - Discussion Board - View Thread</title>
<link rel="stylesheet" href="dboard.css" type="text/css" />
<link rel="stylesheet" href="../default.css" type="text/css" />
</head>
<body>
<?php
require("sidebar.php");
?>
<div id="content"><div id="topbanner">
<?php
	print "{$_COOKIE['topicName']}";
?>        
</div>

<table class="noborder" width="85%"><tr><td colspan="3" style="font-size: larger; font-weight: bold; text-decoration: underline"><?php print "{$currentThread->getName()}"; ?></td></tr>
<tr><td align="left" colspan="3">
<?php
        if (!$watchList->isOnWatchlist($currentUser)) {
                print "<a href=\"{$link}&amp;watchThread=true\">Watch This Thread</a>&nbsp;&nbsp;";
                print "(Receive an e-mail every time a new post is added.)<br />";
        }
        else {
                print "<i>You are currently watching this thread.</i><br />";
                print "<a href=\"{$link}&amp;unwatchThread=true\">Unwatch This Thread</a><br />";
        }
?>
</td></tr>
<tr style="font-size: smaller; font-weight: bold"><td align="left">Goto Page: 
<?php
foreach($pages as $page)
        print "$page&nbsp;";
?>
</td><td align="center"><?php print "<a href=\"dboard.php\">iGroups Discussion Board</a> -&gt; <a href=\"{$_COOKIE['topicLink']}\">{$_COOKIE['topicName']}</a>"; ?></td><td class="post_options" align="right"><?php echo "<a href=\"create.php?mode=thread$globaltext\">"; ?><img src="../img/newthread.png" style="border-style: none" alt="New Thread" title="New Thread" /></a>&nbsp;<?php echo "<a href=\"create.php?mode=post$globaltext$threadtext\">"; ?><img src="../img/newpost.png" style="border-style: none" alt="Post Reply" title="Post Reply" /></a></td></tr></table>

<table width="85%" cellspacing="0" cellpadding="5" style="table-layout: fixed;">
<tr><td class="view_options" style="text-align: left; font-weight: bold; width: 100px">
<?php
	if ($prevThread)
		print "<a href=\"viewThread.php?id={$prevThread->getID()}\">&lt; Previous Thread</a>";
	else
		print "&nbsp;";
?>
</td><td class="view_options" style="text-align: right; font-weight: bold">
<?
	if ($nextThread)
		print "<a href=\"viewThread.php?id={$nextThread->getID()}\">Next Thread &gt;</a>";
	else
		print "&nbsp;";
?>
</td></tr>
<tr><th style="width: 100px"><a name="top"></a>Author</th><th>Message</th></tr>
<?php

foreach ($posts as $post) {
	print "<tr><td valign=\"top\" style=\"width:20%\"><span style=\"font-size: smaller; font-weight: bold\">{$post->getAuthorLink()}<br />";
	$author = $post->getAuthor();
	if (!$_COOKIE['global']) {
		$group = new Group ($currentThread->getGroupID(), $_COOKIE['groupType'], $_COOKIE['groupSemester'], $db);
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
	print "$title</span></td>";
	if ((isset($currentGroup) && $currentUser->isGroupModerator($currentGroup)) || isset($_SESSION['adminView']))
		$delete = "<br />[<a href=\"edit.php?post=".$post->getID()."\">Edit</a>] [<a href=\"viewThread.php?id={$currentThread->getID()}&amp;delete={$post->getID()}\">Delete</a>]";
	else if($post->getAuthorID() == $currentUser->getID())
		$delete = "<br />[<a href=\"edit.php?post=".$post->getID()."\">Edit</a>]";
	else
		$delete = "";
	print "<td><img src=\"../img/icon_minipost.png\" alt=\"*\" title=\"Post #".$post->getID()."\" /><span style=\"font-size: x-small\">Posted: {$post->getDateTime()}</span>&nbsp; 
$delete<hr />";
	print "<p>".str_replace("\n", "<br />", $post->getBody())."</p><br /><br /></td></tr>";
	print "<tr><td style=\"font-size: smaller\"><a href=\"#top\">Back to Top</a></td><td>&nbsp;</td></tr>";
	print "<tr><td class=\"divide\" colspan=\"2\"></td></tr>";
}

?>
<tr style="text-align: right; font-weight: bold"><td class="view_options" colspan="2"><?php print "$nav"; ?></td></tr>
</table>
<table class="noborder" width="85%"><tr style="text-align: left; font-size: smaller; font-weight: bold"><td>Goto Page: 
<?php
foreach($pages as $page)
        print "$page&nbsp;";
?>
</td><td><?php print "<a href=\"dboard.php\">iGroups Discussion Board</a> -&gt; <a href=\"{$_COOKIE['topicLink']}\">{$_COOKIE['topicName']}</a>"; ?></td><td class="post_options"><?php echo "<a href=\"create.php?mode=thread$globaltext\">"; ?><img src="../img/newthread.png" alt="New Thread" title="New Thread" style="border-style: none" /></a>&nbsp;<?php echo "<a href=\"create.php?mode=post$globaltext$threadtext\">"; ?><img src="../img/newpost.png" style="border-style: none" alt="Post Reply" title="Post Reply" /></a></td></tr></table>
</div></body>
</html>

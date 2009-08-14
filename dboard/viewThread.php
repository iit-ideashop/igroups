<?php
	include_once('../globals.php');
	include_once('../checklogingroupless.php');
	include_once('../classes/group.php');
	include_once('../classes/topic.php');
	include_once('../classes/globaltopic.php');
	include_once('../classes/thread.php');
	include_once('../classes/post.php');
	include_once('../classes/watchlist.php');
	$POSTS_PER_PAGE = 20;

	if(isset($_GET['id']))
	{
		$query = $db->query('select * from Threads where iID='.$_GET['id']);
		if(!mysql_num_rows($query))
			errorPage('No such thread', 'That thread was not found', 400);
		$query = mysql_fetch_array($query);
		$query2 = mysql_fetch_array($db->query("select * from GlobalTopics where iID=".$query['iTopicID']));
		if(isset($_GET['global']))
		{
			$currentTopic = new GlobalTopic($query['iTopicID'], $db);
			$glob = '&amp;global=true';
			$topicLink = "viewTopic.php?id={$currentTopic->getID()}&amp;global=true";
			$topicName = $currentTopic->getName();
		}
		else if(isset($_GET['semester']))
		{
			$currentTopic = new Topic($query['iTopicID'], $db);
			$currentGroup = new Group($currentTopic->getID(), $_GET['type'], $_GET['semester'], $db);
			$glob = "&amp;type=".$_GET['type']."&amp;semester={$currentGroup->getSemester()}";
			$topicLink = "viewTopic.php?id={$currentTopic->getID()}&amp;type={$currentGroup->getType()}&amp;semester={$currentGroup->getSemester()}";
			$topicName = $currentGroup->getName().' Discussion';
		}
		else
			errorPage('Invalid Request', 'iGroups cannot understand this request', 400);
	}
	else
		errorPage('No topic selected', 'No topic has been selected', 400);
		 
	if(!isset($_GET['topic']) || !is_numeric($_GET['topic']))
		errorPage('No topic selected', 'No topic has been selected', 400);

	if(isset($_GET['id']))
	{
		if(!is_numeric($_GET['id']))
			errorPage('Invalid Request', 'iGroups cannot understand this request', 400);
		$currentThread = new Thread($_GET['id'], $db);
		if(!$currentThread)
			errorPage('No such thread', 'That thread was not found', 400);
	}
	else
		errorPage('No thread selected', 'No thread has been selected', 400);

	if(isset($_GET['delete']) && is_numeric($_GET['delete']))
	{
		if((isset($currentGroup) && $currentUser->isGroupModerator($currentGroup)) || isset($_SESSION['adminView']))
		{
			$post = new Post($_GET['delete'], $db);
			$thread = new Thread($post->getThreadID(), $db);
			if ($thread->getPostCount() == 1)
			{
				$thread->delete();
				$post->delete();
				header("Location: ".str_replace('&amp;', '&', $topicLink));
			}
			$post->delete();
		}
	}

	$watchList = new WatchList($currentThread->getID(), $db);
	
	if(isset($_GET['watchThread']))
		$watchList->addToWatchList($currentUser);

	if(isset($_GET['unwatchThread']))
		$watchList->removeFromWatchList($currentUser);

	$allPosts = $currentThread->getPosts();
	$currentThread->incViews();
	$currentThread->updateDB();
	$nextThread = $currentThread->getNext();
	$prevThread = $currentThread->getPrev();
	$link = "viewThread.php?id={$currentThread->getID()}";

	// determine pages
	$pages = array();
	$posts = array();

	if(isset($_GET['start']) && is_numeric($_GET['start']) && $_GET['start'] > 0 && $_GET['start'] % $POSTS_PER_PAGE == 0)
	{
		$currentPage = (int)($_GET['start']/$POSTS_PER_PAGE)+1;
		$lastPage = ceil(count($allPosts)/$POSTS_PER_PAGE);
		$lastStart = floor(count($allPosts)/$POSTS_PER_PAGE)*$POSTS_PER_PAGE;

		$prevStart1 = $_GET['start'] - $POSTS_PER_PAGE;
		$prevPage1 = $currentPage-1;
		$pages[] = "<a href=\"{$link}&start={$prevStart1}\">Previous</a>";

		if($currentPage > 2 && !($currentPage == 3 && $currentPage == $lastPage))
		{
			$firstStart1 = 0;
			$firstPage1 = 1;
			$pages[] = "<a href=\"{$link}&amp;start={$firstStart1}\">$firstPage1</a>";
			$pages[] = '...';
		}

		if($currentPage == $lastPage && ($lastPage-2 > 0))
		{
			$lastPage2 = $lastPage-2;
			$lastStart2 = $lastStart-2*$POSTS_PER_PAGE;
			$pages[] = "<a href=\"{$link}&amp;start={$lastStart2}\">$lastPage2</a>";
		}

		$pages[] = "<a href=\"{$link}&amp;start={$prevStart1}\">{$prevPage1}</a>";

		$pages[] = "{$currentPage}";

		if((count($allPosts)-$_GET['start']) > $POSTS_PER_PAGE)
		{
			$nextStart1 = $_GET['start'] + $POSTS_PER_PAGE;
			$nextPage1 = $currentPage+1;
			$pages[] = "<a href=\"{$link}&amp;start={$nextStart1}\">$nextPage1</a>";
			if ((count($allPosts)-$_GET['start']) > 2*$POSTS_PER_PAGE)
			{
				$pages[] = '...';
				$pages[] = "<a href=\"{$link}&amp;start={$lastStart}\">$lastPage</a>";
			}
			$pages[] = "<a href=\"{$link}&amp;start={$nextStart1}\">Next</a>";
		}
		// get thread range
		for($i=$_GET['start']; $i<($_GET['start']+$POSTS_PER_PAGE); $i++)
		{
			if ($allPosts[$i])
				$posts[] = $allPosts[$i];
		}
	}
	 else
	 {
		$currentPage = 1;
		$pages[] = '1';
		if(count($allPosts) > $POSTS_PER_PAGE)
		{
			$pages[] = "<a href='{$link}&amp;start={$POSTS_PER_PAGE}'>2</a>";
			if(count($allPosts) > 2*$POSTS_PER_PAGE)
			{
				$newStart = 2*$POSTS_PER_PAGE;
				$pages[] = "<a href=\"{$link}&amp;start={$newStart}\">3</a>";
			}
			if(count($allPosts) > 3*$POSTS_PER_PAGE)
			{
				$pages[] = '...';
				$lastPage = ceil(count($allPosts)/$POSTS_PER_PAGE);
				$lastStart = floor(count($allPosts)/$POSTS_PER_PAGE)*$POSTS_PER_PAGE;
				$pages[] = "<a href=\"{$link}&amp;start={$lastStart}\">$lastPage</a>";
			}
			$pages[] = "<a href=\"{$link}&amp;start={$POSTS_PER_PAGE}\">Next</a>";
		}
		// first 20 threads
		for($i = 0; $i<$POSTS_PER_PAGE; $i++)
		{
			if($allPosts[$i])
				$posts[] = $allPosts[$i];
		}
	}

	$topicID = "&amp;topicID=".$_GET['topic'];
	$threadtext = "&amp;thread=".$currentThread->getID();
	
	//-------Begin XHTML Output-------------------------------------//
		require('../doctype.php');
	require('../iknow/appearance.php');

	echo "<link rel=\"stylesheet\" href=\"../skins/$skin/dboard.css\" type=\"text/css\" title=\"$skin\" />\n";
	foreach($altskins as $altskin)
		echo "<link rel=\"alternate stylesheet\" href=\"../skins/$altskin/dboard.css\" type=\"text/css\" title=\"$altskin\" />\n";
?>
<title><?php echo $appname; ?> - Discussion Board - View Thread</title>
</head>
<body>
<?php
require('sidebar.php');
?>
<div id="content"><div id="topbanner">
<?php
	echo $topicName;
?>	
</div>

<table class="noborder" width="85%"><tr><td colspan="3" style="font-size: larger; font-weight: bold; text-decoration: underline"><?php echo "{$currentThread->getName()}"; ?></td></tr>
<tr><td align="left" colspan="3">
<?php
	if(!$watchList->isOnWatchlist($currentUser))
	{
		echo "<a href=\"{$link}&amp;topic=".$_GET['topic'].$glob."&amp;watchThread=true\">Watch This Thread</a>&nbsp;&nbsp;";
		echo "(Receive an e-mail every time a new post is added.)<br />";
	}
	else
	{
		echo "<i>You are currently watching this thread.</i><br />";
		echo "<a href=\"{$link}&amp;topic=".$_GET['topic'].$glob."&amp;unwatchThread=true\">Unwatch This Thread</a><br />";
	}
?>
</td></tr>
<tr style="font-size: smaller; font-weight: bold"><td align="left">Goto Page: 
<?php
foreach($pages as $page)
	echo "$page&nbsp;";
?>
</td><td align="center"><?php echo "<a href=\"dboard.php\">$appname Discussion Board</a> -&gt; <a href=\"$topicLink\">$topicName</a>"; ?></td><td class="post_options" align="right"><?php echo "<a href=\"create.php?mode=thread$topicID$glob\">"; ?><img src="../skins/<?php echo $skin; ?>/img/newthread.png" style="border-style: none" alt="New Thread" title="New Thread" /></a>&nbsp;<?php echo "<a href=\"create.php?mode=post$topicID$glob$threadtext\">"; ?><img src="../skins/<?php echo $skin; ?>/img/newpost.png" style="border-style: none" alt="Post Reply" title="Post Reply" /></a></td></tr></table>

<table width="85%" cellspacing="0" cellpadding="5" style="table-layout: fixed;">
<tr><td class="view_options" style="text-align: left; font-weight: bold; width: 100px">
<?php
	if($prevThread)
		echo "<a href=\"viewThread.php?id={$prevThread->getID()}\">&lt; Previous Thread</a>";
	else
		echo "&nbsp;";
?>
</td><td class="view_options" style="text-align: right; font-weight: bold">
<?
	if($nextThread)
		echo "<a href=\"viewThread.php?id={$nextThread->getID()}\">Next Thread &gt;</a>";
	else
		echo "&nbsp;";
?>
</td></tr>
<tr><th style="width: 100px"><a name="top"></a>Author</th><th>Message</th></tr>
<?php
	foreach($posts as $post)
	{
		echo "<tr><td valign=\"top\" style=\"width:20%\"><span style=\"font-size: smaller; font-weight: bold\">{$post->getAuthorLink()}<br />";
		$author = $post->getAuthor();
		$profile = $author->getProfile();
		if(!$_GET['global'])
		{
			$group = new Group ($currentThread->getGroupID(), $_GET['type'], $_GET['semester'], $db);
			if ($author->isGroupAdministrator($group))
				$title = "Group Administrator";
			else if ($author->isGroupModerator($group))
				$title = "Group Moderator";
			else if ($author->isGroupGuest($group))
				$title = "Group Guest";
			else if ($author->isGroupMember($group))
				$title = "Group Member";
			else
				$title = "$appname Staff";
		}
		else if ($author->isAdministrator())
			$title = "$appname Staff";
		else
			$title = "";
		echo "$title";
		if ($profile['sPicture'])
			echo "<br /><img src=\"../profile-pics/{$profile['sPicture']}\" alt=\"{$profile['sPicture']}\" width=\"100\" />";
		echo "</span></td>";
		if ((isset($currentGroup) && $currentUser->isGroupModerator($currentGroup)) || isset($_SESSION['adminView']))
			$delete = "<br />[<a href=\"edit.php?topic=".$_GET['topic']."&amp;thread=".$_GET['id']."&amp;post=".$post->getID()."$glob\">Edit</a>] [<a href=\"viewThread.php?id={$currentThread->getID()}&amp;topic=".$_GET['topic'].$glob."&amp;delete={$post->getID()}\">Delete</a>]";
		else if($post->getAuthorID() == $currentUser->getID())
			$delete = "<br />[<a href=\"edit.php?topic=".$_GET['topic']."&amp;thread=".$_GET['id']."&amp;post=".$post->getID()."$glob\">Edit</a>]";
		else
			$delete = "";
		echo "<td><img src=\"../skins/$skin/img/icon_minipost.png\" alt=\"*\" title=\"Post #".$post->getID()."\" /><span style=\"font-size: x-small\">Posted: {$post->getDateTime()}</span>&nbsp;$delete<hr />";
		echo "<p>".str_replace("\n", "<br />", $post->getBody())."</p><br /><br /></td></tr>\n";
		echo "<tr><td style=\"font-size: smaller\"><a href=\"#top\">Back to Top</a></td><td>&nbsp;</td></tr>\n";
		echo "<tr><td class=\"divide\" colspan=\"2\"></td></tr>\n";
	}
?>
<tr style="text-align: right; font-weight: bold"><td class="view_options" colspan="2"><?php echo "$nav"; ?></td></tr>
</table>
<table class="noborder" width="85%"><tr style="text-align: left; font-size: smaller; font-weight: bold"><td>Goto Page: 
<?php
foreach($pages as $page)
	echo "$page&nbsp;";
?>
</td><td><?php echo "<a href=\"dboard.php\">$appname Discussion Board</a> -&gt; <a href=\"$topicLink\">$topicName</a>"; ?></td><td class="post_options"><?php echo "<a href=\"create.php?mode=thread$topicID$glob\">"; ?><img src="../skins/<?php echo $skin; ?>/img/newthread.png" alt="New Thread" title="New Thread" style="border-style: none" /></a>&nbsp;<?php echo "<a href=\"create.php?mode=post$topicID$glob$threadtext\">"; ?><img src="../skins/<?php echo $skin; ?>/img/newpost.png" style="border-style: none" alt="Post Reply" title="Post Reply" /></a></td></tr></table>
</div></body></html>

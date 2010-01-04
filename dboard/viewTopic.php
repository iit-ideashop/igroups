<?php
	include_once('../globals.php');
	include_once('../checklogingroupless.php');
	include_once('../classes/group.php');
	include_once('../classes/topic.php');
	include_once('../classes/globaltopic.php');
	include_once('../classes/thread.php');
	include_once('../classes/post.php');
	$THREADS_PER_PAGE = 10;
	

	if(isset($_GET['id']))
	{
		if(!is_numeric($_GET['id']))
			errorPage('Invalid Request', 'iGroups cannot understand this request', 400);
		if(isset($_GET['global']) && $_GET['global'] == 'true')
		{
			$currentTopic = new GlobalTopic($_GET['id'], $db);
			if(!$currentTopic)
				errorPage('No such topic', 'That topic was not found', 400);
			$link = "viewTopic.php?id={$currentTopic->getID()}&amp;global=true";
			$glob = '&amp;global=true';
		}
		else
		{
			if(!is_numeric($_GET['type']) || !is_numeric($_GET['semester']))
				errorPage('Invalid Request', 'iGroups cannot understand this request', 400);
			$currentTopic = new Topic($_GET['id'], $db);
			$currentGroup = new Group($currentTopic->getID(), $_GET['type'], $_GET['semester'], $db);
			if(!$currentGroup)
				errorPage('No such topic', 'That topic was not found', 400);
			$link = "viewTopic.php?id={$currentTopic->getID()}&amp;type={$currentGroup->getType()}&amp;semester={$currentGroup->getSemester()}";
			$glob = '&amp;type='.$_GET['type']."&amp;semester={$currentGroup->getSemester()}";
		}
	}
	else
		errorPage('No topic selected', 'No topic has been selected', 400);

	if(isset($currentGroup) && !$currentUser->isGroupMember($currentGroup) && !isset($_SESSION['adminView']))
		errorPage('Credentials Required', 'You are not a member of this group', 403);

	if(isset($_GET['delete']) && is_numeric($_GET['delete']))
	{
		if((isset($currentGroup) && $currentUser->isGroupModerator($currentGroup)) || isset($_SESSION['adminView']))
		{
			$thread = new Thread($_GET['delete'], $db);
			$thread->delete();
		}
	}

	$allThreads = $currentTopic->getThreads();

	// determine pages
	$pages = array();
	$threads = array();
	if(isset($_GET['start']) && is_numeric($_GET['start']) && $_GET['start'] > 0 && $_GET['start'] % $THREADS_PER_PAGE == 0)
	{
		$currentPage = (int)($_GET['start']/$THREADS_PER_PAGE)+1;
		$lastPage = ceil(count($allThreads)/$THREADS_PER_PAGE);
		$lastStart = floor(count($allThreads)/$THREADS_PER_PAGE)*$THREADS_PER_PAGE;

		$prevStart1 = $_GET['start'] - $THREADS_PER_PAGE;
		$prevPage1 = $currentPage-1;
		$pages[] = "<a href='{$link}&start={$prevStart1}'>Previous</a>";

		if($currentPage > 2 && !($currentPage == 3 && $currentPage == $lastPage))
		{
			$firstStart1 = 0;
			$firstPage1 = 1;
			$pages[] = "<a href=\"{$link}&start={$firstStart1}\">$firstPage1</a>";
			$pages[] = "...";
		}	
		
		if($currentPage == $lastPage && ($lastPage-2 > 0))
		{
			$lastPage2 = $lastPage-2;
			$lastStart2 = $lastStart-2*$THREADS_PER_PAGE;
			$pages[] = "<a href=\"{$link}&amp;start={$lastStart2}\">$lastPage2</a>";
		}

		$pages[] = "<a href=\"{$link}&amp;start={$prevStart1}\">{$prevPage1}</a>";

		$pages[] = "$currentPage";

		if((count($allThreads)-$_GET['start']) > $THREADS_PER_PAGE)
		{
			$nextStart1 = $_GET['start'] + $THREADS_PER_PAGE;
			$nextPage1 = $currentPage+1;
			$pages[] = "<a href=\"{$link}&amp;start={$nextStart1}\">$nextPage1</a>";
			if((count($allThreads)-$_GET['start']) > 2*$THREADS_PER_PAGE)
			{
				$pages[] = '...';
				$pages[] = "<a href=\"{$link}&amp;start={$lastStart}\">$lastPage</a>";
			}
			$pages[] = "<a href=\"{$link}&amp;start={$nextStart1}\">Next</a>";
		}
		// get thread range
		for($i = $_GET['start']; $i < ($_GET['start']+$THREADS_PER_PAGE); $i++)
		{
			if($allThreads[$i])
				$threads[] = $allThreads[$i];
		}
	}
	else
	{
		$currentPage = 1;	
		$pages[] = '1';
		if(count($allThreads) > $THREADS_PER_PAGE)
		{
			$pages[] = "<a href=\"{$link}&amp;start={$THREADS_PER_PAGE}\">2</a>";
			if(count($allThreads) > 2*$THREADS_PER_PAGE)
			{
				$newStart = 2*$THREADS_PER_PAGE;
				$pages[] = "<a href=\"{$link}&amp;start={$newStart}\">3</a>";
			}
			if(count($allThreads) > 3*$THREADS_PER_PAGE)
			{
				$pages[] = "...";
				$lastPage = ceil(count($allThreads)/$THREADS_PER_PAGE);
				$lastStart = floor(count($allThreads)/$THREADS_PER_PAGE)*$THREADS_PER_PAGE;
				$pages[] = "<a href=\"{$link}&amp;start={$lastStart}\">$lastPage</a>";
			}
			$pages[] = "<a href=\"{$link}&amp;start={$THREADS_PER_PAGE}\">Next</a>";
		}
		// first 20 threads
		for($i = 0; $i < $THREADS_PER_PAGE; $i++)
		{
			if($allThreads[$i])
				$threads[] = $allThreads[$i];
		}
	}
	$topicID = "&amp;topicID=".$_GET['id'];
		
	if(!isset($currentGroup))
		$topicName = $currentTopic->getName();
	else
		$topicName = $currentGroup->getName().' Discussion';
	
	//-------Begin XHTML Output-------------------------------------//
		require('../doctype.php');
	require('../iknow/appearance.php');

	echo "<link rel=\"stylesheet\" href=\"../skins/$skin/dboard.css\" type=\"text/css\" title=\"$skin\" />\n";
	foreach($altskins as $altskin)
		echo "<link rel=\"alternate stylesheet\" href=\"../skins/$altskin/dboard.css\" type=\"text/css\" title=\"$altskin\" />\n";
?>
<title><?php echo $appname; ?> - Discussion Board - View Topic</title>
</head>
<body>
<?php

  /**** begin html head *****/
   require('htmlhead.php'); 
  //starts main container
  /****end html head content ****/

	echo "<div id=\"topbanner\">$topicName</div>\n";
?>	
	<table class="noborder" width="85%">
	<tr><td style="text-align: left; font-size: smaller; font-weight: bold">Goto Page: 
<?php
	foreach($pages as $page)
		echo "$page&nbsp;";
?>
	</td>
	<td style="font-size: smaller; font-weight: bold; text-align: center"><?php echo "<a href=\"dboard.php\">$appname Discussion Board</a> -&gt; <strong>$topicName</strong>"; ?></td><td style="text-align: right; font-weight:bold" class="post_options"><?php echo "<a href=\"create.php?mode=thread$topicID$glob\">"; ?><img src="../skins/<?php echo $skin; ?>/img/newthread.png" style="border-style: none" alt="New Thread" title="New Thread" /></a></td></tr></table>
	<table width="85%" cellspacing="0" cellpadding="5">
	<tr><th style="width:45%" colspan="2">Threads</th><th>Replies</th><th>Author</th><th>Views</th><th>Last Post</th></tr>
<?php

	if(count($threads) > 0)
	{
		foreach($threads as $thread)
		{
			$lastPost = $thread->getLastPost();
			if($lastPost) 
				$text = "{$lastPost->getDateTime()}<br />{$lastPost->getAuthorLink()}";
			else
				$text = "<i>No Posts</i>";
			if((isset($currentGroup) && $currentUser->isGroupModerator($currentGroup)) || isset($_SESSION['adminView']))
				$delete = "&nbsp;&nbsp;[<a href=\"$link&amp;delete={$thread->getID()}\">Delete</a>]";
			else
				$delete = '';
			echo "<tr><td style=\"width:1%\"><img src=\"../skins/$skin/img/thread.png\" alt=\"*\" title=\"Thread #".$thread->getID()."\" /></td><td style=\"font-weight: bold; font-size: smaller\"><a href=\"viewThread.php?id={$thread->getID()}&amp;topic=".$_GET['id']."$glob\">{$thread->getName()}</a>$delete</td><td align=\"center\">{$thread->getPostCount()}</td><td align=\"center\">{$thread->getAuthorLink()}</td><td align=\"center\">{$thread->getViews()}</td><td align=\"center\">$text</td></tr>";
		}
	}
	else
		echo "<tr><td colspan=\"6\" style=\"text-align: center; font-style: italic\">There are currently no threads for this topic.</td></tr>\n";

?>
	</table>
	<table class="noborder" width="85%" style="text-align: center"><tr style="font-size: smaller; font-weight: bold"><td style="text-align: left">Goto Page: 
<?php
	foreach($pages as $page)
		echo "$page&nbsp;";
?>
</td><td style="text-align: center"><?php echo "<a href=\"dboard.php\">$appname Discussion Board</a> -&gt; $topicName"; ?></td><td style="text-align: right" class="post_options"><?php echo "<a href=\"create.php?mode=thread$topicID$glob\">"; ?><img src="../skins/<?php echo $skin; ?>/img/newthread.png" style="border-style: none" alt="New Thread" title="New Thread" /></a></td></tr><tr style="font-size: smaller; font-weight: bold"><td>Page # <?php echo "$currentPage"; ?></td></tr></table>


<?php
 	/**** begin html footer*****/
  //include rest of html layout file
  require('htmlfoot.php');
  // ends main container
  /****** end html footer*****/
?>
</body>
</html>

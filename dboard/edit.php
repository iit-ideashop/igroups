<?php
	include_once( "../checklogingroupless.php" );
	include_once( "../classes/group.php" );
	include_once( "../classes/topic.php" );
	include_once( "../classes/globaltopic.php" );
	include_once( "../classes/thread.php" );
	include_once( "../classes/post.php" );
	include_once( "../classes/watchlist.php");

	if(isset($_GET['topic']))
	{
		if(isset($_GET['global']))
		{
			$currentTopic = new GlobalTopic($_COOKIE['topic'], $db);
			$glob = "&amp;global=true";
		}
		else
		{
			$currentTopic = new Topic($_COOKIE['topic'], $db);
			$currentGroup = new Group($_COOKIE['topic'], $_COOKIE['groupType'], $_COOKIE['groupSemester'], $db);
			$glob = "";
		}
	}
	else
	    	 die("No topic selected.");
	
	if(isset($_GET['thread']))
		$currentThread = new Thread($_COOKIE['thread'], $db);
	else
		die("No thread selected.");
	if(isset($_GET['post']) && is_numeric($_GET['post']))
		$post = new Post($_GET['post'], $db);
	else
		die("No post selected.");
		
	if(!((isset($currentGroup) && $currentUser->isGroupModerator($currentGroup)) || isset($_SESSION['adminView']) || $currentUser->getID() == $post->getAuthorID()))
		die("You do not have the necessary privileges to edit this post.");

	if (isset($_POST['editPost']))
	{
		$post->setBody($_POST['body']);
		$post->updateDB();
		header("Location: viewThread?id={$_COOKIE['thread']}");
	}
	else if(!isset($_GET['post']) || !is_numeric($_GET['post']))
		die("Invalid Request");
	

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
	print "{$_COOKIE['topicName']}";
?>	
</div>
<table class="noborder" width="85%"><tr><td><a href="dboard.php">iGroups Discussion Board</a> -&gt; <a href="<?php print "{$_COOKIE['topicLink']}"; ?>"><?php print "{$_COOKIE['topicName']}"; ?></a> -> <a href="viewThread.php?id=<?php print "{$currentThread->getID()}&amp;topic={$_GET['topic']}$glob"; ?>"><?php print "{$currentThread->getName()}"; ?></a></td></tr></table>
<form action="edit.php?post=<?php echo $_GET['post']; ?>" method="post" id="postForm"><fieldset><legend>Edit Post</legend>
<table width="85%">
<tr><td valign="top"><label for="body">Message Body</label></td><td><textarea cols="60" rows="20" name="body" id="body"><?php echo $post->getBody(); ?></textarea></td></tr>
<tr><td align="center" colspan="2"><input type="submit" name="editPost" value="Edit Post" /></td></tr>
</table>
</fieldset></form></div></body></html>

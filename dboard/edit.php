<?php
	include_once("../globals.php");
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
			$currentTopic = new GlobalTopic($_GET['topic'], $db);
			$glob = "&amp;global=true";
			$topicLink = "viewTopic.php?id={$currentTopic->getID()}&amp;global=true";
			$topicName = $currentTopic->getName();
		}
		else
		{
			$currentTopic = new Topic($_GET['topic'], $db);
			$currentGroup = new Group($_GET['topic'], $_GET['type'], $_GET['semester'], $db);
			$glob = "&amp;type=".$_GET['type']."&amp;semester={$currentGroup->getSemester()}";
			$topicLink = "viewTopic.php?id={$currentTopic->getID()}&amp;type={$currentGroup->getType()}&amp;semester={$currentGroup->getSemester()}";
			$topicName = $currentGroup->getName() . " Discussion";
		}
	}
	else
	    	 die("No topic selected.");
	
	if(isset($_GET['thread']))
		$currentThread = new Thread($_GET['thread'], $db);
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
		header("Location: viewThread?id={$_GET['thread']}&topic=".$currentTopic->getID().str_replace('&amp;', '&', $glob));
	}
	else if(!isset($_GET['post']) || !is_numeric($_GET['post']))
		die("Invalid Request");
	

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<!-- This web-based application is Copyrighted &copy; 2008 Interprofessional Projects Program, Illinois Institute of Technology -->
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en"><head>
<title><?php echo $appname; ?> - Discussion Board</title>
<?php
require("../iknow/appearance.php");
echo "<link rel=\"stylesheet\" href=\"../skins/$skin/dboard.css\" type=\"text/css\" title=\"$skin\" />\n";
foreach($altskins as $altskin)
	echo "<link rel=\"alternate stylesheet\" href=\"../skins/$altskin/dboard.css\" type=\"text/css\" title=\"$altskin\" />\n";
?>
</head>
<body>
<?php
require("sidebar.php");
?>
<div id="content"><div id="topbanner">
<?php
	print $topicName;
?>	
</div>
<table class="noborder" width="85%"><tr><td><a href="dboard.php"><?php echo $appname; ?> Discussion Board</a> -&gt; <a href="<?php print $topicLink; ?>"><?php print $topicName; ?></a> -&gt; <a href="viewThread.php?id=<?php print "{$currentThread->getID()}&amp;topic={$_GET['topic']}$glob"; ?>"><?php print "{$currentThread->getName()}"; ?></a></td></tr></table>
<form action="edit.php?post=<?php echo $_GET['post']."&amp;topic=".$_GET['topic']."&amp;thread=".$_GET['thread'].$glob; ?>" method="post" id="postForm"><fieldset><legend>Edit Post</legend>
<table width="85%">
<tr><td valign="top"><label for="body">Message Body</label></td><td><textarea cols="60" rows="20" name="body" id="body"><?php echo $post->getBody(); ?></textarea></td></tr>
<tr><td align="center" colspan="2"><input type="submit" name="editPost" value="Edit Post" /></td></tr>
</table>
</fieldset></form></div></body></html>

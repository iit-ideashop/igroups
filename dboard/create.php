<?php
	include_once("../globals.php");
	include_once( "../checklogingroupless.php" );
	include_once( "../classes/group.php" );
	include_once( "../classes/topic.php" );
	include_once( "../classes/globaltopic.php" );
	include_once( "../classes/thread.php" );
	include_once( "../classes/post.php" );
	include_once( "../classes/watchlist.php");

	if(isset($_GET['topicID']) && is_numeric($_GET['topicID']))
	{
		if(isset($_GET['global']) && $_GET['global'] == 'true')
		{
			$currentTopic = new GlobalTopic($_GET['topicID'], $db);
			$glob = "&amp;global=true";
			$topicLink = "viewTopic.php?id={$currentTopic->getID()}&amp;global=true";
			$topicName = $currentTopic->getName();
		}
		else
		{
			$currentTopic = new Topic($_GET['topicID'], $db);
			$currentGroup = new Group($currentTopic->getID(), $_GET['type'], $_GET['semester'], $db);
			$topicLink = "viewTopic.php?id={$currentTopic->getID()}&amp;type={$currentGroup->getType()}&amp;semester={$currentGroup->getSemester()}";
			$topicName = $currentGroup->getName() . " Discussion";
			$glob = "&amp;type=".$_GET['type']."&amp;semester={$currentGroup->getSemester()}";
		}
		$globurl = str_replace('&amp;', '&', $glob);
	}
	else
	    	 die("No topic selected");
	
	if (!isset($_GET['thread']) && !is_numeric($_GET['thread']) && $_GET['mode'] == 'post')
		die("No thread selected");

	if (isset($_GET['mode']) && $_GET['mode'] == 'thread') {
	
	}
	else if (isset($_GET['mode']) && $_GET['mode'] == 'post') {

	}
	else if (isset($_POST['newThread'])) {
		$thread = createThread($_POST['name'], $currentUser->getID(), $currentTopic->getID(), $db);
		$post = createPost($thread->getID(), $_POST['body'], $currentUser->getID(), $db);
		header("Location: viewThread.php?id={$thread->getID()}&topic={$_GET['topicID']}$globurl");
	}
	else if (isset($_POST['newPost'])) {
		$post = createPost($_GET['thread'], $_POST['body'], $currentUser->getID(), $db);
		$watchList = new WatchList($_GET['thread'], $db);
		$watchList->sendNotification($post, $topicName);
		header("Location: viewThread.php?id={$_GET['thread']}&topic={$_GET['topicID']}$globurl");
	}
	else
		die("Invalid Request");

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<!-- This web-based application is Copyrighted &copy; 2008 Interprofessional Projects Program, Illinois Institute of Technology -->
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en"><head>
<title><?php echo $appname; ?> - Discussion Board</title>
<?php
require("../iknow/appearance.php");
echo "<link rel=\"stylesheet\" href=\"../skins/$skin/dboard.css\" type=\"text/css\" />\n";
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

<?php

if ($_GET['mode'] == 'thread') {

?>

<table class="noborder" width="85%"><tr><td><a href="dboard.php"><?php echo $appname; ?> Discussion Board</a> -&gt; <a href="<?php print $topicLink; ?>"><?php print $topicName; ?></a></td></tr></table>
<form action="create.php?topicID=<?php echo $currentTopic->getID().$glob; ?>" method="post" id="threadForm"><fieldset><legend>Create a New Thread</legend>
<table width="85%">
<tr><td><label for="name">Name</label></td><td><input type="text" size="60" name="name" id="name" /></td></tr>
<tr><td valign="top"><label for="body">Message Body</label></td><td><textarea cols="60" rows="20" name="body" id="body"></textarea></td></tr>
<tr><td align="center" colspan="2"><input type="submit" name="newThread" value="Create Thread" /></td></tr>
</table>
</fieldset></form>

<script type="text/javascript">document.getElementById('threadForm').name.focus(); </script>
<?php
}

else if ($_GET['mode'] == 'post') {

$currentThread = new Thread($_GET['thread'], $db);
?>

<table class="noborder" width="85%"><tr><td><a href="dboard.php"><?php echo $appname; ?> Discussion Board</a> -&gt; <a href="<?php print $topicLink; ?>"><?php print $topicName; ?></a> -&gt; <a href="viewThread.php?id=<?php print "{$currentThread->getID()}&amp;topic=".$_GET['topicID'].$glob ?>"><?php print "{$currentThread->getName()}"; ?></a></td></tr></table>
<form action="create.php?topicID=<?php echo $currentTopic->getID()."&amp;thread=".$currentThread->getID().$glob; ?>" method="post" id="postForm"><fieldset><legend>Post Reply</legend>
<table width="85%" align="center">
<tr><td valign="top"><label for="body">Message Body</label></td><td><textarea cols="60" rows="20" name="body" id="body"></textarea></td></tr>
<tr><td align="center" colspan="2"><input type="submit" name="newPost" value="Post Reply" /></td></tr>
</table>
</fieldset></form>

<script type="text/javascript"> document.getElementById('postForm').body.focus(); </script>
<?php

}

?>
</div>
</body>
</html>

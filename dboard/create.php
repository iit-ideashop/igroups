<?php
	session_start();
	include_once( "../classes/db.php" );
	include_once( "../classes/person.php" );
	include_once( "../classes/group.php" );
	include_once( "../classes/topic.php" );
	include_once( "../classes/globaltopic.php" );
	include_once( "../classes/thread.php" );
	include_once( "../classes/post.php" );
	include_once( "../classes/watchlist.php");


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

	if(isset($_GET['topicID']) && is_numeric($_GET['topicID']))
	{
		if(isset($_GET['global']) && $_GET['global'] == 'true')
			$currentTopic = new GlobalTopic($_GET['topicID'], $db);
		else
			$currentTopic = new Topic($_GET['topicID'], $db);
	}
	else
	    	 die("No topic selected");
	
	if(isset($_GET['thread']) && is_numeric($_GET['thread']))
		setcookie('thread', $_GET['thread'], time()*60*60*6);

	if (isset($_GET['mode']) && $_GET['mode'] == 'thread') {
	
	}
	else if (isset($_GET['mode']) && $_GET['mode'] == 'post') {

	}
	else if (isset($_POST['newThread'])) {
		$thread = createThread($_POST['name'], $currentUser->getID(), $currentTopic->getID(), $db);
		$post = createPost($thread->getID(), $_POST['body'], $currentUser->getID(), $db);
		header("Location: viewThread?id={$thread->getID()}");
	}
	else if (isset($_POST['newPost'])) {
		$post = createPost($_COOKIE['thread'], $_POST['body'], $currentUser->getID(), $db);
		$watchList = new WatchList($_COOKIE['thread'], $db);
		$watchList->sendNotification($post, $_COOKIE['topicName']);
		header("Location: viewThread?id={$_COOKIE['thread']}");
	}
	else
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

<?php

if ($_GET['mode'] == 'thread') {

?>

<table class="noborder" width="85%"><tr><td><a href="dboard.php">iGroups Discussion Board</a> -&gt; <a href="<?php print "{$_COOKIE['topicLink']}"; ?>"><?php print "{$_COOKIE['topicName']}"; ?></a></td></tr></table>
<form action="create.php?topicID=<?php echo $currentTopic->getID(); ?>" method="post" id="threadForm"><fieldset><legend>Create a New Thread</legend>
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

$currentThread = new Thread($_COOKIE['thread'], $db);
?>

<table class="noborder" width="85%"><tr><td><a href="dboard.php">iGroups Discussion Board</a> -&gt; <a href="<?php print "{$_COOKIE['topicLink']}"; ?>"><?php print "{$_COOKIE['topicName']}"; ?></a> -> <a href="viewThread.php?id=<?php print "{$currentThread->getID()}"; ?>"><?php print "{$currentThread->getName()}"; ?></a></td></tr></table>
<form action="create.php?topicID=<?php echo $currentTopic->getID()."&amp;thread=".$currentThread->getID(); ?>" method="post" id="postForm"><fieldset><legend>Post Reply</legend>
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

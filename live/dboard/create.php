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
                 }
        }
        else
            	 die("No topic selected");
	

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
		$post = createPost($_SESSION['threadID'], $_POST['body'], $currentUser->getID(), $db);
		$watchList = new WatchList($_SESSION['threadID'], $db);
		$watchList->sendNotification($post, $_SESSION['topicName']);
		header("Location: viewThread?id={$_SESSION['threadID']}");
	}
	else
		die("Invalid Request");

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
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
	print "{$_SESSION['topicName']}";
?>        
</div>

<?php

if ($_GET['mode'] == 'thread') {

?>

<table class='noborder' width='85%'><tr><td><a href='dboard.php'>iGroups Discussion Board</a> -> <a href='<?php print "{$_SESSION['topicLink']}"; ?>'><?php print "{$_SESSION['topicName']}"; ?></a></td></tr></table>
<form action='create.php' method='post' id='threadForm'>
<table width='85%' border='1'>
<tr><th colspan='2'>Create a New Thread</th></tr>
<tr><td>Name</td><td><input type='text' size='60' name='name' /></td></tr>
<tr><td valign='top'>Message Body</td><td><textarea cols='60' rows='20' name='body'></textarea></td></tr>
<tr><td align='center' colspan='2'><input type='submit' name='newThread' value='Create Thread' /></td></tr>
</table>
</form>

<script language="Javascript" type="text/javascript">document.getElementById('threadForm').name.focus(); </script>
<?php
}

else if ($_GET['mode'] == 'post') {

$currentThread = new Thread($_SESSION['threadID'], $db);
?>

<table class='noborder' width='85%'><tr><td><a href='dboard.php'>iGroups Discussion Board</a> -> <a href='<?php print "{$_SESSION['topicLink']}"; ?>'><?php print "{$_SESSION['topicName']}"; ?></a> -> <a href='viewThread.php?id=<?php print "{$currentThread->getID()}"; ?>'><?php print "{$currentThread->getName()}"; ?></a></td></tr></table>
<form action='create.php' method='post' id='postForm'>
<table width='85%' border='1' align='center'>
<tr><th colspan='2'>Post Reply</th></tr>
<tr><td valign='top'>Message Body</td><td><textarea cols='60' rows='20' name='body'></textarea></td></tr>
<tr><td align='center' colspan='2'><input type='submit' name='newPost' value='Post Reply' /></td></tr>
</table>
</form>

<script language="JavaScript" type="text/javascript"> document.getElementById('postForm').body.focus(); </script>
<?php

}

?>
</div>
</body>
</html>

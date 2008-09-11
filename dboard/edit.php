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

	if(isset($_COOKIE['topic']))
	{
		if($_COOKIE['global'])
			$currentTopic = new GlobalTopic($_COOKIE['topic'], $db);
		else
			$currentTopic = new Topic($_COOKIE['topic'], $db);
			$currentGroup = new Group($_COOKIE['topic'], $_COOKIE['groupType'], $_COOKIE['groupSemester'], $db);
	}
	else
	    	 die("No topic selected.");
	
	if(isset($_COOKIE['thread']))
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
<table class="noborder" width="85%"><tr><td><a href="dboard.php">iGroups Discussion Board</a> -&gt; <a href="<?php print "{$_COOKIE['topicLink']}"; ?>"><?php print "{$_COOKIE['topicName']}"; ?></a> -> <a href="viewThread.php?id=<?php print "{$currentThread->getID()}"; ?>"><?php print "{$currentThread->getName()}"; ?></a></td></tr></table>
<form action="edit.php?post=<?php echo $_GET['post']; ?>" method="post" id="postForm"><fieldset><legend>Edit Post</legend>
<table width="85%">
<tr><td valign="top"><label for="body">Message Body</label></td><td><textarea cols="60" rows="20" name="body" id="body"><?php echo $post->getBody(); ?></textarea></td></tr>
<tr><td align="center" colspan="2"><input type="submit" name="editPost" value="Edit Post" /></td></tr>
</table>
</fieldset></form></div></body></html>
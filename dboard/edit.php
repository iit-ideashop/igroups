<?php
	include_once('../globals.php');
	include_once('../checklogingroupless.php');
	include_once('../classes/group.php');
	include_once('../classes/topic.php');
	include_once('../classes/globaltopic.php');
	include_once('../classes/thread.php');
	include_once('../classes/post.php');
	include_once('../classes/watchlist.php');

	if(isset($_GET['topic']))
	{
		if(isset($_GET['global']))
		{
			$currentTopic = new GlobalTopic($_GET['topic'], $db);
			$glob = '&amp;global=true';
			$topicLink = "viewTopic.php?id={$currentTopic->getID()}&amp;global=true";
			$topicName = $currentTopic->getName();
		}
		else
		{
			$currentTopic = new Topic($_GET['topic'], $db);
			$currentGroup = new Group($_GET['topic'], $_GET['type'], $_GET['semester'], $db);
			$glob = "&amp;type=".$_GET['type']."&amp;semester={$currentGroup->getSemester()}";
			$topicLink = "viewTopic.php?id={$currentTopic->getID()}&amp;type={$currentGroup->getType()}&amp;semester={$currentGroup->getSemester()}";
			$topicName = $currentGroup->getName().' Discussion';
		}
	}
	else
		errorPage('No topic selected', 'No topic has been selected', 400);
	
	if(isset($_GET['thread']))
		$currentThread = new Thread($_GET['thread'], $db);
	else
		errorPage('No thread selected', 'No thread has been selected', 400);
	if(isset($_GET['post']) && is_numeric($_GET['post']))
		$post = new Post($_GET['post'], $db);
	else
		errorPage('No post selected', 'No post has been selected', 400);
		
	if(!((isset($currentGroup) && $currentUser->isGroupModerator($currentGroup)) || isset($_SESSION['adminView']) || $currentUser->getID() == $post->getAuthorID()))
		errorPage('Credentials Required', '"You do not have the necessary privileges to edit this post.', 403);

	if (isset($_POST['editPost']))
	{
		$post->setBody($_POST['body']);
		$post->updateDB();
		header("Location: viewThread?id={$_GET['thread']}&topic=".$currentTopic->getID().str_replace('&amp;', '&', $glob));
	}
	else if(!isset($_GET['post']) || !is_numeric($_GET['post']))
		errorPage('Invalid Request', 'iGroups cannot understand this request', 400);

	//-------Begin XHTML Output-------------------------------------//
		require('../doctype.php');
	require('../iknow/appearance.php');

	echo "<link rel=\"stylesheet\" href=\"../skins/$skin/dboard.css\" type=\"text/css\" title=\"$skin\" />\n";
	foreach($altskins as $altskin)
		echo "<link rel=\"alternate stylesheet\" href=\"../skins/$altskin/dboard.css\" type=\"text/css\" title=\"$altskin\" />\n";
?>
<title><?php echo $appname; ?> - Discussion Board</title>
<script type="text/javascript" src="ChangeLocation.js"></script>
</head>
<body>
<?php
	 /**** begin html head *****/
   require('htmlhead.php'); 
  //starts main container
  /****end html head content ****/
?>
<table class="noborder" width="85%"><tr><td><a href="dboard.php"><?php echo $appname; ?> Discussion Board</a> -&gt; <a href="<?php echo $topicLink; ?>"><?php echo $topicName; ?></a> -&gt; <a href="viewThread.php?id=<?php echo "{$currentThread->getID()}&amp;topic={$_GET['topic']}$glob"; ?>"><?php echo "{$currentThread->getName()}"; ?></a></td></tr></table>
<form action="edit.php?post=<?php echo $_GET['post']."&amp;topic=".$_GET['topic']."&amp;thread=".$_GET['thread'].$glob; ?>" method="post" id="postForm"><fieldset><legend>Edit Post</legend>
<table width="85%">
<tr><td valign="top"><label for="body">Message Body</label></td><td><textarea cols="60" rows="20" name="body" id="body"><?php echo $post->getBody(); ?></textarea></td></tr>
<tr><td align="center" colspan="2"><input type="submit" name="editPost" value="Edit Post" /></td></tr>
</table>
</fieldset></form>
<?php
 	/**** begin html footer*****/
  //include rest of html layout file
  require('htmlfoot.php');
  // ends main container
  /****** end html footer*****/
?>
</body>
</html>

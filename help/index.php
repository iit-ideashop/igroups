<?php
	session_start();

	include_once('../globals.php');
	include_once('../classes/person.php');
	include_once('../classes/helpcat.php');

	if(isset($_SESSION['userID']))
		$currentUser = new Person($_SESSION['userID'], $db);
	else
		$currentUser = false;
	
	if($currentUser && !$currentUser->isValid())
	{
		$currentUser = false;
		unset($_SESSION['userID']);
	}
	
	require('../doctype.php');
	require('../appearance.php');
	echo "<link rel=\"stylesheet\" href=\"../skins/$skin/default.css\" type=\"text/css\" title=\"$skin\" />\n";
	foreach($altskins as $altskin)
		echo "<link rel=\"alternate stylesheet\" href=\"../skins/$altskin/default.css\" type=\"text/css\" title=\"$altskin\" />\n";

	echo "<title>$appname Help Center</title>\n";
	
	echo "</head><body>\n";
	

  /**** begin html head *****/
   require('htmlhead.php'); 
  //starts main container
  /****end html head content ****/


	echo "<h1>$appname Help Center</h1>\n";
	echo "<p>Welcome to the $appname Help Center, where you can find information related to using $appname. If the Help Center does not solve your problem, you may contact us using the <a href=\"../needhelp.php\">Need Help form</a>.</p>\n";
	echo "<p>Recently reported, known problems will be listed on the <a href=\"known.php\">Known Issues</a> page.</p>\n";
	
	$categories = getAllHelpCategories($db);
	$numcats = count($categories);
	if($numcats)
	{
		echo "<div id=\"helptopics\">\n";
		$i = 1;
		if($numcats > 1)
			echo "<div id=\"helpleft\">\n";
		foreach($categories as $cat)
		{
			$topics = $cat->getAllTopics();
			if(count($topics))
			{
				echo "<h2>{$cat->getTitle()}</h2>\n";
				echo "<ul>\n";
				foreach($topics as $id => $topic)
					echo "<li><a href=\"view.php?id=$id\">{$topic->getTitle()}</a></li>\n";
				echo "</ul>\n";
			}
			if($numcats > 1 && $i++ == floor($numcats/2))
				echo "</div><div id=\"helpright\">";
		}
		if($numcats > 1)
			echo "</div></div>\n";
		else
			echo "</div>\n";
	}
?>	

<?php
 	/**** begin html footer*****/
  //include rest of html layout file
  require('htmlcontentfoot.php');
  // ends main container
  /****** end html footer*****/
?>
</body></html>

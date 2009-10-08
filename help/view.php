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

	if(is_numeric($_GET['id']))
	{
		$currTopic = new HelpTopic($_GET['id'], $db);
		if(!$currTopic->isValid())
			errorPage('Bad Topic', 'That help topic was not found', 400);
	}
	else
		errorPage('ID required', 'A help topic ID is required for this page', 400);
	
	require('../doctype.php');
	require('../appearance.php');
	echo "<link rel=\"stylesheet\" href=\"../skins/$skin/default.css\" type=\"text/css\" title=\"$skin\" />\n";
	foreach($altskins as $altskin)
		echo "<link rel=\"alternate stylesheet\" href=\"../skins/$altskin/default.css\" type=\"text/css\" title=\"$altskin\" />\n";

	echo "<title>$appname Help Center</title>\n";
	
	echo "</head><body>\n";
	require('sidebar.php');
	echo "<div id=\"content\">\n";
	echo "<h1>$appname Help Center</h1>\n";
	echo "<h2>{$currTopic->getTitle()}</h2>\n";
	echo "<div id=\"helptopicbody\">{$currTopic->getText()}</div>\n";
	echo "<p><a href=\"index.php\">Back to Help Center index</a></p>\n";
?>	
<p id="copyright">Copyright &copy; 2009 Illinois Institute of Technology Interprofessional Projects Program. All Rights Reserved.</p>
</div></body></html>

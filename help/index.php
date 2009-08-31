<?php
	session_start();

	include_once('../globals.php');
	include_once('../classes/person.php');

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
	require('sidebar.php');
	echo "<div id=\"content\">\n";
	echo "<h1>$appname Help Center</h1>\n";
	echo "<p>Welcome to the $appname Help Center, where you can find information related to using $appname. If the Help Center does not solve your problem, you may contact us using the <a href=\"../needhelp.php\">Need Help form</a>.</p>\n";
	echo "<p>Recently reported, known problems will be listed on the <a href=\"known.php\">Known Issues</a> page.</p>\n";
?>	
<p id="copyright">Copyright &copy; 2009 Illinois Institute of Technology Interprofessional Projects Program. All Rights Reserved.</p>
</div></body></html>

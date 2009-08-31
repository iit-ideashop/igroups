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

	echo "<title>$appname Help - Known Issues</title>\n";
	
	echo "</head><body>\n";
	require('../dboard/sidebar.php');
	echo "<div id=\"content\">\n";
	echo "<h1>$appname Help Center</h1>\n";
	echo "<h2>Known Issues</h2>\n";
	echo "<ul>\n";
	echo "<li>No issues at this time.</li>\n";
	echo "</ul>\n";
?>	
</div>
<p id="copyright">Copyright &copy; 2009 Illinois Institute of Technology Interprofessional Projects Program. All Rights Reserved.</p>
</div></body></html>

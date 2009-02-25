<?php
	include_once("classes/db.php");
	$db = new dbConnection();
	$row = mysql_fetch_row($db->igroupsQuery("select sValue from Appearance where sKey='appname'"));
	$appname = $row[0];
	$row = mysql_fetch_row($db->igroupsQuery("select sValue from Appearance where sKey='appurl'"));
	$appurl = $row[0];
	$row = mysql_fetch_row($db->igroupsQuery("select sValue from Appearance where sKey='contactemail'"));
	$contactemail = $row[0];

	if(!function_exists('errorPage'))
	{
		function errorPage($title, $desc, $response)
		{
			global $appname, $appurl, $contactemail;
			if($response == 400)
				header('HTTP/1.1 400 Bad Request');
			else if($response == 401)
				header('HTTP/1.1 401 Authorization Required');
			else if($response == 403)
				header('HTTP/1.1 403 Forbidden');
			else
				header('HTTP/1.1 500 Internal Server Error');
			echo<<<EOF
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<!-- This web-based application is Copyrighted &copy; 2009 Interprofessional Projects Program, Illinois Institute of Technology -->
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en"><head>
EOF;
			require('appearance.php');
			echo "<link rel=\"stylesheet\" href=\"skins/$skin/tasks.css\" type=\"text/css\" title=\"$skin\" />\n";
			foreach($altskins as $altskin)
				echo "<link rel=\"alternate stylesheet\" href=\"skins/$altskin/tasks.css\" type=\"text/css\" title=\"$altskin\" />\n";
			echo "<title>$appname - Fatal Error</title>\n";
			echo "</head><body>\n";
			require('sidebar.php');
			echo "<div id=\"content\">\n";
			echo "<h1>Fatal Error</h1>\n";
			echo "<h2>$title</h2>\n";
			echo "<p>$appname cannot perform the operation you requested, for the following reason: $desc</p>\n";
			echo "<p>If you wish to receive support for this problem, please email <a href=\"mailto:$contactemail\">$contactemail</a> with the information below, along with any additional information that you feel is relevant.</p>\n";
			echo "<ul>\n";
			echo "<li><b>Requested URI</b>: {$_SERVER['REQUEST_URI']}</li>\n";
			echo "<li><b>Referring page</b>: {$_SERVER['HTTP_REFERER']}</li>\n";
			echo "<li><b>User Agent</b>: {$_SERVER['HTTP_USER_AGENT']}</li>\n";
			if($currentUser)
				echo "<li><b>User ID</b>: {$currentUser->getID()}</li>\n";
			if($currentGroup)
				echo "<li><b>Group ID</b>: {$currentGroup->getID()}</li>\n";
			echo "</ul>\n";
			echo '</div></body></html>';
			die();
		}
	}
?>

<?php
	include_once('classes/db.php');
	
	$db = new dbConnection();
	$row = mysql_fetch_row($db->query('select sValue from Appearance where sKey="appname"'));
	$appname = $row[0];
	if(stristr(__FILE__ , '/home/iproadmin/public_html') !== false)
		$appurl = 'http://sloth.iit.edu/~iproadmin/igroups';
	else
	{
		$row = mysql_fetch_row($db->query('select sValue from Appearance where sKey="appurl"'));
		$appurl = $row[0];
	}
	$row = mysql_fetch_row($db->query('select sValue from Appearance where sKey="contactemail"'));
	$contactemail = $row[0];
	
	$igroupsUploadedFileDir = '/files/igroups/';

	if(!function_exists('errorPage'))
	{
		function errorPage($title, $desc, $response)
		{
			global $appname, $appurl, $contactemail, $db, $currentUser, $currentGroup;
			$responses = array(400 => 'Bad Request', 401 => 'Authorization Required', 403 => 'Forbidden', 500 => 'Internal Server Error');
			if(!array_key_exists($response, $responses))
				$response = 500;
			header("HTTP/1.1 $response {$responses[$response]}");
			require('doctype.php');
			require('appearance.php');
			echo "<link rel=\"stylesheet\" href=\"$appurl/skins/$skin/default.css\" type=\"text/css\" title=\"$skin\" />\n";
			foreach($altskins as $altskin)
				echo "<link rel=\"alternate stylesheet\" href=\"$appurl/skins/$altskin/default.css\" type=\"text/css\" title=\"$altskin\" />\n";
			echo "<title>$appname - Fatal Error</title>\n";
			echo "</head><body>\n";
			include_once('sidebar.php');
			echo "<div id=\"content\">\n";
			echo "<h1>Fatal Error - $response {$responses[$response]}</h1>\n";
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
			echo "<li><b>Error title:</b> $title</li>\n";
			echo "<li><b>Error description:</b> $desc</li>\n";
			echo "<li><b>HTTP response code:</b> $response</li>\n";
			echo "</ul>\n";
			die('</div></body></html>');
		}
	}
	
	if(!function_exists('printTR'))
	{
		function printTR()
		{
			static $shade = false;
			echo '<tr'.($shade ? ' class="shade"' : '').'>';
			$shade = !$shade;
		}
	}
?>

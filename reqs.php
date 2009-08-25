<?php
	session_start();
	include_once('globals.php');
	
	require('doctype.php');
	require('appearance.php');
	echo "<link rel=\"stylesheet\" href=\"skins/$skin/default.css\" type=\"text/css\" title=\"$skin\" />\n";
	foreach($altskins as $altskin)
		echo "<link rel=\"alternate stylesheet\" href=\"skins/$altskin/default.css\" type=\"text/css\" title=\"$altskin\" />\n";
?>
<title><?php echo $appname; ?> - Browser Requirements</title>
</head>
<body>
<?php
	require('sidebar.php');
	echo "<div id=\"content\"><h1>$appname Browser Requirements</h1>\n";
	echo "<p>$appname supports all modern browsers, \"modern\" being defined as a version released less than three years ago. JavaScript should be enabled (or iit.edu whitelisted in NoScript) for proper operation of $appname.</p>\n";
	echo "<p>As of this writing (August 24, 2009), \"modern\" includes the following browsers:</p>\n";
	echo "<ul>\n";
	echo "<li>Internet Explorer &gt;=7</li>\n";
	echo "<li>Firefox &gt;=2</li>\n";
	echo "<li>Safari &gt;=3</li>\n";
	echo "<li>Google Chrome (all)</li>\n";
	echo "<li>Opera &gt;=9</li>\n";	echo "</ul>\n";
	echo "<p>Older versions of these browsers <i>might</i> work fine, but often do not. Notably, the most common not-modern browser, Internet Explorer 6, may perform quirkily and $appname utilizes CSS2 attributes that IE 6 does not know about. Therefore, <b>the use of $appname with Internet Explorer 6 is neither supported nor recommended</b>; users should upgrade to Internet Explorer 7 or 8, or use an alternative browser.</p>\n";
?>
</div></body></html>

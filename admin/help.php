<?php
	include_once('../globals.php');
	include_once('checkadmin.php');
	
	//---------Start XHTML Output-----------------------------------//
	
	require('../doctype.php');
	require('../iknow/appearance.php');

	echo "<link rel=\"stylesheet\" href=\"../skins/$skin/default.css\" type=\"text/css\" title=\"$skin\" />\n";
	foreach($altskins as $altskin)
		echo "<link rel=\"alternate stylesheet\" href=\"../skins/$altskin/default.css\" type=\"text/css\" title=\"$altskin\" />\n";
?>
<title><?php echo $appname;?> - Help Center Management</title>
</head>
<body>
<?php
	require('sidebar.php');
	echo "<div id=\"content\">";
	
	//TODO Manage categories, pages, and known issues
?>
</div></body></html>

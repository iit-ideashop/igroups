<?php
	include_once('../globals.php');
	include_once('checkadmin.php');
	include_once('../classes/groupemail.php');
	include_once('../classes/group.php');
	
	//-----------Start XHTML Output---------------------------------//
	
	require('../doctype.php');
	require("../iknow/appearance.php");

	echo "<link rel=\"stylesheet\" href=\"../skins/$skin/default.css\" type=\"text/css\" title=\"$skin\" />\n";
	foreach($altskins as $altskin)
		echo "<link rel=\"alternate stylesheet\" href=\"../skins/$altskin/default.css\" type=\"text/css\" title=\"$altskin\" />\n";
?>
<title><?php echo $appname;?> - Display Email</title>
</head>
<body>
<?php
	if($email = new GroupEmail($_GET['id'], $db))
	{
		$author = $email->getSender();
		echo "<p><b>Subject:</b> ".$email->getSubjectHTML()."<br />";
		echo "<b>From:</b> ".$author->getFullName()."<br />";
		echo "<b>Date:</b> ".$email->getDate()."<br />";
		echo "<b>To:</b> ".$email->getTo()."<br />";
		$files = $email->getAttachments();
		foreach($files as $file)
			echo "$file<br />";
		echo "</p><p>".$email->getBodyHTML()."</p>";
	}
?>
</body></html>

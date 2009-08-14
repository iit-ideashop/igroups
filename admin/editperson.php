<?php
	include_once('../globals.php');
	include_once('checkadmin.php');
	
	//----------Start XHTML Output----------------------------------//
	
	require('../doctype.php');
	require('../iknow/appearance.php');
	echo "<link rel=\"stylesheet\" href=\"../skins/$skin/default.css\" type=\"text/css\" title=\"$skin\" />\n";
	foreach($altskins as $altskin)
		echo "<link rel=\"alternate stylesheet\" href=\"../skins/$altskin/default.css\" type=\"text/css\" title=\"$altskin\" />\n";
?>
<title><?php echo $appname;?> - Edit Person</title>
</head>
<body>
<?php
	if(isset($_GET['id']) && is_numeric($_GET['id']))
	{
		$contactInfo = mysql_fetch_array($db->query("SELECT * FROM People WHERE iID={$_GET['id']}"));
		if ($contactInfo)
			$uid = $contactInfo['iID'];
		else
			errorPage('None Found', 'No users matched that ID.', 400);
		if(isset($_POST['fname']) && isset($_POST['lname']))
		{
			if($db->query("update People set sFName='".mysql_real_escape_string($_POST['fname'])."', sLName='".mysql_real_escape_string($_POST['lname'])."' where iID=$uid"))
			{
				$message = 'Person successfully updated.';
				$contactInfo = mysql_fetch_array($db->query("SELECT * FROM People WHERE iID=$uid"));
			}
			else
				$message = 'ERROR: Update failed: '.mysql_error();
		}
	require('sidebar.php');
?>
		<div id="content">
		<form method="post" action="editperson.php?id=<?php echo $uid; ?>"><fieldset><legend>Edit Person</legend>
		<p><b>Primary email:</b> <?php echo $contactInfo['sEmail']; ?></p>
		<label><b>First name:</b> <input type="text" name="fname" value="<?php echo $contactInfo['sFName']; ?>" /></label><br />
		<label><b>Last name:</b> <input type="text" name="lname" value="<?php echo $contactInfo['sLName']; ?>" /></label><br />
		<p style="font-size: smaller">A user's primary email address may not be edited.</p>
<input type="submit" value="Edit Person" /><input type="reset" /></fieldset></form>
<?php
		echo "<a href=\"people.php?uid=$uid\">Back to user profile</a>\n";
	}
	else //if no email given in URL
	{
?>
		<div id="content">
		<form method="get" action="people.php"><fieldset>
		<label for="email">Email address query:</label><input type="text" name="email" id="email" /><input type="submit" name="Submit" />
		</fieldset></form>
<?php
	}
?>
</div></body></html>

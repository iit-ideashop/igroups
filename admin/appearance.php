<?php
	include_once('../globals.php');
	include_once('checkadmin.php');
	
	if(isset($_POST['sub']))
	{
		$db->query("update Appearance set sValue='".mysql_real_escape_string($_POST['appname'])."' where sKey='appname'");
		$db->query("update Appearance set sValue='".mysql_real_escape_string($_POST['appurl'])."' where sKey='appurl'");
		$db->query("update Appearance set sValue='".mysql_real_escape_string($_POST['contactemail'])."' where sKey='contactemail'");
		$message = "The appearance has been updated";
	}
	
	//---------Start XHTML Output-----------------------------------//
	
	require('../doctype.php');
	require_once('../iknow/appearance.php');

	echo "<link rel=\"stylesheet\" href=\"../skins/$skin/default.css\" type=\"text/css\" title=\"$skin\" />\n";
	foreach($altskins as $altskin)
		echo "<link rel=\"alternate stylesheet\" href=\"../skins/$altskin/default.css\" type=\"text/css\" title=\"$altskin\" />\n";
?>
<title><?php echo $appname;?> - Appearance</title>
</head><body>
<?php
	require('sidebar.php');
?>
<div id="content"><div id="topbanner">Appearance</div>
<form method="post" action="appearance.php"><fieldset>
<?php
	$query = mysql_fetch_row($db->query("select sValue from Appearance where sKey='appname' and sCSSAttribute is null"));
	echo "<label for=\"appname\">Application name:</label><input type=\"text\" name=\"appname\" id=\"appname\" value=\"".$query[0]."\" /><br />\n";
	$query = mysql_fetch_row($db->query("select sValue from Appearance where sKey='appurl' and sCSSAttribute is null"));
	echo "<label for=\"appurl\">Application URL:</label><input type=\"text\" name=\"appurl\" id=\"appurl\" value=\"".$query[0]."\" /><br />\n";
	$query = mysql_fetch_row($db->query("select sValue from Appearance where sKey='contactemail' and sCSSAttribute is null"));
	echo "<label for=\"contactemail\">Contact email:</label><input type=\"text\" name=\"contactemail\" id=\"contactemail\" value=\"".$query[0]."\" /><br />\n";
?>
<input type="submit" name="sub" /><input type="reset" />
</fieldset></form>	
</div></body></html>

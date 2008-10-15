<?php
	include_once( "checkadmin.php" );
	
	if(isset($_POST['sub']))
	{
		$db->igroupsQuery("update Appearance set sValue='".mysql_real_escape_string($_POST['appname'])."' where sKey='appname'");
		$db->igroupsQuery("update Appearance set sValue='".mysql_real_escape_string($_POST['appurl'])."' where sKey='appurl'");
		$db->igroupsQuery("update Appearance set sValue='".mysql_real_escape_string($_POST['contactemail'])."' where sKey='contactemail'");
		$db->igroupsQuery("update Appearance set sValue='".mysql_real_escape_string($_POST['linkcolor'])."' where sKey='a:link, a:visited' and sCSSAttribute='color'");
		$message = "The appearance has been updated";
	}
	include_once("../globals.php");
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<!-- This web-based application is Copyrighted &copy; 2008 Interprofessional Projects Program, Illinois Institute of Technology -->
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en"><head>
<?php require("../iknow/appearance.php"); ?>
<title><?php echo $appname;?> - Appearance</title>
</head><body>
<?php
	require("sidebar.php");
?>
<div id="content"><div id="topbanner">Appearance</div>
<form method="post" action="appearance.php"><fieldset>
<?php
	$query = mysql_fetch_row($db->igroupsQuery("select sValue from Appearance where sKey='appname' and sCSSAttribute is null"));
	echo "<label for=\"appname\">Application name:</label><input type=\"text\" name=\"appname\" id=\"appname\" value=\"".$query[0]."\" /><br />\n";
	$query = mysql_fetch_row($db->igroupsQuery("select sValue from Appearance where sKey='appurl' and sCSSAttribute is null"));
	echo "<label for=\"appurl\">Application URL:</label><input type=\"text\" name=\"appurl\" id=\"appurl\" value=\"".$query[0]."\" /><br />\n";
	$query = mysql_fetch_row($db->igroupsQuery("select sValue from Appearance where sKey='contactemail' and sCSSAttribute is null"));
	echo "<label for=\"contactemail\">Contact email:</label><input type=\"text\" name=\"contactemail\" id=\"contactemail\" value=\"".$query[0]."\" /><br />\n";
	$query = mysql_fetch_row($db->igroupsQuery("select sValue from Appearance where sKey='a:link, a:visited' and sCSSAttribute='color'"));
	echo "<label for=\"linkcolor\">Link color:</label><input type=\"text\" name=\"linkcolor\" id=\"linkcolor\" value=\"".$query[0]."\" /><br />\n";
?>
<input type="submit" name="sub" /><input type="reset" />
</fieldset></form>	
</div></body></html>

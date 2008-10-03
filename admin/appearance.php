<?php
	include_once( "checkadmin.php" );
	
	if(isset($_POST['sub']))
	{
		$db->igroupsQuery("update Appearance set sValue='".mysql_real_escape_string($_POST['appname'])."' where sKey='appname'");
		$db->igroupsQuery("update Appearance set sValue='".mysql_real_escape_string($_POST['linkcolor'])."' where sKey='a:link, a:visited' and sCSSAttribute='color'");
		$message = "The appearance has been updated";
	}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<!-- This web-based application is Copyrighted &copy; 2008 Interprofessional Projects Program, Illinois Institute of Technology -->
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en"><head>
<title>iGroups - Appearance</title>
<link rel="stylesheet" href="../default.css" type="text/css" />
<style type="text/css">
<?php
	$query = $db->igroupsQuery("select distinct sKey from Appearance where sCSSAtrribute not null");
	while($row = mysql_fetch_row($query))
	{
		echo $row[0]." {\n";
		$query2 = $db->igroupsQuery("select sCSSAttribute, sValue from Appearance where sKey='".$row[0]."' and sCSSAttribute not null");
		while($row2 = mysql_fetch_array($query2))
		{
			echo "\t".$row2['sCSSAttribute'].": ".$row2['sValue'].";\n";
		}
		echo "}\n";
	}
?>
</style>
</head><body>
<?php
	require("sidebar.php");
?>
<div id="content"><div id="topbanner">Appearance</div>
<form method="post" action="appearance.php"><fieldset>
<?php
	$query = mysql_fetch_row($db->igroupsQuery("select sValue from Appearance where sKey='appname' and sCSSAttribute=NULL"));
	echo "<label for=\"appname\">Application name:</label><input type=\"text\" name=\"appname\" id=\"appname\" value=\"".$query[0]."\" />\n";
	$query = mysql_fetch_row($db->igroupsQuery("select sValue from Appearance where sKey='a:link, a:visited' and sCSSAttribute='color'"));
	echo "<label for=\"linkcolor\">Link color:</label><input type=\"text\" name=\"linkcolor\" id=\"linkcolor\" value=\"".$query[0]."\" />\n";
?>
<input type="submit" name=\"sub\" /><input type="reset" />
</fieldset></form>	
</div></body></html>

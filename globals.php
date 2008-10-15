<?php
	include_once("classes/db.php");
	$db = new dbConnection();
	$row = mysql_fetch_row($db->igroupsQuery("select sValue from Appearance where sKey='appname'"));
	$appname = $row[0];
	$row = mysql_fetch_row($db->igroupsQuery("select sValue from Appearance where sKey='appurl'"));
	$appurl = $row[0];
	$row = mysql_fetch_row($db->igroupsQuery("select sValue from Appearance where sKey='contactemail'"));
	$contactemail = $row[0];
?>

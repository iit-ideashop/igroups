<?php
	include_once("../classes/db.php");
	
	$_DB = new dbConnection();
	if(isset($_GET['name'])){
		$name = $_GET['name'];
		$query = "SELECT * FROM NuggetFiles WHERE sOrigName = '$name'";
		$result = $_DB->igroupsQuery($query);
		$row = mysql_fetch_array($result);
		$diskName = $row['sDiskName'];
		header("Content-Length: ". filesize("/files/iknow/$diskName"));
		$orgName = $row['sOrigName'];
		header("Content-Disposition: attachment; filename='".$orgName."'");
		header("Cache-Control: must-revalidate,post-check=0,pre-check=0");
		header("Pragma: public");
		header("Expires: 0");
	
		readfile("/files/iknow/$diskName");
	}
?>
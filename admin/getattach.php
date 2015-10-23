<?php
	require_once('../globals.php');
	require_once('checkadmin.php');
	include_once('../classes/group.php');
	include_once('../classes/groupemail.php');
	if($email = new GroupEmail($_GET['email'], $db))
	{
		$query = $db->query("SELECT * FROM GroupEmailFiles WHERE iID={$_GET['id']}");
		$file = mysql_fetch_array($query);
		header("Content-Type: {$file['sMimeType']}");
		header("Content-Length: " . filesize("$disk_prefix/emails/{$file['sDiskName']}"));
		header('Content-Disposition: attachment; filename="'.$file['sOrigName'].'"');
		header("Cache-Control: must-revalidate,post-check=0,pre-check=0");
		header("Cache-Control: public",false);
		header("Pragma: public");
		header("Expires: 0");

		readfile("$disk_prefix/emails/{$file['sDiskName']}");
	}
?>

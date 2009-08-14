<?php
	include_once('../globals.php');
	include_once('checkadmin.php');
	include_once('../classes/group.php');
	include_once('../classes/file.php');
	include_once('../classes/nugget.php');
		
	if(!is_numeric($_GET['id']))
		errorPage('Invalid Request', "$appname could not understand that request", 400);

	if($file = new File( $_GET['id'], $db))
	{
		header("Content-Type: {$file->getMimeType()}");
		header('Content-Length: '.filesize($file->getDiskName()));
		header('Content-Disposition: attachment; filename="'.$file->getOriginalName().'"');
		header('Cache-Control: must-revalidate,post-check=0,pre-check=0');
		header('Cache-Control: public', false);
		header('Pragma: public');
		header('Expires: 0');

		readfile($file->getDiskName());
	}
?>

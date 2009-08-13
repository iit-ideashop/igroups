<?php
	include_once('globals.php');
	include_once('checklogin.php');
	include_once('classes/file.php');
		
	if(!is_numeric($_GET['id']))
		errorPage('Invalid File ID', 'The file ID provided is invalid', 400);

	if($file = new File( $_GET['id'], $db))
	{
		$group = new Group($file->getGroupID(), $file->getGroupType(), $file->getSemester(), $db);
		if(!$currentUser->isGroupMember($group) && !$file->isIPROFile())
			errorPage('Group Credentials Required', 'You are not a member of this group', 403);
		if($file->isPrivate() && ($file->getAuthorID() != $currentUser->getID() && !$currentUser->isGroupAdministrator($file->getGroup())))
			errorPage('Group Credentials Required', 'You are not authorized to download this file', 403);
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

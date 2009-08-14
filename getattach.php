<?php
	include_once('checklogin.php');
	include_once('classes/email.php');
		
	if ( $email = new Email( $_GET['email'], $db ) )
	{
		$group = $email->getGroup();
		if(!$group->isGroupMember($currentUser))
			errorPage('Group Credentials Required', 'You are not authorized to download this file.', 403);
		$query = $db->query("SELECT * FROM EmailFiles WHERE iID={$_GET['id']}");
		$file = mysql_fetch_array($query);
		header("Content-Type: {$file['sMimeType']}");
		header('Content-Length: '.filesize("/files/igroups/emails/{$file['sDiskName']}"));
		header('Content-Disposition: attachment; filename="'.$file['sOrigName'].'"');
		header('Cache-Control: must-revalidate,post-check=0,pre-check=0');
		header('Cache-Control: public', false);
		header('Pragma: public');
		header('Expires: 0');

		readfile("/files/igroups/emails/{$file['sDiskName']}");
	}
?>

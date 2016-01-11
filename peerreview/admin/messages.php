<?php 
include_once('../classes/db.php');
include_once('../classes/person.php');
include_once('../classes/group.php');
include_once('../classes/message.php');

include_once('checkadmin.php');

if(!$currentUser->isAdministrator())
	errorPage('Credentials Required', 'You must be an administrator to access this page', 403);

if(is_numeric($_GET['delete']))
	$db->query("DELETE FROM Messages WHERE id={$_GET['delete']}");
include_once('../includes/header.php');
?>
<title><?php echo $GLOBALS['systemname']; ?> - Manage Surveys</title>
<?php
include_once('../includes/sidebar.php');
?>
<div id="content">
<h2>Manage Messages</h2>
<p>Here, you can create pre-written messages to e-mail students. To send one of these messages, use the 'Admin Survey' link on the left.</p>
<hr<?php echo $st; ?>>
<table width="100%">
<tr><th colspan="4">Messages</th></tr>
<tr style="text-align: center; font-weight: bold"><td width="20%">Subject</td><td>Text</td><td colspan="2">Actions</td></tr>
<?php
$messages = getAllMessages($db);
foreach($messages as $id => $message)
{
	$short = htmlspecialchars(substr($message->getText(),0,255));
	if(strlen($short) >= 255)
		$short .= '...';
	echo "<tr><td>".htmlspecialchars($message->getName())."</td><td>$short</td><td style=\"text-align: center\" width=\"10%\"><a href=\"editmessage.php?message=$id\" onclick=\"return popup(this)\">Edit</a></td><td style=\"text-align: center\" width=\"10%\"><a href=\"messages.php?delete=$id\">Delete</a></td></tr>\n";
}
?>
</table>
<br<?php echo $st; ?>>
<p style="text-align: center"><a href="editmessage.php">Create New Message</a></p>
</div></body></html>

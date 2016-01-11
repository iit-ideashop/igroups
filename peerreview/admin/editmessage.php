<?php 
include_once('../classes/db.php');
include_once('../classes/person.php');
include_once('../classes/group.php');
include_once('../classes/message.php');

include_once('checkadmin.php');

if(isset($_POST['submitMessage']))
{
	if(isset($_POST['messageID']))
	{
		$msg = new Message ($_POST['messageID'], $db);
		$msg->setName($_POST['name']);
		$msg->setText($_POST['text']);
		$msg->updateDB();
	}
	else
	{
		$nm = mysql_real_escape_string(stripslashes($_POST['name']));
		$tx = mysql_real_escape_string(stripslashes($_POST['text']));
		$db->query("INSERT INTO Messages (name, contents) VALUES ('$nm', '$tx')");
	}
}

if(is_numeric($_GET['message']))
	$message = ($_GET['message'] != 0) ? new Message($_GET['message'], $db) : false;
include_once('../includes/header.php');
?>
<title><?php echo $GLOBALS['systemname']; ?> - Edit Message</title>
<?php
include_once('../includes/sidebar.php');
?>
<div id="content">
<form action="editmessage.php" method="post"><fieldset><legend>Create/Edit Message</legend>
<?php 
if($message)
{
	echo "<p><label>Subject: <input type=\"text\" size=\"50\" name=\"name\" value=\"".htmlspecialchars($message->getName())."\"$st></label></p>\n";
	echo "<p><label>Message: <textarea name=\"text\" rows=\"25\" cols=\"50\">".htmlspecialchars($message->getText())."</textarea></label></p>\n";
	echo "<input type=\"hidden\" name=\"messageID\" value=\"{$message->getID()}\"$st>\n";
}
else
{
	echo "<p><label>Subject: <input type=\"text\" name=\"name\" size=\"50\"$st></label></p>\n";
	echo "<p><label>Message: <textarea name=\"text\" rows=\"25\" cols=\"50\"></textarea></label></p>\n";
}
echo "<br$st><br$st>\n";
?>
<input type="submit" name="submitMessage" value="Submit"<?php echo $st; ?>>
</fieldset></form></div></body></html>

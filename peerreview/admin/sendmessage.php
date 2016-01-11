<?php 
include_once('../classes/db.php');
include_once('../classes/person.php');
include_once('../classes/group.php');
include_once('../classes/message.php');

include_once('checkadmin.php');

if(isset($_POST['sendEmail']))
{
	$toList = $_POST['toList'];
	$subject = $_POST['subject'];
	$body = "The following message was sent through the {$GLOBALS['systemname']}:\n\n".wordwrap($_POST['body'], 100);
	$headers = "From: {$currentUser->getFullName()} <{$currentUser->getEmail()}>\n";
	$headers .= "To: $toList\n";

	if(!mail('', $subject, $body, $headers))
		errorPage('Not Sent', 'E-mail could not be sent.', 500);
	include_once('../includes/header.php');
	echo "<title>{$GLOBALS['systemname']} - Message Sent</title>\n";
	echo "<meta http-equiv=\"Refresh\" content=\"3; URL=adminsurvey.php\"$st>\n";
	include_once('../includes/sidebar.php');
	echo "<div id=\"content\"><h1>Success</h1>\n<p>Email was sent successfully. You will be redirected in three seconds.</p>\n</div>";
	die('</body></html>');
}
else if(!isset($_POST['sendEmail']) && !isset($_POST['messageID']))
	errorPage('No Action Selected', 'You must view this page through the proper channels.', 400);
else if(!isset($_POST['sendTo']))
	header('Location: adminsurvey.php?nousers=1');

$message = false;
if(is_numeric($_POST['messageID']) && $_POST['messageID'] > 0)
{
	$message = new Message($_POST['messageID'], $db);
	if($message->getID() < 0)
		$message = false;
}

$toList = array();

foreach($_POST['sendTo'] as $userID)
{
	$temp = new Person($userID, $db);
	$toList[] = $temp->getEmail();
}
$subject = $message ? htmlspecialchars($message->getName()) : '';
$body = $message ? htmlspecialchars($message->getText()) : '';
include_once('../includes/header.php');
echo "<title>{$GLOBALS['systemname']} - Send Message</title>\n";
include_once('../includes/sidebar.php');
?>
<div id="content">
<form action="sendmessage.php" method="post"><fieldset><legend>Send a Message</legend>
<p><b>To:</b> 
<?php
$emails = htmlspecialchars(implode($toList, ", "));
echo $emails;
?>
</p>
<p><label for="subject">Subject:</label></p>
<input type="text" size="75" maxlength="100" name="subject" id="subject" value="<?php echo $subject."\"$st"; ?>>
<br<?php echo $st; ?>>
<p><label for="body">Body:</label></p>
<textarea rows="20" cols="100" name="body" id="body"><?php echo $body; ?></textarea>
<br<?php echo $st; ?>><br<?php echo $st; ?>>
<input type="hidden" name="toList" value="<?php echo "$emails\"$st"; ?>>
<input type="submit" name="sendEmail" value="Send"<?php echo $st; ?>>
</fieldset></form>
</div></body></html>

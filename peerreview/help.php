<?php
include_once('classes/db.php');
include_once('classes/person.php');
session_start();
$db = new dbConnection();
include_once('includes/header.php');
?>
<title><?php echo $GLOBALS['systemname']; ?> - Help</title>
<?php
include_once('includes/sidebar.php');
?>
<div id="content">
<h1><?php echo $GLOBALS['systemname']; ?> Help</h1>
<p>Here are some basic instructions about how to use the <?php echo $GLOBALS['systemname']; ?>. If your question isn't answered here, feel free to e-mail the administrator at <a href="mailto:<?php echo $GLOBALS['adminemail']; ?>"><?php echo $GLOBALS['adminemail'];?></a>.</p>
<hr<?php echo $st; ?>>

<h3>Logging In</h3>
<p>You log in by entering your username and password on the left. This login information is the same as for your iGroups account.</p>
<h2>For Students...</h2>
<h3>Viewing Your Status</h3>
<p>When you first login, you are presented with your status. This is a table showing all of the surveys you must complete for all of your IPROs this semester. To take a survey, click the 'Take Survey' button. You are done with your peer reviews when the status of all your surveys reads 'Complete'. You can also view all of your entered data at once by clicking the 'View All' link at the top of the table.</p>

<h3>Taking Surveys</h3>
<p>Each survey contains two sections: A numeric rating section and an optional written comments section. Follow the instructions on the top of the page to complete the survey. If you wish to provide additional written comments for Section 2, you may do so. Your responses will be included in a ratings summary, but your individual responses will remain anonymous. You can submit your complete survey by clicking the 'Submit' button at the bottom of the form. If you submit an incomplete survey, you will have to go back and finish your responses before the survey will be counted as complete.</p>

<h2>For Faculty...</h2>
<h3>Viewing Your Team's Status</h3>
<p>First, choose 'View Status' from the left navigation menu. You are then presented with a dropdown box containing the names of all of your teams. Choose the one you wish to view, and click 'Select Group'.</p>

<h3>Sending Reminders</h3>
<p>From the 'View Status' page, click the checkboxes next to the students to which you wish to send a message. Then press'Compose a Message' to write your message. Finally, click 'Send' to send the message.</p>
<h3>Removing Invalid Data</h3>
<p>If you wish to remove a certain person's data from your team's reports, please contact the systems administrator at <a href="mailto:<?php echo $GLOBALS['adminemail']; ?>"><?php echo $GLOBALS['adminemail']; ?></a>. Please be sure to include your team's name, and a specific description of what you would like removed.</p>
</div></body></html>

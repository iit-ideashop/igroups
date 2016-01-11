<?php 
include_once('classes/db.php');
include_once('classes/person.php');
include_once('classes/group.php');
include_once('classes/rating.php');

if(isset($_POST['login']) && isset($_POST['username']) && isset($_POST['password']))
{
	include_once('validate.php');
	$db = new dbConnection();
	session_start();
	if($userID = validate_user($_POST['username'], $_POST['password'], $db))
	{
		$_SESSION['uID'] = $userID;
		$u = new Person($userID, $db);
		$_SESSION['uType'] = $u->getUserType();
		if(isset($_POST['remember']))
		{
			setcookie('userID', $_POST['username'], time()+60*60*24*7);
			setcookie('password', md5($_POST['password']), time()+60*60*24*7);
		}
	}
	else
	{
		$_SESSION['loginError'] = true;
		header("Location: {$GLOBALS['rootdir']}index.php");
	}
}

include_once('checklogin.php');
include_once('includes/header.php');
?>
<title><?php echo $GLOBALS['systemname']; ?> - Status</title>
<?php
include_once('includes/sidebar.php');
?>
<div id="content">
<?php
if($currentUser->isFaculty())
{
	echo "<h2>Peer Review Administration</h2>\n";
	echo "<hr$st>\n";
	echo "<p>You can check the progress of your team's peer review process at any time. To see who has not yet completed his or her reviews, click 'View Status' on the left. To download your team's reports, click 'Download Reports'.</p>\n";
}
else if($currentUser->isAdministrator())
{
	echo "<h2>Peer Review Administration</h2>\n";
	echo "<hr$st>\n";
	echo "<h4>System-wide Status</h4>\n";
	echo "<p>Administrators: ";

	$query = $db->query("SELECT count(*) FROM People where userType=2");
	$result = mysql_fetch_row($query);
	echo "{$result[0]}</p>\n";

	echo "<p>Users: ";

	$query = $db->query("SELECT count(*) FROM People where userType=0");
	$result = mysql_fetch_row($query);
	echo "{$result[0]}</p>\n";

	echo "<p>Surveys Completed: ";

	$query = $db->query("SELECT count(*) FROM Ratings where isComplete=1");
	$result = mysql_fetch_row($query);
	echo "{$result[0]}</p>\n";

	echo "<p>Incomplete Surveys: ";

	$query = $db->query("SELECT count(*) FROM Ratings where isComplete=0");
	$result = mysql_fetch_row($query);
	echo "{$result[0]}</p>\n";
}
else
{
	echo "<h2>My Peer Review Status</h2>\n";
	echo "<hr$st>\n";
	echo "<p>You must complete a survey for every member in your group, including a self-assessment. To take a survey, click the 'Take Survey' button in the action column. You may NOT edit your responses after submitting each survey, so make sure you double-check before clicking 'Submit Survey'.</p> \n";
	echo "<table width=\"60%\">\n";
	echo "<tr><th colspan=\"4\">My Surveys</th></tr>";
	echo "<tr><th>Group</th><th>Name</th><th>Status</th><th>Action</th></tr>";

	$ratings = $currentUser->getRatings();
	foreach ($ratings as $rating)
	{
		if ($rating->isComplete())
		{
			$complete = "<td class=\"complete\">Complete</td>";
			$action = "<input type=\"button\" value=\"View\" onclick=\"self.location='viewsurvey.php?rID={$rating->getID()}'\"$st>";
		}
		else
		{
			$complete = "<td class=\"incomplete\">Incomplete</td>";
			$action = "<input type=\"button\" value=\"Take Survey\" onclick=\"self.location='takesurvey.php?rID={$rating->getID()}'\"$st>";
		}
		
		$grname = htmlspecialchars($rating->getGroupName());
		$rtname = htmlspecialchars($rating->getRatedName());
		if ($rating->getRatedID() == $rating->getRaterID())
			echo "<tr><td>$grname</td><td>$rtname<br$st><b>(Self-Assessment)</b></td>$complete<td>$action</td></tr>\n";
		else
			echo "<tr><td>$grname</td><td>$rtname</td>$complete<td>$action</td></tr>\n";
	}
	echo "</table>\n";
}
?>
</div></body></html>

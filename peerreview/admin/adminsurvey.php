<?php 
include_once('../classes/db.php');
include_once('../classes/person.php');
include_once('../classes/group.php');
include_once('../classes/rating.php');
include_once('../classes/message.php');

include_once('checkadmin.php');

if(is_numeric($_GET['group']) || $_GET['group'] == 'all')
	$get = "?group={$_GET['group']}";
else
{
	unset($_GET['group']);
	$get = '';
}

if(isset($_GET['selectGroup']) && $_GET['group'])
	$_SESSION['group'] = $_GET['group'];

if(isset($_SESSION['group']))
{
	$currentGroup = ($_SESSION['group'] != 'all') ? new Group($_SESSION['group'], $db) : 'all';

	if($currentGroup != 'all' && isset($_GET['resetGroup']) && $_GET['resetGroup'])
	{
		$resetGroup = $currentGroup;
		$resetGroup->sendToArchive();
		$lid = $resetGroup->getListID();
		$delMembers = $resetGroup->getGroupMembers();
		$db->query("delete from Ratings where groupID={$resetGroup->getID()}");
		$db->query("delete from CustomCriteriaRatings where ccID in (select id from CustomCriteria where groupID={$resetGroup->getID()})");
		foreach ($delMembers as $user)
		{
			if(!$user->isFaculty())
			{
				foreach ($delMembers as $otherUser)
				{
					if(!$otherUser->isFaculty())
						$db->query("insert into Ratings (raterID, ratedID, groupID, isComplete, listID) values ({$user->getID()}, {$otherUser->getID()}, {$resetGroup->getID()}, 0, $lid)");
				}
			}
		}
	}
}
		
if(isset($_GET['remove']) && is_numeric($_GET['remove']))
	$db->query("update Ratings set isComplete=0 WHERE groupID={$currentGroup->getID()} AND raterID={$_GET['remove']}");

$groups = array();

$query = $db->query(($currentUser->isFaculty()) ? "SELECT g.id FROM Groups g, PeopleGroupMap m WHERE g.id = m.groupID AND m.userID={$currentUser->getID()}" : "SELECT id FROM Groups ORDER BY name");
	 
while($row = mysql_fetch_row($query))
	$groups[] = new Group($row[0], $db);

$query = $db->query("SELECT id FROM Messages");

while($row = mysql_fetch_row($query))
	$massages[] = new Message($row[0], $db);
include_once('../includes/header.php');
$onload = 'checkMailForm()';
?>
<script type="text/javascript">
//<![CDATA[
function confirmReset()
{
	var answer = confirm("All data associated with this group will become archival and will no longer be able to be edited or viewed. A clean running of the peer review process in this semester will be set up. Are you sure you want to do this?")
	if(answer)
		window.location = "adminsurvey.php<?php if (isset($currentGroup) && is_object($currentGroup)) echo "?resetGroup=1";?>";
}

function checkMailForm()
{
	var count = 0;
	var checkboxes = document.forms['mailform'].elements['sendTo[]'];
	for(var i = 0; i < checkboxes.length; ++i)
		if(checkboxes[i].checked)
			++count;
	document.forms['mailform'].elements['sendMessage'].disabled = (count == 0);
	document.getElementById('recipients').style.visibility = ((count == 0) ? 'visible' : 'hidden');
}
//]]>
</script>
<title><?php echo $GLOBALS['systemname']; ?> - Admin Survey</title>
<?php
include_once('../includes/sidebar.php');
?>
<div id="content">
<h2>Administer Your Surveys</h2>
<p>Here, you can check the status of posted surveys as well as send messages to users. To send a message, check the boxes next to the student(s) you want to send a message to, then click on the 'Compose Message' button. The feature for sending messages becomes very useful when you need to send reminders to students who have not completed the surveys before the deadline.</p>
<?php
echo "<hr$st>\n";
echo "<form action=\"adminsurvey.php$get\" method=\"get\"><fieldset><legend>Select a Group</legend>\n";
echo "<select name=\"group\">\n";

foreach($groups as $group)
{
	$grname = htmlspecialchars($group->getName());
	$s = (is_object($currentGroup) && $group->getID() == $currentGroup->getID()) ? " $selected" : '';
	echo "<option value=\"{$group->getID()}\"$s>$grname</option>\n";
}

if($currentUser->isAdministrator())
{
	$s = ($_GET['group'] == 'all') ? " $selected" : '';
	echo "<option value=\"all\"$s>All Groups</option>\n";
}

echo "</select>\n";
echo "&#160;<input type=\"submit\" name=\"selectGroup\" value=\"Select Group\"$st>\n";
echo "</fieldset></form>\n";

if(isset($_SESSION['group']) && $_SESSION['group'] != 'all')
{
	$members = $currentGroup->getGroupMembers();
	if(count($members))
	{
		echo "<form action=\"sendmessage.php\" method=\"post\" id=\"mailform\"><fieldset><legend>Send a Message</legend>\n";
		echo "<label>Use Message Template:<br$st><select name=\"messageID\">\n\t<option value=\"0\">Blank</option>\n";
		$massages = getAllMessages($db);
		foreach($massages as $mid => $massage)
			echo "\t<option value=\"$mid\">".htmlspecialchars($massage->getName())."</option>\n";
		echo "</select></label><br$st>\n";
		echo "<p><input type=\"submit\" value=\"Compose Message\" name=\"sendMessage\"$st> ";
		if($_GET['nousers'])
			echo "<span id=\"recipients\" class=\"error\">Error: You must select recipients first.</span>\n";
		else
			echo "<span id=\"recipients\"><small>(You must select recipient(s) first.)</small></span>\n";
		echo "</p>\n";
		
		echo "<table width=\"80%\" class=\"centered\">";

		$numCols = 4;
		
		$grname = htmlspecialchars($currentGroup->getName());
		echo "<tr><th colspan=\"$numCols\">$grname<br$st><a href=\"adminsurvey.php?resetGroup=1\" onclick=\"confirmReset(); return false\" style=\"font-size: smaller\">[Reset Peer Review Process]</a></th></tr>\n";
		echo "<tr><th>Reviewer</th><th>Surveys Completed</th><th>Send Message</th><th>Remove Surveys</th></tr>\n";
	
		foreach($members as $user)
		{
			if(!$user->isFaculty())
			{
				$total = $user->getNumRatingsByGroup($currentGroup->getID()) * $user->getNumRatingsByGroup($currentGroup->getID());
				$fac = '';
			}
			else
				$fac = ' (F)';
	
			if($user->getNumCompletedByGroup($currentGroup->getID()) == $user->getNumRatingsByGroup($currentGroup->getID()))
				$bg = 'completed';
			else
				$bg = 'pending';

			$flname = htmlspecialchars($user->getFullName()).$fac;
			$email = htmlspecialchars($user->getEmail());
			echo "<tr class=\"$bg\"><td>$flname ($email)</td>";
			$count += $user->getNumCompletedByGroup($currentGroup->getID());
			echo "<td> {$user->getNumCompletedByGroup($currentGroup->getID())} / {$user->getNumRatingsByGroup($currentGroup->getID())}</td>";
			echo "<td><input type=\"checkbox\" name=\"sendTo[]\" value=\"{$user->getID()}\" onchange=\"checkMailForm()\"$st></td><td>[<a href=\"adminsurvey.php?remove={$user->getID()}\">Remove</a>]</td></tr>\n";
		}

		echo "<tr><th>TOTAL</th><td> $count / $total </td><td></td></tr>\n";
		echo "</table></fieldset></form>\n";
	}
	else
		echo "<h2>{$currentGroup->getName()}</h2>\n<p>This group is empty.</p>\n";
	
}
else if(isset($_SESSION['group']) && $_SESSION['group'] == 'all')
{

	echo "<h3 style=\"text-align: center;\">Overview for All Groups</h3>";

	foreach($groups as $currentGroup)
	{
		$members = $currentGroup->getGroupMembers();
		if(!count($members))
			continue;
		echo "<table width=\"80%\" class=\"centered\">\n";

		$numCols = 2;
		
		$grname = htmlspecialchars($currentGroup->getName());
		echo "<tr><th colspan=\"$numCols\">$grname</th></tr>\n";
		echo "<tr><th>Reviewer</th><th>Surveys Completed</th></tr>\n";

		foreach($members as $user)
		{
			if(!$user->isFaculty())
			{
				$total = $user->getNumRatingsByGroup($currentGroup->getID()) * $user->getNumRatingsByGroup($currentGroup->getID());
				$fac = '';
			}
			else
				$fac = ' (F)';

			if($user->getNumCompletedByGroup($currentGroup->getID()) == $user->getNumRatingsByGroup($currentGroup->getID()))
				$bg = 'completed';
			else
				$bg = 'pending';

			echo "<tr class=\"$bg\"><td>".htmlspecialchars($user->getFullName())."$fac</td>";
			$count += $user->getNumCompletedByGroup($currentGroup->getID());
			echo "<td> {$user->getNumCompletedByGroup($currentGroup->getID())} / {$user->getNumRatingsByGroup($currentGroup->getID())}</td></tr>\n";
		}

		echo "<tr><th>TOTAL</th><td colspan=\"2\"> $count / $total </td></tr>";
		echo "</table>\n";
		$count = 0;
	}
}
?>
</div></body></html>

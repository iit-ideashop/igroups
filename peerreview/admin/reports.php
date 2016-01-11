<?php 
include_once('../classes/db.php');
include_once('../classes/person.php');
include_once('../classes/group.php');

include_once('checkadmin.php');

if(isset($_GET['selectGroup']))
	$currentGroup = new Group($_GET['groupID'], $db);

include_once('../includes/header.php');
?>
<title><?php echo $GLOBALS['systemname']; ?> - Reports</title>
<?php
include_once('../includes/sidebar.php');
?>
<div id="content">
<h2>Reports</h2>
<?php
$groups = array();

if($currentUser->isAdministrator())
	$query = $db->query('SELECT id FROM Groups ORDER BY name');
else
	$query = $db->query("SELECT g.id FROM Groups g, PeopleGroupMap m WHERE g.id = m.groupID AND m.userID={$currentUser->getID()} ORDER BY name");

while($row = mysql_fetch_row($query))
	$groups[] = new Group($row[0], $db);
echo "<hr$st>\n";
if(count($groups))
{
	echo "<form action=\"download.php\" method=\"get\"><fieldset><legend>Team Report</legend>\n";
	echo "<p>This report compares the mean scores for each rubric across all students in a given team. It also includes a detailed breakdown of each student's own reviews.</p>\n";
	echo "<select name=\"groupID\">\n";
	foreach($groups as $group)
		echo "\t<option value=\"{$group->getID()}\">".htmlspecialchars($group->getName())."</option>\n";
	echo "</select>\n";
	echo "<input type=\"submit\" name=\"downloadTeam\" value=\"Download Report\"$st> <input type=\"submit\" name=\"viewTeam\" value=\"Preview in Browser\"$st>\n";
	echo "</fieldset></form>\n";

	echo "<form action=\"reports.php\" method=\"get\"><fieldset><legend>Individual Reports</legend>\n";
	echo "<p>These reports are intended to be distributed to students and show a particular student's average rating for each rubric compared the group's average. Additional comments made by others are also provided here.</p>\n";
	echo "<select name=\"groupID\">\n";
	foreach($groups as $group)
		echo "\t<option value=\"{$group->getID()}\">".htmlspecialchars($group->getName())."</option>\n";
	echo "</select>\n";
	echo "<input type=\"submit\" name=\"selectGroup\" value=\"Select Group\"$st>\n";
	echo "</fieldset></form>\n";
	if(isset($currentGroup))
	{
		$users = $currentGroup->getGroupStudents();
		if(count($users))
		{
			echo "<form action=\"download.php\" method=\"get\"><fieldset>\n";
			echo "<select name=\"userID\">\n";
			foreach($users as $user) 
				echo "\t<option value=\"{$user->getID()}\">".htmlspecialchars($user->getFullName())."</option>\n";
			echo "</select>\n";
			echo "<input type=\"hidden\" name=\"group\" value=\"{$currentGroup->getID()}\"$st>\n";
			echo "<input type=\"submit\" name=\"downloadInd\" value=\"Download Report\"$st> <input type=\"submit\" name=\"viewInd\" value=\"Preview in Browser\"$st>\n";
			echo "</fieldset></form>\n";
		}
		else
			echo "<p>No users found.</p>\n";
	}
	
	echo "<form action=\"download.php\" method=\"get\"><fieldset><legend>Distribute Individual Reports</legend>\n";
	echo "<p>To release the individual reports to your team via e-mail, click the button below. (Note that this should only be done once, after the peer review has completed.)</p>\n";
	echo "<select name=\"groupID\">\n";
	foreach($groups as $group)
		echo "\t<option value=\"{$group->getID()}\">".htmlspecialchars($group->getName())."</option>\n";
	echo "</select>\n";
	echo "<input type=\"submit\" name=\"distribute\" value=\"Distribute Reports\"$st></fieldset></form>\n";

	echo "<form action=\"download.php\" method=\"get\"><fieldset><legend>Download Individual Reports (zipped)</legend>\n";
	echo "<p>This is the same as above, but all the individual reports are zipped into one file for your convenience.</p>\n";
	echo "<select name=\"group\">\n";
	foreach($groups as $group)
		echo "\t<option value=\"{$group->getID()}\">".htmlspecialchars($group->getName())."</option>\n";
	echo "</select>\n";
	echo "<input type=\"submit\" name=\"downloadIndZip\" value=\"Download Reports\"$st>\n";
	echo "</fieldset></form>\n";
}
else
	echo "<p>There are no groups to make reports for.</p>\n";
?>
</div></body></html>

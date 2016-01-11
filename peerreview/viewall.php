<?php 
include_once('classes/db.php');
include_once('classes/person.php');
include_once('classes/group.php');
include_once('classes/criterion.php');
include_once('classes/rating.php');
include_once('classes/customcriteria.php');
include_once('classes/customrating.php');

include_once('checklogin.php');

if(!isset($_GET['uID']) || ($currentUser->getID() != $_GET['uID']))
	errorPage('Not Authorized', 'You are not authorized to view this survey.', 403);
include_once('includes/header.php');
?>
<title><?php echo $GLOBALS['systemname']; ?> - View Responses</title>
<?php
include_once('includes/sidebar.php');
?>
<div id="content">
<h2>Viewing All Responses</h2>
<?php
echo "<hr$st>\n";
?>
<p>Use this information to compare your responses for all team members. To view the question description, hover your mouse pointer over its number in the table.</p>
<?php

$query = $db->query("select distinct groupID from Ratings where raterID=".$currentUser->getID()." and isComplete=1");
$groups = array();
while($result=mysql_fetch_row($query))
	$groups[] = new Group($result[0], $db);

foreach($groups as $currGroup)
{
	$query = $db->query("SELECT * FROM Ratings where raterID={$currentUser->getID()} AND isComplete=1 AND groupID=".$currGroup->getID());
	$ratings = array();
	while ($result = mysql_fetch_array($query))
		$ratings[] = new Rating($result['id'],$db);
	
	$query = $db->query("SELECT * FROM Criteria where listID=".$currGroup->getListID());
	$customCriteria = getCustomCriteria($currGroup->getID(), $db);
	$critlen = mysql_num_rows($query) + count($customCriteria);
	$currGroupName = htmlspecialchars($currGroup->getName());
	echo "<table width=\"97%\" class=\"centered\">\n";
	echo "<tr><th colspan=\"".($critlen+3)."\" style=\"text-align: left\">$currGroupName</th></tr>\n";
	echo "<tr><th rowspan=\"2\">Name</th><th colspan=\"$critlen\">Question Number</th><th rowspan=\"2\">Rank</th><th rowspan=\"2\">Overall</th></tr>\n";
	echo "<tr>";

	$i = 1;
	$g = $currGroup->getID();
	while($rubric = mysql_fetch_array($query))
	{
		$name = htmlspecialchars($rubric['name']);
		$desc = htmlspecialchars($rubric['description']);
		echo "<td class=\"questiontitle\"><a href=\"\" onmouseover=\"showEvent('G$g"."R$i',event.clientX+document.documentElement.scrollLeft, event.clientY+document.documentElement.scrollTop);\" onmouseout=\"hideEvent('G$g"."R$i');\">$i</a><div class=\"event\" id=\"G$g"."R$i\"><span class=\"question\">$name</span><br$st><span class=\"description\">$desc</span></div></td>";
		$i++;
	}
	$i = 1;
	foreach($customCriteria as $crit)
	{
		$name = htmlspecialchars($crit->getName());
		$desc = htmlspecialchars($crit->getDescription());
		echo "<td class=\"questiontitle\"><a href=\"\" onmouseover=\"showEvent('X$g"."R$i',event.clientX+document.documentElement.scrollLeft, event.clientY+document.documentElement.scrollTop);\" onmouseout=\"hideEvent('X$g"."R$i');\">X$i</a><div class=\"event\" id=\"X$g"."R$i\"><span class=\"question\">{$crit->getName()}</span><br$st><span class=\"description\">{$crit->getDescription()}</span></div></td>";
		$i++;
	}
	echo "</tr>\n";

	foreach($ratings as $rating)
	{
		$rtname = htmlspecialchars($rating->getRatedName());
		echo "<tr><td>$rtname</td>";
		for($j = 1 ; $j <= ($critlen-count($customCriteria)); $j++)
			echo "<td>{$rating->getQuestion($j)}</td>";
		foreach($customCriteria as $crit)
		{
			$rat = new CustomRating($rating->getRaterID(), $rating->getRatedID(), $crit->getID(), $db);
			echo "<td>{$rat->getRating()}</td>";
		}
		echo "<td>{$rating->getRank()}</td>";
		echo "<td class=\"total\">{$rating->getOverall()}</td></tr>\n";
	}

	echo "</table>\n";
}

echo "<p><a href=\"status.php\">Back</a></p>\n";
?>
</div></body></html>

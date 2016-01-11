<?php 
include_once('classes/db.php');
include_once('classes/person.php');
include_once('classes/group.php');
include_once('classes/criteriaList.php');
include_once('classes/criterion.php');
include_once('classes/rating.php');
include_once('classes/customcriteria.php');
include_once('classes/customrating.php');

include_once('checklogin.php');

if(isset($_GET['rID']) && is_numeric($_GET['rID']))
{
	$rating = new Rating($_GET['rID'], $db);
	if($rating->getID() < 0)
		errorPage('rID Not Found', 'The given rID was not found in the database', 400);
	$currentGroup = new Group($rating->getGroupID(), $db);
	$ratedUser = new Person($rating->getRatedID(), $db);
	$raterUser = new Person($rating->getRaterID(), $db);
}
else if(isset($_GET['rID']))
	errorPage('rID Not Numeric', 'The rID must be an integer', 400);
else
	errorPage('rID Not Set', 'The rID was not set.', 400);

if($raterUser->getID() != $currentUser->getID())
	errorPage('Not Authorized', 'You are not authorized to view this survey.', 403);
include_once('includes/header.php');
?>
<title><?php echo $GLOBALS['systemname']; ?> - View Survey</title>
<?php
include_once('includes/sidebar.php');
?>
<div id="content">
<h1>Viewing a Survey</h1>
<p>You are free to review your reponses to the surveys you already completed. However, you may not alter your responses after you have submitted.</p>
<?php

echo "<hr$st>\n<h2>Reviewing ".htmlspecialchars($ratedUser->getFullName())." in ".htmlspecialchars($currentGroup->getName())."</h2>\n";
echo "<br$st>\n";
echo "<table width=\"90%\" class=\"centered\"><tr><th colspan=\"6\">Section 1: Competency Rating</th></tr>\n";
echo "<tr><td colspan=\"6\" class=\"instructions\">How effective is the person at demonstrating each of the following behaviors? (Higher is better)</td></tr>\n";
echo "<tr><td></td><td>1</td><td>2</td><td>3</td><td>4</td><td>5</td></tr>\n";

$survey = new CriteriaList($currentGroup->getListID(), $db);
$criteria = $survey->getCriteria();
$customCriteria = $currentGroup->getCustomCriteria();
$ratings = $rating->getRating();

$i=0;
$check = "<img src=\"img/checkmark.gif\" alt=\"[X]\"$st>";
$nocheck = "<img src=\"img/nocheck.gif\" alt=\"[ ]\"$st>";
foreach($criteria as $criterion)
{
	$name = htmlspecialchars($criterion->getName());
	$desc = htmlspecialchars($criterion->getDescription());
	echo '<tr><td>'.($i+1).". <span class=\"question\">$name.</span><br$st><span class=\"description\">$desc</span></td>";
	echo '<td>'.($ratings[$i] == '1' ? $check : $nocheck).'</td>';
	echo '<td>'.($ratings[$i] == '2' ? $check : $nocheck).'</td>';
	echo '<td>'.($ratings[$i] == '3' ? $check : $nocheck).'</td>';
	echo '<td>'.($ratings[$i] == '4' ? $check : $nocheck).'</td>';
	echo '<td>'.($ratings[$i] == '5' ? $check : $nocheck)."</td></tr>\n";
	$i++;
}
$i=0;
foreach($customCriteria as $criterion)
{
	$name = htmlspecialchars($criterion->getName());
	$desc = htmlspecialchars($criterion->getDescription());
	$crating = new CustomRating($rating->getRaterID(), $rating->getRatedID(), $criterion->getID(), $db);
	echo '<tr><td>X'.($i+1).". <span class=\"question\">$name.</span><br$st><span class=\"description\">$desc</span></td>";
	echo '<td>'.($crating->getRating() == 1 ? $check : $nocheck).'</td>';
	echo '<td>'.($crating->getRating() == 2 ? $check : $nocheck).'</td>';
	echo '<td>'.($crating->getRating() == 3 ? $check : $nocheck).'</td>';
	echo '<td>'.($crating->getRating() == 4 ? $check : $nocheck).'</td>';
	echo '<td>'.($crating->getRating() == 5 ? $check : $nocheck)."</td></tr>\n";
	$i++;
}
echo "</table>\n<br$st>";
echo "<table width=\"70%\"><tr><th>Section 2: Written Comments</th></tr>\n";
echo "<tr><td><label>Additional comments, if any:<br$st><textarea name=\"comment\" cols=\"100\" rows=\"6\" $readonly>";
$query = $db->query("SELECT comment FROM Ratings WHERE id={$rating->getID()}");
$ans = mysql_fetch_row($query);
if($ans[0] != '')
	echo htmlspecialchars(stripslashes($ans[0]));
?>
</textarea></label></td></tr></table>
</div></body></html>

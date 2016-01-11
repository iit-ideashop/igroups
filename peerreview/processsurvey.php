<?php 
include_once('classes/db.php');
include_once('classes/person.php');
include_once('classes/group.php');
include_once('classes/criteriaList.php');
include_once('classes/customcriteria.php');
include_once('classes/customrating.php');
include_once('classes/rating.php');

include_once('checklogin.php');

if(isset($_POST['rID']))
{
	$rating = new Rating($_POST['rID'], $db);
	$currentGroup = new Group($rating->getGroupID(), $db);
	$ratedUser = new Person($rating->getRatedID(), $db);
}
else
	errorPage('rID Not Set', 'Submit was unsuccessful: rID not set', 400);

$listID = $rating->getListID();
$survey = new CriteriaList($listID, $db);

$questions = $survey->getCriteria();
$ratings = '';
for($i=1; $i <= count($questions); $i++)
	$ratings .= isset($_POST["q$i"]) ? $_POST["q$i"][0] : '!';

$rater = $rating->getRaterID();
$rated = $rating->getRatedID();

$customCriteria = $currentGroup->getCustomCriteria();
foreach($customCriteria as $criterion)
{
	if(isset($_POST["c{$criterion->getID()}"]))
	{
		$crating = new CustomRating($rater, $rated, $criterion->getID(), $db);
		$crating->setRating($_POST["c{$criterion->getID()}"]);
	}
}

$db->query("update Ratings set rank={$_POST['rank']}, rating='$ratings', comment='".mysql_real_escape_string(stripslashes($_POST['comment']))."' where id={$rating->getID()}");
include_once('includes/header.php');

if(substr_count($ratings, '!') == 0)
{
	$db->query("update Ratings set isComplete=1 where id={$rating->getID()}");
?>
<title><?php echo $GLOBALS['systemname']; ?> - Submitted</title>
<meta http-equiv="Refresh" content="3; URL=status.php"<?php echo $st;?>>
<?php
include_once('includes/sidebar.php');
?>
<div id="content">
<h2>Survey Submitted</h2>
<p>Your survey was submitted successfully. Click "My Status" on the left menu to take more surveys. You will be taken there automatically in 3 seconds.</p>
<?php
}
else
{
	echo "<title>{$GLOBALS['systemname']} - Error</title>\n";
	include_once('includes/sidebar.php');
	echo "<div id=\"content\">\n<h1>Error</h1>\n<p>We could not process your survey because it contained the following error(s):</p>\n";
	echo "<ul>\n";
	for($i=0; $i < strlen($ratings); $i++)
	{
		if($ratings[$i] == '!')
			echo "<li>Criterion ".($i+1)." was left blank</li>\n";
	}
	echo "</ul>\n";
	echo "<p>Please click the Back button on your browser, correct these errors and resubmit.</p>\n";
}
?>
</div></body></html>

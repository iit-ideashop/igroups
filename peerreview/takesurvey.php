<?php 
include_once('classes/db.php');
include_once('classes/person.php');
include_once('classes/group.php');
include_once('classes/criteriaList.php');
include_once('classes/criterion.php');
include_once('classes/customcriteria.php');
include_once('classes/customrating.php');
include_once('classes/rating.php');

include_once('checklogin.php');

if(isset($_GET['rID']) && is_numeric($_GET['rID']))
{
	$rating = new Rating($_GET['rID'], $db);
	if($rating->getID() < 0)
		errorPage('rID Not Found', 'The given rID was not found in the database', 400);
	$currentGroup = new Group($rating->getGroupID(), $db);
	$ratedUser = new Person($rating->getRatedID(), $db);
	$raterUser = new Person($rating->getRaterID(), $db);
	$members = $currentGroup->getGroupMembers();
	$numMembers = 0;
	foreach($members as $member)
	{
		if(!$member->isFaculty())
			$numMembers++;
	}
}
else if(isset($_GET['rID']))
	errorPage('rID Not Numeric', 'The rID must be an integer', 400);
else
	errorPage('rID Not Set', 'The rID was not set.', 400);
if($raterUser->getID() != $currentUser->getID())
	errorPage('Not Authorized', 'You are not authorized to take this survey.', 403);

include_once('includes/header.php');
$onload = 'checkAnswers()';
?>
<title><?php echo $GLOBALS['systemname']; ?> - Take Survey</title>
<script type="text/javascript">
//<![CDATA[
function checkAnswers()
{
	var tot = 0, checked = 0;
	var form = document.forms['answers'];
	var prevElemName = '';
	for(var i = 0; i < form.elements.length; ++i)
	{
		var elem = form.elements[i];
		if(elem.type == 'radio')
		{
			if(elem.name != prevElemName)
				++tot;
			if(elem.checked)
				++checked;
		}
		prevElemName = elem.name;
	}
	form.elements['submitSurvey'].disabled = (checked != tot);
	document.getElementById('numanswered').innerHTML = checked;
	document.getElementById('numtotal').innerHTML = tot;
}
//]]>
</script>
<?php
include_once('includes/sidebar.php');
?>
<div id="content">
<h1>Completing a Survey</h1>
<p>The Peer Review surveys include two sections: Competency Rating and Written Comments (optional). For Section 1: Competency Rating, you are answering the question, "How effective is the person at demonstrating the following behavior?" Select one of five possible ratings for each statement. When you are done, click the 'Submit Survey' button at the bottom of the page. All questions must be answered for the survey to be considered complete.</p>
<?php
echo "<hr$st>\n";
$ratedname = htmlspecialchars($ratedUser->getFullName());
$currGroupName = htmlspecialchars($currentGroup->getName());
echo "<form action=\"processsurvey.php\" method=\"post\" id=\"answers\"><fieldset><legend>Reviewing $ratedName in $currGroupName</legend>\n";
echo "<table class=\"centered\"><tr><th colspan=\"6\">Section 1: Competency Rating</th></tr>\n";
echo "<tr><td colspan=\"6\" class=\"instructions\">How effective is the person at demonstrating each of the following behaviors?<br$st><small>(1 means 'not effective', 5 means 'most effective')</small></td></tr>\n";

//Questions
$groupID = $currentGroup->getID();
$group = new Group($groupID, $db);
$listID = $group->getListID();

$survey = new CriteriaList($listID, $db);

$questions = $survey->getCriteria();
$i=1;
foreach($questions as $question)
{
	$name = htmlspecialchars($question->getName());
	$desc = htmlspecialchars($question->getDescription());
	echo "<tr><td><span class=\"question\">$name</span><br$st><span class=\"description\">$desc</span></td><td><label>1<br$st><input type=\"radio\" name=\"q$i\" value=\"1\" onchange=\"checkAnswers()\"$st></label></td><td><label>2<br$st><input type=\"radio\" name=\"q$i\" value=\"2\" onchange=\"checkAnswers()\"$st></label></td><td><label>3<br$st><input type=\"radio\" name=\"q$i\" value=\"3\" onchange=\"checkAnswers()\"$st></label></td><td><label>4<br$st><input type=\"radio\" name=\"q$i\" value=\"4\" onchange=\"checkAnswers()\"$st></label></td><td><label>5<br$st><input type=\"radio\" name=\"q$i\" value=\"5\" onchange=\"checkAnswers()\"$st></label></td></tr>\n";
	$i++;
}
$questions = $group->getCustomCriteria();
foreach($questions as $question)
{
	$i = $question->getID();
	$name = htmlspecialchars($question->getName());
	$desc = htmlspecialchars($question->getDescription());
	$sel = array(1 => '', 2 => '', 3 => '', 4 => '', 5 => '');
	echo "<tr><td><span class=\"question\">$name</span><br$st><span class=\"description\">$desc</span></td><td><label>1<br$st><input type=\"radio\" name=\"c$i\" value=\"1\" onchange=\"checkAnswers()\"$st></label></td><td><label>2<br$st><input type=\"radio\" name=\"c$i\" value=\"2\" onchange=\"checkAnswers()\"$st></label></td><td><label>3<br$st><input type=\"radio\" name=\"c$i\" value=\"3\" onchange=\"checkAnswers()\"$st></label></td><td><label>4<br$st><input type=\"radio\" name=\"c$i\" value=\"4\" onchange=\"checkAnswers()\"$st></label></td><td><label>5<br$st><input type=\"radio\" name=\"c$i\" value=\"5\" onchange=\"checkAnswers()\"$st></label></td></tr>\n";
}

//Ranking
$query = $db->query("SELECT rank FROM Ratings WHERE id={$rating->getID()}");
$rank = mysql_fetch_row($query);
echo "<tr><td><b>Overall Ranking in Team</b><br$st>Rank this person from 1 to $numMembers, with '1' being high and most important, to $numMembers being low, ties not allowed.</td>";
echo "<td colspan=\"5\"><select name=\"rank\">";
for($i = 1; $i<=$numMembers; $i++)
{
	if($rank[0] == $i)
		echo "<option value=\"$i\" $selected>$i</option>\n";
	else
		echo "<option value=\"$i\">$i</option>\n";
}
echo "</select></td></tr>\n";
echo "</table>\n<br$st>\n";
echo "<table width=\"70%\" class=\"centered\"><tr><th>Section 2: Written Comments</th></tr>\n";
echo "<tr><td><b>Instructions:</b> Use this section to provide additional comments or insights that may help this person understand how to effectively use his or her strengths and to address specific development needs. Your written comments will be presented anonymously as part of the individual's report. <br$st><br$st>Guidelines for completing this section:<ul><li>Do not enter any names</li><li>Be specific in your feedback, reference actual behavior when appropriate</li><li>Offer contructive feedback by sharing information that the person would profit from knowing (i.e., things that would enhance his/her leadership effectiveness)</li></ul></td></tr>\n";
echo "<tr><td><label>Additional comments, if any:<br$st><textarea name=\"comment\" cols=\"75\" rows=\"6\">";
$query = $db->query("SELECT comment FROM Ratings WHERE id={$rating->getID()}");
$ans = mysql_fetch_row($query);
if($ans[0] != '')
	echo $ans[0];
?>
</textarea></label></td></tr>
</table>
<input type="hidden" name="rID" value="<?php echo $rating->getID()."\"$st"; ?>>
<br<?php echo $st; ?>><table width="70%" class="centered"><tr><th>Section 3: Submission</th></tr>
<tr><td class="instructions"><p>Please review your answers and ensure that they you are satisfied with them. All questions must be answered. You may not go back to change your survey after you have submitted it.</p><p>When you are ready to submit your survey, please click the "Submit Survey" button below.</p></td></tr>
<tr><td><input type="submit" name="submitSurvey" id="submitSurvey" value="Submit Survey"<?php echo $st;?>> <span id="uncompleted">(<span id="numanswered">0</span>/<span id="numtotal">0</span> questions answered)</span></td></tr></table>
</fieldset></form>
</div></body></html>

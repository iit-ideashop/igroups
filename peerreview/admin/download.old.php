<?php 
session_start();

include_once( "../classes/db.php" );
include_once( "../classes/person.php" );
include_once( "../classes/archived_group.php" );
include_once( "../classes/rating.php" );
include_once( "../classes/survey.php" );
include_once( "../classes/question.php" );

$db = new dbConnection();

if ( isset( $_SESSION['uID'] ) )
        $currentUser = new Person( $_SESSION['uID'], $db );
else
        die("You are not logged in.");

if ( !$currentUser->isAdministrator() )
        die("You must be an administrator to access this page.");

if (isset($_GET['download'])) {
$group = new Group($_GET['groupID'], $db);
$survey = new Survey($db);
$questions = $survey->getQuestions();
$members = $group->getGroupMembers();

function cmpOverall($a, $b) {
	$db = new dbConnection();
	$group = new Group($_GET['groupID'], $db);
        if ($a->getOverall($group->getID()) >= $b->getOverall($group->getID()))
                return -1;
        else
                return 1;
}

$file = fopen('temp/temp.csv', 'w');

/* OLD FORMAT
fwrite($file, "{$group->getName()} - Team Overall\n");
fwrite($file, "Rater,Attends Meetings,Seeks Alternatives,Takes Responsibility,Shares Responsibility,Recognizes Others,Teamwork Average\n");
foreach ($members as $member) {
	fwrite($file, "{$member->getFullName()},{$member->getAvgByQ('q1', $group->getID())},{$member->getAvgByQ('q2', $group->getID())},{$member->getAvgByQ('q3', $group->getID())},{$member->getAvgByQ('q4', $group->getID())},{$member->getAvgByQ('q5', $group->getID())},{$member->getCatAvg(1, $group->getID())}\n");
}
fwrite($file, "\nRater,Defines Tasks,Sets Realistic Goals,Assigns Tasks,Creates and Monitors Milestones,Project Management Average\n");
foreach ($members as $member) {
	fwrite($file, "{$member->getFullName()},{$member->getAvgByQ('q6', $group->getID())},{$member->getAvgByQ('q7', $group->getID())},{$member->getAvgByQ('q8', $group->getID())},{$member->getAvgByQ('q9', $group->getID())},{$member->getCatAvg(2, $group->getID())}\n");
}
fwrite($file, "\nRater,Communicates Clearly,Listens to Others,Resolves Conflicts,Maintains Confidences,Rejects Bias in Decisions and Work,Completes Work Assignments on Time,Communications and Ethics Average\n");
foreach ($members as $member) {
	fwrite($file, "{$member->getFullName()},{$member->getAvgByQ('q10', $group->getID())},{$member->getAvgByQ('q11', $group->getID())},{$member->getAvgByQ('q12', $group->getID())},{$member->getAvgByQ('q13', $group->getID())},{$member->getAvgByQ('q14', $group->getID())},{$member->getAvgByQ('q15', $group->getID())},{$member->getCatAvg(3, $group->getID())}\n");
}
fwrite($file, "\nRater,Inspires a Vision,Makes Commitment,Establishes Rapport,Seeks Input,Influences Others,Leadership Effectiveness Average\n");
foreach ($members as $member) {
	fwrite($file, "{$member->getFullName()},{$member->getAvgByQ('q16', $group->getID())},{$member->getAvgByQ('q17', $group->getID())},{$member->getAvgByQ('q18', $group->getID())},{$member->getAvgByQ('q19', $group->getID())},{$member->getAvgByQ('q20', $group->getID())},{$member->getCatAvg(4, $group->getID())}\n");
}
fwrite($file, "\nRater,Overall,Rank\n");
usort($members, 'cmpOverall');
$i=1;
foreach ($members as $member) {
	fwrite($file, "{$member->getFullName()},{$member->getOverall($group->getID())},$i\n");
	$i++;
}
*/

fwrite($file, "{$group->getName()} Peer Review - Team Overall\n");
usort ($members, 'cmpOverall');

$str = "";
foreach($members as $member) 
	$str = $str . "{$member->getLastName()},";

fwrite($file, ",$str\n");
$i = 1;

foreach($questions as $question) {

	$str = "";
	foreach($members as $member)
		$str = $str . "{$member->getAvgByQ("q$i", $group->getID())},";
	
	if ($i == 1)
                fwrite($file, "\nDemonstrates Teamwork Skills [Rate each item from 1-5: 5=high; 1=low]\n");
        if ($i == 6)
                fwrite($file, "\nDemonstrates Project Management Skills [Rate each item from 1-5: 5=high; 1=low]\n");
        if ($i == 10)
                fwrite($file, "\nExhibits Communication Skills and Ethical Behavior [Rate each item from 1-5: 5=high; 1=low]\n");
        if ($i == 16)
                fwrite($file, "\nDemonstrates Leadership Effectiveness [Rate each item from 1-5: 5=high; 1=low]\n");

	fwrite($file, "$i. {$question->getName()},$str\n");	
	$i++;
}

$str = "";
foreach($members as $member)
	$str = $str . "{$member->getOverall($group->getID())},";
fwrite($file, "Total,$str\nRank,");

for ($i = 1; $i <= count($members); $i++)
	fwrite ($file, "$i,");	

fwrite($file, "\n\n{$group->getName()} - Individual Reports\n");

foreach ($members as $member) {

	fwrite($file, "{$member->getFullName()}\n");
	fwrite($file, "Individual Report,Your Avg Score,Group Avg,Group High,Group Low\n");
	$i = 1;
	foreach($questions as $question) {

        $str = "";
	$str = $str . "{$member->getAvgByQ("q$i", $group->getID())},{$group->getGroupAvgByQ("q$i")},{$group->getGroupMaxByQ("q$i")},{$group->getGroupMinByQ("q$i")}";

        if ($i == 1)
                fwrite($file, "\nDemonstrates Teamwork Skills [Rate each item from 1-5: 5=high; 1=low]\n");
        if ($i == 6)
                fwrite($file, "\nDemonstrates Project Management Skills [Rate each item from 1-5: 5=high; 1=low]\n");
        if ($i == 10)
                fwrite($file, "\nExhibits Communication Skills and Ethical Behavior [Rate each item from 1-5: 5=high; 1=low]\n");
        if ($i == 16)
                fwrite($file, "\nDemonstrates Leadership Effectiveness [Rate each item from 1-5: 5=high; 1=low]\n");

        fwrite($file, "$i. {$question->getName()},$str\n");
        $i++;
	}
	
	$sum = 0;
	$max = 0;
	$min = 100;

	foreach ($members as $member2) {
		$sum = $sum + $member2->getOverall($group->getID());
		if ($member2->getOverall($group->getID()) > $max)
			$max = $member2->getOverall($group->getID());
		if ($member2->getOverall($group->getID()) < $min)
			$min = $member2->getOverall($group->getID());		
	}
	$avg = $sum / count($members);

	fwrite($file, "Overall,{$member->getOverall($group->getID())},$avg,$max,$min\n\n");

	$ratings = $member->getRatings();

	fwrite($file, "Comments\n");
	foreach ($ratings as $rating) {
		if ($rating->getQuestion(21) != null)
			fwrite($file, "{$rating->getQuestion(21)}\n");
	}
	fwrite($file, "\n");
}

fclose($file);

$filename = str_replace(' ', '_', $group->getName()) . 'PeerReview.csv';
header("Content-Type: application/excel");
header("Content-Length: " . filesize('temp/temp.csv'));
header("Content-Disposition: attachment; filename=$filename");
header("Cache-Control: must-revalidate,post-check=0,pre-check=0");
header("Cache-Control: public",false);
header("Pragma: public");
header("Expires: 0");

readfile('temp/temp.csv');
}

?>

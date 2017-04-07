<?php 
include_once('../classes/db.php');
include_once('../classes/archived_person.php');
include_once('../classes/archived_group.php');
include_once('../classes/rating.php');
include_once('../classes/criterion.php');
include_once('../classes/criteriaList.php');
include_once('../classes/customcriteria.php');
include_once('../classes/customrating.php');
include_once('../classes/zipfile.php');

include_once('../checklogin.php');

if(isset($_GET['downloadTeam']))
{
	$group = new ArGroup($_GET['group'], $db);
	$survey = $group->getList();
	$numCriteria = $survey->getNumCriteria();
	$questions = $survey->getCriteria();
	$members = $group->getGroupStudents();
	$customCriteria = $group->getCustomCriteria();
	
	function cmpOverall($a, $b)
	{
		global $db;
		$group = new ArGroup($_GET['group'], $db);
		if ($a->getOverall($group->getID()) >= $b->getOverall($group->getID()))
			return -1;
		else
			return 1;
	}

	$file = fopen('temp/temp.csv', 'w');

	fwrite($file, "{$group->getName()} Peer Review - Team Overall\n");
	usort($members, 'cmpOverall');

	$str = '';
	foreach($members as $member) 
		$str .= "{$member->getLastName()},";

	fwrite($file, ",$str\n");
	$i = 1;

	foreach($questions as $question)
	{
		$str = '';
		foreach($members as $member)
		{
			$val = round($member->getAvgByQ("$i", $group->getID()),1);
			$str .= "$val,";
		}

		fwrite($file, "$i. {$question->getName()},$str\n");	
		$i++;
	}
	
	$i = 1;
	foreach($customCriteria as $question)
	{
		$str = '';
		foreach($members as $member)
		{
			$val = round($member->getAvgByCCID($question->getID()),1);
			$str .= "$val,";
		}

		fwrite($file, "X$i. {$question->getName()},$str\n");	
		$i++;
	}

	$str = '';
	foreach($members as $member)
	{
		$val = round($member->getOverall($group->getID()),1);
		$str .= "$val,";
	}
	fwrite($file, "Total,$str\n");
	fwrite($file, 'Avg Rank by Peers,');
	foreach($members as $member)
	{
		$avgRank = $member->getAvgRank($group->getID());
		fwrite($file, "$avgRank,");
	}

	fwrite($file, "\nScorewise Rank,");

	for ($i = 1; $i <= count($members); $i++)
		fwrite($file, "$i,");	

	fwrite($file, "\n\nDetailed Individual Reporting\n");

	foreach($members as $member)
	{
		fwrite($file, "\nRater: {$member->getFullName()}\n");
		$i = 1;
		$str = '';
		foreach($questions as $question)
		{
			$str .= ",R$i";
			$i++;
		}
		$i = 1;
		foreach($customCriteria as $question)
		{
			$str .= ",X$i";
			$i++;
		}
		fwrite($file, "Name$str,Rank,OVR\n");
		$query = $db->query("SELECT * FROM Ratings where raterID={$member->getID()} AND isComplete=1 AND groupID={$group->getID()}");
		$ratings = array();

		while($result = mysql_fetch_array($query))
			$ratings[] = new Rating($result['id'],$db);

		foreach($ratings as $rating)
		{
			fwrite($file, "{$rating->getRatedName()},");
			for($i = 1; $i <= $numCriteria; $i++) 
				fwrite($file, "{$rating->getQuestion($i)},");
			foreach($customCriteria as $question)
			{
				$customRating = new CustomRating($rating->getRaterID(), $rating->getRatedID(), $question->getID(), $db);
				fwrite($file, "{$customRating->getRating()},");
			}
			fwrite($file, "{$rating->getRank()},");
			fwrite($file, "{$rating->getOverall()}\n");
		}
	}

	fclose($file);

	$filename = str_replace(' ', '_', $group->getName()) . '-PeerReview.csv';
	header('Content-Type: text/csv');
	header('Content-Length: '.filesize('temp/temp.csv'));
	header("Content-Disposition: attachment; filename=$filename");
	header('Cache-Control: must-revalidate,post-check=0,pre-check=0');
	header('Cache-Control: public', false);
	header('Pragma: public');
	header('Expires: 0');

	readfile('temp/temp.csv');
	@unlink('temp/temp.csv');
	die();
}
else if(isset($_GET['downloadInd']))
{
	$group = new ArGroup($_GET['group'], $db);
	$survey = $group->getList();
	$questions = $survey->getCriteria();
	$numCriteria = $survey->getNumCriteria();
	$members = $group->getGroupStudents();
	$customCriteria = $group->getCustomCriteria();

	function cmpOverall($a, $b)
	{
		global $db;
		$group = new ArGroup($_GET['group'], $db);
		if($a->getOverall($group->getID()) >= $b->getOverall($group->getID()))
			return -1;
		else
			return 1;
	}

	$file = fopen('temp/temp.csv', 'w');
	$member = new ArPerson($_GET['userID'], $db);

	fwrite($file, "{$member->getFullName()} - {$group->getName()}\n");
	fwrite($file, "Individual Report,Your Avg Score,Group Avg,Group High,Group Low\n");
	$i = 1;
	foreach($questions as $question)
	{
		$str = "{$member->getAvgByQ("$i", $group->getID())},{$group->getGroupAvgByQ("$i")},{$group->getGroupMaxByQ("$i")},{$group->getGroupMinByQ("$i")}";
		fwrite($file, "$i. {$question->getName()},$str\n");
		$i++;
	}
	
	$i = 1;
	foreach($customCriteria as $question)
	{
		$str = "{$member->getAvgByCCID($question->getID(), $group->getID())},{$group->getGroupAvgByCCID($question->getID())},{$group->getGroupMaxByCCID($question->getID())},{$group->getGroupMinByCCID($question->getID())}";
		fwrite($file, "X$i. {$question->getName()},$str\n");
		$i++;
	}

	$sum = 0;
	$max = 0;
	$min = 100;

	foreach($members as $member2)
	{
		$sum += $member2->getOverall($group->getID());
		if ($member2->getOverall($group->getID()) > $max)
			$max = $member2->getOverall($group->getID());
		if ($member2->getOverall($group->getID()) < $min)
			$min = $member2->getOverall($group->getID());		
	}
	$avg = round($sum / count($members),1);
	fwrite($file, "Avg Rank by Peers,{$member->getAvgRank($group->getID())}\n");
	fwrite($file, "Overall,{$member->getOverall($group->getID())},$avg,$max,$min\n\n");

	$ratings = $member->getRatingsByGroup($group->getID());

	fwrite($file, "Comments\n");
	foreach($ratings as $rating)
	{
		if($rating->getComment() != null)
		{
			$comment = stripslashes(str_replace(',', '', $rating->getComment()));
			fwrite($file, "{$comment}\n");
		}
	}
	fwrite($file, "\n");
	fclose($file);

	$filename = str_replace(' ', '_', $group->getName()) . '_' . str_replace(' ', '_', $member->getLastName()) . '-PeerReview.csv';
	header('Content-Type: text/csv');
	header('Content-Length: '.filesize('temp/temp.csv'));
	header("Content-Disposition: attachment; filename=$filename");
	header('Cache-Control: must-revalidate,post-check=0,pre-check=0');
	header('Cache-Control: public', false);
	header('Pragma: public');
	header('Expires: 0');

	readfile('temp/temp.csv');
	@unlink('temp/temp.csv');
	die();
}
else if(isset($_GET['downloadIndZip']))
{
	$group = new ArGroup($_GET['group'], $db);
	$survey = $group->getList();
	$questions = $survey->getCriteria();
	$numCriteria = $survey->getNumCriteria();
	$members = $group->getGroupStudents();
	$customCriteria = $group->getCustomCriteria();
	function cmpOverall($a, $b)
	{
		global $db;
		$group = new ArGroup($_GET['group'], $db);
		if ($a->getOverall($group->getID()) >= $b->getOverall($group->getID()))
			return -1;
		else
			return 1;
	}
	
	$zip = new createZip();
	$files = array();
	
	foreach($members as $member)
	{
		$filename = str_replace(' ', '_', $group->getName()) . '_' . str_replace(' ', '_', $member->getLastName()) . '-PeerReview.csv';
		$file = fopen("temp/$filename", 'w');
		$files[] = "temp/$filename";
		fwrite($file, "{$member->getFullName()} - {$group->getName()}\n");
		fwrite($file, "Individual Report,Your Avg Score,Group Avg,Group High,Group Low\n");
		
		$i = 1;
	
		foreach($questions as $question)
		{
			$str = "{$member->getAvgByQ($i, $group->getID())},{$group->getGroupAvgByQ($i)},{$group->getGroupMaxByQ($i)},{$group->getGroupMinByQ($i)}";
			fwrite($file, "$i. {$question->getName()},$str\n");
			$i++;
		}

		$i = 1;
		foreach($customCriteria as $question)
		{
			$str = "{$member->getAvgByCCID($question->getID(), $group->getID())},{$group->getGroupAvgByCCID($question->getID())},{$group->getGroupMaxByCCID($question->getID())},{$group->getGroupMinByCCID($question->getID())}";
			fwrite($file, "X$i. {$question->getName()},$str\n");
			$i++;
		}
		$sum = 0;
		$max = 0;
		$min = 100;

		foreach($members as $member2)
		{
			$sum += $member2->getOverall($group->getID());
			if($member2->getOverall($group->getID()) > $max)
				$max = $member2->getOverall($group->getID());
			if($member2->getOverall($group->getID()) < $min)
				$min = $member2->getOverall($group->getID());
		}
		$avg = round($sum / count($members),1);

		fwrite($file, "Avg Rank by Peers,{$member->getAvgRank($group->getID())}\n");
		fwrite($file, "Overall,{$member->getOverall($group->getID())},$avg,$max,$min\n\n");

		$ratings = $member->getRatingsByGroup($group->getID());

		fwrite($file, "Comments\n");
		foreach($ratings as $rating)
		{
			if($rating->getComment() != null)
			{
				$comment = str_replace(',', '', $rating->getComment());
				fwrite($file, "{$comment}\n");
			}
		}
		fwrite($file, "\n");
		fclose($file);
	
		$fileContents = file_get_contents("temp/$filename");  
		$zip->addFile($fileContents, $filename);
	}

	$filename = str_replace(' ', '_', $group->getName()) . '_' . 'Indiv_Reports-PeerReview.zip';

	$fd = fopen ("temp/$filename", 'wb');
	$out = fwrite ($fd, $zip -> getZippedfile());
	fclose ($fd);

	$zip->forceDownload("temp/$filename");
	@unlink("temp/$filename");

	foreach($files as $file)
		@unlink($file);
	die();
}

if(isset($_GET['downloadArchiveByGroup']) && isset($_GET['group']))
{
	$group = new ArGroup($_GET['group'], $db);
	$list = $group->getList();
	$questions = $list->getCriteria();
	$numCriteria = $list->getNumCriteria();
	$file = fopen('temp/temp.csv', 'w');
	$i = 1;

	fwrite($file, 'Team,Running #,Rater,Ratee');
	foreach($questions as $question)
	{
		fwrite($file, ",R$i");
		$i++;
	}
	fwrite($file, ",Rank,Comments\n");

	$query = $db->query("select * from RatingsArchive r join People p ON(p.id=r.raterID) where groupID={$group->getID()} order by runningID");
	while($res = mysql_fetch_array($query))
	{
		fwrite($file, "{$res['groupName']},{$res['runningID']},{$res['raterID']},{$res['ratedID']}");
		for($i = 0; $i<$numCriteria; $i++)
		{
			$num = substr($res['rating'], $i, 1);
			fwrite($file, ",$num");
		}
		fwrite($file, ",{$res['rank']},{$res['comment']}\n");
	}

	fclose($file);

	$name = str_replace(' ', '_', $group->getName());

	$filename = "PeerReview-{$name}-ArchivalData.csv";
	header('Content-Type: text/csv');
	header('Content-Length: '.filesize('temp/temp.csv'));
	header("Content-Disposition: attachment; filename=$filename");
	header('Cache-Control: must-revalidate,post-check=0,pre-check=0');
	header('Cache-Control: public', false);
	header('Pragma: public');
	header('Expires: 0');

	readfile('temp/temp.csv');
	@unlink('temp/temp.csv');
	die();
}

include_once('../includes/header.php');
echo "<title>{$GLOBALS['systemname']} - View Report</title>\n";
include_once('../includes/sidebar.php');
echo "<div id=\"content\">\n";
if(isset($_GET['viewTeam']))
{
	$group = new ArGroup($_GET['group'], $db);
	$survey = $group->getList();;
	$questions = $survey->getCriteria();
	$numCriteria = $survey->getNumCriteria();
	$members = $group->getGroupStudents();
	$customCriteria = $group->getCustomCriteria();

	function cmpOverall($a, $b)
	{
		global $db;
		$group = new ArGroup($_GET['group'], $db);
		if ($a->getOverall($group->getID()) >= $b->getOverall($group->getID()))
			return -1;
		else
			return 1;
	}
	echo "<h3>".htmlspecialchars($group->getName())." Peer Review - Overall Team Report</h3>\n";
	$cols = count($members) + 1;
	echo "<table width=\"100%\" style=\"border: thin solid black\">\n";

	usort($members, 'cmpOverall');
	$str = '';
	foreach($members as $member)
		$str .= "<td><b>".htmlspecialchars($member->getLastName())."</b></td>";

	echo "<tr><td>&#160;</td>$str</tr>\n";
	$i = 1;

	foreach($questions as $question)
	{
		$qname = htmlspecialchars($question->getName());
		$qdesc = htmlspecialchars($question->getDescription());
		$str = '';
		foreach($members as $member)
		{
			$val = round($member->getAvgByQ("$i", $group->getID()),1);
			$str .="<td>$val</td>";
		}

		echo "<tr><td>$i. <b>$qname</b><br$st>$qdesc</td>$str</tr>\n";
		$i++;
	}
	
	$i = 1;
	foreach($customCriteria as $question)
	{
		$str = '';
		$qname = htmlspecialchars($question->getName());
		$qdesc = htmlspecialchars($question->getDescription());
		foreach($members as $member)
		{
			$val = round($member->getAvgByCCID($question->getID()),1);
			$str .= "<td>$val</td>";
		}

		echo "<tr><td>X$i. <b>$qname</b><br$st>$qdesc</td>$str</tr>\n";
		$i++;
	}

	$str = '';
	foreach($members as $member)
	{
		$val = round($member->getOverall($group->getID()),1);
		$str .= "<td>$val</td>";
	}
	echo "<tr><td>Total</td>$str</tr>\n";
	echo "<tr><td>Avg Rank by Peers</td>";
	foreach($members as $member)
	{
		$avgRank = $member->getAvgRank($group->getID());
		echo "<td>$avgRank</td>";
	}

	echo "</tr>\n<tr><td>Scorewise Rank</td>";

	for($i = 1; $i <= count($members); $i++)
		echo "<td>$i</td>";

	echo "</tr></table>\n<h3>Detailed Individual Reporting</h3>\n";

	foreach($members as $member)
	{
		echo "<table width=\"100%\">\n";
		$tot = count($questions) + count($customCriteria) + 3;
		echo "<tr><th colspan=\"$tot\">Rater: ".htmlspecialchars($member->getFullName())."</th></tr>\n";
		echo "<tr><td>Name</td>";
		$i = 1;
		foreach ($questions as $question)
		{
			echo "<td>R$i</td>";
			$i++;
		}
		$i = 1;
		foreach($customCriteria as $question)
		{
			echo "<td>X$i</td>";
			$i++;
		}
		echo "<td>Rank</td><td>OVR</td></tr>\n";
		$query = $db->query("select * from Ratings where raterID={$member->getID()} and isComplete=1 and groupID={$group->getID()}");
		$ratings = array();

		while ($result = mysql_fetch_array($query))
			$ratings[] = new Rating($result['id'],$db);

		foreach($ratings as $rating)
		{
			echo "<tr><td>".htmlspecialchars($rating->getRatedName())."</td>";
			for ($i=1; $i<=$numCriteria; $i++)
				echo "<td>{$rating->getQuestion($i)}</td>";
			foreach($customCriteria as $question)
			{
				$customRating = new CustomRating($rating->getRaterID(), $rating->getRatedID(), $question->getID(), $db);
				echo "<td>{$customRating->getRating()}</td>";
			}
			echo "<td>{$rating->getRank()}</td>";
			echo "<td>{$rating->getOverall()}</td></tr>\n";
		}
		echo "</table>\n";
	}
}
else if(isset($_GET['viewInd']))
{
	$group = new ArGroup($_GET['group'], $db);
	$survey = $group->getList();
	$questions = $survey->getCriteria();
	$numCriteria = $survey->getNumCriteria();
	$members = $group->getGroupStudents();
	$customCriteria = $group->getCustomCriteria();

	function cmpOverall($a, $b)
	{
		global $db;
		$group = new ArGroup($_GET['group'], $db);
		if ($a->getOverall($group->getID()) >= $b->getOverall($group->getID()))
			return -1;
		else
			return 1;
	}

	$member = new ArPerson($_GET['userID'], $db);
	$mname = htmlspecialchars($member->getFullName());
	$gname = htmlspecialchars($group->getName());
	echo "<h2>$mname - $gname</h2>\n";
	echo "<table width=\"100%\">\n";
	echo "<tr><td>Individual Report</td><td>Your Avg Score</td><td>Group Avg</td><td>Group High</td><td>Group Low</td></tr>\n";
	$i = 1;
	foreach($questions as $question)
	{
		$qname = htmlspecialchars($question->getName());
		$qdesc = htmlspecialchars($question->getDescription());
		$str = "<td>{$member->getAvgByQ($i, $group->getID())}</td><td>{$group->getGroupAvgByQ($i)}</td><td>{$group->getGroupMaxByQ($i)}</td><td>{$group->getGroupMinByQ($i)}</td>";
		echo "<tr><td>$i. <b>$qname</b><br$st>$qdesc</td>$str</tr>\n";
		$i++;
	}
	
	$i = 1;
	foreach($customCriteria as $question)
	{
		$qname = htmlspecialchars($question->getName());
		$qdesc = htmlspecialchars($question->getDescription());
		$str = "<td>{$member->getAvgByCCID($question->getID(), $group->getID())}</td><td>{$group->getGroupAvgByCCID($question->getID())}</td><td>{$group->getGroupMaxByCCID($question->getID())}</td><td>{$group->getGroupMinByCCID($question->getID())}</td>";
		echo "<tr><td>X$i. <b>$qname</b><br$st>$qdesc</td>$str</tr>\n";
		$i++;
	}

	$sum = 0;
	$max = 0;
	$min = 100;

	foreach($members as $member2)
	{
		$sum = $sum + $member2->getOverall($group->getID());
		if($member2->getOverall($group->getID()) > $max)
			$max = $member2->getOverall($group->getID());
		if($member2->getOverall($group->getID()) < $min)
			$min = $member2->getOverall($group->getID());
	}
	$avg = round($sum / count($members),1);
	echo "<tr><td>Avg Rank by Peers</td><td colspan=\"4\">{$member->getAvgRank($group->getID())}</td></tr>\n";
	echo "<tr><td>Overall</td><td>{$member->getOverall($group->getID())}</td><td>$avg</td><td>$max</td><td>$min</td></tr>\n";

	$ratings = $member->getRatingsByGroup($group->getID());

	echo "<tr><td colspan=\"5\">Comments:";
	foreach($ratings as $rating)
	{
		if($rating->getComment() != null)
		{
			$comment = stripslashes(htmlspecialchars($rating->getComment()));
			echo "<br$st>\n{$comment}";
		}
	}
	echo "</td></tr></table>\n";
}
else if(isset($_GET['distribute']))
{
	$group = new ArGroup($_GET['groupID'], $db);
	$survey = $group->getList();
	$questions = $survey->getCriteria();
	$numCriteria = $survey->getNumCriteria();
	$members = $group->getGroupStudents();
	$customCriteria = $group->getCustomCriteria();

	function cmpOverall($a, $b)
	{
		global $db;
		$group = new ArGroup($_GET['group'], $db);
		if($a->getOverall($group->getID()) >= $b->getOverall($group->getID()))
			return -1;
		else
			return 1;
	}

	echo "<p>Sending reports to...</p><ul>\n";
	foreach ($members as $member)
	{
		$file = fopen('temp/temp.csv', 'w');
		fwrite($file, "{$member->getFullName()} - {$group->getName()}\n");
		fwrite($file, "Individual Report,Your Avg Score,Group Avg,Group High,Group Low\n");
	  
		$i = 1;
		foreach($questions as $question)
		{
			$str = "{$member->getAvgByQ($i, $group->getID())},{$group->getGroupAvgByQ($i)},{$group->getGroupMaxByQ($i)},{$group->getGroupMinByQ($i)}";

			fwrite($file, "$i. {$question->getName()},$str\n");
			$i++;
		}
		
		$i = 1;
		foreach($customCriteria as $question)
		{
			$str = "{$member->getAvgByCCID($question->getID(), $group->getID())},{$group->getGroupAvgByCCID($question->getID())},{$group->getGroupMaxByCCID($question->getID())},{$group->getGroupMinByCCID($question->getID())}";
			fwrite($file, "X$i. {$question->getName()},$str\n");
			$i++;
		}

		$sum = 0;
		$max = 0;
		$min = 100;

		foreach($members as $member2)
		{
			$sum += $member2->getOverall($group->getID());
			if ($member2->getOverall($group->getID()) > $max)
				$max = $member2->getOverall($group->getID());
			if ($member2->getOverall($group->getID()) < $min)
				$min = $member2->getOverall($group->getID());
		}
		$avg = round($sum / count($members),1);
		fwrite($file, "Avg Rank by Peers,{$member->getAvgRank($group->getID())}\n");
		fwrite($file, "Overall,{$member->getOverall($group->getID())},$avg,$max,$min\n\n");

		$ratings = $member->getRatingsByGroup($group->getID());

		fwrite($file, "Comments\n");
		foreach($ratings as $rating)
		{
			if($rating->getComment() != null)
			{
				$comment = str_replace(',', '', $rating->getComment());
				fwrite($file, "{$comment}\n");
			}
		}
		fwrite($file, "\n");

		fclose($file);
		$file = fopen('temp/temp.csv', 'rb');
		$filename = str_replace(' ', '_', $group->getName()) . '_' . str_replace(' ', '_', $member->getLastName()) . '-PeerReview.csv';
		$headers = 'From: '.$email_from."\n"."Reply-To: ".$currentUser->getFullName().' <'.$currentUser->getEmail().">\n";
		$mime_boundary = md5(time());
		$headers .= "MIME-Version: 1.0\n";
		$headers .= "Content-Transfer-Encoding: 7-bit\n";
		$headers .= "Content-Type: multipart/mixed; boundary=$mime_boundary\n";
		$msg = '';
		$f_contents=fread($file, filesize('temp/temp.csv'));
		$f_contents=chunk_split(base64_encode($f_contents));
		fclose($file);
		$msg .= "--$mime_boundary\n";
		$msg .= "Content-Type: text/csv;\n name=$filename\n";
		$msg .= "Content-Transfer-Encoding: base64\n";
		$msg .= "Content-Disposition: attachment; filename=$filename\n\n";
		$msg .= "$f_contents\n\n";
		$msg .= "--$mime_boundary\n";
		$msg .= "Content-Type: text/html; charset=iso-8859-1\n";
		$msg .= "Content-Transfer-Encoding: 7-bit\n\n";
		$msg .= "Greetings,\n\nAttached to this e-mail is feedback from the peer review process that you participated in. You should use this data to identify your strengths and weaknesses in working with your team.\n\nIf you have any questions, please contact your instructor or the admin.\n\n";
		$msg .= "--$mime_boundary--\n\n";
		mail($member->getEmail(), "[{$group->getName()}] Your Peer Review Feedback", $msg, $headers);

		@unlink('temp/temp.csv');
		echo "<li>{$member->getFullName()}</li>\n";
	}
	echo "</ul><h1>Success</h1>\n<p>Reports successfully sent. You may want to return to the <a href=\"reports.php\">Reports page</a>.</p>\n";
}
?>
</div></body></html>

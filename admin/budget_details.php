<?php
	session_start();

	include_once('../globals.php');
	include_once('checkadmin.php');
	include_once('../classes/db.php');
	include_once('../classes/person.php');
	include_once('../classes/group.php');
	include_once('../classes/semester.php');

	if(isset($_POST['selectSemester']))
		$_SESSION['selectedIPROSemester'] = $_POST['semester'];
	else
	{
		$query = $db->query('SELECT iID FROM Semesters WHERE bActiveFlag=1');
		$row = mysql_fetch_row($query);
		$_SESSION['selectedIPROSemester'] = $row[0];
	}

	$currentSemester = new Semester($_SESSION['selectedIPROSemester'], $db);
	
	//Function for truncating text
	function shorten($str, $num = 100)
	{
		if(strlen($str) > $num)
			$str = substr($str, 0, $num).'...';
		return $str;
	}

	//Get important info about the project
	$s_selectedGroup = $_GET['iProjectID'];
	$s_selectedSemester = $_GET['iSemesterID'];
	$ipro_num = $_GET['iproNum'];
	$ipro_name = $_GET['iproName'];

	//Handle Admin Actions -- Approve
	if(isset($_POST['approve_budget'])) 
	{
		$order = $_GET['bOrder'];
		$semester = $_GET['iSemesterID'];
		$project = $_GET['iProjectID'];
		$ipro_num = $_GET['iproNum'];
		$ipro_name = $_GET['iproName'];	
	
		$query_approve = $db->query("UPDATE Budgets SET bApproved=bRequested, bStatus='Completed', bApprovedDate=now() WHERE iSemesterID=$semester AND iProjectID=$project AND bOrder=$order") or die('There was a problem with your submission, please go back and try again');
	}
	
	//Handle Admin Actions --Decline
	if(isset($_POST['decline_budget'])) 
	{
		$order = $_GET['bOrder'];
		$semester = $_GET['iSemesterID'];
		$project = $_GET['iProjectID'];
		$ipro_num = $_GET['iproNum'];
		$ipro_name = $_GET['iproName'];	
		$query_approve = $db->query("UPDATE Budgets SET bStatus='Completed', bApprovedDate=now() WHERE iSemesterID=$semester AND iProjectID=$project AND bOrder=$order") or die('There was a problem with your submission, please go back and try again');
	}

	//Handle Admin Actions --Revise
	if(isset($_POST['revise_budget']) && !empty($_POST['revise_budget_amt'])) 
	{
		$order = $_GET['bOrder'];
		$semester = $_GET['iSemesterID'];
		$project = $_GET['iProjectID'];
		$revised_amt = $_POST['revise_budget_amt'];
		$ipro_num = $_GET['iproNum'];
		$ipro_name = $_GET['iproName'];	
		$query_approve = $db->query("UPDATE Budgets SET bApproved=$revised_amt, bStatus='Completed', bApprovedDate=now() WHERE iSemesterID=$semester AND iProjectID=$project AND bOrder=$order") or die('There was a problem with your submission, please go back and try again');
	}
	
	//Handle Admin Actions --Approve All
	if(isset($_POST['approve_all'])) 
	{
		$semester = $_GET['iSemesterID'];
		$project = $_GET['iProjectID'];
		$ipro_num = $_GET['iproNum'];
		$ipro_name = $_GET['iproName'];	
		$query_approve = $db->query("UPDATE Budgets SET bApproved=bRequested, bStatus='Completed', bApprovedDate=now() WHERE iSemesterID=$semester AND iProjectID=$project") or die('There was a problem with your submission, please go back and try again');
	}
	
	//Handle Admin Actions --Decline All
	if(isset($_POST['decline_all'])) 
	{
	$semester=$_GET['iSemesterID'];
	$project=$_GET['iProjectID'];
	$ipro_num = $_GET['iproNum'];
	$ipro_name = $_GET['iproName'];	
	$query_decline = $db->query("UPDATE Budgets set bApproved=0, bStatus='Completed', bApprovedDate=now() WHERE iSemesterID=$semester AND iProjectID=$project") or die('There was a problem with your submission, please go back and try again');
	}
	
	//Handle Admin Actions --Notify Team (Send Email)
	if(isset($_POST['notify_team'])) 
	{
		$semester = $_GET['iSemesterID'];
		$project = $_GET['iProjectID'];
		$ipro_num = $_GET['iproNum'];
		$ipro_name = $_GET['iproName'];	
		$msg = $_POST['msg'];
		//Send Automatic Email
		//$msg = "There have been changes made on the budget for your {$ipro_num}: {$ipro_name} group. Please login in to iGROUPS to see the status of your team's submitted budget.\n\n";
		//$msg .= "--- The IPRO Office Team";
		$headers = "From: \"$appname Support\" <$contactemail>\n";
							
		$query_getemails = $db->query("SELECT sFName, sLName, sEmail from People WHERE iID in (SELECT iPersonID from PeopleProjectMap WHERE iProjectID=$project AND iSemesterID=$semester)");
							
		$headers .= 'To: ';
		while ($row = mysql_fetch_assoc($query_getemails))
			$headers .= " \"{$row[sFName]} {$row[sLName]}\" <{$row[sEmail]}>,";
		$headers .= "\nContent-Type: text/plain;\n";
		$headers .= "Content-Transfer-Encoding: 7bit;\n";
		mail('', 'Changes Were Made to Your IPRO Budget', $msg, $headers);

		//Save Email in DB
		$query_savemessages = $db->query("INSERT INTO BudgetEmails(iProjectID, iSemesterID, bEmail, bEmailDate) VALUES($project, $semester, '$msg', now())");

	//Show a Message on Top
		echo "<div id=\"info_msg\">Email has been sent to notify the team that changes have been made to their budget.</div>";
	}

		
	//START Handling Forecast Input
	if(isset($_POST['actual_budget_submit']) && !empty($_POST['actual_amount']) && is_numeric($_POST['actual_amount']))
	{
		$semester = $_GET['iSemesterID'];
		$project = $_GET['iProjectID'];
		$ipro_num = $_GET['iproNum'];
		$ipro_name = $_GET['iproName'];
		$actual = $_POST['actual_amount'];
		$target_category = $_POST['actual_category'];
		$query_actual = $db->query("UPDATE Budgets SET bReimbursed=bReimbursed+$actual, bReimbursedDate=now() WHERE iSemesterID=$semester AND iProjectID=$project AND bOrder='$target_category'") or die('There was a problem with your submission, please go back and try again');

		echo "<div id=\"info_msg\">Reimbursement has been added/updated.</div>";
	}
	
	//---------Start XHTML Output-----------------------------------//
	
	require('../doctype.php');
	require('../iknow/appearance.php');
	
	echo "<link rel=\"stylesheet\" href=\"../skins/$skin/budget.css\" type=\"text/css\" title=\"$skin\" />\n";
	foreach($altskins as $altskin)
		echo "<link rel=\"alternate stylesheet\" href=\"../skins/$altskin/budget.css\" type=\"text/css\" title=\"$altskin\" />\n";
?>
<title><?php echo $appname;?> - Manage Budgets</title>
<script type="text/javascript">
//<![CDATA[
function showEvent(id, x, y)
{
	document.getElementById(id).style.top=(y+20)+"px";
	if(x > window.innerWidth / 2)
		document.getElementById(id).style.left=(x-200)+"px";
	else
		document.getElementById(id).style.left=x+"px";
	document.getElementById(id).style.visibility='visible';
}
		
function hideEvent( id ) {
	document.getElementById(id).style.visibility='hidden';
}
//]]>
</script>
</head>
<body>
<?php
	 /**** begin html head *****/
   require('htmlhead.php'); 
  //starts main container
  /****end html head content ****/
?>
	<div id="topbanner">Manage Budgets</div>
	<h3><a href="budget.php?">&laquo; Back to Budgets List</a></h3>
	
<?php
	//Show IPRO Number and IPRO Name
	echo "<h2>$ipro_num: $ipro_name</h2>";

	//Show details for each individual category	
	echo "<table class=\"budget\" cellpadding=\"10\" cellspacing=\"0\"><tr><th>Category</th><th>Requested</th><th>Approved</th><th>Reimbursed</th><th>Explanation</th><th>Status</th><th>Actions</th></tr>";	

	$query = $db->query("SELECT * FROM Budgets WHERE iProjectID={$s_selectedGroup} AND iSemesterID={$s_selectedSemester} ORDER by bOrder");

	$i = 0;
	$divs = array();
	while($row = mysql_fetch_assoc($query))
	{	
		//truncate the description to 50 characters
		$desc = shorten( $row[bDesc], $num = 50 );
		$i++;
		if($i&1)
			echo "<tr class=\"shade\">";
		else
			echo '<tr>';
		echo "<td>{$row['bCategory']}</td>";
		if($row['bStatus'] == 'Pending')
			echo "<td class=\"highlight\">$ {$row['bRequested']}<div class=\"b_date\">{$row['bRequestedDate']}</div></td>";
		else
			echo "<td>$ {$row['bRequested']}<div class=\"b_date\">{$row['bRequestedDate']}</div></td>";
		if($row['bApprovedDate'] == NULL)
 			echo "<td><div class=\"b_date\"><strong>Awaiting<br />Approval</strong></div></td>";
		else
			echo "<td>$ {$row['bApproved']}<div class=\"b_date\">{$row['bApprovedDate']}</div></td>";
		if($row['bReimbursed'] == NULL)
			echo "<td><div class=\"b_date\"><strong>None</strong></div></td>";
		else
			echo "<td>$ $row[bReimbursed]<div class=\"b_date\">$row[bReimbursedDate]</div></td>";
			
		echo "<td><a href=\"#\" onmouseover=\"showEvent('R{$row['bOrder']}',event.clientX+document.documentElement.scrollLeft, event.clientY+document.documentElement.scrollTop);\" onmouseout=\"hideEvent('R{$row['bOrder']}');\">".$desc."</a></td>";
		if($row['bStatus'] == 'Pending')
			echo "<td class=\"highlight\">{$row['bStatus']}<div class=\"b_date\">{$row['bRequestedDate']}</div></td>";
		else
			echo "<td style=\"font-weight: bold\">{$row['bStatus']}</td>";

		echo "<td class=\"actions\">
			 <form method=\"post\" id=\"budget_manage_form".$row['bOrder']."\" action=\"budget_details.php?bOrder={$row['bOrder']}amp;iSemesterID={$row['iSemesterID']}&amp;iProjectID={$row['iProjectID']}&amp;iproNum={$_GET['iproNum']}&amp;iproName={$_GET['iproName']}\"><fieldset>
			 <input type=\"submit\" id=\"approve_budget".$row['bOrder']."\" name=\"approve_budget\" value=\"Approve\" class=\"approve_btn\" />
			 or
			 <input type=\"submit\" id=\"decline_budget".$row['bOrder']."\" name=\"decline_budget\" value=\"Decline\" class=\"decline_btn\" />
			 or
			 <strong>$</strong> <input type=\"text\" id=\"revise_budget_amt".$row['bOrder']."\" name=\"revise_budget_amt\" size=\"5\" />
			 <input type=\"submit\" id=\"revise_budget".$row['bOrder']."\" name=\"revise_budget\" value=\"Revise\" class=\"revise_btn\" />
			 </fieldset></form></td></tr>";
		$divs[$row['bOrder']] = "<div class=\"description\" id=\"R".$row['bOrder']."\">".str_replace("\n", "<br />", htmlspecialchars($row['bDesc']))."</div>";
	}

	//Totals
	$total_amt = $db->query("SELECT sum(bRequested), sum(bApproved), sum(bReimbursed) from Budgets WHERE iProjectID={$s_selectedGroup} AND iSemesterID={$s_selectedSemester} GROUP by iProjectID");
	$total = mysql_fetch_row($total_amt);

	echo "<tr class=\"budget_col_totals\"><td style=\"font-weight: bold\">TOTAL</td><td class=\"req_budget_total\">$".round($total[0], 2)."</td><td class=\"app_budget_total\">$".round($total[1], 2)."</td><td>$".round($total[2], 2)."</td><td colspan=\"2\">&nbsp;</td>
		<td align=\"center\">
		<form method=\"post\" id=\"budget_manage_form".(count($divs)+1)."\" action=\"budget_details.php?iSemesterID=$s_selectedSemester&amp;iProjectID=$s_selectedGroup&amp;iproNum=$_GET[iproNum]&amp;iproName=$_GET[iproName]\"><fieldset>
		<input type=\"submit\" id=\"approve_all\" name=\"approve_all\" value=\"Approve All\" class=\"approve_btn\" />
		OR
		<input type=\"submit\" id=\"decline_all\" name=\"decline_all\" value=\"Decline All\" class=\"decline_btn\" />
		</fieldset></form>
		</td></tr>";

	echo "</table>\n";
	foreach($divs as $div)
		echo $div;
?>
	<div class="notices">
	<div class="notice_msg">
		  <h3>Notify the Team (Send Email)</h3>
		  Change the contents of this email if needed:
		  <form method="post" name="notify_team_form" id="notify_team_form" action="budget_details.php?iSemesterID=<?php echo $s_selectedSemester?>&amp;iProjectID=<?php echo $s_selectedGroup?>&amp;iproNum=<?php echo $_GET['iproNum']?>&amp;iproName=<?php echo $_GET['iproName']?>"><fieldset>
		  <textarea cols="35" rows="8" id="msg" name="msg">The admin has taken action on your submitted budget for <?php echo $_GET['iproNum']?>:<?php echo $_GET['iproName']?>.</textarea>
		  <input type="submit" id="notify_team" name="notify_team" value="Send Email" />
		  </fieldset></form>
</div>
		  
<h2>Responses Sent to this Team</h2>
<?php
	$sent_msgs = $db->query("SELECT * FROM BudgetEmails WHERE iProjectID={$s_selectedGroup} AND iSemesterID={$s_selectedSemester}");

	while($row = mysql_fetch_assoc($sent_msgs))
	{	
		echo "<p><small>";
		echo $row['bEmailDate'];
		echo "</small><br />";
		echo $row['bEmail'];
		echo "</p>\n"; 
	}
?>
</div>
<h3>History of Previously Requested Items:</h3>
<?php
	$query = $db->query("SELECT * FROM BudgetsHistory WHERE iProjectID={$s_selectedGroup} AND iSemesterID={$s_selectedSemester} ORDER by bApprovedDate");

	if(mysql_num_rows($query) == 0)
		echo "<p>There is no history yet.</p>";
	else
	{
		//Show details for each individual category	
		echo "<table class=\"budget\" cellpadding=\"10\" cellspacing=\"0\"><tr><th>Category</th><th>Requested</th><th>Approved</th><th>Explanation</th><th>Status</th></tr>";	
		$divs = array();

		while($row = mysql_fetch_assoc($query))
		{	
			//truncate the description to 50 characters
			$desc = shorten($row['bDesc'], $num = 50);
	
			//spit out results in the table
			echo "<tr>";
			echo "<td>{$row['bCategory']}</td>";
			echo "<td>$ {$row['bRequested']}<div class=\"b_date\">{$row['bRequestedDate']}</div></td>";
			echo "<td>$ {$row['bApproved']}<div class=\"b_date\">{$row['bApprovedDate']}</div></td>";
			echo "<td><a href=\"#\" onmouseover=\"showEvent('RR{$row['bOrder']}',event.clientX+document.documentElement.scrollLeft, event.clientY+document.documentElement.scrollTop);\" onmouseout=\"hideEvent('RR".$row['bOrder']."');\">".$desc."</a></td>";
			echo "<td>{$row['bStatus']}</td>";
			$divs[$row['bOrder']] = "<div class=\"description\" id=\"RR{$row['bOrder']}\">".str_replace("\n", "<br />", htmlspecialchars($row['bDesc']))."</div>";
		}
		echo "</tr></table>\n";
		foreach($divs as $div)
			echo $div;
}	
?>
	<br />
	<h2>Submit a Reimbursement</h2>
	<form method="post" name="actual_budget_form" id="budget_form" action="budget_details.php?iSemesterID=<?php echo $s_selectedSemester?>&amp;iProjectID=<?php echo $s_selectedGroup?>&amp;iproNum=<?php echo $_GET[iproNum]?>&amp;iproName=<?php echo $_GET[iproName]?>"><fieldset>
	<label for="actual_category">Category:</label>
	<select name="actual_category" id="actual_category">
<?php
	$category_list = $db->query("SELECT bOrder, bCategory from Budgets WHERE iProjectID={$s_selectedGroup} AND iSemesterID={$s_selectedSemester} ORDER BY bOrder");
	while($row = mysql_fetch_array($category_list))
		echo "<option value=\"".$row[0]."\">".$row[1]."</option>";
	echo "</select>\n";
	echo "<label for=\"actual_category\">Amount:</label> $ <input type=\"text\" name=\"actual_amount\" size=\"5\" /> ";
?>
<input type="submit" name="actual_budget_submit" value="Submit" /> <br />
<div style="margin-top: 5px; color: #666; font-size: 85%;">* If the actual amount is $0, you have to type it in the format of $0.00</div>
</fieldset></form>


<?php
 	/**** begin html footer*****/
  //include rest of html layout file
  require('htmlfoot.php');
  // ends main container
  /****** end html footer*****/
?>

</body></html>

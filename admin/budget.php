<?php
	include_once('../globals.php');
	include_once('checkadmin.php');
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
		
	//START Handling Forecast Input
	if(isset($_POST['actual_budget_submit']) && !empty($_POST['actual_amount']) && is_numeric($_POST['actual_amount']))
	{
		$actual = $_POST['actual_amount'];
		$target_category = $_POST['actual_category'];
		$query_actual = $db->query("UPDATE Budgets SET bReimbursed=$actual, bReimbursedDate=now() WHERE iSemesterID=$s_selectedSemester AND iProjectID=$s_selectedGroup AND bOrder='$target_category'") or die('There was a problem with your submission, please go back and try again');

		echo "<div id=\"info_msg\">Reimbursement has been added/updated.</div>\n";
	}
	
	//-------Start XHTML Output-------------------------------------//
	
	require('../doctype.php');
	require('../iknow/appearance.php');

	echo "<link rel=\"stylesheet\" href=\"../skins/$skin/budget.css\" type=\"text/css\" title=\"$skin\" />\n";
	foreach($altskins as $altskin)
		echo "<link rel=\"alternate stylesheet\" href=\"../skins/$altskin/budget.css\" type=\"text/css\" title=\"$altskin\" />\n";
?>
<title><?php echo $appname;?> - Manage Budgets</title>
</head>
<body>
<?php
	 /**** begin html head *****/
   require('htmlhead.php'); 
  //starts main container
  /****end html head content ****/
?>
	<div id="topbanner">Manage Budgets</div>
	<form method="post" action="budget.php"><fieldset><legend>Select Semester:</legend>
			<select name="semester">
<?php
	$semesters = $db->query('SELECT iID FROM Semesters ORDER BY iID DESC');
	while($row = mysql_fetch_row($semesters))
	{
		$semester = new Semester($row[0], $db);
		if(isset($currentSemester) && $semester->getID() == $currentSemester->getID())
			echo "<option value=\"".$semester->getID()."\" selected=\"selected\">".$semester->getName()."</option>\n";
		else
			echo "<option value=\"".$semester->getID()."\">".$semester->getName()."</option>\n";
	}
?>
			</select>
			<input type="submit" name="selectSemester" value="Select Semester" />
			</fieldset></form>
	
	<h2>Submitted Budgets</h2>
<?php	
	$query = $db->query("SELECT iProjectID, sum(bRequested) as requested, sum(bApproved) as approved, sum(bReimbursed) as reimbursed, sum(bReimbursed)-sum(bApproved) as difference FROM Budgets WHERE iSemesterID={$currentSemester->getID()} GROUP by iProjectID ORDER by iProjectID");
	
	//Get semester ID in a variable to use it down in the URL construct
	$this_semester = $currentSemester->getID();
	
	//Get the number of rows in order to display a message if there are no budgets in a 
	$num_rows = mysql_num_rows($query);
	$currow = 0;
	
	if($num_rows == 0)
		echo "<p>There were no budgets submitted for this semester.</p>";	
	else
	{
		echo "<table id=\"budget\" cellpadding=\"7\" cellspacing=\"0\">";
		echo "<tr><th>IPRO Name</th><th>Requested</th><th>Approved</th><th>Reimbursed</th><th>Balance</th><th>Waiting Approval?</th></tr>";
		while ($row = mysql_fetch_assoc($query))
		{	
			$get_ipro_name = $db->query("SELECT sIITID, sName FROM Projects WHERE iID={$row[iProjectID]}");
			$result = mysql_fetch_row($get_ipro_name);
			echo "<tr".($currow & 1 ? ' class="shade"' : '')."><td><a href=\"budget_details.php?iProjectID=$row[iProjectID]&amp;iSemesterID=$this_semester&amp;iproNum=$result[0]&amp;iproName=$result[1]\"><strong>$result[0]:</strong> $result[1]</a></td><td>$".round($row[requested], 2)."</td><td>$".round($row[approved], 2)."</td><td>$".round($row[reimbursed], 2)."</td><td>$".round($row[difference], 2)."</td>";
		
			//Check for Pending Requests
			$query_pending = $db->query("SELECT bStatus FROM Budgets WHERE iSemesterID={$this_semester} AND iProjectID={$row[iProjectID]} AND bStatus='Pending'");
			$is_pending = mysql_num_rows($query_pending);
			if($is_pending == 0)
				echo "<td>No</td></tr>";
			else
				echo "<td class=\"highlight\">Yes</td></tr>";
			$currow++;					
		}	
			
		
		$semester_total = $db->query("SELECT sum(bRequested) as requested_total, sum(bApproved) as approved_total, sum(bReimbursed) as reimbursed_total, sum(bRevised)-sum(bApproved) as difference_total  FROM Budgets WHERE iSemesterID={$currentSemester->getID()}");
		$row = mysql_fetch_row($semester_total);
		?>
		<tr><td class="budget_col_total" style="font-weight: bold">SEMESTER TOTALS</td><td class="req_budget_total"><?php echo "$".round($row[0], 2)?></td><td class="app_budget_total"><?php echo "$".round($row[1], 2)?></td><td><?php echo "$".round($row[2], 2)?></td><td><?php echo "$".round($row[3], 2)?></td></tr>	
		</table>
<?php
	}
?>	

<?php
 	/**** begin html footer*****/
  //include rest of html layout file
  require('htmlfoot.php');
  // ends main container
  /****** end html footer*****/
?>
</body></html>

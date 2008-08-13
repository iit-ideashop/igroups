<?php
	session_start();

	include_once( "../classes/db.php" );
	include_once( "../classes/person.php" );
	include_once( "../classes/group.php" );
	include_once( "../classes/semester.php" );	

	$db = new dbConnection();
	
	if ( isset( $_SESSION['userID'] ) ) 
		$currentUser = new Person( $_SESSION['userID'], $db );
	else
		die("You are not logged in.");
		
	if ( !$currentUser->isAdministrator() )
               die("You must be an administrator to access this page.");

    if ( isset( $_POST['selectSemester'] ) ) {
                $_SESSION['selectedIPROSemester'] = $_POST['semester'];
        }
	else {
		$query = $db->iknowQuery("SELECT iID FROM Semesters WHERE bActiveFlag=1");
		$row = mysql_fetch_row($query);
		$_SESSION['selectedIPROSemester'] = $row[0];
	}

        $currentSemester = new Semester( $_SESSION['selectedIPROSemester'], $db );

		
	//START Handling Forecast Input
	if (isset($_POST['actual_budget_submit']) && !empty($_POST['actual_amount']) && is_numeric($_POST['actual_amount'])) {
		$actual=$_POST['actual_amount'];
		$target_category=$_POST['actual_category'];
		$query_actual = $db->igroupsQuery("UPDATE Budgets SET bReimbursed=$actual, bReimbursedDate=now() WHERE iSemesterID=$s_selectedSemester AND iProjectID=$s_selectedGroup AND bOrder='$target_category'") or die('There was a problem with your submission, please go back and try again');

		echo "<div id='info_msg'>Reimbursement has been added/updated.</div>";
	}
		
?>


	

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
   "http://www.w3.org/TR/html4/loose.dtd">

<!-- This web-based application is Copyrighted &copy; 2007 Interprofessional Projects Program, Illinois Institute of Technology -->

<html>
<head>
	<title>iGROUPS - Manage Budgets</title>
	<style type="text/css">
		@import url("../default.css");
	.submit_budget tr td {
			border-top: 1px solid #cc0000;
			background: #eee;
			vertical-align:top;
		}
		
		.submit_budget tr td .submit_budget_button {
			vertical-align:bottom;
		}
		
		.submit_budget tr td h3 {
			margin-top: 0px;
			padding-top:0px;
		}
		
		.description {
			border:solid 1px #000;
			background-color:#fff;
			position:absolute;
			visibility:hidden;
			width:300px;
			padding:5px;
			top:0;
			left:0;
			overflow:hidden;
		}
		
		#budget {
			background: #eee;
		}
		
		#budget tr td {
			border: 1px solid #ccc;
		}
		
		#budget tr th {
			border: 1px solid #ccc;
			background: #ccc;
		}

		.budget_result {
			background: #eee;
			padding:5px;
			border-right: 1px solid #ccc;
			border-bottom: 1px solid #ccc;
			margin-bottom:10px;
		}
		
		.req_budget_total {
			background: #ff9999;
			padding:7px;
			font-weight: bold;
			margin: 10px 0px;
		}
		
		.app_budget_total {
			background: #AFCCA8;
			padding:7px;
			font-weight: bold;
			margin: 10px 0px;
		}
		
		.budget_col_totals {
			background: #ccc;
		}
		
		#info_msg {
			background: #ffffcc; 
			border: 1px solid #fcd900;
			padding: 7px;
		}
				
	</style>
</head>
<body>
	<div id="topbanner">
<?php
		print "Manage Budgets";
?>
	</div>

	<h2>Select Semester:</h2>
	<form method="post" action="budget.php">
                        <select name="semester">
<?php
                        $semesters = $db->iknowQuery( "SELECT iID FROM Semesters ORDER BY iID DESC" );
                        while ( $row = mysql_fetch_row( $semesters ) ) {
                                $semester = new Semester( $row[0], $db );
                                if (isset($currentSemester) && $semester->getID() == $currentSemester->getID())
                                        print "<option value=".$semester->getID()." selected>".$semester->getName()."</option>";
                                else
                                        print "<option value=".$semester->getID().">".$semester->getName()."</option>";
                        }
?>
                        </select>
                        <input type="submit" name="selectSemester" value="Select Semester">
			</div>
	
	<h2>Submitted Budgets</h2>
<?php	
	$query = mysql_query("SELECT iProjectID, sum(bRequested) as requested, sum(bApproved) as approved, sum(bReimbursed) as reimbursed, sum(bReimbursed)-sum(bApproved) as difference FROM Budgets WHERE iSemesterID={$currentSemester->getID()} GROUP by iProjectID ORDER by iProjectID");
	
	//Get semester ID in a variable to use it down in the URL construct
	$this_semester = $currentSemester->getID();
	
	//Get the number of rows in order to display a message if there are no budgets in a 
	$num_rows = mysql_num_rows($query);
	
	if ($num_rows ==0) {
		echo "There were no budgets submitted for this semester";
	}
		
	else {
		echo "<table id='budget' cellpadding=7 cellspacing=0 border=0>";
		echo "<tr><th>IPRO Name</th><th>Requested</th><th>Approved</th><th>Reimbursed</th><th>Balance</th><th>Waiting Approval?</th></tr>";
		while ($row = mysql_fetch_assoc($query))
		{	
		$get_ipro_name = $db->iknowQuery("SELECT sIITID, sName FROM Projects WHERE iID={$row[iProjectID]}");
		$result = mysql_fetch_row($get_ipro_name);
		echo "<tr><td><a href=\"budget_details.php?iProjectID=$row[iProjectID]&iSemesterID=$this_semester&iproNum=$result[0]&iproName=$result[1]\"><strong>$result[0]:</strong> $result[1]</a></td><td>$row[requested]</td><td>$row[approved]</td><td>$row[reimbursed]</td><td>$row[difference]</td>";
		
			//Check for Pending Requests
			$query_pending = mysql_query("SELECT bStatus FROM Budgets WHERE iSemesterID={$this_semester} AND iProjectID={$row[iProjectID]} AND bStatus='Pending'");
			$is_pending = mysql_num_rows($query_pending);
			if ($is_pending ==0) {
			echo "<td>No</td></tr>";
			}
			else {
			echo "<td style='background: yellow;'>Yes</td></tr>";
			}					
		}	
			
		
		$semester_total = $db->igroupsQuery("SELECT sum(bRequested) as requested_total, sum(bApproved) as approved_total, sum(bReimbursed) as reimbursed_total, sum(bRevised)-sum(bApproved) as difference_total  FROM Budgets WHERE iSemesterID={$currentSemester->getID()}");
		$row = mysql_fetch_row($semester_total);
		echo "<tr><td class='budget_col_total'><strong>SEMESTER TOTALS</strong></td><td class='req_budget_total'>$row[0]</td><td class='app_budget_total'>$row[1]</td><td>$row[2]</td><td>$row[3]</td></tr>";	
		echo "</table>";
	}
?>	


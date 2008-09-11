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

//Get important info about the project
$s_selectedGroup = $_GET['iProjectID'];
$s_selectedSemester = $_GET['iSemesterID'];
$ipro_num = $_GET['iproNum'];
$ipro_name = $_GET['iproName'];


//Handle Admin Actions -- Approve
if (isset($_POST['approve_budget'])) 
	{
	$order=$_GET['bOrder'];
	$semester=$_GET['iSemesterID'];
	$project=$_GET['iProjectID'];
	$ipro_num = $_GET['iproNum'];
	$ipro_name = $_GET['iproName'];	
	$query_approve = $db->igroupsQuery("UPDATE Budgets SET bApproved=bRequested, bStatus='Approved' WHERE iSemesterID=$semester AND iProjectID=$project AND bOrder=$order") or die('There was a problem with your submission, please go back and try again');
	}
	

//Handle Admin Actions --Decline
if (isset($_POST['decline_budget'])) 
	{
	$order=$_GET['bOrder'];
	$semester=$_GET['iSemesterID'];
	$project=$_GET['iProjectID'];
	$ipro_num = $_GET['iproNum'];
	$ipro_name = $_GET['iproName'];	
	$query_approve = $db->igroupsQuery("UPDATE Budgets SET bApproved=0, bStatus='Declined' WHERE iSemesterID=$semester AND iProjectID=$project AND bOrder=$order") or die('There was a problem with your submission, please go back and try again');
	}
		

//Handle Admin Actions --Revise
if (isset($_POST['revise_budget']) && !empty($_POST['revise_budget_amt'])) 
	{
	$order=$_GET['bOrder'];
	$semester=$_GET['iSemesterID'];
	$project=$_GET['iProjectID'];
	$revised_amt=$_POST['revise_budget_amt'];
	$ipro_num = $_GET['iproNum'];
	$ipro_name = $_GET['iproName'];	
	$query_approve = $db->igroupsQuery("UPDATE Budgets SET bApproved=$revised_amt, bStatus='Revised' WHERE iSemesterID=$semester AND iProjectID=$project AND bOrder=$order") or die('There was a problem with your submission, please go back and try again');
	}
	
//Handle Admin Actions --Approve All
if (isset($_POST['approve_all'])) 
	{
	$semester=$_GET['iSemesterID'];
	$project=$_GET['iProjectID'];
	$ipro_num = $_GET['iproNum'];
	$ipro_name = $_GET['iproName'];	
	$query_approve = $db->igroupsQuery("UPDATE Budgets SET bApproved=bRequested, bStatus='Approved' WHERE iSemesterID=$semester AND iProjectID=$project") or die('There was a problem with your submission, please go back and try again');
	}
	
//Handle Admin Actions --Decline All
if (isset($_POST['decline_all'])) 
	{
	$semester=$_GET['iSemesterID'];
	$project=$_GET['iProjectID'];
	$ipro_num = $_GET['iproNum'];
	$ipro_name = $_GET['iproName'];	
	$query_approve = $db->igroupsQuery("UPDATE Budgets SET bApproved=0, bStatus='Declined' WHERE iSemesterID=$semester AND iProjectID=$project") or die('There was a problem with your submission, please go back and try again');
	}
	
	
//Handle Admin Actions --Notify Team (Send Email)
if (isset($_POST['notify_team'])) 
	{
	$semester=$_GET['iSemesterID'];
	$project=$_GET['iProjectID'];
	$ipro_num = $_GET['iproNum'];
	$ipro_name = $_GET['iproName'];	
//Send Automatic Email
	$msg = "There have been changes made on the budget for your {$ipro_num}: {$ipro_name} group. Please login in to iGROUPS to see the status of your team's submitted budget.\n\n";
	$msg .= "--- The IPRO Office Team";
	$headers = "From: \"IPRO Office\" <iproadmin@iit.edu>\n";
							
	$query_getemails = $db->iknowQuery("SELECT sFName, sLName, sEmail from People WHERE iID in (SELECT iPersonID from PeopleProjectMap WHERE iProjectID=$project AND iSemesterID=$semester)");
							
	$headers .= "To: ";
		while ($row = mysql_fetch_assoc($query_getemails))
		{	
	$headers .= " \"{$row[sFName]} {$row[sLName]}\" <{$row[sEmail]}>,";
		}
	$headers .= "\nContent-Type: text/plain;\n";
	$headers .= "Content-Transfer-Encoding: 7bit;\n";
	mail('', 'Changes Were Made to Your IPRO Budget', $msg, $headers);

//Show a Message on Top
	echo "<div id='info_msg'>Email has been sent to notify the team that changes have been made to their budget.</div>";
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
			background: #AFCCA8;
			padding:7px;
			font-weight: bold;
			margin: 10px 0px;
		}
		
		.app_budget_total {
			background: #F95F55;
			padding:7px;
			font-weight: bold;
			margin: 10px 0px;
		}
		
		.budget_col_totals {
			background: #ccc;
		}
		
		.budget_col_totals_actions {
			background: #F95F55;
			color: #333;
		}
		
		.actions {
			background: #e9967a;
			color: #333;
		}
		
		#info_msg {
			background: #ffffcc; 
			border: 1px solid #fcd900;
			padding: 7px;
		}
		
		.notice_msg {
			background: #ffffcc; 
			margin-top: 20px;
			padding: 0px 10px 10px 10px;
			border: 2px solid #e6db55;
			width: 330px;
			float: right;
		}
		
		.notice {
			background: #fcd900; 
			padding: 5px 10px;
			border: 2px solid #fcd900;
			width: 330px;
			float: right;
		}
	</style>

<script type="text/javascript">

function showEvent( id, x, y ) {
	document.getElementById(id).style.top=(y+20)+"px";
	if ( x > window.innerWidth/2 )
		document.getElementById(id).style.left=(x-200)+"px";
	else
		document.getElementById(id).style.left=x+"px";
	document.getElementById(id).style.visibility='visible';
}
		
function hideEvent( id ) {
	document.getElementById(id).style.visibility='hidden';
}
</script>
</head>

<body>

	<div id="topbanner">
<?php
		print "Manage Budgets";
?>
	</div>

	
	<h3><a href="budget.php?">&laquo Back to Budgets List</a></h3>
	
<?php


//Show IPRO Number and IPRO Name
echo "<h2>$ipro_num: $ipro_name</h2>";

//Show details for each individual category	
	echo "<table id='budget' cellpadding=10 cellspacing=0 border=0><tr><th>Category</th><th>Requested</th><th>Approved</th><th>Actual</th><th>Explanation</th><th>Status</th><th>Actions</th></tr>";	

$query = mysql_query("SELECT * FROM Budgets WHERE iProjectID={$s_selectedGroup} AND iSemesterID={$s_selectedSemester} ORDER by bOrder");

while ($row = mysql_fetch_assoc($query))
	{	
	//truncate the description to 50 characters
	$desc = shorten( $row[bDesc], $num = 50 );
	
	//spit out results in the table
	echo "<tr>";
	echo "<td>$row[bCategory]</td>";
	echo "<td>$ $row[bRequested]</td>";
	echo "<td>$ $row[bApproved]</td>";
	echo "<td>$ $row[bSpent]</td>";
	echo "<td><a href='#' onMouseOver='showEvent(".$row[bOrder].",event.clientX+document.documentElement.scrollLeft, event.clientY+document.documentElement.scrollTop);' onMouseOut='hideEvent(".$row[bOrder].");'>".$desc."</a></td>";
	echo "<td>$row[bStatus]</td>";
	echo "<td class='actions' nowrap>
		 <form method='post' name='budget_manage_form' id='budget_manage_form' action='budget_details.php?bOrder=$row[bOrder]&iSemesterID=$row[iSemesterID]&iProjectID=$row[iProjectID]&iproNum=$_GET[iproNum]&iproName=$_GET[iproName]'>
		 <input type='submit' id='approve_budget' name='approve_budget' value='Approve' />
		 OR
		 <input type='submit' id='decline_budget' name='decline_budget' value='Decline' />
		 OR
		 <strong>$</strong> <input type='text' id='revise_budget_amt' name='revise_budget_amt' size=5 />
		 <input type='submit' id='revise_budget' name='revise_budget' value='Revise' />
		 </form>
		  </td>";
	echo "<div class='description' id='".$row[bOrder]."'>".$row[bDesc]."</div>";
	}
	echo "</tr>";
	
		
//Total amount Requested
$total_amt = $db->igroupsQuery("SELECT sum(bRequested), sum(bApproved), sum(bSpent) from Budgets WHERE iProjectID={$s_selectedGroup} AND iSemesterID={$s_selectedSemester} GROUP by iProjectID");
$total = mysql_fetch_row($total_amt);

	echo "<tr class='budget_col_totals'><td><strong>TOTAL</strong></td><td class='req_budget_total'>$ $total[0]</td><td class='app_budget_total'>$ $total[1]</td><td>$ $total[2]</td><td colspan='2'>&nbsp;</td>
		  <td align='center' class='budget_col_totals_actions'>
		  <form method='post' name='budget_manage_form' id='budget_manage_form' action='budget_details.php?iSemesterID=$s_selectedSemester&iProjectID=$s_selectedGroup&iproNum=$_GET[iproNum]&iproName=$_GET[iproName]'>
		  <input type='submit' id='approve_all' name='approve_all' value='Approve All' />
		  OR
		  <input type='submit' id='decline_all' name='decline_all' value='Decline All' />
		  </form>
		  </td></tr>";
	echo "<tr><td colspan=6 style='background: #fff; border: none;'></td><td class='notice'>
		  <form method='post' name='notify_team_form' id='notify_team_form' action='budget_details.php?bOrder=$row[bOrder]&iSemesterID=$s_selectedSemester&iProjectID=$s_selectedGroup&iproNum=$_GET[iproNum]&iproName=$_GET[iproName]'>
		  <center><p><strong>Notify the Team: </strong>
		  <input type='submit' id='notify_team' name='notify_team' value='Send Email' /></p></center>
		  </form></td></tr>";
	echo "</table>";
?>	


<!--	
	<div class="notice_msg">
	<h3>Notify the Team (Send Email)</h3>
	<p>Please do not forget to send an email to the team after making any changes to their budget.</p>
	<form method='post' name='notify_team_form' id='notify_team_form' action='budget_details.php?bOrder=$row[bOrder]&iSemesterID=$s_selectedSemester&iProjectID=$s_selectedGroup&iproNum=$_GET[iproNum]&iproName=$_GET[iproName]'>
	<center><input type='submit' id='notify_team' name='notify_team' value='Send Email' /></center>
	</form>
	</div>
-->

<?php	
//Function for truncating text
function shorten( $str, $num = 100 ) {
  if( strlen( $str ) > $num ) $str = substr( $str, 0, $num ) . "...";
  return $str;
}
?>
</div>

</body>

</html>

<?php
	session_start();

	include_once( "classes/db.php" );
	include_once( "classes/person.php" );
	include_once( "classes/group.php" );
	include_once( "classes/timelog.php" );
	
	$db = new dbConnection();
	
	if ( isset( $_SESSION['userID'] ) ) 
		$currentUser = new Person( $_SESSION['userID'], $db );
	else
		die("You are not logged in.");
		 
	if ( isset($_SESSION['selectedGroup']) && isset($_SESSION['selectedGroupType']) && isset($_SESSION['selectedSemester']) )
		$currentGroup = new Group( $_SESSION['selectedGroup'], $_SESSION['selectedGroupType'], $_SESSION['selectedSemester'], $db );
	else
		die("You have not selected a valid group.");
		
	
	$s_userid = $_SESSION['userID'];
	$s_selectedGroup = $_SESSION['selectedGroup'];
	$s_selectedGroupType = $_SESSION['selectedGroupType'];
	$s_selectedSemester = $_SESSION['selectedSemester'];

//START Handling Budget Form Submission
	
if (isset($_POST['submit_budget']))
{
	
	$i=0;
	foreach ($_POST['category'] as $row=>$category)
	{
	$amount = $_POST['amount'][$row];
	$desc = $_POST['desc'][$row];
	
		if ($amount != NULL && is_numeric($amount)) {
		$i++;
		$query = $db->igroupsQuery("INSERT INTO Budgets(iProjectID, iSemesterID, bCategory, bRequested, bDesc, bModified, bOrder) VALUES($s_selectedGroup, $s_selectedSemester, '$category', $amount, '$desc', now(), $i)") or die('There was a problem with your submission, please go back and try again');
		}
	}					
		echo "<div id='info_msg'>Your submission was successful! Review your submission in the table below or at any time by clicking on the 'Budget' tab of your IPRO.</div>";
}

	
	
//START Handling Forecast Input
if (isset($_POST['actual_budget_submit']) && !empty($_POST['actual_amount']) && is_numeric($_POST['actual_amount'])) 
	{
	$actual=$_POST['actual_amount'];
	$target_category=$_POST['actual_category'];
	$query_actual = $db->igroupsQuery("UPDATE Budgets SET bSpent=$actual WHERE iSemesterID=$s_selectedSemester AND iProjectID=$s_selectedGroup AND bOrder='$target_category'") or die('There was a problem with your submission, please go back and try again');

	echo "<div id='info_msg'>Actual/Forecast expenditure has been updated.</div>";
	}
	
	
//START Handling New Category
if (isset($_POST['new_category_submit']) && !empty($_POST['new_category_amount']) && is_numeric($_POST['new_category_amount'])) 
	{
	//Get max order #
	$query = $db->igroupsQuery("SELECT max(bOrder) as ordernum FROM Budgets WHERE iSemesterID=$s_selectedSemester AND iProjectID=$s_selectedGroup");
	$ordernum = mysql_fetch_row($query);
	
	$new_category=$_POST['new_category'];
	$new_category_amount=$_POST['new_category_amount'];
	$new_category_desc=$_POST['new_category_desc'];
	$ordernum = $ordernum[0]+1;
	$query_new_category = $db->igroupsQuery("INSERT INTO Budgets(iProjectID, iSemesterID, bCategory, bRequested, bDesc, bModified, bOrder) VALUES($s_selectedGroup, $s_selectedSemester, '$new_category', $new_category_amount, '$new_category_desc', now(), $ordernum)") or die('There was a problem with your submission, please go back and try again');

	echo "<div id='info_msg'>New category has been added to your budget.</div>";
	}
?>
	
	
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
   "http://www.w3.org/TR/html4/loose.dtd">

<!-- This web-based application is Copyrighted &copy; 2007 Interprofessional Projects Program, Illinois Institute of Technology -->

<html>
<head>
	<title>iGROUPS - View Timesheet Reports</title>
	<style type="text/css">
		@import url("default.css");

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
			width:400px;
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
		
		#info_msg {
			background: #ffffcc; 
			border: 1px solid #fcd900;
			padding: 7px;
		}
		
		.label_desc {
			margin-top:-10px;
			padding-top:-10px;
			margin-bottom: 15px;
			color: #666;
			font-size: 80%;
			width: 300px;
		}
		
		.edit_desc {
			background: #ffffcc;
			border: 1px solid #ccc;
			padding: 3px;
		}
		
		pre {
			font-family: verdana, arial, sans-serif;
			font-size:100%;
			white-space: pre-wrap; /* css-3 */
			white-space: -moz-pre-wrap; /* Mozilla, since 1999 */
			white-space: -pre-wrap; /* Opera 4-6 */
			white-space: -o-pre-wrap; /* Opera 7 */
			word-wrap: break-word; /* Internet Explorer 5.5+ */
			_white-space: pre; /* IE only hack to re-specify in addition to word-wrap */
		}
		
	</style>

<script type="text/javascript">

function calc(){
  var one = document.budget_form.supplies_amount.value;
  var two = document.budget_form.equipment_amount.value;
  var three = document.budget_form.services_amount.value;
  var four = document.budget_form.travel_amount.value;
  var five = document.budget_form.participant_support_amount.value;
  var six = document.budget_form.other1_amount.value;
  var seven = document.budget_form.other2_amount.value;
  var eight = document.budget_form.other3_amount.value;
  var nine = document.budget_form.other4_amount.value;
  var ten = document.budget_form.other5_amount.value;
  var total = (one * 1) + (two * 1) + (three * 1) + (four * 1) + (five * 1) + (six * 1) + (seven * 1) + (eight * 1) + (nine * 1) + (ten * 1);
  
  document.budget_form.total_amt.value = total;
}

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
		print $currentGroup->getName();
?>
	</div>

<?php
$query = mysql_query("SELECT * FROM Budgets WHERE iProjectID=$s_selectedGroup AND iSemesterID=$s_selectedSemester");
		$result = mysql_fetch_row($query);
		if (!$result)
		{
?>
	<h4>Guidelines: </h4>
	<ul>
	<li>Please <b>only submit one budget</b> form per IPRO per semester. Before submitting, please consult with your teammates and instructor to make sure that you are responsible for filling out this form.</li>
	<li>Fill in the dollar amount for each of the pre-defined categories; for those categories that you are not requesting any funds, please leave the dollar amount blank.</li>
	<li>You have up to five budget categories you can define yourself if some expenses are not fitting the IPRO categories. Use these extra categories for subteam budgets</li>
	<li>Use the "Explain" textboxes to briefly describe the requested amount for each category.</li>
	</ul>
	<hr>
	
	<h2 style="color: #cc0000;">Submit a Budget</h2>
	<form method="post" name="budget_form" id="budget_form" action="budget.php">
	
	<table border="0" cellpadding="10" cellspacing="0" class="submit_budget">
	<tr>
	<td>
	<label for="supplies"><h3>Supplies:</h3></label>
	<div class='label_desc'>Lab supplies, office supplies, etc.</div>
	<input type="hidden" id="supplies" name="category[]" value="Supplies" />
	$ <input type="text" id="supplies_amount" name="amount[]" size="5" onchange="calc()" />
	</td>
	<td>
	<label for="supplies_desc">Explain:</label><br />
	<textarea id="supplies_desc" name="desc[]" rows="3" cols="50"></textarea>
	</td>
	</tr>
	
	<tr>
	<td>
	<label for="equipment"><h3>Equipment:</h3></label>
	<div class='label_desc'>Purchase materials and/or parts for testing or construction.</div>
	<input type="hidden" id="equipment" name="category[]" value="Equipment" />
	$ <input type="text" id="equipment_amount" name="amount[]" size="5" onchange="calc()" />
	</td>
	<td>
	<label for="equipment_desc">Explain:</label><br />
	<textarea id="equipment_desc" name="desc[]" rows="3" cols="50"></textarea>
	</td>
	</tr>
	
	<tr>
	<td>
	<label for="services"><h3>Services:</h3></label>
	<div class='label_desc'>Printing, rentals, consulting, patent searching, etc.</div>
	<input type="hidden" id="services" name="category[]" value="Services" />
	$ <input type="text" id="services_amount" name="amount[]" size="5" onchange="calc()" />
	</td>
	<td>
	<label for="services_desc">Explain:</label><br />
	<textarea id="services_desc" name="desc[]" rows="3" cols="50"></textarea>
	</td>
	</tr>
	
	<tr>
	<td>
	<label for="travel"><h3>Travel/Meetings:</h3></label>
	<div class='label_desc'>Transportaion costs, meals, conference passes, etc.</div>
	<input type="hidden" id="travel" name="category[]" value="Travel" />
	$ <input type="text" id="travel_amount" name="amount[]" size="5" onchange="calc()" />
	</td>
	<td>
	<label for="travel_desc">Explain:</label><br />
	<textarea id="travel_desc" name="desc[]" rows="3" cols="50"></textarea>
	</td>
	</tr>
	
	<tr>
	<td>
	<label for="participant_support"><h3>Participant Support:</h3></label>
	<div class='label_desc'>Incentives to participants of usability testing, product testing, user survey, focus groups, etc.</div>
	<input type="hidden" id="participant_support" name="category[]" value="Participant Support" />	
	$ <input type="text" id="participant_support_amount" name="amount[]" size="5" onchange="calc()" />
	</td>
	<td>
	<label for="participant_support_desc">Explain:</label><br />
	<textarea id="participant_support_desc" name="desc[]" rows="3" cols="50"></textarea>
	</td>
	</tr>
	
	<tr>
	<td>
	<label for="other1"><small><strong>Define Your Own Category:</strong></small></label><br />
	<input type="text" id="other1" name="category[]" /><br /><br />
	$ <input type="text" id="other1_amount" name="amount[]" size="5" onchange="calc()" />
	</td>
	<td>
	<label for="other1_desc">Explain:</label><br />
	<textarea id="other1_desc" name="desc[]" rows="3" cols="50"></textarea>
	</td>
	</tr>
		
	<tr>
	<td>
	<label for="other2"><small><strong>Define Your Own Category:</strong></small></label><br />
	<input type="text" id="other2" name="category[]" /><br /><br />
	$ <input type="text" id="other2_amount" name="amount[]" size="5" onchange="calc()" />
	</td>
	<td>
	<label for="other2_desc">Explain:</label><br />
	<textarea id="other2_desc" name="desc[]" rows="3" cols="50"></textarea>
	</td>
	</tr>
	
	<tr>
	<td>
	<label for="other3"><small><strong>Define Your Own Category:</strong></small></label><br />
	<input type="text" id="other3" name="category[]" /><br /><br />
	$ <input type="text" id="other3_amount" name="amount[]" size="5" onchange="calc()" />
	</td>
	<td>
	<label for="other3_desc">Explain:</label><br />
	<textarea id="other3_desc" name="desc[]" rows="3" cols="50"></textarea>
	</td>
	</tr>
	
	<tr>
	<td>
	<label for="other4"><small><strong>Define Your Own Category:</strong></small></label><br />
	<input type="text" id="other4" name="category[]" /><br /><br />
	$ <input type="text" id="other4_amount" name="amount[]" size="5" onchange="calc()" />
	</td>
	<td>
	<label for="other4_desc">Explain:</label><br />
	<textarea id="other4_desc" name="desc[]" rows="3" cols="50"></textarea>
	</td>
	</tr>	
		
	<tr>
	<td>
	<label for="other5"><small><strong>Define Your Own Category:</strong></small></label><br />
	<input type="text" id="other5" name="category[]" /><br /><br />
	$ <input type="text" id="other5_amount" name="amount[]" size="5" onchange="calc()" />
	</td>
	<td>
	<label for="other5_desc">Explain:</label><br />
	<textarea id="other5_desc" name="desc[]" rows="3" cols="50"></textarea>
	</td>
	</tr>
	
	<tr>
	<td colspan="2">
	<label for="supplies"><h3 style="color: #cc0000;">Total Amount:</h3></label>
	$ <input type="text" id="total_amt" name="total_amt" size="5" onchange="calc()" />
	<p>
	<input type="submit" id="submit_budget" value="Submit Budget" name="submit_budget" />
	</p>
	</td>
	</tr>
	</table>

	<input type="hidden" id="iProjectID" name="iProjectID" value="<?php echo $s_selectedGroup ?>" />
	<input type="hidden" id="iSemesterID" name="iSemesterID" value="<?php echo $s_selectedSemester ?>" />
	</form>
	
<?php
}

else {

	echo "<h2>Submitted Budget</h2>";

//Show details for each individual category	
	echo "<table id='budget' cellpadding=10 cellspacing=0 border=0><tr><th>Category</th><th>Requested</th><th>Approved</th><th>Actual/Forecast</th><th>Explanation</th><th>Status</th></tr>";	

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
	echo "<td><a href='#' onMouseOver='showEvent(".$row[bOrder].",event.clientX+document.documentElement.scrollLeft, event.clientY+document.documentElement.scrollTop);' onMouseOut='hideEvent(".$row[bOrder].");'>".$desc."</a> <span class='edit_desc'><a href='edit_budget.php?bOrder=$row[bOrder]&bDesc=$row[bDesc]'>Edit</a></span></td>";
	echo "<td>$row[bStatus]</td>";
	echo "<div class='description' id='".$row[bOrder]."'><pre>".$row[bDesc]."</pre></div>";
	}
	echo "</tr>";
	

	
//Total Amount
$total_amt = $db->igroupsQuery("SELECT sum(bRequested), sum(bApproved), sum(bSpent) from Budgets WHERE iProjectID={$s_selectedGroup} AND iSemesterID={$s_selectedSemester} GROUP by iProjectID");
$total = mysql_fetch_row($total_amt);

	echo "<tr class='budget_col_totals'><td><strong>TOTAL</strong></td><td class='req_budget_total'>$ $total[0]</td><td class='app_budget_total'>$ $total[1]</td><td>$ $total[2]</td><td colspan='2'>&nbsp;</td></tr>";
	echo "</table>";	

?>


<!--Form for Submitting Actual/Forecast Expenditures-->

<p><br />
<h3>Submit an Actual/Forecast Estimate or Revision:</h3>

<form method='post' name='actual_budget_form' id='budget_form' action='budget.php'>
<label for="actual_category">Category:</label>
<select name="actual_category">
<?php
$category_list = $db->igroupsQuery( "SELECT bOrder, bCategory from Budgets WHERE iProjectID={$s_selectedGroup} AND iSemesterID={$s_selectedSemester} ORDER BY bOrder" );
    while ( $row = mysql_fetch_array( $category_list ) ) {
    echo "<option value=".$row[0].">".$row[1]."</option>";
    }
?>
</select>
<?php
echo "<label for='actual_category'>Amount:</label> $ <input type='text' name='actual_amount' size=5> ";
?>
<input type="submit" name="actual_budget_submit" value="Submit"> <br />
<div style="margin-top: 5px; color: #666; font-size: 85%;">* If the actual amount is $0, you have to type it in the format of $0.00</div>
</form>


<p><br />
<h3>Create a New Budget Category:</h3>

<form method='post' name='new_category_form' id='new_category_form' action='budget.php'>
<label for="new_category">New Category:</label>
<input type="text" id="new_category" name="new_category" />
<label for="new_category"> Amount:</label>
$ <input type="text" id="new_category_amount" name="new_category_amount" size="5" /><p>
<label for="new_category_desc">Explain:</label><br />
<textarea id="new_category_desc" name="new_category_desc" rows="3" cols="50"></textarea><br />
<input type="submit" name="new_category_submit" value="Submit" />
</form>


<p><br />
<h2>Messages from the admin:</h2>

<?php
$sent_msgs = $db->igroupsQuery("SELECT * FROM BudgetEmails WHERE iProjectID={$s_selectedGroup} AND iSemesterID={$s_selectedSemester}");

while ($row = mysql_fetch_assoc($sent_msgs))
	{	
	echo "<p><small>";
	echo $row['bEmailDate'];
	echo "</small><br />";
	echo $row['bEmail'];
	echo "</p>"; 
	}
	
}
?>


<?php	
//Function for truncating text
function shorten( $str, $num = 100 ) {
  if( strlen( $str ) > $num ) $str = substr( $str, 0, $num ) . "...";
  return $str;
}
?>
</body>

</html>
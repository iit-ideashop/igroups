<?php
	include_once("globals.php");
	include_once("checklogin.php");
	include_once( "classes/timelog.php" );
	
	$s_userid = $_SESSION['userID'];
	$s_selectedGroup = $_SESSION['selectedGroup'];
	$s_selectedGroupType = $_SESSION['selectedGroupType'];
	$s_selectedSemester = $_SESSION['selectedSemester'];

//START Handling Budget Form Submission
	
if (isset($_POST['submit_budget']))
{
	
	$i=0;
	$notify = true;
	foreach ($_POST['category'] as $row=>$category)
	{
		$amount = $_POST['amount'][$row];
		$desc = $_POST['desc'][$row];
		if($desc == "Foobar")
			$notify = false;
	
		if ($amount != NULL && is_numeric($amount)) {
		$i++;
		$query = $db->igroupsQuery("INSERT INTO Budgets(iProjectID, iSemesterID, bCategory, bRequested, bDesc, bRequestedDate, bOrder) VALUES($s_selectedGroup, $s_selectedSemester, '$category', $amount, '$desc', now(), $i)") or die('There was a problem with your submission, please go back and try again');
		}
	}					

	if($notify)
	{
		//Send Automatic Email
		$msg = "This is an auto-generated $appname notification to let you know that ". $currentGroup->getName() ." team has submitted a budget and is awaiting your review.\n\n";
		$msg .= "--- $appname System Auto-Generated Massage";
		$headers = "From: \"$appname Support\" <$contactemail>\n";
					
		$headers .= "To: jacobius@iit.edu";
		$headers .= "\nContent-Type: text/plain;\n";
		$headers .= "Content-Transfer-Encoding: 7bit;\n";
		mail('', $currentGroup->getName() .' submitted a budget ' .$s_selectedCategoryName.'', $msg, $headers);
		$message = "Your submission was successful! Review your submission in the table below or at any time by clicking on the 'Budget' tab of your IPRO.";
	}
	else
		$message = "Your test submission was successful!";
	
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
	$query_new_category = $db->igroupsQuery("INSERT INTO Budgets(iProjectID, iSemesterID, bCategory, bRequested, bDesc, bRequestedDate, bOrder) VALUES($s_selectedGroup, $s_selectedSemester, '$new_category', $new_category_amount, '$new_category_desc', now(), $ordernum)") or die('There was a problem with your submission, please go back and try again');

	echo "<div id='info_msg'>New category has been added to your budget.</div>";	
		
//Send Automatic Email
	$msg = "This is an auto-generated $appname notification to let you know that ". $currentGroup->getName() ." team has submitted a new budget category and is awaiting your review.\n\n";
	$msg .= "--- $appname Auto-Generated Massage";
	$headers = "From: \"$appname Support\" <$contactemail>\n";
					
	$headers .= "To: jacobius@iit.edu";
	$headers .= "\nContent-Type: text/plain;\n";
	$headers .= "Content-Transfer-Encoding: 7bit;\n";
	mail('', $currentGroup->getName() .' submitted a new budget category ' .$s_selectedCategoryName.'', $msg, $headers);
}

if(isset($_GET['delete']) && is_numeric($_GET['proj']) && is_numeric($_GET['type']) && is_numeric($_GET['sem']) && isset($_GET['cat']))
{
	$group = new Group($_GET['proj'], $_GET['type'], $_GET['sem'], $db);
	if(!$group->isGroupMember($currentUser))
		die("You are not a member of this group.");
	$db->igroupsQuery("delete from Budgets where iProjectID=".$_GET['proj']." and iSemesterID=".$_GET['sem']." and bCategory='".mysql_real_escape_string($_GET['cat'])."'");
	$message = "Budget item successfully deleted.";
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<!-- This web-based application is Copyrighted &copy; 2008 Interprofessional Projects Program, Illinois Institute of Technology -->
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en"><head>
<title><?php echo $appname; ?> - Budget</title>
<?php
require("appearance.php");
echo "<link rel=\"stylesheet\" href=\"skins/$skin/budget.css\" type=\"text/css\" />\n";
?>
<script type="text/javascript">
<!--
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
//-->
</script>
</head>

<body>
<?php
	require("sidebar.php");
	print "<div id=\"content\"><div id=\"topbanner\">";
	print $currentGroup->getName();
?>
	</div>

<?php
$query = mysql_query("SELECT * FROM Budgets WHERE iProjectID=$s_selectedGroup AND iSemesterID=$s_selectedSemester");
		$result = mysql_fetch_row($query);
		if (!$result)
		{
?>
	<h4>Guidelines:</h4>
	<ul>
	<li>Please submit <b>only one budget form</b> per IPRO per semester. Before submitting, please consult with your teammates and instructor to make sure that you are responsible for filling out this form.</li>
	<li>Fill in the dollar amount for each of the pre-defined categories; for those categories that you are not requesting any funds, please leave the dollar amount blank.</li>
	<li>You have up to five budget categories you can define yourself if some expenses are not fitting the IPRO categories.</li>
	<li>Use the "Explain" fields to briefly describe the requested amount for each category. The justification for the line item amounts must be clearly stated in the context of the project plan, goals and tasks and in terms of what is reasonable within the current semester.</li>
	<li>Please note that the average IPRO team budget over many years has been on the order of $500; however, many teams require and request no funding, while others may have special needs that require extra investment.</li>
    <li>It is advisable to be as realistic as possible rather than request highly conservative amounts, since we understand that the exact amount may vary and unexpected needs may develop over time.</li>
    <li>Our aim is to assure that IPRO teams have sufficient funds to achieve success during the semester; however, we also have budget constraints and must manage our resources wisely.</li> 
	</ul>
	<hr />
	
	<h2 style="color: #cc0000;">Submit a Budget</h2>
	<form method="post" id="budget_form" action="budget.php"><fieldset>
	
	<table cellpadding="10" cellspacing="0" class="submit_budget">
	<tr>
	<td>
	<h3><label for="supplies">Supplies:</label></h3>
	<div class="label_desc">Lab supplies, office supplies, etc.</div>
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
	<h3><label for="equipment">Equipment:</label></h3>
	<div class="label_desc">Purchase materials and/or parts for testing or construction.</div>
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
	<h3><label for="services">Services:</label></h3>
	<div class="label_desc">Printing, rentals, consulting, patent searching, etc.</div>
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
	<h3><label for="travel">Travel/Meetings:</label></h3>
	<div class="label_desc">Transportaion costs, meals, conference passes, etc.</div>
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
	<h3><label for="participant_support">Participant Support:</label></h3>
	<div class="label_desc">Incentives to participants of usability testing, product testing, user survey, focus groups, etc.</div>
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
	<h3 style="color: #cc0000;"><label for="supplies">Total Amount:</label></h3>
	$ <input type="text" id="total_amt" name="total_amt" size="5" onchange="calc()" />
	<p>
	<input type="submit" id="submit_budget" value="Submit Budget" name="submit_budget" />
	</p>
	</td>
	</tr>
	</table>

	<input type="hidden" id="iProjectID" name="iProjectID" value="<?php echo $s_selectedGroup ?>" />
	<input type="hidden" id="iSemesterID" name="iSemesterID" value="<?php echo $s_selectedSemester ?>" />
	</fieldset></form>
	
<?php
}

else {

	echo "<h2>Submitted Budget</h2>";
	$divs = array();
//Show details for each individual category	
	echo "<table class=\"budget\" cellpadding=\"10\" cellspacing=\"0\"><tr><th>Category</th><th>Requested</th><th>Approved</th><th>Reimbursed</th><th>Explanation</th><th>Status</th><th>Edit</th></tr>";	

$query = mysql_query("SELECT * FROM Budgets WHERE iProjectID={$s_selectedGroup} AND iSemesterID={$s_selectedSemester} ORDER by bOrder");

$i=0;
while ($row = mysql_fetch_assoc($query))
	{	
	//truncate the description to 50 characters
	$desc = shorten( $row[bDesc], $num = 50 );
	$i++;
if ( $i&1 ) {
	echo "<tr style=\"background-color: #f8f8f8;\">";
}
else {
	echo "<tr>";
}
	echo "<td style=\"font-weight: bold\">$row[bCategory]</td>";
	echo "<td>$ $row[bRequested]<div class=\"b_date\">$row[bRequestedDate]</div></td>";
if ($row[bApprovedDate]==NULL) {
	echo "<td class=\"b_date\" style=\"font-weight: bold\">Awaiting<br />Approval</td>";
}
else {
	echo "<td>$ $row[bApproved]<div class='b_date'>$row[bApprovedDate]</div></td>";
}
if ($row[bReimbursed]==NULL) {
	echo "<td class=\"b_date\" style=\"font-weight: bold\">None</td>";
}
else {
	echo "<td>$ $row[bReimbursed]<div class=\"b_date\">$row[bReimbursedDate]</div></td>";
}
	echo "<td><a href=\"#\" onmouseover=\"showEvent('R".$row[bOrder]."',event.clientX+document.documentElement.scrollLeft, event.clientY+document.documentElement.scrollTop);\" onmouseout=\"hideEvent('R".$row[bOrder]."');\">".$desc."</a></td>";
if ($row[bStatus]=='Completed') {
	echo "<td style=\"font-weight: bold\">$row[bStatus]</td>";
	}
else {
	echo "<td>$row[bStatus]</td>";
	}
	echo "<td><span class=\"edit_desc\"><a href=\"edit_budget.php?bOrder=$row[bOrder]&amp;bDesc=$row[bDesc]\">Revise</a>";
	if($row[bApprovedDate]==NULL)
		echo " or <a href=\"budget.php?delete=true&amp;proj=$row[iProjectID]&amp;type=$s_selectedGroupType&amp;sem=$row[iSemesterID]&amp;cat=".urlencode($row[bCategory])."\" title=\"Delete this item\">Delete</a>";
	echo "</span></td></tr>";
	$divs[$row[bOrder]] = "<div class=\"description\" id=\"R".$row[bOrder]."\">".str_replace("\n", "<br />", htmlspecialchars($row[bDesc]))."</div>";
	}
	
//Total Amount
$total_amt = $db->igroupsQuery("SELECT sum(bRequested), sum(bApproved), sum(bReimbursed) from Budgets WHERE iProjectID={$s_selectedGroup} AND iSemesterID={$s_selectedSemester} GROUP by iProjectID");
$total = mysql_fetch_row($total_amt);

	echo "<tr class=\"budget_col_totals\"><td style=\"font-weight: bold\">TOTAL</td><td class=\"req_budget_total\">$".round($total[0],2)."</td><td class=\"app_budget_total\">$".round($total[1], 2)."</td><td>$".round($total[2], 2)."</td><td colspan=\"4\">&nbsp;</td></tr>";
	echo "</table>";	
	foreach($divs as $div)
		echo $div; 
?>
<p><a href="reimbursements.php" style="font-weight: bold">Reimbursement Guidelines</a></p>
<h3>History of Previously Requested Items:</h3>
<?php
//Show details for each individual category	
	echo "<table class=\"budget\" cellpadding=\"10\" cellspacing=\"0\"><tr><th>Category</th><th>Requested</th><th>Approved</th><th>Explanation</th><th>Status</th></tr>";	

$query = mysql_query("SELECT * FROM BudgetsHistory WHERE iProjectID={$s_selectedGroup} AND iSemesterID={$s_selectedSemester} ORDER by bOrder");
$divs = array();
while ($row = mysql_fetch_assoc($query))
	{	
	//truncate the description to 50 characters
	$desc = shorten( $row[bDesc], $num = 50 );
	
	//spit out results in the table
	echo "<tr>";
	echo "<td>$row[bCategory]</td>";
	echo "<td>$ $row[bRequested]<div class=\"b_date\">$row[bRequestedDate]</div></td>";
	echo "<td>$ $row[bApproved]<div class=\"b_date\">$row[bApprovedDate]</div></td>";
	echo "<td><a href=\"#\" onmouseover=\"showEvent('RR".$row[bOrder]."',event.clientX+document.documentElement.scrollLeft, event.clientY+document.documentElement.scrollTop);\" onmouseout=\"hideEvent('RR".$row[bOrder]."');\">".$desc."</a></td>";
	echo "<td>$row[bStatus]</td></tr>";
	$divs[$row[bOrder]] = "<div class=\"description\" id=\"RR".$row[bOrder]."\">".str_replace("\n", "<br />", htmlspecialchars($row[bDesc]))."</div>";
	}
	echo "</table>\n";
	foreach($divs as $div)
		echo $div;
?>
<br />
<h3>Create a New Budget Category:</h3>

<form method="post" name="new_category_form" id="new_category_form" action="budget.php">
<label for="new_category">New Category:</label>
<input type="text" id="new_category" name="new_category" />
<label for="new_category"> Amount:</label>
$ <input type="text" id="new_category_amount" name="new_category_amount" size="5" /><br /><br />
<label for="new_category_desc">Explain:</label><br />
<textarea id="new_category_desc" name="new_category_desc" rows="3" cols="50"></textarea><br />
<input type="submit" name="new_category_submit" value="Submit" />
</form>
<br />
<h2>Responses to this budget:</h2>

<?php
$sent_msgs = $db->igroupsQuery("SELECT * FROM BudgetEmails WHERE iProjectID={$s_selectedGroup} AND iSemesterID={$s_selectedSemester}");

while ($row = mysql_fetch_assoc($sent_msgs))
	{	
	echo "<p><span style=\"font-size: smaller\">";
	echo $row['bEmailDate'];
	echo "</span><br />";
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
</div></body>
</html>

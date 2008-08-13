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
	$s_selectedCategory = $_GET['bOrder'];
	$s_selectedDesc = $_GET['bDesc'];

if 	( isset( $_POST['submit_edit_budget'] ) ) {

	$new_budget_amt = $_POST['budget_revised_amt'];
	$new_budget_desc = $_POST['edit_budget'];
	$s_selectedCategory = $_POST['budget_category'];
	$s_selectedCategoryName = $_POST['budget_category_name'];
	
	//Insert old record in history
	$query = $db->igroupsQuery("INSERT INTO BudgetsHistory(iProjectID, iSemesterID, bCategory, bRequested, bApproved, bDesc, bStatus, bOrder, bRequestedDate, bApprovedDate) SELECT iProjectID, iSemesterID, bCategory, bRequested, bApproved, bDesc, bStatus, bOrder, bRequestedDate, bApprovedDate FROM Budgets WHERE iProjectID=$s_selectedGroup AND iSemesterID=$s_selectedSemester AND bOrder=$s_selectedCategory AND bStatus='Completed'");
	
	//Update record w/ revised info
	$query = $db->igroupsQuery("UPDATE Budgets SET bDesc='$new_budget_desc', bRequested=$new_budget_amt, bStatus='Pending', bRequestedDate=now() WHERE iProjectID=$s_selectedGroup AND iSemesterID=$s_selectedSemester AND bOrder=$s_selectedCategory");

	
//Send Automatic Email
	$msg = "This is an auto-generated iGroups notification to let you know that ". $currentGroup->getName() ." team has made changes in the budget category: $s_selectedCategoryName.\n\n";
	$msg .= "--- iGroups System Auto-Generated Massage";
	$headers = "From: \"IPRO Office\" <igroups@iit.edu>\n";
					
	$headers .= "To: jovaani@iit.edu";
	$headers .= "\nContent-Type: text/plain;\n";
	$headers .= "Content-Transfer-Encoding: 7bit;\n";
	mail('', $currentGroup->getName() .' revised the budget category ' .$s_selectedCategoryName.'', $msg, $headers);
	
//Redirect to Budget Main Page
	header('Location: budget.php');
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
</head>

<body>
	<div id="topbanner">
<?php
		print $currentGroup->getName();
?>
	</div>

	
<?php	
	$query = $db->igroupsQuery("SELECT bDesc, bCategory, bRequested from Budgets WHERE iProjectID=$s_selectedGroup AND iSemesterID=$s_selectedSemester AND bOrder=$s_selectedCategory");
	$result = mysql_fetch_row($query);
	
	echo "Editing budget category: <strong>$result[1]</strong>";
?>

	<form method='post' name='edit_budget_desc' id='edit_budget_desc' action='edit_budget.php'>
	<p>
	<label for="budget_revised_amt"><h3>Amount:</h3></label>
	$ <input type="text" id="budget_revised_amt" name="budget_revised_amt" value="<?php echo $result[2] ?>" size="5" />
	</p>
	<p>
	<h3>Description:</h3>
	<pre><textarea id="edit_budget" name="edit_budget" cols="50" rows="20"><?php echo $result[0] ?></textarea></pre>
	</p>
	<input type="hidden" id="budget_category" name="budget_category" value="<?php echo $s_selectedCategory ?>" />
	<input type="hidden" id="budget_category_name" name="budget_category_name" value="<?php echo $result[1] ?>" />
		<input type="submit" id="submit_edit_budget" value="Edit" name="submit_edit_budget" />
	</form>
	
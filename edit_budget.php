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
					
	$headers .= "To: jacobius@iit.edu";
	$headers .= "\nContent-Type: text/plain;\n";
	$headers .= "Content-Transfer-Encoding: 7bit;\n";
	mail('', $currentGroup->getName() .' revised the budget category ' .$s_selectedCategoryName.'', $msg, $headers);
	
//Redirect to Budget Main Page
	header('Location: budget.php');
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<!-- This web-based application is Copyrighted &copy; 2008 Interprofessional Projects Program, Illinois Institute of Technology -->
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en"><head>
<title>iGroups - Budget</title>
<link rel="stylesheet" href="default.css" type="text/css" />
</head>

<body>
<?php
	require("sidebar.php");
	print "<div id=\"content\"><div id=\"topbanner\">";
	print $currentGroup->getName();
?>
	</div>
<?php	
	$query = $db->igroupsQuery("SELECT bDesc, bCategory, bRequested from Budgets WHERE iProjectID=$s_selectedGroup AND iSemesterID=$s_selectedSemester AND bOrder=$s_selectedCategory");
	$result = mysql_fetch_row($query);
	
	echo "Editing budget category: <strong>$result[1]</strong>";
?>

	<form method="post" name="edit_budget_desc" id="edit_budget_desc" action="edit_budget.php"><fieldset>
	<h3><label for="budget_revised_amt">Amount:</label></h3>
	$ <input type="text" id="budget_revised_amt" name="budget_revised_amt" value="<?php echo $result[2] ?>" size="5" />
	<h3>Description:</h3>
	<textarea id="edit_budget" name="edit_budget" cols="50" rows="20"><?php echo stripTags($result[0]) ?></textarea>
	<input type="hidden" id="budget_category" name="budget_category" value="<?php echo $s_selectedCategory ?>" />
	<input type="hidden" id="budget_category_name" name="budget_category_name" value="<?php echo $result[1] ?>" />
		<input type="submit" id="submit_edit_budget" value="Edit" name="submit_edit_budget" />
	</fieldset></form>
</div></body></html>

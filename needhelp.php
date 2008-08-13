<?php
	session_start();
	include_once( "classes/db.php" );
	include_once( "classes/person.php" );
	include_once( "classes/group.php" );
	include_once( "classes/email.php" );

	$db = new dbConnection();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" http://www.w3.org/TR/html4/loose.dtd">

<!-- This web-based application is Copyrighted &copy; 2007 Interprofessional Projects Program, Illinois Institute of Technology -->



<html>
<head>
<link href="default.css" rel="stylesheet" type="text/css">
</head>
<body>
<?php
if ( isset( $_POST['help'] ) ) {
	mail( $_POST['email'], "Your iGROUPS Help Request", "We have received your inquiry and will respond to it as soon as possible.\n\nThank you for contacting us.\n\n-The IPRO Office Team", "From:igroups@iit.edu" );
	$user = $db->iknowQuery( "SELECT iID FROM People WHERE sEmail='".$_POST['email']."'" );
	if ( $row = mysql_fetch_row( $user ) ) {
		$id = $row[0];
	}
	else
		$id = 753;
	$help = createEmail( '', 'Web based help request', $_POST['problem'], $id, 0, 14, 1, 0, 0, $db );
	$iid = $help->getID();
	mail( "igroups@iit.edu", "iGROUPS Help Request [ID:$iid]", $_POST["problem"], "From:".$_POST['email'] );
	$db->igroupsQuery( "UPDATE Emails SET sSubject='iGROUPS Help Request [ID:$iid]' WHERE iID=$iid" );
}

if ( isset( $_SESSION['iUserID'] ) ) {
	$query = "SELECT sEmail FROM People WHERE iID=".$_SESSION['iUserID'];
	$result = query_db( $query, $iknowDB );
	if ( $row = mysql_fetch_row( $result ) ) {
		$email = $row[0];
	}
}
?>
<h1>Need help?</h1>
<?php
if ( isset( $_POST['help'] ) ) {
	print("Your request for help has been sent and we will respond to it as soon as possible.<br>");
}
?>
If you are having trouble logging in, try <a href="http://igroups.iit.edu/forgotpassword.php" target="mainFrame">resetting your password</a>.
<br><br>If this fails to correct your problem, complete the form below including your login e-mail address and your IPRO number.<p>
<form method="POST" action="needhelp.php">
<?php
	if ( isset( $email ) ) {
		print("<input type='hidden' name='email' value='$email'><p>");
	}
	else {
		print("E-mail address: <input type='text' name='email'><p>");
	}
?>
Please describe the problem you are having in as much detail as possible:<br>
<textarea name="problem" rows="10" cols="50"></textarea><p>
<input type="submit" name="help" value="Report Problem">
</form>
</body>
</html>

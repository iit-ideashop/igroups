<html>
<head>
<title>iGROUPS - Forgot Password</title>
<link href="default.css" rel="stylesheet" type="text/css">
</head>
<body>
<h1>Password Reset Form</h1>
<?php
require_once("classes/db.php");
require_once("classes/person.php");

if ( isset( $_POST['resetPW'] ) ) {
	$db = new dbConnection();
	$ur = $db->iknowQuery( "SELECT iID FROM People WHERE sEmail='".$_POST['email']."'" );
	if ( $row = mysql_fetch_row( $ur ) ) {
		for ( $i=0; $i<8; $i++ ) {
			$pw .= chr( rand( 65, 90 ) );
		}
		$user = new Person( $row[0], $db );
		$user->setPassword( $pw );
		$user->updateDB();
		mail( $_POST['email'], "Your iGROUPS/iKNOW Password has been reset.", "Your password is:\n$pw\nPasswords are case-sensitive.\nYou should change your password the next time you login.", "From: igroups@iit.edu" );
		print "<p>Your password has been reset.  An email has been sent to you containing your new password.</p>";
	}
	else {
		print("<span class='errorText'>Invalid email address</span><p>");
	}
}
?>
<form action="forgotpassword.php" method="POST">
Enter email address: <input type="text" name="email">
<input type="submit" name="resetPW" value="Reset Password">
</form>
</body>
</html>
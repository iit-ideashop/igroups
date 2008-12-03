<?php
	include_once("globals.php");
	if(isset($_POST['resetPW']))
	{
		setcookie('username', '', time()-60);
		setcookie('password', '', time()-60);
	}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<!-- This web-based application is Copyrighted &copy; 2008 Interprofessional Projects Program, Illinois Institute of Technology -->
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en"><head>
<title><?php echo $appname; ?> - Forgotten Password</title>
<?php
require("appearance.php");
echo "<link rel=\"stylesheet\" href=\"skins/$skin/default.css\" type=\"text/css\" title=\"$skin\" />\n";
foreach($altskins as $altskin)
	echo "<link rel=\"alternate stylesheet\" href=\"skins/$altskin/default.css\" type=\"text/css\" title=\"$altskin\" />\n";
?>
</head>
<body>
<?php
require("sidebar.php");
?>
<div id="content"><h1>Password Reset Form</h1>
<?php
require_once("classes/db.php");
require_once("classes/person.php");

if ( isset( $_POST['resetPW'] ) ) {
	$db = new dbConnection();
	$ur = $db->igroupsQuery( "SELECT iID FROM People WHERE sEmail='".$_POST['email']."'" );
	if ( $row = mysql_fetch_row( $ur ) ) {
		for ( $i=0; $i<8; $i++ ) {
			$pw .= chr( rand( 65, 90 ) );
		}
		$user = new Person( $row[0], $db );
		$user->setPassword( $pw );
		$user->updateDB();
		mail( $_POST['email'], "Your $appname password has been reset", "Your password is:\n$pw\nPasswords are case-sensitive.\nYou should change your password (in My Profile) the next time you log in.", "From: $contactemail" );
		print "<p>Your password has been reset. An email has been sent to you containing your new password.</p>";
	}
	else {
		print("<p class=\"errorText\">Invalid email address</p>");
	}
}
?>
<p>To reset your password, simply enter your email address in the form below. An email will be sent to you containing a new password.</p>
<form action="forgotpassword.php" method="post"><fieldset>
<label for="email">Enter email address:</label><input type="text" name="email" id="email" />
<input type="submit" name="resetPW" value="Reset Password" />
</fieldset></form></div></body></html>
